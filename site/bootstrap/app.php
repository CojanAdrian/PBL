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

        // Railway sits behind a chain of proxies (its edge plus a CDN with servers in
        // Bucharest, Zurich, …) that ends HTTPS. Trust the whole chain, so links use
        // https and the visitor's address is the real one: the first entry of
        // X-Forwarded-For, which Railway's edge sets itself (visitors can't fake it).
        // With '*' Laravel would only trust the last hop and see a CDN server instead,
        // which put visitors in the wrong city and shared the order limit between them.
        $middleware->trustProxies(at: ['0.0.0.0/0', '::/0']);

        // Counts page views on every public page for the dashboard (skips the admin).
        $middleware->appendToGroup('web', \App\Http\Middleware\TrackVisit::class);

        // Click-to-edit on public pages opened in the admin's Visual editor.
        $middleware->appendToGroup('web', \App\Http\Middleware\InjectVisualEditor::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
