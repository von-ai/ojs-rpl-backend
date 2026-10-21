<?php

use App\Exceptions\ApiException;
use App\Http\Middleware\ApiRequestId;
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
        $middleware->api(prepend: [
            ApiRequestId::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontFlash(['password', 'password_confirmation', 'token']);

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (Throwable $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            $status = match (true) {
                $exception instanceof ApiException => $exception->status,
                $exception instanceof ValidationException => 422,
                $exception instanceof HttpExceptionInterface => $exception->getStatusCode(),
                default => 500,
            };

            $requestId = $request->attributes->get('request_id') ?? (string) Str::ulid();

            $code = match (true) {
                $exception instanceof ApiException => $exception->errorCode,
                $status === 422 => 'VALIDATION_ERROR',
                $status === 401 => 'UNAUTHENTICATED',
                $status === 423 => 'ACCOUNT_LOCKED',
                $status === 429 => 'RATE_LIMITED',
                $status === 404 => 'NOT_FOUND',
                default => 'INTERNAL_SERVER_ERROR',
            };

            $errors = [];
            if ($exception instanceof ApiException) {
                $errors = $exception->getErrors();
            } elseif ($exception instanceof ValidationException) {
                foreach ($exception->errors() as $field => $messages) {
                    foreach ($messages as $message) {
                        $errors[] = [
                            'field' => $field,
                            'code' => 'INVALID_FIELD',
                            'message' => $message,
                        ];
                    }
                }
            }

            $detail = match (true) {
                $exception instanceof ApiException => $exception->getMessage(),
                $exception instanceof ValidationException => 'Data masukan tidak valid.',
                default => $exception->getMessage() ?: (Response::$statusTexts[$status] ?? 'An error occurred'),
            };

            $headers = $exception instanceof ApiException
                ? $exception->getHeaders()
                : ($exception instanceof HttpExceptionInterface ? $exception->getHeaders() : []);

            return response()->json([
                'type' => 'about:blank',
                'title' => Response::$statusTexts[$status] ?? 'Error',
                'status' => $status,
                'code' => $code,
                'detail' => $detail,
                'instance' => $request->getPathInfo(),
                'request_id' => $requestId,
                'errors' => $errors,
            ], $status, $headers)
                ->header('Content-Type', 'application/problem+json')
                ->header('X-Request-ID', $requestId)
                ->header('Cache-Control', 'no-store');
        });
    })->create();
