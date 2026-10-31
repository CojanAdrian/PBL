<?php

namespace App\Filament\Resources\Ingredients\Pages;

use App\Filament\Resources\Ingredients\IngredientResource;
use App\Filament\Support\Concerns\EditsTranslatableRecord;
use Filament\Resources\Pages\EditRecord;

class EditIngredient extends EditRecord
{
    use EditsTranslatableRecord;

    protected static string $resource = IngredientResource::class;
}
