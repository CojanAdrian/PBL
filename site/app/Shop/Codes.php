<?php

namespace App\Shop;

use App\Models\Meat;
use App\Models\PackType;
use Illuminate\Support\Str;

/**
 * Makes the codes the website and saved orders use for products, meat types and
 * pack types, from their names, so nobody has to type one. A code never changes
 * once it is made.
 */
class Codes
{
    /** The English name from the form's language tabs (or the first one filled in). */
    public static function nameFrom(array $data): string
    {
        $trans = $data['trans'] ?? [];

        return (string) ($trans['en']['name'] ?? collect($trans)->pluck('name')->filter()->first() ?? '');
    }

    /** "texas-bbq" from "Texas BBQ"; other alphabets are spelled out ("Говядина" -> "govyadina"). */
    public static function slug(string $text, string $fallback): string
    {
        return Str::slug($text) ?: $fallback;
    }

    /** "beef-texas-bbq", "beef-texas-bbq-sack", or just "taster-box" for a mix. */
    public static function forProduct(array $data): string
    {
        $type = PackType::find($data['pack_type_id'] ?? null);
        $name = static::slug(static::nameFrom($data), 'product');
        if (! $type || $type->mix) {
            return $name;
        }
        $meat = Meat::find($data['meat_id'] ?? null)?->key;
        // The plain pack is the usual one, so its code has no type: "beef-texas-bbq".
        $suffix = $type->key === 'pack' ? null : $type->key;

        return implode('-', array_filter([$meat, $name, $suffix]));
    }

    /** The code, or code-2, code-3, … when it is already taken. */
    public static function unique(string $model, string $code): string
    {
        $candidate = $code;
        for ($n = 2; $model::where('key', $candidate)->exists(); $n++) {
            $candidate = "{$code}-{$n}";
        }

        return $candidate;
    }
}
