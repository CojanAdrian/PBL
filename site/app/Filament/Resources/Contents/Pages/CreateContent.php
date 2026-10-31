<?php

namespace App\Filament\Resources\Contents\Pages;

use App\Filament\Resources\Contents\ContentResource;
use App\Filament\Support\Concerns\CreatesTranslatableRecord;
use Filament\Resources\Pages\CreateRecord;

class CreateContent extends CreateRecord
{
    use CreatesTranslatableRecord;

    protected static string $resource = ContentResource::class;
}
