<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackTypeTrans extends Model
{
    protected $table = 'pack_type_trans';

    protected $fillable = [
        'name',
        'lang',
        'pack_type_id',
    ];
}
