<?php

namespace App\Filament\Resources\Langs\Pages;

use App\Filament\Resources\Langs\LangResource;
use Filament\Resources\Pages\ManageRecords;

class ManageLangs extends ManageRecords
{
    protected static string $resource = LangResource::class;

    /** No "New language" button: the site is built for EN, RO and RU (LangResource::canCreate() is off too). */
    protected function getHeaderActions(): array
    {
        return [];
    }
}
