<?php

namespace App\Filament\Resources\Faqs\Pages;

use App\Filament\Resources\Faqs\FaqResource;
use App\Filament\Support\Concerns\CreatesTranslatableRecord;
use Filament\Resources\Pages\CreateRecord;

class CreateFaq extends CreateRecord
{
    use CreatesTranslatableRecord;

    protected static string $resource = FaqResource::class;
}
