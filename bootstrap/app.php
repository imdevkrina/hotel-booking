<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);

        // API routes are called from blade views using session auth,
        // so they need the web middleware for sessions & CSRF.
        $middleware->prependToGroup('api', \Illuminate\Session\Middleware\StartSession::class);
        $middleware->prependToGroup('api', \Illuminate\Cookie\Middleware\EncryptCookies::class);
        $middleware->prependToGroup('api', \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
