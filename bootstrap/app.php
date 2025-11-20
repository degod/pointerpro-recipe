<?php

use App\Services\ResponseService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {
            $responseService = app(ResponseService::class);

            if (! $request->is('api/*')) {
                return null;
            }

            return match (true) {
                $e instanceof AuthenticationException =>
                $responseService->error(
                    Response::HTTP_UNAUTHORIZED,
                    'Unauthenticated. Please provide a valid token.'
                ),
                $e instanceof UnauthorizedHttpException =>
                $responseService->error(
                    Response::HTTP_UNAUTHORIZED,
                    'Unauthorized: Missing or invalid Bearer token.'
                ),
                $e instanceof ThrottleRequestsException =>
                $responseService->error(
                    Response::HTTP_TOO_MANY_REQUESTS,
                    'Too many requests. Please slow down.'
                ),
                default => null,
            };
        });
    })->create();
