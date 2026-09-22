<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            'webhook/*',
            'api/webhook/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (HttpExceptionInterface $exception, Request $request): ?RedirectResponse {
            if ($exception->getStatusCode() !== 403 || $request->expectsJson() || $request->is('api/*', 'webhook/*') || ! $request->hasSession()) {
                return null;
            }

            return redirect()->route($request->user() ? 'dashboard' : 'login')
                ->with('access_error', 'You do not have permission to perform that action.');
        });
    })->create();
