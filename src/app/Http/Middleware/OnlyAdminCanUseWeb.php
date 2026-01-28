<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class OnlyAdminCanUseWeb
{
    public function handle(Request $request, Closure $next)
    {
        // Permitir rutas públicas (login, register, forgot-password, etc.)
        $publicRoutes = [
            'login',
            'login.store',
            'logout',
            'register',
            'register.store',
            'password.request',
            'password.email',
            'password.reset',
            'password.update',
            'two-factor.login',
            'two-factor.login.store',
            'webauthn.login',
            'webauthn.login.options',
        ];

        if (in_array($request->route()?->getName(), $publicRoutes)) {
            return $next($request);
        }

        // Si está en rutas públicas sin nombre (storage, livewire, etc.)
        if ($request->is('storage/*') || $request->is('livewire/*') || $request->is('up')) {
            return $next($request);
        }

        // Deja pasar invitados (páginas públicas)
        if (! $request->user()) {
            return $next($request);
        }

        // 🔒 SEGURIDAD CRÍTICA: Si está autenticado y NO es admin → BLOQUEAR
        if (! $request->user()->hasRole('admin')) {
            auth()->logout();

            return redirect('/login')
                ->withErrors(['email' => 'Este usuario no tiene acceso a la plataforma web.']);
        }

        return $next($request);
    }
}
