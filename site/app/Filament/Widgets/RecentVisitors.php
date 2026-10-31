<?php

namespace App\Filament\Widgets;

use App\Models\Visit;
use Filament\Widgets\Widget;

class RecentVisitors extends Widget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = ['md' => 2, 'xl' => 4];

    protected string $view = 'filament.widgets.recent-visitors';

    protected function getViewData(): array
    {
        return ['visits' => Visit::latest('created_at')->limit(7)->get()];
    }
}
