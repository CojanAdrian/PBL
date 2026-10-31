<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Filament\Support\Concerns\EditsTranslatableRecord;
use App\Models\Product;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    use EditsTranslatableRecord;

    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['type'] ?? null) === Product::BOX) {
            $data['meat'] = null;
        } else {
            $data['grams'] = null;
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->modalDescription('Orders keep their items, but the product disappears from the website. To hide it for a while, switch "On the website" off instead.'),
        ];
    }
}
