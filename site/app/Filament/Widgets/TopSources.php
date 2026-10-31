<?php

namespace App\Filament\Widgets;

use App\Models\Visit;
use Filament\Support\Icons\Heroicon;

class TopSources extends RankingWidget
{
    protected static ?int $sort = 8;

    protected function heading(): string
    {
        return __('Sources');
    }

    protected function description(): string
    {
        return __('How visitors found you');
    }

    protected function rows(): iterable
    {
        return Visit::since($this->periodStart())
            ->selectRaw('referrer, count(distinct visitor) as total')
            ->groupBy('referrer')
            ->orderByDesc('total')
            ->limit($this->limit)
            ->get()
            ->map(fn (Visit $visit) => [
                'label' => $visit->referrer ?? __('Direct / bookmark'),
                'icon' => $visit->referrer ? Heroicon::OutlinedGlobeAlt : Heroicon::OutlinedCursorArrowRays,
                'value' => (int) $visit->total,
            ]);
    }
}
