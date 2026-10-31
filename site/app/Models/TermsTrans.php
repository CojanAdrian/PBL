<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TermsTrans extends Model
{
    use HasFactory;

    protected $table = 'terms_trans';
    protected $fillable = [
        'title',
        'text',
        'lang',
        'term_id'
    ];

}
