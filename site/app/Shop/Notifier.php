<?php

namespace App\Shop;

use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Tells Ed about new orders: an email (through Brevo or Resend's web API, since
 * Railway blocks SMTP), an optional Telegram message, and a row in the Google
 * Sheet. Each channel's last result is kept so the admin can show it.
 */
class Notifier
{
    /** Called after the order is saved; a failing channel never affects the others. */
    public function orderPlaced(Order $order): void
    {
        foreach (['email' => fn () => $this->emailOrder($order), 'telegram' => fn () => $this->telegram($order), 'sheet' => fn () => $this->sheetAdd([$order])] as $channel => $send) {
            try {
                $send();
            } catch (Throwable $e) {
                Log::error("Order {$order->code}: {$channel} failed: {$e->getMessage()}");
            }
        }
    }

    // ---------------- Email ----------------

    public function emailEnabled(): bool
    {
        $c = config('shop.notify');

        return filled($c['email_to']) && (filled($c['resend_key']) || (filled($c['brevo_key']) && filled($c['from'])));
    }

    public function emailProvider(): ?string
    {
        $c = config('shop.notify');

        return filled($c['resend_key']) ? 'Resend' : (filled($c['brevo_key']) ? 'Brevo' : null);
    }

    public function emailOrder(Order $order): void
    {
        if (! $this->emailEnabled()) {
            return;
        }
        $html = view('email.order', ['order' => $order, 'adminUrl' => url('/admin/orders')])->render();
        $text = implode("\n", array_filter([
            "New preorder {$order->code}",
            "Total: {$order->total_price} {$order->currency} ({$order->total_grams} g)",
            ...array_map(fn ($l) => "- {$l}", $order->lines()),
            "Customer: {$order->name}",
            "Phone: {$order->phone}",
            $order->email ? "Email: {$order->email}" : null,
            $order->wanted_date ? 'Wanted date: '.$order->wanted_date->format('Y-m-d') : null,
            $order->note ? "Note: {$order->note}" : null,
            'Open orders: '.url('/admin/orders'),
        ]));

        $this->sendEmail("New preorder {$order->code}: {$order->total_price} {$order->currency}", $text, $html, $order->email);
    }

    public function sendEmail(string $subject, string $text, string $html, ?string $replyTo = null): void
    {
        $c = config('shop.notify');
        $this->track('email', function () use ($c, $subject, $text, $html, $replyTo) {
            if (! $this->emailEnabled()) {
                throw new RuntimeException('Email is OFF. Set BREVO_API_KEY (or RESEND_API_KEY) and EMAIL_FROM on the server.');
            }
            if (filled($c['resend_key'])) {
                $response = Http::timeout(10)->withToken($c['resend_key'])->post('https://api.resend.com/emails', array_filter([
                    'from' => $c['from'] ?: "Big Ed's Jerky <onboarding@resend.dev>",
                    'to' => [$c['email_to']],
                    'subject' => $subject,
                    'text' => $text,
                    'html' => $html,
                    'reply_to' => $replyTo,
                ]));
                $label = 'Resend';
            } else {
                [$fromName, $fromEmail] = static::parseFrom($c['from']);
                $response = Http::timeout(10)->withHeaders(['api-key' => $c['brevo_key']])->post('https://api.brevo.com/v3/smtp/email', array_filter([
                    'sender' => ['email' => $fromEmail, 'name' => $fromName ?: "Big Ed's Jerky"],
                    'to' => [['email' => $c['email_to']]],
                    'subject' => $subject,
                    'textContent' => $text,
                    'htmlContent' => $html,
                    'replyTo' => $replyTo ? ['email' => $replyTo] : null,
                ]));
                $label = 'Brevo';
            }
            if ($response->failed()) {
                throw new RuntimeException("{$label} responded {$response->status()} ".mb_substr($response->body(), 0, 300));
            }
        });
    }

    /** "Big Ed's Jerky <orders@x.md>" -> ["Big Ed's Jerky", "orders@x.md"] */
    public static function parseFrom(?string $from): array
    {
        if (preg_match('/^\s*(.*?)\s*<([^>]+)>\s*$/', (string) $from, $m)) {
            return [trim($m[1], '" ') ?: null, trim($m[2])];
        }

        return [null, trim((string) $from)];
    }

    // ---------------- Telegram ----------------

    public function telegram(Order $order): void
    {
        $c = config('shop.notify');
        if (blank($c['telegram_token']) || blank($c['telegram_chat_id'])) {
            return;
        }
        $lines = [
            "New preorder {$order->code}",
            ...$order->lines(),
            "Total: {$order->total_price} {$order->currency} ({$order->total_grams} g)",
            implode(' - ', array_filter([$order->name, $order->phone, $order->email])),
            'Delivery',
        ];
        if ($order->wanted_date) {
            $lines[] = 'Wanted: '.$order->wanted_date->format('Y-m-d');
        }
        if ($order->note) {
            $lines[] = "Note: {$order->note}";
        }
        Http::timeout(5)->post("https://api.telegram.org/bot{$c['telegram_token']}/sendMessage", [
            'chat_id' => $c['telegram_chat_id'],
            'text' => implode("\n", $lines),
        ])->throw();
    }

    // ---------------- Google Sheet ----------------

    public function sheetEnabled(): bool
    {
        return filled(config('shop.notify.sheets_url'));
    }

    /** Adds the orders the sheet doesn't have yet (rows already there are left alone). */
    public function sheetAdd(iterable $orders): ?array
    {
        $rows = collect($orders)->map(fn (Order $o) => static::sheetRow($o))->values()->all();

        return $this->sheetPost(['orders' => $rows]);
    }

    public function sheetRemove(array $codes): ?array
    {
        return $this->sheetPost(['action' => 'delete', 'codes' => array_values($codes)]);
    }

    /** One ready-to-write row, so the sheet's script never needs to know about prices or flavours. */
    public static function sheetRow(Order $order): array
    {
        return [
            'code' => $order->code,
            'created' => $order->created_at?->toIso8601String(),
            'status' => 'New',
            'name' => $order->name,
            'phone' => $order->phone,
            'email' => (string) $order->email,
            'delivery' => 'Delivery',
            'address' => (string) $order->address,
            'date' => $order->wanted_date?->format('Y-m-d') ?? '',
            'items' => implode("\n", $order->lines()),
            'packs' => $order->packs(),
            'grams' => $order->total_grams,
            'total' => $order->total_price,
            'currency' => $order->currency,
            'note' => (string) $order->note,
        ];
    }

    protected function sheetPost(array $payload): ?array
    {
        if (! $this->sheetEnabled()) {
            return null;
        }

        return $this->track('sheet', function () use ($payload) {
            // Google answers a POST with a redirect to the result, which Http follows.
            $response = Http::timeout(20)->post(config('shop.notify.sheets_url'), ['token' => (string) config('shop.notify.sheets_token'), ...$payload]);
            $data = $response->json();
            if (! is_array($data) || empty($data['ok'])) {
                $reason = is_array($data) ? ($data['error'] ?? null) : null;
                $reason ??= str_contains(strtolower($response->body()), '<html')
                    ? "Google returned a web page instead of the script. Is the web app deployed with access for 'Anyone'?"
                    : mb_substr($response->body(), 0, 200);
                throw new RuntimeException('Google Sheets: '.($reason ?: "responded {$response->status()}"));
            }

            return $data;
        });
    }

    // ---------------- Status for the admin ----------------

    /** @return array{lastError: ?string, lastOkAt: ?string} */
    public function status(string $channel): array
    {
        return Cache::get("shop.notify.{$channel}", ['lastError' => null, 'lastOkAt' => null]);
    }

    protected function track(string $channel, callable $send): mixed
    {
        try {
            $result = $send();
            Cache::forever("shop.notify.{$channel}", ['lastError' => null, 'lastOkAt' => now()->toIso8601String()]);

            return $result;
        } catch (Throwable $e) {
            Cache::forever("shop.notify.{$channel}", [...$this->status($channel), 'lastError' => $e->getMessage()]);
            throw $e;
        }
    }
}
