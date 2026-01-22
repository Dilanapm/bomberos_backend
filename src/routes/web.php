<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Laragear\WebAuthn\Http\Routes as WebAuthnRoutes;



// Rutas de WebAuthn
WebAuthnRoutes::register()->withoutMiddleware(VerifyCsrfToken::class);


// Rutas protegidas con 2FA para administradores
Route::get('/admin-zone', function () {
    return 'Ok admin test';
})->middleware(['auth', 'role:admin', '2fa.required']);

Route::get('/2fa-setup', function () {
    return 'Aquí irá tu pantalla de activación 2FA (Livewire/Blade).';
})->middleware(['auth']);

Route::get('/dashboard', fn () => 'Dashboard')->middleware(['auth']);
Route::get('/home', fn () => redirect('/dashboard'));
