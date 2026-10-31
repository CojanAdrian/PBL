<?php

namespace App\Shop;

use App\Models\Product;
use App\Support\Cms\Cms;
use Illuminate\Support\Collection;

/**
 * The menu the order builder runs on: active products in the current language,
 * shop settings, and the builder's own texts (from Text blocks).
 */
class Menu
{
    /** @return Collection<int, Product> */
    public static function products(): Collection
    {
        return Product::active()->with('text')->orderBy('order_column')->get();
    }

    /** Price of ONE pack or box. Custom pack sizes are priced from the popular size. */
    public static function linePrice(Product $product, int $grams): int
    {
        if ($product->isBox()) {
            return $product->price;
        }
        $popular = (int) config('shop.popular_size');

        return $grams === $popular ? $product->price : (int) round($product->price / $popular * $grams);
    }

    public static function build(): array
    {
        $cms = app(Cms::class);
        $products = static::products();
        $locale = app()->getLocale();

        $texts = [];
        foreach (Texts::ALL as $key => $t) {
            if (str_starts_with($key, 'shop_')) {
                $texts[substr($key, 5)] = $cms->plain($key, $t['en']);
            }
        }

        $item = fn (Product $p) => [
            'id' => $p->key,
            'name' => $p->translation()?->name ?? $p->key,
            'tagline' => (string) $p->translation()?->tagline,
            'notes' => (string) $p->translation()?->notes,
            'price' => $p->price,
            'accent' => $p->accent,
            'band' => $p->band,
            'bandText' => $p->band_text,
        ];

        $meats = collect(config('shop.meats'))
            ->filter(fn ($meat, $id) => $products->contains('meat', $id))
            ->map(fn ($meat, $id) => ['id' => $id, 'name' => Product::meatName($id), 'pouch' => $meat['pouch']])
            ->values();

        return [
            'locale' => $locale,
            // How the currency is written in this language (orders are stored in config('shop.currency')).
            'currency' => $texts['currency'],
            'sizes' => config('shop.sizes'),
            'popularSize' => config('shop.popular_size'),
            'custom' => config('shop.custom'),
            'limits' => [
                'maxPacksPerLine' => config('shop.limits.max_packs_per_line'),
                'maxLines' => config('shop.limits.max_lines'),
                'maxTotalGrams' => config('shop.limits.max_total_grams'),
            ],
            'paymentNote' => $texts['payment_note'],
            'meats' => $meats,
            'flavours' => $products->reject->isBox()->map(fn (Product $p) => [...$item($p), 'meat' => $p->meat, 'heat' => $p->heat])->values(),
            'boxes' => $products->filter->isBox()->map(fn (Product $p) => [...$item($p), 'grams' => $p->grams])->values(),
            'endpoint' => route('front.preorder', ['locale' => $locale]),
            't' => $texts,
        ];
    }
}
