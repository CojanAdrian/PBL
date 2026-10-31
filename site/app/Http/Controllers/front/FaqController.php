<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\Faq;
use App\Models\Image;
use App\Models\PageMeta;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class FaqController extends Controller
{

    const ACTIVE = 1;

    public function index($locale)
    {
        $img = Image::getImageByPageId(1);
        $content = Content::getContentByPage($locale, 1);
        $meta = PageMeta::getMetaForPage($locale, 7);
        $services = Service::getForFrontAllServices($locale);

        $faqs = Faq::select('faqs.id', 'faqs.active', 'faq_trans.title', 'faq_trans.text', 'faqs.created_at')
            ->leftJoin('faq_trans', function ($join) use ($locale) {
                $join->on('faq_trans.faq_id', 'faqs.id')
                    ->where('faq_trans.lang', $locale);
            })
            ->orderBy('faqs.order_column')
            ->where('faqs.active', self::ACTIVE)
            ->get();

        return View::make('front.pages.faq', [
            'faqs' => $faqs,
            'content' => $content,
            'images' => $img,
            'services' => $services,
            'meta'=>$meta
        ]);
    }


}
