<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

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
        'size' => 'integer',
        'download_count' => 'integer',
        'view_count' => 'integer',
    ];

    /**
     * Get the user who uploaded this file.
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get evidences that use this file.
     */
    public function evidences()
    {
        return $this->belongsToMany(Evidence::class, 'evidence_files')
            ->withPivot('order')
            ->withTimestamps();
    }

    /**
     * Get the file's formatted size.
     */
    public function getSizeFormattedAttribute(): string
    {
        if ($this->size == 0) return '0 Bytes';
        
        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($this->size) / log($k));
        
        return round($this->size / pow($k, $i), 2) . ' ' . $sizes[$i];
    }

    /**
     * Get the file's URL.
     */
    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    /**
     * Get the file's thumbnail URL.
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->thumbnail_path) {
            return null;
        }

        return Storage::disk($this->disk)->url($this->thumbnail_path);
    }

    /**
     * Check if file can be previewed.
     */
    public function getCanPreviewAttribute(): bool
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
     * Get the file's preview URL.
     */
    public function getPreviewUrlAttribute(): ?string
    {
        if (!$this->can_preview) {
            return null;
        }

        return $this->url;
    }

    /**
     * Check if file is an image.
     */
    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Check if file is a document.
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
        ];

        return in_array($this->mime_type, $documentTypes);
    }

    /**
     * Check if file is a video.
     */
    public function getIsVideoAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'video/');
    }

    /**
     * Check if file is audio.
     */
    public function getIsAudioAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'audio/');
    }

    /**
     * Check if file is an archive.
     */
    public function getIsArchiveAttribute(): bool
    {
        $archiveTypes = [
            'application/zip',
            'application/x-rar-compressed',
            'application/x-tar',
            'application/gzip',
            'application/x-7z-compressed',
        ];

        return in_array($this->mime_type, $archiveTypes);
    }

    /**
     * Get file's category badge color.
     */
    public function getCategoryBadgeColorAttribute(): string
    {
        return match ($this->category) {
            'document' => 'bg-blue-100 text-blue-800',
            'image' => 'bg-green-100 text-green-800',
            'video' => 'bg-purple-100 text-purple-800',
            'audio' => 'bg-pink-100 text-pink-800',
            'archive' => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get access level badge color.
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
     * Check if file is expired.
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Scope to get files by category.
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to get public files.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope to get files by access level.
     */
    public function scopeByAccessLevel($query, $level)
    {
        return $query->where('access_level', $level);
    }

    /**
     * Scope to search files.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('original_name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Scope to get files uploaded in date range.
     */
    public function scopeUploadedBetween($query, $start, $end)
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }

    /**
     * Scope to get files by uploader.
     */
    public function scopeByUploader($query, $userId)
    {
        return $query->where('uploaded_by', $userId);
    }

    /**
     * Scope to get non-expired files.
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Increment download count.
     */
    public function incrementDownloadCount()
    {
        $this->increment('download_count');
    }

    /**
     * Increment view count.
     */
    public function incrementViewCount()
    {
        $this->increment('view_count');
    }

    /**
     * Delete file from storage.
     */
    public function deleteFromStorage(): bool
    {
        $deleted = true;

        // Delete main file
        if (Storage::disk($this->disk)->exists($this->path)) {
            $deleted = Storage::disk($this->disk)->delete($this->path);
        }

        // Delete thumbnail if exists
        if ($this->thumbnail_path && Storage::disk($this->disk)->exists($this->thumbnail_path)) {
            Storage::disk($this->disk)->delete($this->thumbnail_path);
        }

        return $deleted;
    }

    /**
     * Get file statistics.
     */
    public function getStatsAttribute(): array
    {
        return [
            'evidences_count' => $this->evidences()->count(),
            'total_downloads' => $this->download_count,
            'total_views' => $this->view_count,
            'is_popular' => $this->download_count > 10 || $this->view_count > 50,
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-delete file from storage when model is deleted
        static::deleting(function ($file) {
            $file->deleteFromStorage();
        });
    }
}
