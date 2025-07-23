<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Evidence extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'evidences';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'category',
        'priority',
        'status',
        'submitted_by',
        'assigned_to',
        'project_id',
        'milestone_id',
        'metadata',
        'incident_date',
        'progress_percentage',
        'time_spent',
        'location',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
        'incident_date' => 'datetime',
        'progress_percentage' => 'integer',
        'time_spent' => 'decimal:2',
        'tags' => 'array',
    ];

    /**
     * Get the user who submitted this evidence.
     */
    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Get the user assigned to this evidence.
     */
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get files associated with this evidence.
     */
    public function files()
    {
        return $this->belongsToMany(File::class, 'evidence_files')
            ->withPivot('order')
            ->withTimestamps()
            ->orderBy('evidence_files.order');
    }

    /**
     * Get evaluations for this evidence.
     */
    public function evaluations()
    {
        return $this->hasMany(EvidenceEvaluation::class);
    }

    /**
     * Get history entries for this evidence.
     */
    public function history()
    {
        return $this->hasMany(EvidenceHistory::class)->latest();
    }

    /**
     * Get the project this evidence belongs to.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the milestone this evidence belongs to.
     */
    public function milestone(): BelongsTo
    {
        return $this->belongsTo(ProjectMilestone::class, 'milestone_id');
    }

    /**
     * Get the evidence's status badge color.
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'under_review' => 'bg-blue-100 text-blue-800',
            'approved' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            'archived' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get the evidence's priority badge color.
     */
    public function getPriorityBadgeColorAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'bg-gray-100 text-gray-800',
            'medium' => 'bg-yellow-100 text-yellow-800',
            'high' => 'bg-orange-100 text-orange-800',
            'critical' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get the evidence's category badge color.
     */
    public function getCategoryBadgeColorAttribute(): string
    {
        return match ($this->category) {
            'security' => 'bg-red-100 text-red-800',
            'investigation' => 'bg-purple-100 text-purple-800',
            'compliance' => 'bg-blue-100 text-blue-800',
            'audit' => 'bg-green-100 text-green-800',
            'incident' => 'bg-orange-100 text-orange-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get status display name.
     */
    public function getStatusDisplayNameAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pendiente',
            'under_review' => 'En Revisión',
            'approved' => 'Aprobado',
            'rejected' => 'Rechazado',
            'archived' => 'Archivado',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get priority display name.
     */
    public function getPriorityDisplayNameAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'Baja',
            'medium' => 'Media',
            'high' => 'Alta',
            'critical' => 'Crítica',
            default => ucfirst($this->priority),
        };
    }

    /**
     * Get category display name.
     */
    public function getCategoryDisplayNameAttribute(): string
    {
        return match ($this->category) {
            'security' => 'Seguridad',
            'investigation' => 'Investigación',
            'compliance' => 'Cumplimiento',
            'audit' => 'Auditoría',
            'incident' => 'Incidente',
            'other' => 'Otro',
            default => ucfirst($this->category),
        };
    }

    /**
     * Check if evidence is pending.
     */
    public function getIsPendingAttribute(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if evidence is under review.
     */
    public function getIsUnderReviewAttribute(): bool
    {
        return $this->status === 'under_review';
    }

    /**
     * Check if evidence is approved.
     */
    public function getIsApprovedAttribute(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if evidence is rejected.
     */
    public function getIsRejectedAttribute(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if evidence is critical priority.
     */
    public function getIsCriticalAttribute(): bool
    {
        return $this->priority === 'critical';
    }

    /**
     * Check if evidence is high priority.
     */
    public function getIsHighPriorityAttribute(): bool
    {
        return in_array($this->priority, ['high', 'critical']);
    }

    /**
     * Get average rating from evaluations.
     */
    public function getAverageRatingAttribute(): ?float
    {
        $ratings = $this->evaluations()->whereNotNull('rating')->pluck('rating');
        
        if ($ratings->isEmpty()) {
            return null;
        }

        return round($ratings->average(), 1);
    }

    /**
     * Get latest evaluation.
     */
    public function getLatestEvaluationAttribute(): ?EvidenceEvaluation
    {
        return $this->evaluations()->latest()->first();
    }

    /**
     * Get days since submission.
     */
    public function getDaysSinceSubmissionAttribute(): int
    {
        return $this->created_at->diffInDays(now());
    }

    /**
     * Check if evidence is overdue (pending for more than 7 days).
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->is_pending && $this->days_since_submission > 7;
    }

    /**
     * Scope to get evidences by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get evidences by priority.
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope to get evidences by category.
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to get evidences assigned to user.
     */
    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope to get evidences submitted by user.
     */
    public function scopeSubmittedBy($query, $userId)
    {
        return $query->where('submitted_by', $userId);
    }

    /**
     * Scope to search evidences.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('location', 'like', "%{$search}%");
        });
    }

    /**
     * Scope to get pending evidences.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get critical evidences.
     */
    public function scopeCritical($query)
    {
        return $query->where('priority', 'critical');
    }

    /**
     * Scope to get overdue evidences.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'pending')
            ->where('created_at', '<', now()->subDays(7));
    }

    /**
     * Scope to get evidences created in date range.
     */
    public function scopeCreatedBetween($query, $start, $end)
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }

    /**
     * Get evidence statistics.
     */
    public function getStatsAttribute(): array
    {
        return [
            'files_count' => $this->files()->count(),
            'evaluations_count' => $this->evaluations()->count(),
            'history_count' => $this->history()->count(),
            'average_rating' => $this->average_rating,
            'days_since_submission' => $this->days_since_submission,
            'is_overdue' => $this->is_overdue,
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-create history entry when evidence is created
        static::created(function ($evidence) {
            $evidence->history()->create([
                'user_id' => $evidence->submitted_by,
                'action' => 'created',
                'new_values' => $evidence->toArray(),
                'notes' => 'Evidencia creada'
            ]);
        });
    }
}
