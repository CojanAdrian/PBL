<?php

namespace App\Filament\Resources\Meats\Pages;

use App\Filament\Resources\Meats\MeatResource;
use App\Filament\Support\Concerns\EditsTranslatableRecord;
use Filament\Resources\Pages\EditRecord;

class EditMeat extends EditRecord
{
    use EditsTranslatableRecord;

    protected static string $resource = MeatResource::class;
}
