<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeatTrans extends Model
{
    protected $table = 'meat_trans';

    protected $fillable = [
        'name',
        'lang',
        'meat_id',
    ];
}
