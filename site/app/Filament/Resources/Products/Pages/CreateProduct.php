<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Filament\Support\Concerns\CreatesTranslatableRecord;
use App\Models\Product;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    use CreatesTranslatableRecord;

    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // A box has no meat or heat; a pack has no box weight.
        if (($data['type'] ?? null) === Product::BOX) {
            $data['meat'] = null;
            $data['heat'] = 1;
        } else {
            $data['grams'] = null;
        }

        return $data;
    }
}
