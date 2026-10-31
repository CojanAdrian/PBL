<?php

namespace App\Filament\Resources\Socials\Pages;

use App\Filament\Resources\Socials\SocialResource;
use App\Filament\Support\Concerns\CreatesTranslatableRecord;
use Filament\Resources\Pages\CreateRecord;

class CreateSocial extends CreateRecord
{
    use CreatesTranslatableRecord;

    protected static string $resource = SocialResource::class;
}
