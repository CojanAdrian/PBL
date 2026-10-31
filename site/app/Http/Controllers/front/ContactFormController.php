<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactFormRequest;
use App\Mail\ContactFormMail;
use App\Models\Content;
use App\Models\Image;
use App\Models\Inbox;
use App\Models\PageMeta;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;

class ContactFormController extends Controller
{


    public function index($locale)
    {
        $meta = PageMeta::getMetaForPage($locale, 5);
        $content = Content::getContentByPage($locale, 1);
        $img = Image::getImageByPageId(1);
        $services = Service::getForFrontAllServices($locale);

        return View::make('front.pages.contact', [
            'meta' => $meta,
            'content' => $content,
            'services' => $services,
            'images' => $img
        ]);
    }


    public function store(ContactFormRequest $request)
    {
        $data = $request->validated();

        Inbox::create($data);

        if ($adminEmail = config('app.admin_email')) {
            Mail::to($adminEmail)->send(new ContactFormMail($data));
        }

        return redirect()->route('front.contact.thank', app()->getLocale());
    }


    public function thankForm()
    {
        return View::make('front.components.thank-for-message-page');
    }

}
