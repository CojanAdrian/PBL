<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    const ACTIVE = 1;

    protected $table = 'images';
    protected $fillable = [
        'key',
        'img',
        'page_id'
    ];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public static function getImageByPageId($pageId)
    {
        return Image::select('images.key', 'images.img', 'images.id')
            ->where('images.page_id', $pageId)
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->key => $item];
            });
    }
}
