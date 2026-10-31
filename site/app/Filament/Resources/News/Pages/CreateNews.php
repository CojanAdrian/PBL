<?php

namespace App\Filament\Resources\News\Pages;

use App\Filament\Resources\News\NewsResource;
use App\Filament\Support\Concerns\CreatesTranslatableRecord;
use Filament\Resources\Pages\CreateRecord;

class CreateNews extends CreateRecord
{
    use CreatesTranslatableRecord;

    protected static string $resource = NewsResource::class;
}
