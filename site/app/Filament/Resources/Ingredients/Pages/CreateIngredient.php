<?php

namespace App\Filament\Resources\Ingredients\Pages;

use App\Filament\Resources\Ingredients\IngredientResource;
use App\Filament\Support\Concerns\CreatesTranslatableRecord;
use Filament\Resources\Pages\CreateRecord;

class CreateIngredient extends CreateRecord
{
    use CreatesTranslatableRecord;

    protected static string $resource = IngredientResource::class;
}
