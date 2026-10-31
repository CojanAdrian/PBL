<?php

namespace App\Filament\Resources\Meats\Pages;

use App\Filament\Resources\Meats\MeatResource;
use App\Filament\Support\Concerns\CreatesTranslatableRecord;
use Filament\Resources\Pages\CreateRecord;

class CreateMeat extends CreateRecord
{
    use CreatesTranslatableRecord;

    protected static string $resource = MeatResource::class;
}
