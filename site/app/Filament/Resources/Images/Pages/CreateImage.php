<?php

namespace App\Filament\Resources\Images\Pages;

use App\Filament\Support\Concerns\CancelsToList;
use App\Filament\Resources\Images\ImageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateImage extends CreateRecord
{
    use CancelsToList;

    protected static string $resource = ImageResource::class;
}
