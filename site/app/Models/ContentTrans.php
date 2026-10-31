<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentTrans extends Model
{
    use HasFactory;

    protected $table = 'content_trans';
    protected $fillable = [
        'text',
        'link',
        'lang',
        'content_id'
    ];
}
