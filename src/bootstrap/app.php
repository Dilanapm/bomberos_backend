<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            '2fa.required'          => \App\Http\Middleware\EnsureTwoFactorEnabledForPrivilegedRoles::class,
            'admin.passkey.required' => \App\Http\Middleware\EnsureAdminHasPasskey::class,
            'admin.only'            => \App\Http\Middleware\OnlyAdminCanUseWeb::class,
            'prevent.back'          => \App\Http\Middleware\PreventBackHistory::class,
            'verified'              => \App\Http\Middleware\EnsureEmailIsVerified::class,
            'mobile.access'         => \App\Http\Middleware\EnsureMobileRoleAccess::class,
            'api.client.key'        => \App\Http\Middleware\VerifyClientApiKey::class,
            // Spatie Permission Middleware aliases
            'role'                  => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'            => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission'    => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        // Middlewares globales para TODAS las rutas /api/*
        $middleware->api(append: [
            \App\Http\Middleware\VerifyClientApiKey::class,
            \App\Http\Middleware\SecureApiHeaders::class,
        ]);

        // Middlewares globales para rutas web
        $middleware->web(append: [
            \App\Http\Middleware\LogPasswordResetRequest::class,
        ]);
    })
    ->withProviders([
        App\Providers\EventServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        // Toda excepción en rutas /api/* se responde siempre con JSON
        $exceptions->shouldRenderJsonWhen(
            fn (\Illuminate\Http\Request $request) => $request->is('api/*')
        );

        // Excepción de validación → 422 con JSON estructurado
        $exceptions->render(function (
            \Illuminate\Validation\ValidationException $e,
            \Illuminate\Http\Request $request
        ) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Los datos enviados no son válidos.',
                    'code'    => 'VALIDATION_ERROR',
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        // Modelo no encontrado → 404 JSON (por si usamos findOrFail en el futuro)
        $exceptions->render(function (
            \Illuminate\Database\Eloquent\ModelNotFoundException $e,
            \Illuminate\Http\Request $request
        ) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Recurso no encontrado.',
                    'code'    => 'NOT_FOUND',
                ], 404);
            }
        });

        // Sin autenticación (token inválido/expirado) → 401 JSON
        $exceptions->render(function (
            \Illuminate\Auth\AuthenticationException $e,
            \Illuminate\Http\Request $request
        ) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'No autenticado. Token inválido o expirado.',
                    'code'    => 'UNAUTHENTICATED',
                ], 401);
            }
        });

        // Sin autorización → 403 JSON
        $exceptions->render(function (
            \Illuminate\Auth\Access\AuthorizationException $e,
            \Illuminate\Http\Request $request
        ) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'No tienes permiso para realizar esta acción.',
                    'code'    => 'FORBIDDEN',
                ], 403);
            }
        });
    })->create();
