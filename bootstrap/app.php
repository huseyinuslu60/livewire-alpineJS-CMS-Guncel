<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'permission' => \App\Http\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \App\Http\Middleware\RoleOrPermissionMiddleware::class,
            'module.active' => \App\Http\Middleware\ModuleActiveMiddleware::class,
            'log.http' => \App\Http\Middleware\LogHttpRequests::class,
            'check.permission' => \App\Http\Middleware\CheckPermission::class,
        ]);

        // HTTP request logging middleware
        $middleware->web(append: [
            \App\Http\Middleware\LogHttpRequests::class,
            \App\Http\Middleware\SecurityHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // AuthorizationException'ı yakala (Gate::authorize() hataları)
        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, $request) {
            $message = $e->getMessage();

            // İngilizce mesajları Türkçeleştir
            if (str_contains($message, 'User does not have the right permissions')) {
                $message = 'Bu işlem için yetkiniz bulunmuyor.';
            } elseif (str_contains($message, 'User does not have the right roles')) {
                $message = 'Bu işlem için gerekli role sahip değilsiniz.';
            } elseif (empty($message) || $message === 'This action is unauthorized.') {
                $message = 'Bu işlem için yetkiniz bulunmuyor.';
            }

            // Eğer AJAX isteği ise JSON döndür
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'error' => 'Forbidden',
                ], 403);
            }

            // Web isteği ise view döndür
            return response()->view('errors.403', [
                'message' => $message,
            ], 403);
        });

        // 403 HttpException'ları yakala
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            if ($e->getStatusCode() === 403) {
                $message = $e->getMessage();

                // Spatie Permission hatalarını Türkçeleştir
                if (str_contains($message, 'User does not have the right permissions')) {
                    $message = 'Bu işlem için yetkiniz bulunmuyor.';
                } elseif (str_contains($message, 'User does not have the right roles')) {
                    $message = 'Bu işlem için gerekli role sahip değilsiniz.';
                } elseif (empty($message) || $message === 'This action is unauthorized.') {
                    $message = 'Bu işlem için yetkiniz bulunmuyor.';
                }

                // Eğer AJAX isteği ise JSON döndür
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => $message,
                        'error' => 'Forbidden',
                    ], 403);
                }

                // Web isteği ise view döndür
                return response()->view('errors.403', [
                    'message' => $message,
                ], 403);
            }
        });
    })->create();
