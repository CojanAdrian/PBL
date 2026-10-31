<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\News;
use App\Models\NewsTrans;
use App\Models\NewsMeta;
use App\Models\Image;
use App\Models\Page;
use App\Models\PageMeta;
use Illuminate\Support\Facades\Redirect;

class FrontNewsController extends Controller
{
    public function index($locale)
    {

        $meta = PageMeta::getMetaForPage($locale, Page::NEWS);
        $content = Content::getContentByPage($locale, Page::HOME);
        $image = Image::getImageByPageId(Page::HOME);
        $newses = News::getAllNewsForFront($locale);
        return view()->make('front.pages.news.news', [
            'meta' => $meta,
            'content' => $content,
            'locale' => $locale,
            'image' => $image,
            'newses' => $newses
        ]);
    }

    public function show($locale, $slug)
    {
        $newsId = NewsTrans::select('news_id', 'lang')
            ->where('slug', $slug)
            ->firstOrFail();

        if ($newsId->lang !== $locale) {
            $newsRedirect = NewsTrans::select('slug')
                ->where([
                    ['news_id', $newsId->news_id],
                    ['lang', $locale]
                ])
                ->first();
            abort_unless($newsRedirect, 404);

            return Redirect::route('front.single_news', [
                'locale' => $locale,
                'slug' => $newsRedirect->slug
            ]);
        }

        $news = News::getSingleNewsForFront($locale, $newsId->news_id);
        $newses = News::getAllNewsForFront($locale);
        $content = Content::getContentByPage($locale, Page::HOME);
        $image = Image::getImageByPageId(Page::HOME);
        $newsMeta = NewsMeta::getNewsMeta($locale, $newsId->news_id);

        return view()->make('front.pages.news.single_news', [
            'newsMeta' => $newsMeta,
            'content' => $content,
            'image' => $image,
            'news' => $news,
            'newses' => $newses
        ]);
    }
}
