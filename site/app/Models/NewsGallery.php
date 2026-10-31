<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsGallery extends Model
{
    use HasFactory;


    protected $table = 'news_galleries';

    protected $fillable = [
        'img',
        'news_id',
        'order_column'
    ];

}
