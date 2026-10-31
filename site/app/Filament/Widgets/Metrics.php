<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Widgets\Concerns\HasPeriod;
use App\Models\Order;
use App\Models\Visit;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;

class Metrics extends Widget
{
    use HasPeriod;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = ['md' => 2, 'xl' => 8];

    protected string $view = 'filament.widgets.metrics';

    protected function getViewData(): array
    {
        $traffic = $this->trafficPerDay();
        [$visitors, $visitorsChange] = $this->countWithChange(Visit::query(), fn (Builder $query) => $query->distinct()->count('visitor'));
        [$views, $viewsChange] = $this->countWithChange(Visit::query());
        [$orders, $ordersChange] = $this->countWithChange(Order::query());
        [$value, $valueChange] = $this->countWithChange(Order::query(), fn (Builder $query) => (int) $query->sum('total_price'));
        $valuePerDay = Order::where('created_at', '>=', $this->periodStart())->get(['created_at', 'total_price'])
            ->groupBy(fn (Order $order) => $order->created_at->toDateString())
            ->map->sum('total_price');
        $currency = config('shop.currency');

        return ['tiles' => [
            [
                'label' => __('Visitors'), 'value' => $visitors, 'change' => $visitorsChange,
                'caption' => __('Unique people'),
                'bars' => $traffic['visitors'], 'tint' => 'blue', 'url' => null,
            ],
            [
                'label' => __('Page views'), 'value' => $views, 'change' => $viewsChange,
                'caption' => $visitors ? __(':count per visitor', ['count' => round($views / $visitors, 1)]) : __('No visits yet'),
                'bars' => $traffic['views'], 'tint' => 'sage', 'url' => null,
            ],
            [
                'label' => __('Orders'), 'value' => $orders, 'change' => $ordersChange,
                'caption' => $visitors ? __(':rate% of visitors ordered', ['rate' => round($orders / $visitors * 100, 1)]) : __('Preorders from the website'),
                'bars' => $this->perDay(Order::query()), 'tint' => 'amber', 'url' => OrderResource::getUrl(),
            ],
            [
                'label' => __('Order value')." ({$currency})", 'value' => $value, 'change' => $valueChange,
                'caption' => $orders ? __(':avg :currency per order', ['avg' => number_format($value / $orders), 'currency' => $currency]) : __('No orders yet'),
                'bars' => $this->dates()->map(fn ($date) => (int) ($valuePerDay[$date->toDateString()] ?? 0))->all(),
                'tint' => 'rose', 'url' => OrderResource::getUrl(),
            ],
        ]];
    }
}
