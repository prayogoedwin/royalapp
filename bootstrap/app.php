<?php

use App\Http\Middleware\ParseMultipartPut;
use App\Http\Middleware\PermissionMiddleware;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
            ParseMultipartPut::class,
        ]);
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (ValidationException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            $firstError = collect($e->errors())->flatten()->first() ?? 'Validation error.';

            return response()->json([
                'status' => false,
                'message' => $firstError,
                'data' => [],
            ], 422);
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated.',
                'data' => [],
            ], 401);
        });

        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'status' => false,
                'message' => 'Data not found.',
                'data' => [],
            ], 404);
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            if ($e instanceof HttpExceptionInterface) {
                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage() ?: 'HTTP error.',
                    'data' => [],
                ], $e->getStatusCode());
            }

            return response()->json([
                'status' => false,
                'message' => app()->hasDebugModeEnabled()
                    ? $e->getMessage()
                    : 'Internal server error.',
                'data' => [],
            ], 500);
        });
    })->create();
