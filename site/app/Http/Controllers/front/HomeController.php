<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageMeta;
use App\Shop\BetaTexts;
use App\Shop\Menu;
use Illuminate\Support\Facades\View;

class HomeController extends Controller
{
    /** The jerky site: one page with the flavours and the order builder. */
    public function index($locale)
    {
        return View::make('front.pages.home', [
            'meta' => PageMeta::getMetaForPage($locale, Page::where('label', 'front.index')->value('id')),
            'menu' => Menu::build(),
        ]);
    }

    /** The redesigned page, tested at /{locale}/betatest before it replaces the main one. */
    public function beta($locale)
    {
        return View::make('front.pages.betatest', [
            'menu' => Menu::build(),
            'b' => BetaTexts::forLocale(app()->getLocale()),
        ]);
    }
}
