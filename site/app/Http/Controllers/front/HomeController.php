<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\Image;
use App\Models\Page;
use App\Models\PageMeta;
use App\Models\Service;
use App\Models\Social;
use App\Models\Testimonial;
use Illuminate\Support\Facades\View;

class HomeController extends Controller
{

    public function index($locale)
    {

        $content = Content::getContentByPage($locale, Page::HOME);
        $img = Image::getImageByPageId(1);
        $socials = Social::getAllSocialsForFront($locale);
        $testimonials = Testimonial::getAllTestimonialsForFront($locale);
        $meta = PageMeta::getMetaForPage($locale, 1);
        $services = Service::getForFrontAllServices($locale);


        return View::make('front.pages.home', [
            'content' => $content,
            'images' => $img,
            'socials' => $socials,
            'testimonials' => $testimonials,
            'meta' => $meta,
            'services' => $services

        ]);

    }


}
