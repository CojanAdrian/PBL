<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Inboxes\InboxResource;
use App\Models\Inbox;
use Filament\Widgets\Widget;

class LatestMessages extends Widget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = ['md' => 2, 'xl' => 8];

    protected string $view = 'filament.widgets.latest-messages';

    protected function getViewData(): array
    {
        return [
            'messages' => Inbox::latest()->limit(6)->get(),
            'inboxUrl' => InboxResource::getUrl(),
        ];
    }
}
