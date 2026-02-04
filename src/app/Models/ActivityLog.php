<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'log_type',
        'description',
        'user_id',
        'causer_id',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Usuario afectado por la acción
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Usuario que realizó la acción
     */
    public function causer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'causer_id');
    }

    /**
     * Obtener el icono según el tipo de log
     */
    public function getIconAttribute(): string
    {
        return match(true) {
            str_contains($this->log_type, 'created') => 'user-plus',
            str_contains($this->log_type, 'updated') => 'edit',
            str_contains($this->log_type, 'deleted') => 'trash-2',
            str_contains($this->log_type, 'activated') => 'user-check',
            str_contains($this->log_type, 'deactivated') => 'user-x',
            str_contains($this->log_type, 'login') => 'log-in',
            str_contains($this->log_type, 'logout') => 'log-out',
            str_contains($this->log_type, 'password') => 'key',
            default => 'activity',
        };
    }

    /**
     * Obtener el color según el tipo de log
     */
    public function getColorAttribute(): string
    {
        return match(true) {
            str_contains($this->log_type, 'created') => 'text-success-600',
            str_contains($this->log_type, 'updated') => 'text-blue-600',
            str_contains($this->log_type, 'deleted') => 'text-primary-600',
            str_contains($this->log_type, 'activated') => 'text-success-600',
            str_contains($this->log_type, 'deactivated') => 'text-amber-600',
            str_contains($this->log_type, 'login') => 'text-blue-600',
            str_contains($this->log_type, 'password') => 'text-amber-600',
            default => 'text-secondary-600',
        };
    }
}
