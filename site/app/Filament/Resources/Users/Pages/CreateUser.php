<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Support\Concerns\CancelsToList;
use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    use CancelsToList;

    protected static string $resource = UserResource::class;
}
