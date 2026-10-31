<?php

namespace App\Filament\Resources\Meats\Pages;

use App\Filament\Resources\Meats\MeatResource;
use App\Filament\Support\Concerns\CreatesTranslatableRecord;
use App\Models\Meat;
use App\Shop\Codes;
use Filament\Resources\Pages\CreateRecord;

class CreateMeat extends CreateRecord
{
    use CreatesTranslatableRecord;

    protected static string $resource = MeatResource::class;

    /** The code is made from the English name: "Pork" -> pork. */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['key'] = Codes::unique(Meat::class, Codes::slug(Codes::nameFrom($data), 'meat'));

        return $data;
    }
}
