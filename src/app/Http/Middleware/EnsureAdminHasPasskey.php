<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminHasPasskey
{
    public function handle(Request $request, Closure $next): Response
    {
        // Passkey opcional — el admin puede usar la app sin tener registrada una passkey.
        // La funcionalidad de passkeys sigue disponible desde la UI de perfil.
        return $next($request);
    }
}
