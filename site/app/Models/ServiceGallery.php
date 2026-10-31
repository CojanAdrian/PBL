<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceGallery extends Model
{
    protected $table = 'service_galleries';
    protected $fillable = [
        'img',
        'service_id',
        'order_column'
    ];
}
