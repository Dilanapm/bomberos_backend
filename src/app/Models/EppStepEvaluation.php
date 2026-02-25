<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EppStepEvaluation extends Model
{
    protected $fillable = [
        'evaluation_id',
        'step_number',
        'step_name',
        'score',
        'status',
        'detected',
        'feedback',
        'time_start',
        'time_end',
        'duration',
    ];

    protected $casts = [
        'detected'   => 'boolean',
        'score'      => 'decimal:4',
        'time_start' => 'decimal:2',
        'time_end'   => 'decimal:2',
        'duration'   => 'decimal:2',
    ];

    // ──────────────────────────────────────────────────────────────
    //  Relaciones
    // ──────────────────────────────────────────────────────────────

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(EppEvaluation::class, 'evaluation_id');
    }

    // ──────────────────────────────────────────────────────────────
    //  Accessors
    // ──────────────────────────────────────────────────────────────

    /** Score en porcentaje legible: 0.9100 → 91.00% */
    public function getScorePercentageAttribute(): string
    {
        return number_format($this->score * 100, 2) . '%';
    }

    /** ¿El paso fue completado correctamente? */
    public function getIsCorrectAttribute(): bool
    {
        return $this->status === 'correcto' && $this->detected;
    }
}
