<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Support\Icons\Heroicon;

class TopFlavours extends RankingWidget
{
    protected static ?int $sort = 6;

    protected function heading(): string
    {
        return __('Flavours');
    }

    protected function description(): string
    {
        return __('Packs ordered in this period');
    }

    protected function valueLabel(): string
    {
        return __('Packs');
    }

    protected function rows(): iterable
    {
        return Order::where('created_at', '>=', $this->periodStart())->get(['items'])
            ->flatMap(fn (Order $order) => $order->items ?? [])
            ->groupBy('name')
            ->map(fn ($items, $name) => [
                'label' => $name,
                'icon' => Heroicon::OutlinedFire,
                'value' => (int) $items->sum('qty'),
            ])
            ->sortByDesc('value')
            ->take($this->limit)
            ->values();
    }
}
