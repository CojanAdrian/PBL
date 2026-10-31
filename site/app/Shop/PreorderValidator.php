<?php

namespace App\Shop;

use App\Models\Product;
use App\Support\Cms\Cms;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Checks a preorder from the website and works out the real price on the
 * server, so nobody can send a made-up total. Error messages are in the
 * visitor's language (Text blocks).
 */
class PreorderValidator
{
    /**
     * @return array{ok: true, order: array}|array{ok: false, errors: array<string, string>}
     */
    public static function validate(array $input, ?Collection $products = null, ?CarbonImmutable $today = null): array
    {
        $msg = fn (string $key) => app(Cms::class)->plain($key, Texts::en($key));
        $products ??= Menu::products();
        $byKey = $products->keyBy('key');
        $errors = [];

        // ---- items ----
        $merged = [];
        foreach (is_array($input['items'] ?? null) ? $input['items'] : [] as $raw) {
            $key = is_array($raw) ? (string) ($raw['flavour'] ?? '') : '';
            $grams = is_array($raw) ? ($raw['grams'] ?? null) : null;
            $qty = is_array($raw) ? ($raw['qty'] ?? null) : null;
            $product = $byKey->get($key);
            if (! $product || ! is_int($grams) || ! static::allowedGrams($product, $grams) || ! is_int($qty) || $qty < 1) {
                $errors['items'] = $msg('shop_err_items');
                break;
            }
            $id = "{$key}:{$grams}";
            $merged[$id] = ['product' => $product, 'grams' => $grams, 'qty' => ($merged[$id]['qty'] ?? 0) + $qty];
        }
        $items = array_values($merged);
        $limits = config('shop.limits');
        if (! isset($errors['items'])) {
            if (! $items) {
                $errors['items'] = $msg('shop_err_items_empty');
            } elseif (count($items) > $limits['max_lines'] || collect($items)->contains(fn ($i) => $i['qty'] > $limits['max_packs_per_line'])) {
                $errors['items'] = $msg('shop_too_big');
            }
        }
        $totalGrams = array_sum(array_map(fn ($i) => $i['grams'] * $i['qty'], $items));
        if (! isset($errors['items']) && $totalGrams > $limits['max_total_grams']) {
            $errors['items'] = $msg('shop_too_big');
        }

        // ---- contact details: name and phone required, email optional ----
        $name = static::clean($input['name'] ?? '', 80);
        if (mb_strlen($name) < 2) {
            $errors['name'] = $msg('shop_err_name');
        }

        $phone = preg_replace('/[\s().-]/', '', (string) (is_scalar($input['phone'] ?? null) ? $input['phone'] : ''));
        if ($phone === '') {
            $errors['phone'] = $msg('shop_err_phone_missing');
        } elseif (! preg_match('/^\+?\d{7,15}$/', $phone)) {
            $errors['phone'] = $msg('shop_err_phone');
        }

        $email = mb_strtolower(static::clean($input['email'] ?? '', 254));
        if ($email !== '' && ! preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', $email)) {
            $errors['email'] = $msg('shop_err_email');
        }

        $date = null;
        if (! empty($input['date'])) {
            $text = (string) $input['date'];
            $today ??= CarbonImmutable::now('UTC')->startOfDay();
            $parsed = preg_match('/^\d{4}-\d{2}-\d{2}$/', $text) ? CarbonImmutable::createFromFormat('!Y-m-d', $text, 'UTC') : null;
            if (! $parsed || $parsed->format('Y-m-d') !== $text || $parsed->lt($today) || $parsed->gt($today->addDays(120))) {
                $errors['date'] = $msg('shop_err_date');
            } else {
                $date = $text;
            }
        }

        $note = mb_substr(trim(str_replace("\r", '', (string) (is_scalar($input['note'] ?? null) ? $input['note'] : ''))), 0, 500);

        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $lines = array_map(fn ($i) => [
            'flavour' => $i['product']->key,
            // Stored in English so the admin, emails and the sheet all read the same.
            'name' => $i['product']->fullName('en'),
            'grams' => $i['grams'],
            'qty' => $i['qty'],
            'price' => Menu::linePrice($i['product'], $i['grams']),
            // A mix (like the taster box) is listed without its weight: "1 x Taster box".
            'box' => $i['product']->isMix(),
        ], $items);

        return [
            'ok' => true,
            'order' => [
                'name' => $name,
                'phone' => $phone,
                'email' => $email ?: null,
                // Delivery only for now, and no address field.
                'delivery' => 'delivery',
                'address' => null,
                'wanted_date' => $date,
                'note' => $note ?: null,
                'items' => $lines,
                'total_grams' => $totalGrams,
                'total_price' => array_sum(array_map(fn ($l) => $l['price'] * $l['qty'], $lines)),
                'currency' => config('shop.currency'),
            ],
        ];
    }

    /** A product's own weight, or (when custom sizes are on) any allowed weight of a one-flavour product. */
    public static function allowedGrams(Product $product, int $grams): bool
    {
        if ($grams === $product->grams) {
            return true;
        }
        if ($product->isMix()) {
            return false;
        }
        $custom = config('shop.custom');

        return ($custom['enabled'] ?? false)
            && $grams >= $custom['min'] && $grams <= $custom['max'] && $grams % $custom['step'] === 0;
    }

    protected static function clean(mixed $value, int $max): string
    {
        $text = is_scalar($value) ? (string) $value : '';

        return mb_substr(trim(preg_replace('/\s+/u', ' ', $text)), 0, $max);
    }
}
