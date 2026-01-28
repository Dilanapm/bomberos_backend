<?php

namespace App\Actions\Fortify\Responses;

use Laravel\Fortify\Contracts\TwoFactorLoginResponse as Contract;

class ConfirmedTwoFactorAuthenticationResponse implements Contract
{
    public function toResponse($request)
    {
        $user = $request->user();

        // Solo admin tiene acceso a la plataforma web
        if ($user && $user->hasRole('admin')) {
            return redirect()->intended('/admin/zone');
        }

        // Si no es admin, desconectar y mostrar error
        auth()->logout();
        return redirect('/login')->withErrors([
            'email' => 'Este usuario no tiene acceso a la plataforma web.',
        ]);
    }
}
