<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductTrans extends Model
{
    protected $table = 'product_trans';

    protected $fillable = [
        'name',
        'tagline',
        'notes',
        'lang',
        'product_id',
    ];
}
