<?php

use App\Http\Controllers\front\ContactFormController;
use App\Http\Controllers\front\FaqController;
use App\Http\Controllers\front\FrontNewsController;
use App\Http\Controllers\front\HomeController;
use App\Http\Controllers\front\PrivacyController;
use App\Http\Controllers\front\ServiceFrontController;
use App\Http\Controllers\front\TermsController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\VisualEditorController;
use Illuminate\Support\Facades\Route;

// The admin panel (/admin) is registered by App\Providers\Filament\AdminPanelProvider.

Route::controller(InvitationController::class)->group(function () {
    Route::get('/invite/{token}', 'show')->name('invitation');
    Route::post('/invite/{token}', 'accept')->name('invitation.accept');
});

// Saves from the admin's Visual editor (admins only, checked in the controller).
Route::controller(VisualEditorController::class)->prefix('cms')->name('cms.')->group(function () {
    Route::post('/text', 'text')->name('text');
    Route::post('/image', 'image')->name('image');
});

Route::redirect('/', '/'.config('app.locale'));

Route::prefix('{locale}')
    ->where(['locale' => '[a-zA-Z]{2,10}'])
    ->middleware('setLocale')
    ->group(function () {
        Route::get('/', [HomeController::class, 'index'])->name('front.index');
        Route::get('/faq', [FaqController::class, 'index'])->name('front.faq');
        Route::get('/privacy-policy', [PrivacyController::class, 'index'])->name('front.privacy');
        Route::get('/terms', [TermsController::class, 'index'])->name('front.terms');

        Route::get('/services', [ServiceFrontController::class, 'index'])->name('front.service');
        Route::get('/single-services/{slug}', [ServiceFrontController::class, 'show'])->name('front.single.service');

        Route::get('/news', [FrontNewsController::class, 'index'])->name('front.news');
        Route::get('/single-news/{slug}', [FrontNewsController::class, 'show'])->name('front.single_news');

        Route::get('/contact', [ContactFormController::class, 'index'])->name('front.contact');
        Route::post('/storage-contact', [ContactFormController::class, 'store'])
            ->middleware('throttle:5,1')
            ->name('front.contact.storage');
        Route::get('/thank-form', [ContactFormController::class, 'thankForm'])->name('front.contact.thank');
    });
