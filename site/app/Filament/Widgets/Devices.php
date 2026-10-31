<?php

namespace App\Filament\Widgets;

use App\Models\Visit;
use Filament\Support\Icons\Heroicon;

class Devices extends RankingWidget
{
    protected static ?int $sort = 9;

    protected function heading(): string
    {
        return __('Devices');
    }

    protected function description(): string
    {
        return __('What people browse with');
    }

    protected function rows(): iterable
    {
        $visits = Visit::since($this->periodStart())
            ->selectRaw('device, browser, count(distinct visitor) as total')
            ->groupBy('device', 'browser')
            ->get();

        return $visits->groupBy('device')
            ->map(fn ($rows, string $device) => [
                'label' => match ($device) {
                    'mobile' => __('Mobile'),
                    'tablet' => __('Tablet'),
                    default => __('Desktop'),
                },
                'sub' => __('mostly :browser', ['browser' => $rows->sortByDesc('total')->first()->browser]),
                'icon' => match ($device) {
                    'mobile' => Heroicon::OutlinedDevicePhoneMobile,
                    'tablet' => Heroicon::OutlinedDeviceTablet,
                    default => Heroicon::OutlinedComputerDesktop,
                },
                'value' => (int) $rows->sum('total'),
            ])
            ->sortByDesc('value')
            ->values();
    }
}
