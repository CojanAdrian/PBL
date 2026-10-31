<?php

namespace App\Models;

use App\Models\Concerns\HasOrderColumn;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Policy extends Model
{
    use HasFactory, HasOrderColumn, HasTranslations;

    protected $table = 'policies';
    protected $fillable = [
        'active',
        'order_column',
    ];

    protected $cast = [
        'active' => 'boolean'
    ];

    public function text()
    {
        return $this->hasMany(PolicyTrans::class, 'policy_id', 'id');
    }

}
