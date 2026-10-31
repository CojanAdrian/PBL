<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

/**
 * One page view on the public site. See config/analytics.php.
 */
class Visit extends Model
{
    use Prunable;

    public const UPDATED_AT = null;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function prunable(): Builder
    {
        return static::where('created_at', '<', now()->subMonths(config('analytics.keep_months')));
    }

    public function scopeSince(Builder $query, \DateTimeInterface $date): void
    {
        $query->where('created_at', '>=', $date);
    }

    /** "Chișinău, Moldova", or just the country, or null. */
    public function getLocationAttribute(): ?string
    {
        return collect([$this->city, $this->country])->filter()->join(', ') ?: null;
    }
}
