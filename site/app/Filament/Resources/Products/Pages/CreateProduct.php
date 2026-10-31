<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Filament\Support\Concerns\CreatesTranslatableRecord;
use App\Models\PackType;
use App\Models\Product;
use App\Shop\Codes;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    use CreatesTranslatableRecord;

    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // A mix (like a box) has no meat or heat of its own.
        if (PackType::find($data['pack_type_id'] ?? null)?->mix) {
            $data['meat_id'] = null;
            $data['heat'] = 1;
        }
        $data['key'] = Codes::unique(Product::class, Codes::forProduct($data));

        return $data;
    }
}
