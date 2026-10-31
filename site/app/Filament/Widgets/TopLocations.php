<?php

namespace App\Filament\Widgets;

use App\Models\Visit;

class TopLocations extends RankingWidget
{
    protected static ?int $sort = 4;

    protected int $limit = 7;

    protected function heading(): string
    {
        return __('Locations');
    }

    protected function description(): string
    {
        return __('Where your visitors are');
    }

    protected function rows(): iterable
    {
        return Visit::since($this->periodStart())
            ->whereNotNull('country_code')
            ->selectRaw('country_code, country, city, count(distinct visitor) as total')
            ->groupBy('country_code', 'country', 'city')
            ->orderByDesc('total')
            ->limit($this->limit)
            ->get()
            ->map(fn (Visit $visit) => [
                'label' => $visit->city ?: $visit->country,
                'sub' => $visit->city ? $visit->country : null,
                'flag' => $visit->country_code,
                'value' => (int) $visit->total,
            ]);
    }
}
