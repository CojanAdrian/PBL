<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaqTrans extends Model
{
    use HasFactory;

    protected $table = 'faq_trans';
    protected $fillable = [
        'title',
        'text',
        'lang',
        'faq_id'
    ];
}
