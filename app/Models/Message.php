<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'subject',
        'content',
        'sender_id',
        'type',
        'group_id',
        'priority',
        'attachments',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'attachments' => 'array',
    ];

    /**
     * Get the user who sent this message.
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the group this message belongs to (if applicable).
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the recipients of this message.
     */
    public function recipients()
    {
        return $this->belongsToMany(User::class, 'message_recipients')
            ->withPivot('read_at')
            ->withTimestamps();
    }

    /**
     * Get unread recipients.
     */
    public function unreadRecipients()
    {
        return $this->recipients()->wherePivot('read_at', null);
    }

    /**
     * Get read recipients.
     */
    public function readRecipients()
    {
        return $this->recipients()->whereNotNull('message_recipients.read_at');
    }

    /**
     * Get the message's priority badge color.
     */
    public function getPriorityBadgeColorAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'bg-gray-100 text-gray-800',
            'normal' => 'bg-blue-100 text-blue-800',
            'high' => 'bg-orange-100 text-orange-800',
            'urgent' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get the priority display name.
     */
    public function getPriorityDisplayNameAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'Baja',
            'normal' => 'Normal',
            'high' => 'Alta',
            'urgent' => 'Urgente',
            default => ucfirst($this->priority),
        };
    }

    /**
     * Get the type display name.
     */
    public function getTypeDisplayNameAttribute(): string
    {
        return match ($this->type) {
            'direct' => 'Directo',
            'group' => 'Grupo',
            'system' => 'Sistema',
            default => ucfirst($this->type),
        };
    }

    /**
     * Get the message's excerpt.
     */
    public function getExcerptAttribute(): string
    {
        return \Str::limit(strip_tags($this->content), 100);
    }

    /**
     * Check if message has attachments.
     */
    public function getHasAttachmentsAttribute(): bool
    {
        return !empty($this->attachments);
    }

    /**
     * Get attachment count.
     */
    public function getAttachmentCountAttribute(): int
    {
        return count($this->attachments ?? []);
    }

    /**
     * Check if message is read by user.
     */
    public function isReadBy(User $user): bool
    {
        return $this->recipients()
            ->where('user_id', $user->id)
            ->whereNotNull('message_recipients.read_at')
            ->exists();
    }

    /**
     * Mark message as read by user.
     */
    public function markAsReadBy(User $user): void
    {
        $this->recipients()->updateExistingPivot($user->id, [
            'read_at' => now(),
        ]);
    }

    /**
     * Mark message as unread by user.
     */
    public function markAsUnreadBy(User $user): void
    {
        $this->recipients()->updateExistingPivot($user->id, [
            'read_at' => null,
        ]);
    }

    /**
     * Add recipient to message.
     */
    public function addRecipient(User $user): void
    {
        if (!$this->recipients()->where('user_id', $user->id)->exists()) {
            $this->recipients()->attach($user->id);
        }
    }

    /**
     * Add multiple recipients to message.
     */
    public function addRecipients(array $userIds): void
    {
        $existingIds = $this->recipients()->pluck('user_id')->toArray();
        $newIds = array_diff($userIds, $existingIds);
        
        if (!empty($newIds)) {
            $this->recipients()->attach($newIds);
        }
    }

    /**
     * Scope to get messages by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get messages by priority.
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope to get direct messages.
     */
    public function scopeDirect($query)
    {
        return $query->where('type', 'direct');
    }

    /**
     * Scope to get group messages.
     */
    public function scopeGroup($query)
    {
        return $query->where('type', 'group');
    }

    /**
     * Scope to get system messages.
     */
    public function scopeSystem($query)
    {
        return $query->where('type', 'system');
    }

    /**
     * Scope to get messages sent by user.
     */
    public function scopeSentBy($query, $userId)
    {
        return $query->where('sender_id', $userId);
    }

    /**
     * Scope to get messages received by user.
     */
    public function scopeReceivedBy($query, $userId)
    {
        return $query->whereHas('recipients', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    /**
     * Scope to get unread messages for user.
     */
    public function scopeUnreadBy($query, $userId)
    {
        return $query->whereHas('recipients', function ($q) use ($userId) {
            $q->where('user_id', $userId)->whereNull('read_at');
        });
    }

    /**
     * Scope to search messages.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('subject', 'like', "%{$search}%")
              ->orWhere('content', 'like', "%{$search}%");
        });
    }

    /**
     * Get message statistics.
     */
    public function getStatsAttribute(): array
    {
        return [
            'recipient_count' => $this->recipients()->count(),
            'read_count' => $this->readRecipients()->count(),
            'unread_count' => $this->unreadRecipients()->count(),
            'attachment_count' => $this->attachment_count,
            'read_percentage' => $this->recipients()->count() > 0 
                ? round(($this->readRecipients()->count() / $this->recipients()->count()) * 100, 1)
                : 0,
        ];
    }
}
