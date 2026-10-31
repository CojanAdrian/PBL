<?php

namespace App\Filament\Resources\PackTypes\Pages;

use App\Filament\Resources\PackTypes\PackTypeResource;
use App\Filament\Support\Concerns\EditsTranslatableRecord;
use Filament\Resources\Pages\EditRecord;

class EditPackType extends EditRecord
{
    use EditsTranslatableRecord;

    protected static string $resource = PackTypeResource::class;
}
