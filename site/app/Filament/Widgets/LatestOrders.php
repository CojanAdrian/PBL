<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Widgets\Widget;

class LatestOrders extends Widget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = ['md' => 2, 'xl' => 8];

    protected string $view = 'filament.widgets.latest-orders';

    protected function getViewData(): array
    {
        return [
            'orders' => Order::latest()->limit(6)->get(),
            'ordersUrl' => OrderResource::getUrl(),
        ];
    }
}
