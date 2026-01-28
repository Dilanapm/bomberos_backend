<?php

use Illuminate\Support\Facades\Route;
use Laragear\WebAuthn\Http\Routes as WebAuthnRoutes;
use App\Livewire\Admin\Passkeys;
use App\Livewire\Auth\TwoFactorSetup;

/*
|--------------------------------------------------------------------------
| WebAuthn / Passkeys (Laragear)
|--------------------------------------------------------------------------
| - Registro de passkeys: SOLO admin autenticado (auth + role + 2FA + password.confirm)
| - Login con passkey: público en /passkeys-login (no requiere auth)
|
| Nota: Por compatibilidad con tu versión (no acepta null), registramos ambos paths
| y simplemente NO usamos el path "dummy" en cada grupo.
*/

// 1) Registro (attest) SOLO admin autenticado
Route::middleware(['web', 'auth', 'role:admin', '2fa.required', 'password.confirm'])
    ->group(function () {
        WebAuthnRoutes::register(
            attest: 'admin/passkeys',
            assert: 'passkeys-login' // existe pero no lo usamos aquí
        );
    });

// 2) Login (assert) público (sin auth)
Route::middleware(['web'])
    ->group(function () {
        WebAuthnRoutes::register(
            attest: 'passkeys-register-public', // existe pero no lo usamos aquí
            assert: 'passkeys-login'
        );
    });

/*
|--------------------------------------------------------------------------
| Admin (Web)
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'auth', 'role:admin', '2fa.required', 'admin.passkey.required'])
    ->prefix('admin')
    ->group(function () {

        // Zona admin (bloqueada si falta 2FA por tu middleware)
        Route::get('/zone', fn () => 'Ok admin test')->name('admin.zone');

        // UI para registrar/gestionar passkeys (requiere 2FA + confirmar password)
        Route::get('/passkeys-ui', Passkeys::class)
            ->middleware(['password.confirm'])
            ->name('admin.passkeys.ui');
    });

/*
|--------------------------------------------------------------------------
| Seguridad / 2FA Setup
|--------------------------------------------------------------------------
*/
Route::get('/2fa-setup', TwoFactorSetup::class)
  ->middleware(['web','auth','password.confirm'])
  ->name('2fa.setup');

/*
|--------------------------------------------------------------------------
| General
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'auth', 'admin.only'])
    ->group(function () {
        Route::get('/dashboard', fn () => 'Dashboard')->name('dashboard');
    });

Route::get('/home', fn () => redirect('/dashboard'))->middleware(['web'])->name('home');
