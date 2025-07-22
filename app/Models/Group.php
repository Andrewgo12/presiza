<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'type',
        'created_by',
        'is_active',
        'settings',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    /**
     * Get the user who created this group.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the members of this group.
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'group_members')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    /**
     * Get the admins of this group.
     */
    public function admins()
    {
        return $this->members()->wherePivot('role', 'admin');
    }

    /**
     * Get the moderators of this group.
     */
    public function moderators()
    {
        return $this->members()->wherePivot('role', 'moderator');
    }

    /**
     * Get messages sent to this group.
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get the group's type badge color.
     */
    public function getTypeBadgeColorAttribute(): string
    {
        return match ($this->type) {
            'public' => 'bg-green-100 text-green-800',
            'private' => 'bg-blue-100 text-blue-800',
            'restricted' => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get the type display name.
     */
    public function getTypeDisplayNameAttribute(): string
    {
        return match ($this->type) {
            'public' => 'Público',
            'private' => 'Privado',
            'restricted' => 'Restringido',
            default => ucfirst($this->type),
        };
    }

    /**
     * Get the group's member count.
     */
    public function getMemberCountAttribute(): int
    {
        return $this->members()->count();
    }

    /**
     * Check if user is a member of this group.
     */
    public function hasMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if user is an admin of this group.
     */
    public function isAdmin(User $user): bool
    {
        return $this->members()
            ->where('user_id', $user->id)
            ->wherePivot('role', 'admin')
            ->exists();
    }

    /**
     * Check if user is a moderator of this group.
     */
    public function isModerator(User $user): bool
    {
        return $this->members()
            ->where('user_id', $user->id)
            ->wherePivot('role', 'moderator')
            ->exists();
    }

    /**
     * Check if user can manage this group.
     */
    public function canManage(User $user): bool
    {
        return $this->created_by === $user->id || $this->isAdmin($user) || $user->isAdmin();
    }

    /**
     * Add a member to the group.
     */
    public function addMember(User $user, string $role = 'member'): void
    {
        if (!$this->hasMember($user)) {
            $this->members()->attach($user->id, [
                'role' => $role,
                'joined_at' => now(),
            ]);
        }
    }

    /**
     * Remove a member from the group.
     */
    public function removeMember(User $user): void
    {
        $this->members()->detach($user->id);
    }

    /**
     * Update member role.
     */
    public function updateMemberRole(User $user, string $role): void
    {
        $this->members()->updateExistingPivot($user->id, ['role' => $role]);
    }

    /**
     * Scope to get active groups.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get groups by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get public groups.
     */
    public function scopePublic($query)
    {
        return $query->where('type', 'public');
    }

    /**
     * Scope to search groups.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Scope to get groups where user is a member.
     */
    public function scopeWhereUserIsMember($query, $userId)
    {
        return $query->whereHas('members', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    /**
     * Scope to get groups created by user.
     */
    public function scopeCreatedBy($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Get group statistics.
     */
    public function getStatsAttribute(): array
    {
        return [
            'member_count' => $this->member_count,
            'admin_count' => $this->admins()->count(),
            'moderator_count' => $this->moderators()->count(),
            'message_count' => $this->messages()->count(),
            'recent_messages' => $this->messages()->where('created_at', '>=', now()->subDays(7))->count(),
        ];
    }
}
