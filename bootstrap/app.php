<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {

                if ($request->bearerToken() && !auth()->check()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Token tidak valid atau bukan milik Anda.'
                    ], Response::HTTP_UNAUTHORIZED);
                }

                if (!$request->bearerToken()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda harus login untuk mengakses resource ini.'
                    ], Response::HTTP_UNAUTHORIZED);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Silakan login terlebih dahulu.'
                ], Response::HTTP_UNAUTHORIZED);
            }

            return $e->render($request);
        });

        $exceptions->render(function (UnauthorizedException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengguna tidak memiliki peran yang tepat.'
                ], Response::HTTP_FORBIDDEN);
            }
            return $e->render($request);
        });
    })->create();
