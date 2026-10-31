<?php

use App\Models\Lang;
use App\Models\Order;
use App\Models\ProductCategory;
use App\Models\Status;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

function storage($disk, $file)
{
    return Storage::disk($disk)->url($file);
}

function removeFile($disk, $file)
{
    return Storage::disk($disk)->delete($file);
}

function storeFile($disk, $file)
{
    return Storage::disk($disk)->put('/', $file);
}

function getMenuPages()
{
    $pages = \App\Models\Page::select('pages.label', 'page_meta_trans.title')
        ->leftJoin('page_metas', 'page_metas.page_id', 'pages.id')
        ->leftJoin('page_meta_trans', function ($join) {
            $join->on('page_meta_trans.page_meta_id', 'page_metas.id')
                ->where('page_meta_trans.lang', app()->getLocale());
        })
        ->where('pages.active', \App\Models\Page::ACTIVE)
        ->get();
    return $pages;
}


//STORAGE HELPER FOR DISKS

function helperStoragePath($disk, $file)
{
    return Storage::disk($disk)->path($file);
}


//front

function getLangs()
{
    $langs = Lang::select('code', 'name')->where([
        ['active', 1],
        ['code', 'not like', app()->getLocale()]
    ])->get();
    return $langs;
}

//THIS FUNCTION IS FOR FRONT IT HELP YOU TO HAVE UNIVERSAL TRANSLATE KEY WORDS ON WHOLE WEBSITE

function getTrans($key)
{
    $text = \App\Models\Content::select('content_trans.text')
        ->leftJoin('content_trans', 'content_trans.content_id', 'contents.id')
        ->where([
            ['content_trans.lang', app()->getLocale()],
            ['contents.key', $key]
        ])
        ->first();
    return $text->text ?? $key;
}

//front - THIS ONE HELP YOU TO MAKE MENU

function currentLangName()
{
    $lang = Lang::select('name')
        ->where('code', app()->getLocale())
        ->first();

    return $lang ? $lang->name : 'Lang';
}

function getTextId($key)
{
    return \App\Models\Content::select('contents.id')
        ->where('contents.key', $key)
        ->first();
}

function getImageId($key)
{
    return \App\Models\Image::select('images.id')
        ->where('images.key', $key)
        ->first();
}

function getImgId($key)
{
    $img = \App\Models\Image::select('images.img')
        ->where('images.key', $key)
        ->first();
    return $img->img ?? $key;

}




