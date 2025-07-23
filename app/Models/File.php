<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class File extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'filename',
        'original_name',
        'path',
        'disk',
        'size',
        'mime_type',
        'extension',
        'category',
        'tags',
        'description',
        'uploaded_by',
        'is_public',
        'access_level',
        'download_count',
        'view_count',
        'thumbnail_path',
        'metadata',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tags' => 'array',
        'metadata' => 'array',
        'is_public' => 'boolean',
        'expires_at' => 'datetime',
        'download_count' => 'integer',
        'view_count' => 'integer',
        'size' => 'integer',
    ];

    /**
     * Get the user who uploaded this file.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the evidences that use this file.
     */
    public function evidences(): BelongsToMany
    {
        return $this->belongsToMany(Evidence::class, 'evidence_files');
    }

    /**
     * Get the messages that have this file attached.
     */
    public function messages(): MorphToMany
    {
        return $this->morphedByMany(Message::class, 'fileable', 'file_attachments');
    }

    /**
     * Get the projects that have this file attached.
     */
    public function projects(): MorphToMany
    {
        return $this->morphedByMany(Project::class, 'fileable', 'file_attachments');
    }

    /**
     * Check if the file is expired.
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if the file is an image.
     */
    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Check if the file is a document.
     */
    public function getIsDocumentAttribute(): bool
    {
        $documentTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
            'text/html',
            'text/css',
            'text/javascript',
            'application/json',
        ];

        return in_array($this->mime_type, $documentTypes);
    }

    /**
     * Check if the file is a video.
     */
    public function getIsVideoAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'video/');
    }

    /**
     * Check if the file is an audio file.
     */
    public function getIsAudioAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'audio/');
    }

    /**
     * Check if the file is an archive.
     */
    public function getIsArchiveAttribute(): bool
    {
        $archiveTypes = [
            'application/zip',
            'application/x-rar-compressed',
            'application/x-7z-compressed',
            'application/x-tar',
            'application/gzip',
        ];

        return in_array($this->mime_type, $archiveTypes);
    }

    /**
     * Get the file size in human readable format.
     */
    public function getSizeFormattedAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get the file URL.
     */
    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    /**
     * Get the download URL.
     */
    public function getDownloadUrlAttribute(): string
    {
        return route('files.download', $this);
    }

    /**
     * Get the preview URL if available.
     */
    public function getPreviewUrlAttribute(): ?string
    {
        if ($this->canPreview()) {
            return route('files.preview', $this);
        }
        
        return null;
    }

    /**
     * Check if the file can be previewed.
     */
    public function canPreview(): bool
    {
        $previewableTypes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'application/pdf',
            'text/plain', 'text/html', 'text/css', 'text/javascript',
            'application/json'
        ];
        
        return in_array($this->mime_type, $previewableTypes);
    }

    /**
     * Get the file icon based on type.
     */
    public function getIconAttribute(): string
    {
        if ($this->is_image) {
            return 'fas fa-image';
        } elseif ($this->is_video) {
            return 'fas fa-video';
        } elseif ($this->is_audio) {
            return 'fas fa-music';
        } elseif ($this->is_archive) {
            return 'fas fa-file-archive';
        } elseif ($this->mime_type === 'application/pdf') {
            return 'fas fa-file-pdf';
        } elseif (str_contains($this->mime_type, 'word')) {
            return 'fas fa-file-word';
        } elseif (str_contains($this->mime_type, 'excel') || str_contains($this->mime_type, 'spreadsheet')) {
            return 'fas fa-file-excel';
        } elseif (str_contains($this->mime_type, 'powerpoint') || str_contains($this->mime_type, 'presentation')) {
            return 'fas fa-file-powerpoint';
        } else {
            return 'fas fa-file';
        }
    }

    /**
     * Get the category badge color.
     */
    public function getCategoryBadgeColorAttribute(): string
    {
        return match ($this->category) {
            'document' => 'bg-blue-100 text-blue-800',
            'image' => 'bg-green-100 text-green-800',
            'video' => 'bg-purple-100 text-purple-800',
            'audio' => 'bg-yellow-100 text-yellow-800',
            'archive' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get the access level badge color.
     */
    public function getAccessLevelBadgeColorAttribute(): string
    {
        return match ($this->access_level) {
            'public' => 'bg-green-100 text-green-800',
            'internal' => 'bg-blue-100 text-blue-800',
            'restricted' => 'bg-yellow-100 text-yellow-800',
            'confidential' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Scope to filter by category.
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to filter by access level.
     */
    public function scopeAccessLevel($query, $level)
    {
        return $query->where('access_level', $level);
    }

    /**
     * Scope to filter public files.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope to filter private files.
     */
    public function scopePrivate($query)
    {
        return $query->where('is_public', false);
    }

    /**
     * Scope to filter non-expired files.
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope to filter expired files.
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
                    ->where('expires_at', '<=', now());
    }

    /**
     * Scope to search files.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('original_name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('filename', 'like', "%{$search}%");
        });
    }

    /**
     * Scope to filter by uploader.
     */
    public function scopeUploadedBy($query, $userId)
    {
        return $query->where('uploaded_by', $userId);
    }

    /**
     * Increment download count.
     */
    public function incrementDownloadCount(): void
    {
        $this->increment('download_count');
    }

    /**
     * Increment view count.
     */
    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    /**
     * Delete the physical file from storage.
     */
    public function deleteFromStorage(): bool
    {
        if (Storage::disk($this->disk)->exists($this->path)) {
            return Storage::disk($this->disk)->delete($this->path);
        }

        return true;
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Delete physical file when model is deleted
        static::deleting(function ($file) {
            $file->deleteFromStorage();
        });
    }
}
