<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'setLocale' => \App\Http\Middleware\SetLocale::class,
        ]);

        // Railway (and most hosts) sit behind a proxy that ends HTTPS; trust it so
        // links, assets and cookies use https.
        $middleware->trustProxies(at: '*');

        // Counts page views on every public page for the dashboard (skips the admin).
        $middleware->appendToGroup('web', \App\Http\Middleware\TrackVisit::class);

        // Click-to-edit on public pages opened in the admin's Visual editor.
        $middleware->appendToGroup('web', \App\Http\Middleware\InjectVisualEditor::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
