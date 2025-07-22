<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'status',
        'priority',
        'start_date',
        'end_date',
        'deadline',
        'progress_percentage',
        'budget',
        'client_name',
        'project_manager_id',
        'repository_url',
        'documentation_url',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'deadline' => 'date',
        'progress_percentage' => 'integer',
        'budget' => 'decimal:2',
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    // Status constants
    const STATUS_PLANNING = 'planning';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_ON_HOLD = 'on_hold';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    // Priority constants
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_CRITICAL = 'critical';

    /**
     * Get the project manager.
     */
    public function projectManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'project_manager_id');
    }

    /**
     * Alias for projectManager for backward compatibility.
     */
    public function manager(): BelongsTo
    {
        return $this->projectManager();
    }

    /**
     * Get the team members assigned to this project.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_user')
            ->withPivot([
                'role', 'hourly_rate', 'can_log_time',
                'can_view_reports', 'can_manage_milestones',
                'joined_at', 'left_at'
            ])
            ->withTimestamps();
    }

    /**
     * Alias for users for backward compatibility.
     */
    public function members(): BelongsToMany
    {
        return $this->users();
    }

    /**
     * Get the evidences submitted for this project.
     */
    public function evidences(): HasMany
    {
        return $this->hasMany(Evidence::class);
    }

    /**
     * Get the milestones for this project.
     */
    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class)->orderBy('order');
    }

    /**
     * Get the time logs for this project.
     */
    public function timeLogs(): HasMany
    {
        return $this->hasMany(TimeLog::class);
    }

    /**
     * Get the project's group/team.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    public function scopeOverdue($query)
    {
        return $query->where('deadline', '<', now())
            ->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function scopeByManager($query, $managerId)
    {
        return $query->where('project_manager_id', $managerId);
    }

    public function scopeByMember($query, $userId)
    {
        return $query->whereHas('members', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    // Accessors
    public function getStatusDisplayNameAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PLANNING => 'Planificación',
            self::STATUS_IN_PROGRESS => 'En Progreso',
            self::STATUS_ON_HOLD => 'En Pausa',
            self::STATUS_COMPLETED => 'Completado',
            self::STATUS_CANCELLED => 'Cancelado',
            default => 'Desconocido',
        };
    }

    public function getPriorityDisplayNameAttribute(): string
    {
        return match ($this->priority) {
            self::PRIORITY_LOW => 'Baja',
            self::PRIORITY_MEDIUM => 'Media',
            self::PRIORITY_HIGH => 'Alta',
            self::PRIORITY_CRITICAL => 'Crítica',
            default => 'Media',
        };
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PLANNING => 'bg-blue-100 text-blue-800',
            self::STATUS_IN_PROGRESS => 'bg-yellow-100 text-yellow-800',
            self::STATUS_ON_HOLD => 'bg-gray-100 text-gray-800',
            self::STATUS_COMPLETED => 'bg-green-100 text-green-800',
            self::STATUS_CANCELLED => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getPriorityBadgeColorAttribute(): string
    {
        return match ($this->priority) {
            self::PRIORITY_LOW => 'bg-green-100 text-green-800',
            self::PRIORITY_MEDIUM => 'bg-yellow-100 text-yellow-800',
            self::PRIORITY_HIGH => 'bg-orange-100 text-orange-800',
            self::PRIORITY_CRITICAL => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->deadline) {
            return null;
        }

        return now()->diffInDays($this->deadline, false);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->deadline && 
               $this->deadline->isPast() && 
               !in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function getCompletionRateAttribute(): float
    {
        $totalMilestones = $this->milestones()->count();
        if ($totalMilestones === 0) {
            return 0;
        }

        $completedMilestones = $this->milestones()->where('status', 'completed')->count();
        return ($completedMilestones / $totalMilestones) * 100;
    }

    public function getTotalHoursLoggedAttribute(): float
    {
        return $this->timeLogs()->sum('hours');
    }

    public function getBudgetUsedAttribute(): float
    {
        return $this->timeLogs()
            ->join('project_members', function ($join) {
                $join->on('time_logs.user_id', '=', 'project_members.user_id')
                     ->on('time_logs.project_id', '=', 'project_members.project_id');
            })
            ->sum(\DB::raw('time_logs.hours * project_members.hourly_rate'));
    }

    // Methods
    public function addMember(User $user, string $role = 'developer', float $hourlyRate = 0): void
    {
        $this->members()->attach($user->id, [
            'role' => $role,
            'joined_at' => now(),
            'hourly_rate' => $hourlyRate,
        ]);
    }

    public function removeMember(User $user): void
    {
        $this->members()->detach($user->id);
    }

    public function hasMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }

    public function updateProgress(): void
    {
        $this->update([
            'progress_percentage' => $this->completion_rate,
        ]);
    }

    public function canBeAccessedBy(User $user): bool
    {
        return $user->isAdmin() || 
               $this->project_manager_id === $user->id || 
               $this->hasMember($user);
    }

    public function canBeEditedBy(User $user): bool
    {
        return $user->isAdmin() || $this->project_manager_id === $user->id;
    }
}
