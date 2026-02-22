<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verifica que la petición proviene de un cliente autorizado
 * mediante un API Key compartido (header X-Client-Key).
 *
 * Protege TODOS los endpoints /api/* incluyendo el login público,
 * de modo que atacantes externos no puedan ni intentar fuerza bruta
 * sin conocer previamente este secreto.
 *
 * Configuración (.env):
 *   API_CLIENT_KEY=tu_clave_muy_larga_y_aleatoria
 */
class VerifyClientApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = config('app.api_client_key');

        // Si no está configurada la clave, bloqueamos todo en producción
        if (empty($expected)) {
            if (app()->isProduction()) {
                return $this->deny();
            }
            // En local/staging permite pasar si no está configurada
            return $next($request);
        }

        $provided = $request->header('X-Client-Key');

        // Comparación en tiempo constante para evitar timing attacks
        if (! $provided || ! hash_equals($expected, $provided)) {
            return $this->deny();
        }

        return $next($request);
    }

    private function deny(): Response
    {
        return response()->json([
            'status'  => 'error',
            'message' => 'Cliente no autorizado.',
            'code'    => 'INVALID_CLIENT_KEY',
        ], Response::HTTP_UNAUTHORIZED);
    }
}
