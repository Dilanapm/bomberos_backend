<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorEnabledForPrivilegedRoles
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Si no está logueado, que lo maneje auth middleware
        if (! $user) {
            return $next($request);
        }

        // Solo aplica a roles privilegiados
        $requires2fa = $user->hasAnyRole(['admin', 'instructor']);

        if (! $requires2fa) {
            return $next($request);
        }

        // Requiere 2FA habilitado y confirmado
        $has2fa = ! empty($user->two_factor_secret) && ! empty($user->two_factor_confirmed_at);

        if ($has2fa) {
            return $next($request);
        }

        // Evitar bucle: permitir acceso a endpoints relacionados a 2FA y logout
        $allowed = [
            'user/two-factor-qr-code',
            'two-factor-challenge',
            'logout',
        ];

        $path = ltrim($request->path(), '/');

        foreach ($allowed as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return $next($request);
            }
        }

        // Si es request JSON/API, devuelve 403
        if ($request->expectsJson()) {
            return response()->json([
                'message' => '2FA requerido para este rol.',
            ], 403);
        }

        // Si es web, redirige a un lugar donde activará 2FA
        // return redirect()->route('profile.show')
        //     ->with('error', 'Debes habilitar y confirmar 2FA para continuar.');
        return redirect('/2fa-setup')
                ->with('error', 'Debes habilitar y confirmar 2FA para continuar.');

    }
}
