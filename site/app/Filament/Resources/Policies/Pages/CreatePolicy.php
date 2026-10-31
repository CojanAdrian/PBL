<?php

namespace App\Filament\Resources\Policies\Pages;

use App\Filament\Resources\Policies\PolicyResource;
use App\Filament\Support\Concerns\CreatesTranslatableRecord;
use Filament\Resources\Pages\CreateRecord;

class CreatePolicy extends CreateRecord
{
    use CreatesTranslatableRecord;

    protected static string $resource = PolicyResource::class;
}
