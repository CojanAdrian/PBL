<?php

namespace App\Filament\Resources\Testimonials\Pages;

use App\Filament\Resources\Testimonials\TestimonialResource;
use App\Filament\Support\Concerns\CreatesTranslatableRecord;
use Filament\Resources\Pages\CreateRecord;

class CreateTestimonial extends CreateRecord
{
    use CreatesTranslatableRecord;

    protected static string $resource = TestimonialResource::class;
}
