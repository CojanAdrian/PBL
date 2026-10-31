<?php

namespace App\Models;

use App\Models\Concerns\HasOrderColumn;
use App\Models\Concerns\HasTranslations;
use App\Support\Cms\Cms;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A pack of one flavour (beef or chicken) or a box with several flavours.
 */
class Product extends Model
{
    use HasOrderColumn, HasTranslations;

    public const PACK = 'pack';

    public const BOX = 'box';

    protected $fillable = [
        'key', 'type', 'meat', 'price', 'grams', 'heat',
        'accent', 'band', 'band_text', 'active', 'order_column',
    ];

    protected $casts = [
        'active' => 'boolean',
        'price' => 'integer',
        'grams' => 'integer',
        'heat' => 'integer',
    ];

    public function text(): HasMany
    {
        return $this->hasMany(ProductTrans::class, 'product_id', 'id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function isBox(): bool
    {
        return $this->type === self::BOX;
    }

    /** The meat's name in the current language ("Beef", "Vită", "Говядина"), from Text blocks. */
    public static function meatName(?string $meat): string
    {
        if (! $meat) {
            return '';
        }

        return app(Cms::class)->plain("meat_{$meat}", config("shop.meats.{$meat}.name", ucfirst($meat)));
    }

    /**
     * "Texas BBQ Beef"; a box keeps its own name. With `en`, always English
     * (that is how orders are saved), whatever the visitor's language.
     */
    public function fullName(?string $locale = null): string
    {
        $name = $this->translation($locale)?->name ?? $this->key;
        if ($this->isBox()) {
            return $name;
        }
        $meat = $locale === 'en'
            ? config("shop.meats.{$this->meat}.name", ucfirst((string) $this->meat))
            : static::meatName($this->meat);

        return trim("{$name} {$meat}");
    }
}
