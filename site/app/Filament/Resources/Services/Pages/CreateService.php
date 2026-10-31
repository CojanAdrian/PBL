<?php

namespace App\Filament\Resources\Services\Pages;

use App\Filament\Resources\Services\ServiceResource;
use App\Filament\Support\Concerns\CreatesTranslatableRecord;
use Filament\Resources\Pages\CreateRecord;

class CreateService extends CreateRecord
{
    use CreatesTranslatableRecord;

    protected static string $resource = ServiceResource::class;
}
