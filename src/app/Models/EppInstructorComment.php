<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EppInstructorComment extends Model
{
    protected $fillable = [
        'evaluation_id',
        'instructor_id',
        'step_number',
        'comment',
        'type',
    ];

    protected $casts = [
        'step_number' => 'integer',
    ];

    // ──────────────────────────────────────────────────────────────
    //  Relaciones
    // ──────────────────────────────────────────────────────────────

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(EppEvaluation::class, 'evaluation_id');
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    // ──────────────────────────────────────────────────────────────
    //  Scopes
    // ──────────────────────────────────────────────────────────────

    /** Solo comentarios generales (sin paso específico) */
    public function scopeGeneral($query)
    {
        return $query->whereNull('step_number');
    }

    /** Solo comentarios de un paso específico */
    public function scopeForStep($query, int $step)
    {
        return $query->where('step_number', $step);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
