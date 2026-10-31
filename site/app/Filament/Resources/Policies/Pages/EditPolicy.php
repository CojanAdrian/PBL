<?php

namespace App\Filament\Resources\Policies\Pages;

use App\Filament\Resources\Policies\PolicyResource;
use App\Filament\Support\Concerns\EditsTranslatableRecord;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPolicy extends EditRecord
{
    use EditsTranslatableRecord;

    protected static string $resource = PolicyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
