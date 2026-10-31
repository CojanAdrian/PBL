<?php

namespace App\Filament\Resources\PackTypes\Pages;

use App\Filament\Resources\PackTypes\PackTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPackTypes extends ListRecords
{
    protected static string $resource = PackTypeResource::class;

    public function getSubheading(): ?string
    {
        return 'Drag to change the order on the website.';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
