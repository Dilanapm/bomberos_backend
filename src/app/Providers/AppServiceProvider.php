<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Redirigir al login después de cerrar sesión
        $this->app->instance(LogoutResponseContract::class, new class implements LogoutResponseContract {
            public function toResponse($request)
            {
                return redirect('/login');
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ─────────────────────────────────────────────────────────────
        //  Rate limiting para el endpoint de login móvil
        //
        //  Límite: 5 intentos por IP + email en 60 segundos.
        //  Responde con HTTP 429 + header Retry-After automáticamente.
        // ─────────────────────────────────────────────────────────────
        RateLimiter::for('api.login', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->string('email')->lower() . '|' . $request->ip())
                ->response(function () {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Demasiados intentos de inicio de sesión. Inténtalo más tarde.',
                        'code'    => 'TOO_MANY_REQUESTS',
                    ], 429);
                });
        });

        // ─────────────────────────────────────────────────────────────
        //  Rate limiting para registro de aprendices
        //  Límite: 5 intentos por IP en 10 minutos.
        // ─────────────────────────────────────────────────────────────
        RateLimiter::for('api.register', function (Request $request) {
            return Limit::perMinutes(10, 5)
                ->by($request->ip())
                ->response(function () {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Demasiados intentos de registro. Inténtalo más tarde.',
                        'code'    => 'TOO_MANY_REQUESTS',
                    ], 429);
                });
        });

        // ─────────────────────────────────────────────────────────────
        //  Rate limiting general para la API
        // ─────────────────────────────────────────────────────────────
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)
                ->by($request->user()?->id ?: $request->ip());
        });

        // ─────────────────────────────────────────────────────────────
        //  Rate limiting para olvidé mi contraseña
        //
        //  Límite: 3 solicitudes por IP + email en 10 minutos.
        //  Evita el abuso del servidor de correo y la enumeración de emails.
        // ─────────────────────────────────────────────────────────────
        RateLimiter::for('api.password.forgot', function (Request $request) {
            return Limit::perMinutes(10, 3)
                ->by($request->string('email')->lower() . '|' . $request->ip())
                ->response(function () {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Demasiadas solicitudes. Inténtalo en unos minutos.',
                        'code'    => 'TOO_MANY_REQUESTS',
                    ], 429);
                });
        });

        // ─────────────────────────────────────────────────────────────
        //  Rate limiting para restablecer contraseña
        //
        //  Límite: 5 intentos por IP en 10 minutos.
        //  Un token solo puede usarse una vez (Laravel lo invalida tras usarse).
        // ─────────────────────────────────────────────────────────────
        RateLimiter::for('api.password.reset', function (Request $request) {
            return Limit::perMinutes(10, 5)
                ->by($request->ip())
                ->response(function () {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Demasiados intentos. Inténtalo más tarde.',
                        'code'    => 'TOO_MANY_REQUESTS',
                    ], 429);
                });
        });

        // ─────────────────────────────────────────────────────────────
        //  Rate limiting para verificar OTP de correo
        //  Límite: 10 intentos por IP en 10 minutos.
        // ─────────────────────────────────────────────────────────────
        RateLimiter::for('api.email.verify', function (Request $request) {
            return Limit::perMinutes(10, 10)
                ->by($request->ip())
                ->response(function () {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Demasiados intentos. Inténtalo más tarde.',
                        'code'    => 'TOO_MANY_REQUESTS',
                    ], 429);
                });
        });

        // ─────────────────────────────────────────────────────────────
        //  Rate limiting para reenviar OTP de correo
        //  Límite: 3 reenvíos por IP en 10 minutos.
        // ─────────────────────────────────────────────────────────────
        RateLimiter::for('api.email.resend', function (Request $request) {
            return Limit::perMinutes(10, 3)
                ->by($request->ip())
                ->response(function () {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Demasiados reenvíos. Espera unos minutos.',
                        'code'    => 'TOO_MANY_REQUESTS',
                    ], 429);
                });
        });
    }
}
