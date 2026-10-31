<?php

namespace App\Models;

use App\Models\Concerns\HasOrderColumn;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A meat type: beef, chicken, … with its name per language and pouch colours.
 */
class Meat extends Model
{
    use HasOrderColumn, HasTranslations;

    protected $fillable = ['key', 'pouch_top', 'pouch_bottom', 'pouch_cap', 'active', 'order_column'];

    protected $casts = ['active' => 'boolean'];

    public function text(): HasMany
    {
        return $this->hasMany(MeatTrans::class, 'meat_id', 'id');
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
