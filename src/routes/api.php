<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AdminStatsController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\EvaluationController;
use App\Http\Controllers\Api\InstructorCodeController;
use App\Http\Controllers\Api\InstructorCommentController;
use App\Http\Controllers\Api\InstructorStatsController;
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

            // Comentarios en evaluaciones de aprendices
            Route::prefix('evaluations/{evaluationId}/comments')
                ->where(['evaluationId' => '[0-9]+'])
                ->group(function () {
                    Route::get('/',               [InstructorCommentController::class, 'index'])
                        ->name('api.v1.instructor.comments.index');
                    Route::post('/',              [InstructorCommentController::class, 'store'])
                        ->name('api.v1.instructor.comments.store');
                    Route::delete('/{commentId}', [InstructorCommentController::class, 'destroy'])
                        ->where(['commentId' => '[0-9]+'])
                        ->name('api.v1.instructor.comments.destroy');
                });

            // ── Evaluaciones EPP (instructor) ────────────────────────────
            // Guardar una evaluación en nombre de un aprendiz (user_id en body)
            Route::post('/evaluations',             [EvaluationController::class, 'store'])
                ->name('api.v1.instructor.evaluations.store');

            // Detalle completo de cualquier evaluación
            Route::get('/evaluations/{id}',         [EvaluationController::class, 'show'])
                ->where('id', '[0-9]+')
                ->name('api.v1.instructor.evaluations.show');

            // Lista de TODOS los aprendices registrados (para seleccionar antes de un entrenamiento)
            Route::get('/aprendices/all',            [EvaluationController::class, 'allAprendices'])
                ->name('api.v1.instructor.aprendices.all');

            // Lista de aprendices que tienen al menos una evaluación
            Route::get('/aprendices',               [EvaluationController::class, 'aprendices'])
                ->name('api.v1.instructor.aprendices.index');

            // Historial de evaluaciones de un aprendiz específico
            Route::get('/aprendices/{aprendizId}/evaluations', [EvaluationController::class, 'historyForAprendiz'])
                ->where('aprendizId', '[0-9]+')
                ->name('api.v1.instructor.aprendices.evaluations');

            // ── Estadísticas del grupo (instructor) ──────────────────────
            Route::prefix('stats')->group(function () {
                Route::get('/my-group',      [InstructorStatsController::class, 'myGroup'])
                    ->name('api.v1.instructor.stats.my-group');
                Route::get('/ranking',       [InstructorStatsController::class, 'ranking'])
                    ->name('api.v1.instructor.stats.ranking');
                Route::get('/need-help',     [InstructorStatsController::class, 'needHelp'])
                    ->name('api.v1.instructor.stats.need-help');
                Route::get('/step-analysis', [InstructorStatsController::class, 'stepAnalysis'])
                    ->name('api.v1.instructor.stats.step-analysis');
            });
        });

    // ──────────────────────────────────────────────────────────────
    //  Evaluaciones EPP (aprendiz autenticado)
    // ──────────────────────────────────────────────────────────────
    Route::middleware(['auth:sanctum', 'mobile.access', 'role:aprendiz'])
        ->prefix('evaluations')
        ->group(function () {
            // Guardar evaluación enviada por FastAPI
            Route::post('/',            [EvaluationController::class, 'store'])
                ->name('api.v1.evaluations.store');

            // Historial paginado del aprendiz
            Route::get('/',             [EvaluationController::class, 'history'])
                ->name('api.v1.evaluations.history');

            // Estadísticas del aprendiz (mejoradas)
            Route::get('/stats',        [EvaluationController::class, 'stats'])
                ->name('api.v1.evaluations.stats');

            // Análisis avanzado del aprendiz
            Route::get('/analytics',    [EvaluationController::class, 'analytics'])
                ->name('api.v1.evaluations.analytics');

            // Detalle de una evaluación específica
            Route::get('/{id}',         [EvaluationController::class, 'show'])
                ->where('id', '[0-9]+')
                ->name('api.v1.evaluations.show');
        });

    // ──────────────────────────────────────────────────────────────
    //  Estadísticas globales (administrador)
    // ──────────────────────────────────────────────────────────────
    Route::middleware(['auth:sanctum', 'role:admin'])
        ->prefix('admin/stats')
        ->group(function () {
            Route::get('/global',        [AdminStatsController::class, 'global'])
                ->name('api.v1.admin.stats.global');
            Route::get('/instructors',   [AdminStatsController::class, 'instructors'])
                ->name('api.v1.admin.stats.instructors');
            Route::get('/step-analysis', [AdminStatsController::class, 'stepAnalysis'])
                ->name('api.v1.admin.stats.step-analysis');
            Route::get('/trends',        [AdminStatsController::class, 'trends'])
                ->name('api.v1.admin.stats.trends');
        });
});
