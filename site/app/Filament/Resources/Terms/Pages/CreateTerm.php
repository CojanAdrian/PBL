<?php

namespace App\Filament\Resources\Terms\Pages;

use App\Filament\Resources\Terms\TermResource;
use App\Filament\Support\Concerns\CreatesTranslatableRecord;
use Filament\Resources\Pages\CreateRecord;

class CreateTerm extends CreateRecord
{
    use CreatesTranslatableRecord;

    protected static string $resource = TermResource::class;
}
