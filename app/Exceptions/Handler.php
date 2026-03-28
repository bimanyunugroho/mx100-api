<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->renderable(function (Throwable $e, $request) {
            if (! $request->expectsJson()) {
                return null;
            }

            return match (true) {

                $e instanceof ApiException => response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    ...($e->getErrors() ? ['errors' => $e->getErrors()] : []),
                ], $e->getStatusCode()),

                $e instanceof ValidationException => response()->json([
                    'success' => false,
                    'message' => 'Data yang dikirim tidak valid.',
                    'errors'  => $e->errors(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY),

                $e instanceof AuthenticationException => response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated. Silakan login terlebih dahulu.',
                ], Response::HTTP_UNAUTHORIZED),

                $e instanceof ModelNotFoundException => response()->json([
                    'success' => false,
                    'message' => 'Resource tidak ditemukan.',
                ], Response::HTTP_NOT_FOUND),

                $e instanceof HttpException => response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: (Response::$statusTexts[$e->getStatusCode()] ?? 'HTTP Error'),
                ], $e->getStatusCode()),

                default => (function () use ($e) {
                    report($e);

                    return response()->json([
                        'success' => false,
                        'message' => app()->isProduction()
                            ? 'Terjadi kesalahan pada server. Silakan coba beberapa saat lagi.'
                            : $e->getMessage(),
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                })(),
            };
        });
    }
}
