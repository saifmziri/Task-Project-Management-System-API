<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckUserRole;
use App\Http\Middleware\CheckAccountOwnerOrAdmin;
use App\Exceptions\ApiExceptionRenderer;
use App\Http\Middleware\SecurityHeaders;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'CheckUser' =>CheckUserRole::class,
            'IsOwnerOrAdmin' => CheckAccountOwnerOrAdmin::class, 
        ]);
       // 🟢 جعله Global Middleware يشمل الـ 404 والـ API
        $middleware->append(SecurityHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, $request) {
        if ($request->expectsJson()) {
            return ApiExceptionRenderer::render($e);
        }
    });
    })->create();
