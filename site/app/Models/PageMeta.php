<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageMeta extends Model
{
    use HasFactory, HasTranslations;

    protected $table = 'page_metas';
    protected $fillable = [
        'img',
        'page_id'
    ];

    public function text()
    {
        return $this->hasMany(PageMetaTrans::class, 'page_meta_id', 'id');
    }

    public static function getMetaForPage($locale, $page)
    {
        return PageMeta::select('page_metas.img', 'page_meta_trans.title',
            'page_meta_trans.text as description', 'page_meta_trans.keywords')
            ->leftJoin('page_meta_trans', function ($join) use ($locale) {
                $join->on('page_meta_trans.page_meta_id', 'page_metas.id')
                    ->where('page_meta_trans.lang', $locale);
            })
            ->where('page_metas.page_id', $page)
            ->first();
    }

}
