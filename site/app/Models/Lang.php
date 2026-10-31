<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lang extends Model
{
    use HasFactory;

    protected $table = "langs";
    protected $fillable = [
        'name',
        'code',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    public static function getAllLang()
    {
        return Lang::select('code', 'name')->get();
    }
}
