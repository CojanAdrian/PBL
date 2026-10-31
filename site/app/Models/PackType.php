<?php

namespace App\Models;

use App\Models\Concerns\HasOrderColumn;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * How jerky is sold: a pack, a box, a sack, … `mix` types (like the taster box)
 * hold several flavours, so they have no meat or heat of their own.
 */
class PackType extends Model
{
    use HasOrderColumn, HasTranslations;

    /** How each type is drawn on the website. */
    public const STYLES = ['pouch' => 'Pouch', 'box' => 'Box', 'sack' => 'Sack'];

    protected $fillable = ['key', 'style', 'mix', 'active', 'order_column'];

    protected $casts = ['mix' => 'boolean', 'active' => 'boolean'];

    public function text(): HasMany
    {
        return $this->hasMany(PackTypeTrans::class, 'pack_type_id', 'id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function name(?string $locale = null): string
    {
        return $this->translation($locale)?->name ?? ucfirst($this->key);
    }
}
