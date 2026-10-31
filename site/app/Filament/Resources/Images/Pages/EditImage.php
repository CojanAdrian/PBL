<?php

namespace App\Filament\Resources\Images\Pages;

use App\Filament\Support\Concerns\CancelsToList;
use App\Filament\Resources\Images\ImageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditImage extends EditRecord
{
    use CancelsToList;

    protected static string $resource = ImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
