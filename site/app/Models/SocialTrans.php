<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialTrans extends Model
{
    use HasFactory;

    protected $table = 'social_trans';

    protected $fillable = [
        'name',
        'social_id',
        'lang',
    ];

}
