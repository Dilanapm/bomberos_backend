<?php

namespace App\Actions\Fortify;

use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Support\Facades\Password;
use Laravel\Fortify\Contracts\RequestPasswordResetLinkViewResponse;
use Laravel\Fortify\Http\Requests\PasswordResetLinkRequest;

class SendPasswordResetLinkResponse implements RequestPasswordResetLinkViewResponse
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        $email = $request->input('email');
        
        // Intentar enviar el enlace (silenciosamente si el usuario no existe)
        Password::broker(config('fortify.passwords'))
            ->sendResetLink(['email' => $email]);
        
        // SIEMPRE devolver el mismo mensaje, independientemente del resultado
        // Esto previene User Enumeration Attacks
        return back()->with('status', trans('passwords.sent'));
    }
}
