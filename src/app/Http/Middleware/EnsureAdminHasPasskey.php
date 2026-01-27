<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminHasPasskey
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Solo aplica a admin autenticado
        if (! $user || ! method_exists($user, 'hasRole') || ! $user->hasRole('admin')) {
            return $next($request);
        }

        // No bloquear las rutas necesarias para salir del "loop"
        if ($request->is('admin/passkeys-ui') ||
            $request->is('admin/passkeys*') ||
            $request->is('2fa-setup') ||
            $request->is('user/confirm-password')) {
            return $next($request);
        }

        // Si Laragear está bien instalado, el User tiene relación webAuthnCredentials()
        $hasPasskey = method_exists($user, 'webAuthnCredentials')
            && $user->webAuthnCredentials()->whereNull('disabled_at')->exists();
        if (! $hasPasskey) {
            return redirect()->route('admin.passkeys.ui');
        }

        return $next($request);
    }
}
