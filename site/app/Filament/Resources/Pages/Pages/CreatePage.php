<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use App\Filament\Support\Concerns\CreatesTranslatableRecord;
use Filament\Resources\Pages\CreateRecord;

class CreatePage extends CreateRecord
{
    use CreatesTranslatableRecord;

    protected static string $resource = PageResource::class;
}
