<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageMetaTrans extends Model
{
    use HasFactory;

    protected $table = 'page_meta_trans';
    protected $fillable = [
        'title',
        'text',
        'keywords',
        'lang',
        'page_meta_id'
    ];
}
