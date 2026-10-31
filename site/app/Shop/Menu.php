<?php

namespace App\Shop;

use App\Models\Meat;
use App\Models\Product;
use App\Support\Cms\Cms;
use Illuminate\Support\Collection;

/**
 * The menu the order builder runs on: active products in the current language,
 * their meat and pack types, shop settings, and the builder's own texts.
 */
class Menu
{
    /** @return Collection<int, Product> */
    public static function products(): Collection
    {
        return Product::active()->with(['text', 'packType.text', 'meat.text'])->orderBy('order_column')->get();
    }

    /** Price of ONE item. A custom weight (when switched on) is priced from the product's own weight. */
    public static function linePrice(Product $product, int $grams): int
    {
        if ($product->isMix() || $grams === $product->grams) {
            return $product->price;
        }

        return (int) round($product->price / $product->grams * $grams);
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
            'grams' => $p->grams,
            'type' => $p->packType->key,
            'typeName' => $p->packType->name(),
            'style' => $p->packType->style,
            'accent' => $p->accent,
            'band' => $p->band,
            'bandText' => $p->band_text,
        ];

        // Meats in their admin order, only those with something on sale.
        $meatIds = $products->pluck('meat_id')->filter()->unique();
        $meats = Meat::with('text')->whereIn('id', $meatIds)->orderBy('order_column')->get()
            ->map(fn (Meat $m) => [
                'id' => $m->key,
                'name' => $m->name(),
                'pouch' => ['top' => $m->pouch_top, 'bottom' => $m->pouch_bottom, 'cap' => $m->pouch_cap],
            ])->values();

        return [
            'locale' => $locale,
            // How the currency is written in this language (orders are stored in config('shop.currency')).
            'currency' => $texts['currency'],
            'custom' => config('shop.custom'),
            'limits' => [
                'maxPacksPerLine' => config('shop.limits.max_packs_per_line'),
                'maxLines' => config('shop.limits.max_lines'),
                'maxTotalGrams' => config('shop.limits.max_total_grams'),
            ],
            'paymentNote' => $texts['payment_note'],
            // The logo printed on the pouch drawings (Images -> site_logo).
            'logo' => $cms->imageUrl('site_logo', 'img/logo.webp'),
            // In the admin's Visual editor the builder's texts become click-to-edit.
            'editing' => $cms->editing(),
            'meats' => $meats,
            'flavours' => $products->reject->isMix()->map(fn (Product $p) => [...$item($p), 'meat' => $p->meat?->key, 'heat' => $p->heat])->values(),
            'boxes' => $products->filter->isMix()->map($item)->values(),
            'endpoint' => route('front.preorder', ['locale' => $locale]),
            't' => $texts,
        ];
    }
}
