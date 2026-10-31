<?php

namespace App\Filament\Resources\Langs\Pages;

use App\Filament\Resources\Langs\LangResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageLangs extends ManageRecords
{
    protected static string $resource = LangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
