<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Laragear\WebAuthn\Contracts\WebAuthnAuthenticatable;
use Laragear\WebAuthn\WebAuthnAuthentication;

class User extends Authenticatable implements WebAuthnAuthenticatable, MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasApiTokens, Notifiable, HasRoles, TwoFactorAuthenticatable, WebAuthnAuthentication;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'avatar',
        'disabled_at',
        'can_access_ai_module',
        'can_view_student_stats',
        'email_otp',
        'email_otp_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'      => 'datetime',
            'email_otp_expires_at'   => 'datetime',
            'disabled_at'            => 'datetime',
            'password'               => 'hashed',
            'can_access_ai_module'   => 'boolean',
            'can_view_student_stats' => 'boolean',
        ];
    }

    /**
     * URL pública del avatar.
     * Si no tiene avatar, devuelve null (Flutter mostrará el placeholder).
     */
    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar
            ? asset('storage/' . $this->avatar)
            : null;
    }

    /**
     * Códigos de registro generados por este instructor
     */
    public function registrationCodes(): HasMany
    {
        return $this->hasMany(RegistrationCode::class, 'instructor_id');
    }

    /**
     * Verifica si el usuario está activo
     */
    public function isActive(): bool
    {
        return is_null($this->disabled_at);
    }

    /**
     * Verifica si el usuario está desactivado
     */
    public function isDisabled(): bool
    {
        return !is_null($this->disabled_at);
    }

    /**
     * Verifica si el usuario tiene acceso al módulo de IA
     */
    public function canAccessAiModule(): bool
    {
        return $this->can_access_ai_module && !$this->hasRole('admin');
    }

    /**
     * Verifica si el usuario puede ver estadísticas de aprendices
     */
    public function canViewStudentStats(): bool
    {
        return $this->can_view_student_stats && $this->hasRole('instructor');
    }

    /**
     * Entrenamientos realizados por el usuario
     */
    public function trainings(): HasMany
    {
        return $this->hasMany(Training::class);
    }

    /**
     * Entrenamientos donde el usuario es instructor
     */
    public function instructedTrainings(): HasMany
    {
        return $this->hasMany(Training::class, 'instructor_id');
    }

    /**
     * Reportes creados por el usuario
     */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    /**
     * Reportes asignados al usuario
     */
    public function assignedReports(): HasMany
    {
        return $this->hasMany(Report::class, 'assigned_to');
    }

    /**
     * Logs de actividad del usuario
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Logs de actividad causados por el usuario
     */
    public function causedActivityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'causer_id');
    }
}
