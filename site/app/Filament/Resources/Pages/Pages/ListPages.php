<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use Filament\Resources\Pages\ListRecords;

class ListPages extends ListRecords
{
    protected static string $resource = PageResource::class;

    /** No "New page" button: Big Ed's is one page (PageResource::canCreate() is off too). */
    protected function getHeaderActions(): array
    {
        return [];
    }
}
