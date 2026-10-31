<?php

namespace App\Models;


use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsTrans extends Model
{
    use HasFactory, Sluggable;

    protected $table = 'news_trans';

    protected $fillable = [
        'title',
        'sub_title',
        'excerpt',
        'text',

        'news_id',
        'slug',
        'lang'
    ];


    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title',
                'onUpdate' => true
            ]
        ];
    }

}
