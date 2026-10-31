<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestimonialTrans extends Model
{
    use HasFactory, Sluggable;

    protected $table = 'testimonial_trans';

    protected $fillable = [
        'name',
        'function',
        'text',

        'testimonial_id',
        'slug',
        'lang',
    ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
                'onUpdate' => true
            ]
        ];
    }
}
