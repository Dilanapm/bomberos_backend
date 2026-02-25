<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EppError extends Model
{
    protected $fillable = [
        'evaluation_id',
        'step_number',
        'error_type',
        'description',
        'severity',
    ];

    // ──────────────────────────────────────────────────────────────
    //  Relaciones
    // ──────────────────────────────────────────────────────────────

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(EppEvaluation::class, 'evaluation_id');
    }

    // ──────────────────────────────────────────────────────────────
    //  Scopes
    // ──────────────────────────────────────────────────────────────

    public function scopeHighSeverity($query)
    {
        return $query->where('severity', 'alta');
    }

    public function scopeByStep($query, int $step)
    {
        return $query->where('step_number', $step);
    }
}
