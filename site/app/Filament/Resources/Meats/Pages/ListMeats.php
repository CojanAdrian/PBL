<?php

namespace App\Filament\Resources\Meats\Pages;

use App\Filament\Resources\Meats\MeatResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMeats extends ListRecords
{
    protected static string $resource = MeatResource::class;

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
