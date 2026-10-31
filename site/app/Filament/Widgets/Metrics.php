<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Inboxes\InboxResource;
use App\Filament\Widgets\Concerns\HasPeriod;
use App\Models\Inbox;
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
        [$messages, $messagesChange] = $this->countWithChange(Inbox::query());

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
                'label' => __('Messages'), 'value' => $messages, 'change' => $messagesChange,
                'caption' => $visitors ? __(':rate% conversion', ['rate' => round($messages / $visitors * 100, 1)]) : __('From the contact form'),
                'bars' => $this->perDay(Inbox::query()), 'tint' => 'amber', 'url' => InboxResource::getUrl(),
            ],
            [
                'label' => __('Unread'), 'value' => Inbox::unread()->count(), 'change' => null,
                'caption' => __('Need a reply'),
                'bars' => $this->perDay(Inbox::unread()), 'tint' => 'rose', 'url' => InboxResource::getUrl(),
            ],
        ]];
    }
}
