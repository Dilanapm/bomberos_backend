<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogPasswordResetRequest
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->isMethod('post') && $request->is('forgot-password')) {
            Log::info('security.password_reset.requested', [
                'email' => (string) $request->input('email'),
                'ip'    => $request->ip(),
                'ua'    => substr((string) $request->userAgent(), 0, 300),
            ]);
        }

        return $next($request);
    }
}
