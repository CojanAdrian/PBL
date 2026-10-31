<?php

namespace App\Filament\Resources\PackTypes\Pages;

use App\Filament\Resources\PackTypes\PackTypeResource;
use App\Filament\Support\Concerns\CreatesTranslatableRecord;
use Filament\Resources\Pages\CreateRecord;

class CreatePackType extends CreateRecord
{
    use CreatesTranslatableRecord;

    protected static string $resource = PackTypeResource::class;
}
