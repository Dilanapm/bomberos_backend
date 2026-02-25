<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EppTimeline extends Model
{
    protected $table = 'epp_timeline';

    protected $fillable = [
        'evaluation_id',
        'time',
        'step_detected',
        'confidence',
    ];

    protected $casts = [
        'time'       => 'decimal:2',
        'confidence' => 'decimal:4',
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

    /** Confianza como porcentaje: 0.8900 → 89.00% */
    public function getConfidencePercentageAttribute(): string
    {
        return number_format($this->confidence * 100, 2) . '%';
    }
}
