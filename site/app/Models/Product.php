<?php

namespace App\Models;

use App\Models\Concerns\HasOrderColumn;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Something on the menu: one flavour of one meat in a pack type (e.g. Texas BBQ
 * Beef, 100 g pack), or a mix type (e.g. the taster box) with several flavours.
 */
class Product extends Model
{
    use HasOrderColumn, HasTranslations;

    protected $fillable = [
        'key', 'pack_type_id', 'meat_id', 'price', 'grams', 'heat',
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

    public function packType(): BelongsTo
    {
        return $this->belongsTo(PackType::class);
    }

    public function meat(): BelongsTo
    {
        return $this->belongsTo(Meat::class);
    }

    /** On the website: switched on, with its pack type (and meat, if any) switched on too. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true)
            ->whereHas('packType', fn (Builder $q) => $q->where('active', true))
            ->where(fn (Builder $q) => $q->whereNull('meat_id')->orWhereHas('meat', fn (Builder $m) => $m->where('active', true)));
    }

    /** Several flavours in one (a box), rather than one flavour of one meat. */
    public function isMix(): bool
    {
        return (bool) $this->packType?->mix;
    }

    /** "Texas BBQ Beef"; a mix keeps its own name. With `en` it is always English (how orders are saved). */
    public function fullName(?string $locale = null): string
    {
        $name = $this->translation($locale)?->name ?? $this->key;
        if ($this->isMix() || ! $this->meat) {
            return $name;
        }

        return trim($name.' '.$this->meat->name($locale ?? app()->getLocale()));
    }
}
