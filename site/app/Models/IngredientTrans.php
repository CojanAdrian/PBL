<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IngredientTrans extends Model
{
    protected $table = 'ingredient_trans';

    protected $fillable = [
        'name',
        'lang',
        'ingredient_id',
    ];
}
