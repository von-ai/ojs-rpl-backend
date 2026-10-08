<?php

use App\Exceptions\IdentityException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
        $exceptions->dontFlash(['password', 'password_confirmation', 'token']);
        $exceptions->render(function (Throwable $exception, Request $request) {
            if (! $request->is('api/v1/auth/register', 'api/v1/auth/email/verify', 'api/v1/auth/email/resend')) {
                return null;
            }
            $status = match (true) {
                $exception instanceof IdentityException => $exception->status,
                $exception instanceof ValidationException => 422,
                $exception instanceof HttpExceptionInterface => $exception->getStatusCode(),
                default => 500,
            };
            $id = $request->attributes->get('request_id') ?? (string) Str::uuid();
            $errors = [];
            if ($exception instanceof ValidationException) {
                foreach ($exception->errors() as $field => $messages) {
                    foreach ($messages as $message) {
                        $errors[] = ['field' => $field, 'code' => 'INVALID_FIELD', 'message' => $message];
                    }
                }
            }
            $headers = $exception instanceof HttpExceptionInterface ? $exception->getHeaders() : [];

            return response()->json([
                'type' => 'about:blank', 'title' => Response::$statusTexts[$status] ?? 'Error',
                'status' => $status,
                'code' => $exception instanceof IdentityException ? $exception->errorCode : match ($status) {
                    422 => 'VALIDATION_FAILED', 429 => 'RATE_LIMITED', default => 'REQUEST_FAILED',
                },
                'detail' => $exception instanceof IdentityException ? $exception->getMessage() : 'Permintaan tidak dapat diproses.',
                'instance' => $request->getPathInfo(), 'request_id' => $id, 'errors' => $errors,
            ], $status, $headers)->header('Content-Type', 'application/problem+json')
                ->header('X-Request-ID', $id)->header('Cache-Control', 'no-store');
        });
    })->create();
