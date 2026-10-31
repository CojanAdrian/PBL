<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsMetaTrans extends Model
{
    use HasFactory;

    protected $table = 'news_meta_trans';

    protected $fillable = [
        'title',
        'text',
        'keywords',
        'lang',
        'news_meta_id'
    ];

}
