<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsMeta extends Model
{
    use HasFactory, HasTranslations;

    protected $table = 'news_metas';

    protected $fillable = [
        'img',
        'news_id'
    ];


    public function text()
    {
        return $this->hasMany(NewsMetaTrans::class, 'news_meta_id', 'id');
    }

    public static function getNewsMeta($locale = 'en', $newsId)
    {
        return NewsMeta::select('news_metas.img', 'news_meta_trans.title')
            ->leftJoin('news_meta_trans', function ($join) use ($locale) {
                $join->on('news_metas.id', 'news_meta_trans.news_meta_id')
                    ->where('news_meta_trans.lang', $locale);
            })
            ->where('news_metas.news_id', $newsId)
            ->first();
    }

}
