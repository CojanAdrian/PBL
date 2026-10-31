<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    const ACTIVE = 1;
    const INACTIVE = 0;


    //Pages
    const HOME = 1;

    const SERVICES = 2;
    const PORTFOLIOS = 3;
    const NEWS = 4;
    const CONTACT = 5;
    const PRIVACY = 6;
    const FAQ = 7;

    const TERMS = 8;


    protected $table = 'pages';
    protected $fillable = [
        'active',
        'label'
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    public function contents()
    {
        return $this->hasMany(Content::class);
    }

    public function meta()
    {
        return $this->hasOne(PageMeta::class, 'page_id', 'id');
    }

    public static function getActivePages()
    {
        return Page::select('id', 'label')
            ->where('active', Page::ACTIVE)
            ->get();
    }
}
