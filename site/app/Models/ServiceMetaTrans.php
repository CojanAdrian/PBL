<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceMetaTrans extends Model
{
    use HasFactory;

    protected $table = 'service_meta_trans';
    protected $fillable = [
        'title',
        'text',
        'keywords',
        'lang',
        'service_meta_id'
    ];
}
