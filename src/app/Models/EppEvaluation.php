<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EppEvaluation extends Model
{
    protected $fillable = [
        'session_id',
        'user_id',
        'general_score',
        'total_steps',
        'steps_completed',
        'correct_order',
        'duration_seconds',
        'total_frames',
        'frames_with_detection',
        'detection_rate',
        'status',
        'recommendations',
    ];

    protected $casts = [
        'correct_order'       => 'boolean',
        'general_score'       => 'decimal:2',
        'duration_seconds'    => 'decimal:2',
        'detection_rate'      => 'decimal:2',
        'created_at'          => 'datetime',
        'updated_at'          => 'datetime',
    ];

    // ──────────────────────────────────────────────────────────────
    //  Relaciones
    // ──────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(EppStepEvaluation::class, 'evaluation_id')
            ->orderBy('step_number');
    }

    public function errors(): HasMany
    {
        return $this->hasMany(EppError::class, 'evaluation_id');
    }

    public function timeline(): HasMany
    {
        return $this->hasMany(EppTimeline::class, 'evaluation_id')
            ->orderBy('time');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(EppInstructorComment::class, 'evaluation_id')
            ->orderBy('created_at', 'desc');
    }

    // ──────────────────────────────────────────────────────────────
    //  Accessors
    // ──────────────────────────────────────────────────────────────

    /** ¿Aprobó con todos los criterios? */
    public function getIsApprovedAttribute(): bool
    {
        return $this->general_score >= 70
            && $this->steps_completed >= 5
            && $this->correct_order;
    }

    /** Puntaje formateado como porcentaje */
    public function getScorePercentageAttribute(): string
    {
        return number_format($this->general_score, 1) . '%';
    }

    // ──────────────────────────────────────────────────────────────
    //  Scopes
    // ──────────────────────────────────────────────────────────────

    public function scopeApproved($query)
    {
        return $query->where('status', 'aprobado');
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
