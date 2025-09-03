<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Ensure API unauthenticated requests return 401 JSON (no redirect)
        $middleware->alias([
            'auth' => App\Http\Middleware\Authenticate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Ensure unauthenticated API requests do not attempt to redirect to a non-existent 'login' route
        $exceptions->renderable(function (AuthenticationException $e, Request $request) {
            // Treat any API route as JSON, including routes that return files like PDFs
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthenticated',
                ], 401);
            }
            return null; // Fall back to default for non-API routes
        });
    })->create();
