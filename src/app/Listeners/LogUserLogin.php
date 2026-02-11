<?php

namespace App\Listeners;

use App\Services\ActivityLogger;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Auth;

class LogUserLogin
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        // Verificar si el usuario no es admin
        if (!$event->user->hasRole('admin')) {
            // Cerrar sesión inmediatamente
            Auth::logout();
            
            // Registrar intento de acceso
            ActivityLogger::log(
                'user.login.blocked',
                "Intento de acceso web bloqueado: {$event->user->name} (rol: {$event->user->getRoleNames()->first()})",
                $event->user,
                $event->user,
                ['reason' => 'non_admin_web_access']
            );
            
            // Redirigir con error
            request()->session()->flash('error', 'Tu cuenta no tiene acceso a la plataforma web. Por favor, utiliza la aplicación móvil.');
            
            return;
        }
        
        // Si es admin, registrar login exitoso
        ActivityLogger::userLoggedIn($event->user);
    }
}
