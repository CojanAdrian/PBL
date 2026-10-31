<?php

namespace App\Filament\Resources\PackTypes\Pages;

use App\Filament\Resources\PackTypes\PackTypeResource;
use App\Filament\Support\Concerns\CreatesTranslatableRecord;
use App\Models\PackType;
use App\Shop\Codes;
use Filament\Resources\Pages\CreateRecord;

class CreatePackType extends CreateRecord
{
    use CreatesTranslatableRecord;

    protected static string $resource = PackTypeResource::class;

    /** The code is made from the English name: "Sack" -> sack. */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['key'] = Codes::unique(PackType::class, Codes::slug(Codes::nameFrom($data), 'pack'));

        return $data;
    }
}
