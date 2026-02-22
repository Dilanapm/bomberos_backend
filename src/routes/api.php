<?php

declare(strict_types=1);

use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\InstructorCodeController;
use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RegisterController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Aplicación Móvil (Flutter)
|--------------------------------------------------------------------------
|
| Prefijo base : /api/v1
| Auth driver  : Sanctum (tokens de portador)
| Roles        : instructor | aprendiz   (admin → bloqueado)
|
*/

Route::prefix('v1')->group(function () {

    // ──────────────────────────────────────────────────────────────
    //  Autenticación pública (sin token)
    // ──────────────────────────────────────────────────────────────
    Route::prefix('auth')->group(function () {
        Route::post('/login', [MobileAuthController::class, 'login'])
            ->middleware('throttle:api.login')
            ->name('api.v1.auth.login');

        Route::post('/register', [RegisterController::class, 'register'])
            ->middleware('throttle:api.register')
            ->name('api.v1.auth.register');

        Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword'])
            ->middleware('throttle:api.password.forgot')
            ->name('api.v1.auth.forgot-password');

        Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])
            ->middleware('throttle:api.password.reset')
            ->name('api.v1.auth.reset-password');

        // Verificación de email por OTP de 6 dígitos
        Route::post('/email/verify', [EmailVerificationController::class, 'verify'])
            ->middleware('throttle:api.email.verify')
            ->name('api.v1.auth.email.verify');

        Route::post('/email/resend', [EmailVerificationController::class, 'resend'])
            ->middleware('throttle:api.email.resend')
            ->name('api.v1.auth.email.resend');
    });

    // ──────────────────────────────────────────────────────────────
    //  Endpoints protegidos — cualquier usuario móvil autenticado
    //  (instructor o aprendiz)
    // ──────────────────────────────────────────────────────────────
    Route::middleware(['auth:sanctum', 'mobile.access'])->group(function () {

        // Auth
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [MobileAuthController::class, 'logout'])
                ->name('api.v1.auth.logout');
            Route::get('/me', [MobileAuthController::class, 'me'])
                ->name('api.v1.auth.me');
        });

        // Perfil (nombre, username, contraseña, avatar)
        Route::prefix('profile')->group(function () {
            Route::patch('/',        [ProfileController::class, 'update'])
                ->name('api.v1.profile.update');
            Route::post('/password', [ProfileController::class, 'changePassword'])
                ->name('api.v1.profile.password');
            Route::post('/avatar',   [ProfileController::class, 'uploadAvatar'])
                ->name('api.v1.profile.avatar.upload');
            Route::delete('/avatar', [ProfileController::class, 'deleteAvatar'])
                ->name('api.v1.profile.avatar.delete');
        });
    });

    // ──────────────────────────────────────────────────────────────
    //  Endpoints exclusivos de Instructor
    // ──────────────────────────────────────────────────────────────
    Route::middleware(['auth:sanctum', 'mobile.access', 'role:instructor'])
        ->prefix('instructor')
        ->group(function () {
            // Generar código de registro para aprendices
            Route::post('/registration-code', [InstructorCodeController::class, 'generate'])
                ->name('api.v1.instructor.code.generate');

            // Ver código activo (para mostrarlo en pantalla con cuenta regresiva)
            Route::get('/registration-code/active', [InstructorCodeController::class, 'active'])
                ->name('api.v1.instructor.code.active');

            // Revocar código activo manualmente
            Route::delete('/registration-code', [InstructorCodeController::class, 'revoke'])
                ->name('api.v1.instructor.code.revoke');
        });

    // ──────────────────────────────────────────────────────────────
    //  Recursos futuros (protegidos por defecto)
    // ──────────────────────────────────────────────────────────────
    Route::middleware(['auth:sanctum', 'mobile.access'])->group(function () {
        // Route::apiResource('trainings', TrainingController::class);
        // Route::apiResource('reports', ReportController::class);
    });
});
