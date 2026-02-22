<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Training extends Model
{
    protected $fillable = [
        'user_id',
        'instructor_id',
        'training_type',
        'module',
        'status',
        'score',
        'duration_minutes',
        'ai_feedback',
        'results',
        'notes',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'ai_feedback' => 'array',
        'results' => 'array',
        'score' => 'decimal:2',
        'duration_minutes' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * El aprendiz que realiza el entrenamiento
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * El instructor asignado (si aplica)
     */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    /**
     * Verificar si el entrenamiento está completo
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Verificar si el entrenamiento fue aprobado
     */
    public function isPassed(): bool
    {
        return $this->isCompleted() && $this->score >= 70;
    }

    /**
     * Obtener el porcentaje de progreso
     */
    public function getProgressPercentage(): int
    {
        return match($this->status) {
            'not_started' => 0,
            'in_progress' => 50,
            'completed', 'failed' => 100,
            'cancelled' => 0,
            default => 0,
        };
    }
}
