<?php

namespace App\Models;

use App\Models\Concerns\HasOrderColumn;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One item of "What goes in", in the story section.
 */
class Ingredient extends Model
{
    use HasOrderColumn, HasTranslations;

    protected $fillable = ['active', 'order_column'];

    protected $casts = ['active' => 'boolean'];

    public function text(): HasMany
    {
        return $this->hasMany(IngredientTrans::class, 'ingredient_id', 'id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }
}
