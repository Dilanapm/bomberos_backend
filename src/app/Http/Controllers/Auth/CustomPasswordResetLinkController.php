<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Password;
use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse;
use Laravel\Fortify\Contracts\RequestPasswordResetLinkViewResponse;
use Laravel\Fortify\Contracts\SuccessfulPasswordResetLinkRequestResponse;
use Laravel\Fortify\Fortify;

class CustomPasswordResetLinkController extends Controller
{
    /**
     * Show the reset password link request view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Laravel\Fortify\Contracts\RequestPasswordResetLinkViewResponse
     */
    public function create(Request $request): RequestPasswordResetLinkViewResponse
    {
        return app(RequestPasswordResetLinkViewResponse::class);
    }

    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            Fortify::email() => 'required|email',
        ]);

        $email = $request->input(Fortify::email());

        // Log para auditoría de seguridad (sin revelar si existe o no)
        \Log::info('security.password_reset.requested', [
            'email' => $email,
            'ip' => $request->ip(),
            'ua' => $request->userAgent(),
        ]);

        // Intentar enviar el reset link (silenciosamente)
        $status = Password::broker(config('fortify.passwords'))->sendResetLink(
            $request->only(Fortify::email())
        );

        // 🔒 SEGURIDAD CRÍTICA: Siempre devolver el mismo mensaje
        // Independientemente de si el usuario existe o no.
        // Esto previene User Enumeration Attacks donde un atacante
        // podría descubrir qué emails están registrados.
        
        // Si el email existe: se envía el correo
        // Si NO existe: no se envía nada, pero mostramos el mismo mensaje
        
        return back()->with('status', trans('passwords.sent'));
    }
}
