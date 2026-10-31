<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\Image;
use App\Models\PageMeta;
use App\Models\Service;
use App\Models\Terms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class TermsController extends Controller
{


    const ACTIVE = 1;

    public function index($locale)
    {
        $img = Image::getImageByPageId(1);
        $content = Content::getContentByPage($locale, 1); //PAGE ID FROM DATABASE
        $meta = PageMeta::getMetaForPage($locale, 8); //PAGE ID FROM DATABASE
        $services = Service::getForFrontAllServices($locale);

        $terms = Terms::select('terms.id', 'terms.active', 'terms_trans.title', 'terms_trans.text', 'terms.created_at')
            ->leftJoin('terms_trans', function ($join) use ($locale) {
                $join->on('terms_trans.term_id', 'terms.id')
                    ->where('terms_trans.lang', $locale);
            })
            ->orderBy('terms.order_column')
            ->where('terms.active', self::ACTIVE)
            ->get();

        return View::make('front.pages.terms', [
            'terms' => $terms,
            'content' => $content,
            'images' => $img,
            'services' => $services,
            'meta'=>$meta
        ]);
    }


}
