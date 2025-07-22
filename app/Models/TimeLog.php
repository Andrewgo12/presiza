<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'project_id',
        'milestone_id',
        'task_description',
        'hours',
        'date',
        'start_time',
        'end_time',
        'is_billable',
        'hourly_rate',
        'notes',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'hours' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'is_billable' => 'boolean',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the user who logged this time.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the project this time log belongs to.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the milestone this time log belongs to.
     */
    public function milestone(): BelongsTo
    {
        return $this->belongsTo(Milestone::class, 'milestone_id');
    }

    /**
     * Get the user who approved this time log.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeBillable($query)
    {
        return $query->where('is_billable', true);
    }

    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }

    public function scopePending($query)
    {
        return $query->whereNull('approved_at');
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereBetween('date', [
            now()->startOfMonth(),
            now()->endOfMonth()
        ]);
    }

    // Accessors
    public function getTotalAmountAttribute(): float
    {
        return $this->hours * $this->hourly_rate;
    }

    public function getIsApprovedAttribute(): bool
    {
        return !is_null($this->approved_at);
    }

    public function getFormattedDurationAttribute(): string
    {
        $hours = floor($this->hours);
        $minutes = ($this->hours - $hours) * 60;
        
        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        } else {
            return "{$minutes}m";
        }
    }

    // Methods
    public function approve(User $approver): void
    {
        $this->update([
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);
    }

    public function canBeEditedBy(User $user): bool
    {
        // Only the user who logged the time can edit it (if not approved)
        // Or project manager/admin can edit
        return ($this->user_id === $user->id && !$this->is_approved) ||
               $user->isAdmin() ||
               $this->project->project_manager_id === $user->id;
    }

    public function canBeApprovedBy(User $user): bool
    {
        return $user->isAdmin() || 
               $this->project->project_manager_id === $user->id ||
               in_array($user->role, ['admin', 'analyst']);
    }

    // Boot method to calculate hours automatically
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($timeLog) {
            // Auto-calculate hours if start_time and end_time are provided
            if ($timeLog->start_time && $timeLog->end_time && !$timeLog->hours) {
                $timeLog->hours = $timeLog->end_time->diffInMinutes($timeLog->start_time) / 60;
            }

            // Set hourly rate from project member if not provided
            if (!$timeLog->hourly_rate && $timeLog->project && $timeLog->user) {
                $member = $timeLog->project->members()
                    ->where('user_id', $timeLog->user_id)
                    ->first();
                
                if ($member) {
                    $timeLog->hourly_rate = $member->pivot->hourly_rate ?? 0;
                }
            }
        });
    }
}
