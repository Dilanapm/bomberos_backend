<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationCode extends Model
{
    protected $fillable = [
        'code',
        'instructor_id',
        'expires_at',
        'max_uses',
        'uses',
        'revoked',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'revoked'    => 'boolean',
        'max_uses'   => 'integer',
        'uses'       => 'integer',
    ];

    // ──────────────────────────────────────────────────────────────
    //  Relaciones
    // ──────────────────────────────────────────────────────────────

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    // ──────────────────────────────────────────────────────────────
    //  Helpers
    // ──────────────────────────────────────────────────────────────

    /**
     * El código es válido si:
     *  - No ha expirado
     *  - No fue revocado manualmente
     *  - No ha alcanzado el máximo de usos
     */
    public function isValid(): bool
    {
        return ! $this->revoked
            && $this->expires_at->isFuture()
            && $this->uses < $this->max_uses;
    }

    /**
     * Registra un uso del código.
     */
    public function registerUse(): void
    {
        $this->increment('uses');
    }

    // ──────────────────────────────────────────────────────────────
    //  Scopes
    // ──────────────────────────────────────────────────────────────

    /**
     * Solo códigos aún válidos (para búsquedas rápidas).
     */
    public function scopeValid(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query
            ->where('revoked', false)
            ->where('expires_at', '>', now())
            ->whereColumn('uses', '<', 'max_uses');
    }
}
