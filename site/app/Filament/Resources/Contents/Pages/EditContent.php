<?php

namespace App\Filament\Resources\Contents\Pages;

use App\Filament\Resources\Contents\ContentResource;
use App\Filament\Support\Concerns\EditsTranslatableRecord;
use Filament\Resources\Pages\EditRecord;

class EditContent extends EditRecord
{
    use EditsTranslatableRecord;

    protected static string $resource = ContentResource::class;

    protected function getHeaderActions(): array
    {
        // No Delete button: keys can only be changed.
        return [];
    }
}
