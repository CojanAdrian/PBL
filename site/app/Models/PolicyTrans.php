<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PolicyTrans extends Model
{
    use HasFactory;

    protected $table = 'policy_trans';
    protected $fillable = [
        'title',
        'text',
        'lang',
        'policy_id'
    ];

}
