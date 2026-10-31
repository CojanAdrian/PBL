<?php

use App\Http\Controllers\front\HomeController;
use App\Http\Controllers\front\PreorderController;
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

        // Big Ed's Jerky is one page. The starter's other pages (services, news, contact,
        // FAQ, terms, privacy) are switched off; their controllers and views are still here.
        Route::post('/preorder', [PreorderController::class, 'store'])
            ->middleware('throttle:60,1')
            ->name('front.preorder');
    });
