<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Milestone extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'project_id',
        'name',
        'description',
        'status',
        'priority',
        'order',
        'progress_percentage',
        'estimated_hours',
        'actual_hours',
        'due_date',
        'started_at',
        'completed_at',
        'assigned_to',
        'notes',
        'deliverables',
        'acceptance_criteria',
        'dependencies',
        'risk_level',
        'budget_allocated',
        'budget_used',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'due_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'progress_percentage' => 'integer',
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
        'budget_allocated' => 'decimal:2',
        'budget_used' => 'decimal:2',
        'deliverables' => 'array',
        'acceptance_criteria' => 'array',
        'dependencies' => 'array',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [];

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = [
        'status_display_name',
        'priority_display_name',
        'status_badge_color',
        'priority_badge_color',
        'is_overdue',
        'days_until_due',
        'completion_percentage',
        'budget_utilization_percentage',
    ];

    /**
     * Status constants.
     */
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_ON_HOLD = 'on_hold';

    /**
     * Priority constants.
     */
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_CRITICAL = 'critical';

    /**
     * Risk level constants.
     */
    const RISK_LOW = 'low';
    const RISK_MEDIUM = 'medium';
    const RISK_HIGH = 'high';
    const RISK_CRITICAL = 'critical';

    /**
     * Get the project that owns the milestone.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user assigned to this milestone.
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the time logs for this milestone.
     */
    public function timeLogs(): HasMany
    {
        return $this->hasMany(TimeLog::class);
    }

    /**
     * Get the status display name.
     */
    public function getStatusDisplayNameAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_IN_PROGRESS => 'En Progreso',
            self::STATUS_COMPLETED => 'Completado',
            self::STATUS_CANCELLED => 'Cancelado',
            self::STATUS_ON_HOLD => 'En Pausa',
            default => 'Desconocido',
        };
    }

    /**
     * Get the priority display name.
     */
    public function getPriorityDisplayNameAttribute(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'Baja',
            self::PRIORITY_MEDIUM => 'Media',
            self::PRIORITY_HIGH => 'Alta',
            self::PRIORITY_CRITICAL => 'Crítica',
            default => 'Media',
        };
    }

    /**
     * Get the status badge color.
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'bg-gray-100 text-gray-800',
            self::STATUS_IN_PROGRESS => 'bg-blue-100 text-blue-800',
            self::STATUS_COMPLETED => 'bg-green-100 text-green-800',
            self::STATUS_CANCELLED => 'bg-red-100 text-red-800',
            self::STATUS_ON_HOLD => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get the priority badge color.
     */
    public function getPriorityBadgeColorAttribute(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'bg-green-100 text-green-800',
            self::PRIORITY_MEDIUM => 'bg-yellow-100 text-yellow-800',
            self::PRIORITY_HIGH => 'bg-orange-100 text-orange-800',
            self::PRIORITY_CRITICAL => 'bg-red-100 text-red-800',
            default => 'bg-yellow-100 text-yellow-800',
        };
    }

    /**
     * Check if the milestone is overdue.
     */
    public function getIsOverdueAttribute(): bool
    {
        if (!$this->due_date || $this->status === self::STATUS_COMPLETED) {
            return false;
        }

        return $this->due_date->isPast();
    }

    /**
     * Get days until due date.
     */
    public function getDaysUntilDueAttribute(): ?int
    {
        if (!$this->due_date || $this->status === self::STATUS_COMPLETED) {
            return null;
        }

        return now()->diffInDays($this->due_date, false);
    }

    /**
     * Get completion percentage (alias for progress_percentage).
     */
    public function getCompletionPercentageAttribute(): int
    {
        return $this->progress_percentage ?? 0;
    }

    /**
     * Get budget utilization percentage.
     */
    public function getBudgetUtilizationPercentageAttribute(): float
    {
        if (!$this->budget_allocated || $this->budget_allocated == 0) {
            return 0;
        }

        return round(($this->budget_used / $this->budget_allocated) * 100, 2);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by priority.
     */
    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope to filter overdue milestones.
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    /**
     * Scope to filter upcoming milestones.
     */
    public function scopeUpcoming($query, int $days = 7)
    {
        return $query->whereBetween('due_date', [now(), now()->addDays($days)])
                    ->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    /**
     * Scope to filter by project.
     */
    public function scopeForProject($query, int $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    /**
     * Scope to filter by assigned user.
     */
    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Mark milestone as completed.
     */
    public function markAsCompleted(): bool
    {
        $this->status = self::STATUS_COMPLETED;
        $this->progress_percentage = 100;
        $this->completed_at = now();

        return $this->save();
    }

    /**
     * Start milestone.
     */
    public function start(): bool
    {
        $this->status = self::STATUS_IN_PROGRESS;
        $this->started_at = $this->started_at ?? now();

        return $this->save();
    }

    /**
     * Update progress percentage.
     */
    public function updateProgress(int $percentage): bool
    {
        $this->progress_percentage = max(0, min(100, $percentage));
        
        if ($percentage >= 100) {
            $this->markAsCompleted();
        } elseif ($percentage > 0 && $this->status === self::STATUS_PENDING) {
            $this->start();
        }

        return $this->save();
    }

    /**
     * Calculate actual hours from time logs.
     */
    public function calculateActualHours(): float
    {
        $actualHours = $this->timeLogs()->sum('hours');
        $this->actual_hours = $actualHours;
        $this->save();

        return $actualHours;
    }

    /**
     * Calculate budget used from time logs.
     */
    public function calculateBudgetUsed(): float
    {
        $budgetUsed = $this->timeLogs()
                          ->where('is_billable', true)
                          ->sum(\DB::raw('hours * hourly_rate'));
        
        $this->budget_used = $budgetUsed;
        $this->save();

        return $budgetUsed;
    }

    /**
     * Get all available statuses.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_IN_PROGRESS => 'En Progreso',
            self::STATUS_COMPLETED => 'Completado',
            self::STATUS_CANCELLED => 'Cancelado',
            self::STATUS_ON_HOLD => 'En Pausa',
        ];
    }

    /**
     * Get all available priorities.
     */
    public static function getPriorities(): array
    {
        return [
            self::PRIORITY_LOW => 'Baja',
            self::PRIORITY_MEDIUM => 'Media',
            self::PRIORITY_HIGH => 'Alta',
            self::PRIORITY_CRITICAL => 'Crítica',
        ];
    }

    /**
     * Get all available risk levels.
     */
    public static function getRiskLevels(): array
    {
        return [
            self::RISK_LOW => 'Bajo',
            self::RISK_MEDIUM => 'Medio',
            self::RISK_HIGH => 'Alto',
            self::RISK_CRITICAL => 'Crítico',
        ];
    }

    /**
     * Activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name', 'description', 'status', 'priority', 'progress_percentage',
                'due_date', 'assigned_to', 'estimated_hours', 'actual_hours'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($milestone) {
            if (is_null($milestone->order)) {
                $milestone->order = static::where('project_id', $milestone->project_id)->max('order') + 1;
            }
        });

        static::updating(function ($milestone) {
            // Auto-update status based on progress
            if ($milestone->isDirty('progress_percentage')) {
                $progress = $milestone->progress_percentage;
                
                if ($progress >= 100 && $milestone->status !== self::STATUS_COMPLETED) {
                    $milestone->status = self::STATUS_COMPLETED;
                    $milestone->completed_at = now();
                } elseif ($progress > 0 && $milestone->status === self::STATUS_PENDING) {
                    $milestone->status = self::STATUS_IN_PROGRESS;
                    $milestone->started_at = $milestone->started_at ?? now();
                }
            }
        });
    }
}
