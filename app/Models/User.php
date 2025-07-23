<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * User roles constants for our medical system
     */
    const ROLE_ADMIN = 'admin';
    const ROLE_MEDICAL = 'medical';
    const ROLE_EPS = 'eps';
    const ROLE_SYSTEMS = 'systems';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'role',
        'department',
        'position',
        'avatar',
        'is_active',
        'last_login',
        'notification_settings',
        'privacy_settings',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login' => 'datetime',
        'is_active' => 'boolean',
        'notification_settings' => 'array',
        'privacy_settings' => 'array',
        'password' => 'hashed',
    ];

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the user's initials.
     */
    public function getInitialsAttribute(): string
    {
        return strtoupper(substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1));
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Check if user is medical staff.
     */
    public function isMedical(): bool
    {
        return $this->role === self::ROLE_MEDICAL;
    }

    /**
     * Check if user is EPS analyst.
     */
    public function isEPS(): bool
    {
        return $this->role === self::ROLE_EPS;
    }

    /**
     * Check if user is systems administrator.
     */
    public function isSystems(): bool
    {
        return $this->role === self::ROLE_SYSTEMS;
    }

    /**
     * Check if user has specific role(s).
     */
    public function hasRole($roles): bool
    {
        if (is_string($roles)) {
            return $this->role === $roles;
        }

        if (is_array($roles)) {
            return in_array($this->role, $roles);
        }

        return false;
    }

    /**
     * Check if user can manage evidences.
     */
    public function canManageEvidences(): bool
    {
        return in_array($this->role, ['admin', 'analyst', 'investigator']);
    }

    /**
     * Get files uploaded by this user.
     */
    public function files()
    {
        return $this->hasMany(File::class, 'uploaded_by');
    }

    /**
     * Get evidences submitted by this user.
     */
    public function evidences()
    {
        return $this->hasMany(Evidence::class, 'submitted_by');
    }

    /**
     * Get evidences assigned to this user.
     */
    public function assignedEvidences()
    {
        return $this->hasMany(Evidence::class, 'assigned_to');
    }

    /**
     * Get groups where this user is a member.
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_members')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    /**
     * Get groups created by this user.
     */
    public function createdGroups()
    {
        return $this->hasMany(Group::class, 'created_by');
    }

    /**
     * Get messages sent by this user.
     */
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Get messages received by this user.
     */
    public function receivedMessages()
    {
        return $this->belongsToMany(Message::class, 'message_recipients')
            ->withPivot('read_at')
            ->withTimestamps();
    }

    /**
     * Get unread messages for this user.
     */
    public function unreadMessages()
    {
        return $this->receivedMessages()->wherePivot('read_at', null);
    }

    /**
     * Get evidence evaluations made by this user.
     */
    public function evidenceEvaluations()
    {
        return $this->hasMany(EvidenceEvaluation::class, 'evaluator_id');
    }

    /**
     * Get evidence history entries created by this user.
     */
    public function evidenceHistory()
    {
        return $this->hasMany(EvidenceHistory::class, 'user_id');
    }

    /**
     * Get group invitations sent by this user.
     */
    public function sentGroupInvitations()
    {
        return $this->hasMany(GroupInvitation::class, 'invited_by');
    }

    /**
     * Get group invitations received by this user.
     */
    public function receivedGroupInvitations()
    {
        return $this->hasMany(GroupInvitation::class, 'invited_user');
    }

    /**
     * Get projects where this user is a member.
     */
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_user')
            ->withPivot([
                'role', 'hourly_rate', 'can_log_time',
                'can_view_reports', 'can_manage_milestones',
                'joined_at', 'left_at'
            ])
            ->withTimestamps();
    }

    /**
     * Get projects managed by this user.
     */
    public function managedProjects()
    {
        return $this->hasMany(Project::class, 'project_manager_id');
    }

    /**
     * Get time logs created by this user.
     */
    public function timeLogs()
    {
        return $this->hasMany(TimeLog::class);
    }

    /**
     * Get time logs approved by this user.
     */
    public function approvedTimeLogs()
    {
        return $this->hasMany(TimeLog::class, 'approved_by');
    }

    /**
     * Get milestones assigned to this user.
     */
    public function assignedMilestones()
    {
        return $this->hasMany(Milestone::class, 'assigned_to');
    }

    /**
     * Scope to get only active users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get users by role.
     */
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope to search users by name or email.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    /**
     * Update last login timestamp.
     */
    public function updateLastLogin()
    {
        $this->update(['last_login' => now()]);
    }

    /**
     * Get user's avatar URL.
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->full_name) . '&color=7F9CF5&background=EBF4FF';
    }

    /**
     * Get user's role badge color.
     */
    public function getRoleBadgeColorAttribute(): string
    {
        return match ($this->role) {
            'admin' => 'bg-red-100 text-red-800',
            'analyst' => 'bg-blue-100 text-blue-800',
            'investigator' => 'bg-purple-100 text-purple-800',
            'user' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get user's role display name.
     */
    public function getRoleDisplayNameAttribute(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN => 'Administrador',
            self::ROLE_MEDICAL => 'Médico',
            self::ROLE_EPS => 'Analista EPS',
            self::ROLE_SYSTEMS => 'Sistemas',
            'analyst' => 'Analista',
            'investigator' => 'Investigador',
            'user' => 'Usuario',
            default => 'Usuario',
        };
    }

    /**
     * Get role color scheme for our medical system
     */
    public function getRoleColorScheme(): array
    {
        return match($this->role) {
            self::ROLE_ADMIN => [
                'primary' => '#dc2626',
                'secondary' => '#ef4444',
                'light' => '#fef2f2',
                'lighter' => '#fee2e2',
                'name' => 'admin'
            ],
            self::ROLE_MEDICAL => [
                'primary' => '#2563eb',
                'secondary' => '#3b82f6',
                'light' => '#eff6ff',
                'lighter' => '#dbeafe',
                'name' => 'medical'
            ],
            self::ROLE_EPS => [
                'primary' => '#059669',
                'secondary' => '#10b981',
                'light' => '#f0fdf4',
                'lighter' => '#dcfce7',
                'name' => 'eps'
            ],
            self::ROLE_SYSTEMS => [
                'primary' => '#ea580c',
                'secondary' => '#f97316',
                'light' => '#fff7ed',
                'lighter' => '#fed7aa',
                'name' => 'systems'
            ],
            default => [
                'primary' => '#6b7280',
                'secondary' => '#9ca3af',
                'light' => '#f9fafb',
                'lighter' => '#f3f4f6',
                'name' => 'default'
            ]
        };
    }

    /**
     * Get all available roles for our medical system
     */
    public static function getAvailableRoles(): array
    {
        return [
            self::ROLE_ADMIN => 'Administrador',
            self::ROLE_MEDICAL => 'Médico',
            self::ROLE_EPS => 'Analista EPS',
            self::ROLE_SYSTEMS => 'Sistemas',
        ];
    }

    /**
     * Check if user has permission to perform action.
     */
    public function hasPermission(string $permission): bool
    {
        $permissions = [
            'admin' => [
                'manage_users',
                'manage_evidences',
                'manage_files',
                'manage_groups',
                'view_analytics',
                'manage_settings',
                'delete_any',
                'assign_evidences'
            ],
            'analyst' => [
                'manage_evidences',
                'view_analytics',
                'evaluate_evidences',
                'assign_evidences'
            ],
            'investigator' => [
                'manage_evidences',
                'evaluate_evidences'
            ],
            'user' => [
                'create_evidences',
                'manage_own_files'
            ]
        ];

        return in_array($permission, $permissions[$this->role] ?? []);
    }

    /**
     * Get user statistics.
     */
    public function getStatsAttribute(): array
    {
        return [
            'files_count' => $this->files()->count(),
            'evidences_count' => $this->evidences()->count(),
            'assigned_evidences_count' => $this->assignedEvidences()->count(),
            'groups_count' => $this->groups()->count(),
            'pending_evidences_count' => $this->assignedEvidences()->where('status', 'pending')->count(),
        ];
    }

}
