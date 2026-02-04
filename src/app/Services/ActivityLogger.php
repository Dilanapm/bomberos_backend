<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogger
{
    /**
     * Registrar una actividad
     */
    public static function log(
        string $logType,
        string $description,
        ?User $user = null,
        ?User $causer = null,
        array $properties = [],
        ?Request $request = null
    ): ActivityLog {
        $request = $request ?? request();

        return ActivityLog::create([
            'log_type' => $logType,
            'description' => $description,
            'user_id' => $user?->id,
            'causer_id' => $causer?->id ?? auth()->id(),
            'properties' => $properties,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    /**
     * Registrar creación de usuario
     */
    public static function userCreated(User $user, array $properties = []): ActivityLog
    {
        return self::log(
            'user.created',
            "Usuario creado: {$user->name}",
            $user,
            auth()->user(),
            array_merge([
                'user_email' => $user->email,
                'user_roles' => $user->getRoleNames()->toArray(),
            ], $properties)
        );
    }

    /**
     * Registrar actualización de usuario
     */
    public static function userUpdated(User $user, array $changes = []): ActivityLog
    {
        return self::log(
            'user.updated',
            "Usuario actualizado: {$user->name}",
            $user,
            auth()->user(),
            ['changes' => $changes]
        );
    }

    /**
     * Registrar activación de usuario
     */
    public static function userActivated(User $user): ActivityLog
    {
        return self::log(
            'user.activated',
            "Usuario activado: {$user->name}",
            $user,
            auth()->user()
        );
    }

    /**
     * Registrar desactivación de usuario
     */
    public static function userDeactivated(User $user): ActivityLog
    {
        return self::log(
            'user.deactivated',
            "Usuario desactivado: {$user->name}",
            $user,
            auth()->user()
        );
    }

    /**
     * Registrar inicio de sesión
     */
    public static function userLoggedIn(User $user): ActivityLog
    {
        return self::log(
            'user.login',
            "Inicio de sesión: {$user->name}",
            $user,
            $user
        );
    }

    /**
     * Registrar cierre de sesión
     */
    public static function userLoggedOut(User $user): ActivityLog
    {
        return self::log(
            'user.logout',
            "Cierre de sesión: {$user->name}",
            $user,
            $user
        );
    }

    /**
     * Registrar restablecimiento de contraseña
     */
    public static function passwordReset(User $user): ActivityLog
    {
        return self::log(
            'user.password.reset',
            "Contraseña restablecida: {$user->name}",
            $user,
            $user
        );
    }
}