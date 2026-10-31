<?php

namespace App\Filament\Resources\Socials\Pages;

use App\Filament\Resources\Socials\SocialResource;
use App\Filament\Support\Concerns\EditsTranslatableRecord;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSocial extends EditRecord
{
    use EditsTranslatableRecord;

    protected static string $resource = SocialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
