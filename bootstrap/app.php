<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckRole;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use App\Helpers\ApiResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        // 401 - Unauthenticated
        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error('Unauthenticated', null, 401);
            }
        });

        // 403 - Unauthorized
        $exceptions->render(function (AuthorizationException $e, $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error('Unauthorized', null, 403);
            }
        });

        // 422 - Validation
        $exceptions->render(function (ValidationException $e, $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error(
                    'Validation failed',
                    $e->errors(),
                    422
                );
            }
        });

    })
    ->create();