<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    protected $fillable = [
        'user_id',
        'assigned_to',
        'report_type',
        'title',
        'description',
        'category',
        'priority',
        'status',
        'metadata',
        'attachments',
        'resolution',
        'resolved_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'attachments' => 'array',
        'resolved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Usuario que generó el reporte
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Usuario a quien se asignó el reporte
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Verificar si el reporte está resuelto
     */
    public function isResolved(): bool
    {
        return in_array($this->status, ['resolved', 'closed']);
    }

    /**
     * Verificar si el reporte es urgente
     */
    public function isUrgent(): bool
    {
        return in_array($this->priority, ['high', 'critical']);
    }

    /**
     * Obtener el color según prioridad
     */
    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'low' => 'text-secondary-500',
            'medium' => 'text-blue-600',
            'high' => 'text-amber-600',
            'critical' => 'text-primary-600',
            default => 'text-secondary-500',
        };
    }

    /**
     * Obtener el icono según tipo de reporte
     */
    public function getTypeIconAttribute(): string
    {
        return match($this->report_type) {
            'incident' => 'alert-triangle',
            'training_summary' => 'book-open',
            'performance' => 'bar-chart-3',
            'equipment' => 'tool',
            'safety' => 'shield',
            default => 'file-text',
        };
    }
}
