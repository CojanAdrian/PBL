<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use HasFactory, HasTranslations;

    const ACTIVE = 1;

    protected $table = 'testimonials';

    protected $fillable = [
        'active',
        'img',
    ];


    protected $casts = [
        'active' => 'boolean'
    ];

    public function text()
    {
        return $this->hasMany(TestimonialTrans::class, 'testimonial_id', 'id');
    }

    public static function getAllTestimonials($locale = 'en')
    {
        return Testimonial::select(
            'testimonials.id',
            'testimonials.active',
            'testimonials.created_at',
            'testimonials.img',
            'testimonial_trans.name',
            'testimonial_trans.function',
            'testimonial_trans.text',
            'testimonial_trans.slug',
        )
            ->leftJoin('testimonial_trans', function ($join) use ($locale) {
                $join->on('testimonial_trans.testimonial_id', 'testimonials.id')
                    ->where('testimonial_trans.lang', $locale);
            })
            ->latest('testimonials.created_at')
            ->get();
    }


    public static function getAllTestimonialsForFront($locale = 'en')
    {
        return Testimonial::select(
            'testimonials.id',
            'testimonials.active',
            'testimonials.created_at',
            'testimonials.img',
            'testimonial_trans.name',
            'testimonial_trans.function',
            'testimonial_trans.text',
            'testimonial_trans.slug',
        )
            ->leftJoin('testimonial_trans', function ($join) use ($locale) {
                $join->on('testimonial_trans.testimonial_id', 'testimonials.id')
                    ->where('testimonial_trans.lang', $locale);
            })
            ->where('testimonials.active', self::ACTIVE)
            ->latest('testimonials.created_at')
            ->get();
    }
}
