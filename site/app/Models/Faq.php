<?php

namespace App\Models;

use App\Models\Concerns\HasOrderColumn;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasFactory, HasOrderColumn, HasTranslations;

    //FAQ MODEL AND RELATION

    protected $table = 'faqs';
    protected $fillable = [
        'active',
        'order_column',
    ];

    protected $cast = [
        'active' => 'boolean'
    ];

    public function text()
    {
        return $this->hasMany(FaqTrans::class, 'faq_id', 'id');
    }
}
