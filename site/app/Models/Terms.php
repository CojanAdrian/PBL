<?php

namespace App\Models;

use App\Models\Concerns\HasOrderColumn;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Terms extends Model
{
    use HasFactory, HasOrderColumn, HasTranslations;

    protected $table = 'terms';
    protected $fillable = [
        'active',
        'order_column',
    ];

    protected $cast = [
        'active' => 'boolean'
    ];

    public function text()
    {
        return $this->hasMany(TermsTrans::class, 'term_id', 'id');
    }

}
