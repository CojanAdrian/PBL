<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Shop\Notifier;
use App\Shop\PreorderValidator;
use App\Shop\Texts;
use App\Support\Cms\Cms;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

use function Illuminate\Support\defer;

class PreorderController extends Controller
{
    // No 0/O/1/I, so a code read over the phone can't be mistaken.
    private const CODE_ALPHABET = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';

    public function store(Request $request, Notifier $notifier): JsonResponse
    {
        $limitKey = 'preorder:'.$request->ip();
        // Only saved orders count towards the limit; invalid forms never do.
        if (RateLimiter::tooManyAttempts($limitKey, config('shop.orders_per_hour'))) {
            return response()->json(['error' => $this->text('shop_err_too_many')], 429);
        }

        // Hidden field only bots fill in: pretend it worked, store nothing.
        if (filled($request->input('website'))) {
            return response()->json(['ok' => true, 'code' => 'BED-00000'], 201);
        }

        $result = PreorderValidator::validate($request->all());
        if (! $result['ok']) {
            return response()->json(['error' => $this->text('shop_fix_fields'), 'errors' => $result['errors']], 400);
        }

        $order = null;
        for ($attempt = 0; $attempt < 5 && ! $order; $attempt++) {
            try {
                $order = Order::create([...$result['order'], 'code' => $this->makeCode(), 'locale' => app()->getLocale()]);
            } catch (UniqueConstraintViolationException) {
                // Same code twice: try another one.
            }
        }
        abort_unless($order, 500, 'Could not generate a unique order code');

        RateLimiter::hit($limitKey, 3600);
        // Email, Telegram and the Google Sheet run after the customer has their answer.
        defer(fn () => $notifier->orderPlaced($order));

        return response()->json([
            'ok' => true,
            'code' => $order->code,
            'totalPrice' => $order->total_price,
            'totalGrams' => $order->total_grams,
            'currency' => $order->currency,
        ], 201);
    }

    private function makeCode(): string
    {
        $code = '';
        for ($i = 0; $i < 5; $i++) {
            $code .= self::CODE_ALPHABET[random_int(0, strlen(self::CODE_ALPHABET) - 1)];
        }

        return "BED-{$code}";
    }

    private function text(string $key): string
    {
        return app(Cms::class)->plain($key, Texts::en($key));
    }
}
