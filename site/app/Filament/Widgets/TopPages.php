<?php

namespace App\Filament\Widgets;

use App\Models\Visit;

class TopPages extends RankingWidget
{
    protected static ?int $sort = 7;

    protected function heading(): string
    {
        return __('Top pages');
    }

    protected function description(): string
    {
        return __('Most viewed pages');
    }

    protected function valueLabel(): string
    {
        return __('Views');
    }

    protected function rows(): iterable
    {
        return Visit::since($this->periodStart())
            ->selectRaw('path, count(*) as total')
            ->groupBy('path')
            ->orderByDesc('total')
            ->limit($this->limit)
            ->get()
            ->map(fn (Visit $visit) => ['label' => $visit->path, 'value' => (int) $visit->total]);
    }
}
