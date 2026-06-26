<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/route.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth' => \App\Http\Middleware\CustomAuthenticate::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'root_admin' => \App\Http\Middleware\CheckRootAdmin::class,
            'track_activity' => \App\Http\Middleware\TrackUserActivity::class,
        ]);

        // Apply track_activity to web routes
        $middleware->web(append: [
            \App\Http\Middleware\TrackUserActivity::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
