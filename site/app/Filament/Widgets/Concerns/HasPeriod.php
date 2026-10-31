<?php

namespace App\Filament\Widgets\Concerns;

use App\Models\Visit;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Reads the dashboard's period switch (7 / 30 / 90 days).
 */
trait HasPeriod
{
    use InteractsWithPageFilters;

    protected function days(): int
    {
        return in_array($days = (int) ($this->pageFilters['period'] ?? 30), [7, 30, 90]) ? $days : 30;
    }

    protected function periodStart(): Carbon
    {
        return now()->subDays($this->days() - 1)->startOfDay();
    }

    protected function previousPeriodStart(): Carbon
    {
        return $this->periodStart()->subDays($this->days());
    }

    /** @return Collection<int, Carbon> Every day of the period, oldest first. */
    protected function dates(): Collection
    {
        return collect(range($this->days() - 1, 0))->map(fn (int $daysAgo) => now()->subDays($daysAgo)->startOfDay());
    }

    /**
     * Count in the current period, and the percent change against the period before
     * (null when there is too little to compare).
     *
     * @param  Closure(Builder): int|null  $count
     */
    protected function countWithChange(Builder $query, ?\Closure $count = null): array
    {
        $count ??= fn (Builder $query) => $query->count();

        $current = $count((clone $query)->where('created_at', '>=', $this->periodStart()));
        $previous = $count((clone $query)->whereBetween('created_at', [$this->previousPeriodStart(), $this->periodStart()]));

        return [$current, $previous >= 5 ? (int) round(($current - $previous) / $previous * 100) : null];
    }

    /** @return array<int, int> Records created per day over the period. */
    protected function perDay(Builder $query): array
    {
        $counts = $query->where('created_at', '>=', $this->periodStart())
            ->pluck('created_at')
            ->countBy(fn (Carbon $date) => $date->toDateString());

        return $this->dates()->map(fn (Carbon $date) => $counts[$date->toDateString()] ?? 0)->all();
    }

    /** @return array{views: array<int, int>, visitors: array<int, int>} Page views and unique visitors per day. */
    protected function trafficPerDay(): array
    {
        $rows = Visit::since($this->periodStart())
            ->selectRaw('date(created_at) as day, count(*) as views, count(distinct visitor) as visitors')
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        return [
            'views' => $this->dates()->map(fn (Carbon $date) => (int) ($rows[$date->toDateString()]->views ?? 0))->all(),
            'visitors' => $this->dates()->map(fn (Carbon $date) => (int) ($rows[$date->toDateString()]->visitors ?? 0))->all(),
        ];
    }
}
