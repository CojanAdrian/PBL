<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A preorder from the website. `items` is a list of
 * { flavour, name, grams, qty, price, box } as they were when the order was placed.
 */
class Order extends Model
{
    protected $fillable = [
        'code', 'name', 'phone', 'email', 'delivery', 'address', 'wanted_date',
        'note', 'items', 'total_grams', 'total_price', 'currency', 'locale',
    ];

    protected $casts = [
        'items' => 'array',
        'wanted_date' => 'date',
        'total_grams' => 'integer',
        'total_price' => 'integer',
    ];

    public function packs(): int
    {
        return (int) collect($this->items)->sum('qty');
    }

    /** "2 x 100 g Texas BBQ Beef", "1 x Taster box". */
    public static function describeItem(array $item): string
    {
        return ! empty($item['box'])
            ? "{$item['qty']} x {$item['name']}"
            : "{$item['qty']} x {$item['grams']} g {$item['name']}";
    }

    /** @return list<string> */
    public function lines(): array
    {
        return array_map(static::describeItem(...), $this->items ?? []);
    }
}
