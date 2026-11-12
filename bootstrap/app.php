<?php

// @feature api
use App\Api\Exceptions\ApiExceptionRenderer;
// @endfeature
// @feature ui:inertia
use App\Web\Middleware\HandleInertiaRequests;
// @endfeature
// @feature ui
use App\Web\Exceptions\WebExceptionRenderer;
// @endfeature
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        // @feature api
        api: __DIR__.'/../routes/api.php',
        // @endfeature
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Middlewares globaux / de groupe (web, api).
        // @feature ui:inertia
        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);
        // @endfeature
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // @feature api
        ApiExceptionRenderer::register($exceptions);
        // @endfeature
        // @feature ui
        WebExceptionRenderer::register($exceptions);
        // @endfeature
    })->create();
