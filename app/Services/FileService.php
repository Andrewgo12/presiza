<?php

namespace App\Services;

use App\Models\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class FileService
{
    /**
     * Upload and store a file.
     */
    public function uploadFile(UploadedFile $uploadedFile, array $metadata = []): File
    {
        // Generate unique filename
        $filename = Str::uuid() . '.' . $uploadedFile->getClientOriginalExtension();
        
        // Store the file
        $path = $uploadedFile->storeAs('files', $filename, 'public');
        
        // Determine category based on MIME type
        $category = $this->determineCategoryFromMimeType($uploadedFile->getMimeType());
        
        // Create file record
        $file = File::create([
            'filename' => $filename,
            'original_name' => $uploadedFile->getClientOriginalName(),
            'path' => $path,
            'disk' => 'public',
            'size' => $uploadedFile->getSize(),
            'mime_type' => $uploadedFile->getMimeType(),
            'extension' => $uploadedFile->getClientOriginalExtension(),
            'category' => $category,
            'uploaded_by' => auth()->id(),
            'access_level' => $metadata['access_level'] ?? 'internal',
            'description' => $metadata['description'] ?? null,
            'tags' => $metadata['tags'] ?? null,
        ]);
        
        // Generate thumbnail for images
        if ($category === 'image') {
            $this->generateThumbnail($file);
        }
        
        return $file;
    }
    
    /**
     * Generate thumbnail for image files.
     */
    protected function generateThumbnail(File $file): void
    {
        try {
            $thumbnailPath = 'thumbnails/' . pathinfo($file->filename, PATHINFO_FILENAME) . '_thumb.jpg';
            
            $image = Image::make(Storage::disk('public')->path($file->path));
            $image->fit(300, 300, function ($constraint) {
                $constraint->upsize();
            });
            
            Storage::disk('public')->put($thumbnailPath, $image->encode('jpg', 80));
            
            $file->update(['thumbnail_path' => $thumbnailPath]);
        } catch (\Exception $e) {
            // Log error but don't fail the upload
            \Log::warning('Failed to generate thumbnail for file: ' . $file->id, ['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Determine file category from MIME type.
     */
    protected function determineCategoryFromMimeType(string $mimeType): string
    {
        $categoryMap = [
            'image' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'],
            'video' => ['video/mp4', 'video/avi', 'video/mov', 'video/wmv', 'video/flv'],
            'audio' => ['audio/mp3', 'audio/wav', 'audio/ogg', 'audio/m4a'],
            'document' => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'text/plain',
                'text/csv',
            ],
            'archive' => ['application/zip', 'application/x-rar-compressed', 'application/x-7z-compressed'],
        ];
        
        foreach ($categoryMap as $category => $mimeTypes) {
            if (in_array($mimeType, $mimeTypes)) {
                return $category;
            }
        }
        
        return 'other';
    }
    
    /**
     * Delete a file and its associated resources.
     */
    public function deleteFile(File $file): bool
    {
        try {
            // Delete physical file
            if (Storage::disk($file->disk)->exists($file->path)) {
                Storage::disk($file->disk)->delete($file->path);
            }
            
            // Delete thumbnail if exists
            if ($file->thumbnail_path && Storage::disk($file->disk)->exists($file->thumbnail_path)) {
                Storage::disk($file->disk)->delete($file->thumbnail_path);
            }
            
            // Delete database record
            $file->delete();
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to delete file: ' . $file->id, ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    /**
     * Get file download response.
     */
    public function getDownloadResponse(File $file)
    {
        if (!Storage::disk($file->disk)->exists($file->path)) {
            abort(404, 'File not found');
        }
        
        // Increment download count
        $file->increment('download_count');
        
        return Storage::disk($file->disk)->download($file->path, $file->original_name);
    }
    
    /**
     * Get file preview response.
     */
    public function getPreviewResponse(File $file)
    {
        if (!Storage::disk($file->disk)->exists($file->path)) {
            abort(404, 'File not found');
        }
        
        // Increment view count
        $file->increment('view_count');
        
        $filePath = Storage::disk($file->disk)->path($file->path);
        
        return response()->file($filePath, [
            'Content-Type' => $file->mime_type,
            'Content-Disposition' => 'inline; filename="' . $file->original_name . '"',
        ]);
    }
    
    /**
     * Clean up expired files.
     */
    public function cleanupExpiredFiles(): int
    {
        $expiredFiles = File::where('expires_at', '<', now())
            ->whereNotNull('expires_at')
            ->get();
        
        $deletedCount = 0;
        
        foreach ($expiredFiles as $file) {
            if ($this->deleteFile($file)) {
                $deletedCount++;
            }
        }
        
        return $deletedCount;
    }
    
    /**
     * Get storage statistics.
     */
    public function getStorageStats(): array
    {
        $totalFiles = File::count();
        $totalSize = File::sum('size');
        $sizeByCategory = File::selectRaw('category, SUM(size) as total_size')
            ->groupBy('category')
            ->pluck('total_size', 'category')
            ->toArray();
        
        return [
            'total_files' => $totalFiles,
            'total_size' => $totalSize,
            'total_size_formatted' => $this->formatBytes($totalSize),
            'size_by_category' => $sizeByCategory,
            'average_file_size' => $totalFiles > 0 ? round($totalSize / $totalFiles) : 0,
        ];
    }
    
    /**
     * Format bytes to human readable format.
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes == 0) return '0 Bytes';
        
        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes) / log($k));
        
        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }
    
    /**
     * Validate file upload.
     */
    public function validateUpload(UploadedFile $file): array
    {
        $errors = [];
        
        // Check file size (max 50MB)
        $maxSize = 50 * 1024 * 1024;
        if ($file->getSize() > $maxSize) {
            $errors[] = 'El archivo es demasiado grande. Máximo permitido: 50MB';
        }
        
        // Check file type
        $allowedMimeTypes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'application/pdf', 'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain', 'text/csv',
            'application/zip', 'application/x-rar-compressed',
            'video/mp4', 'audio/mp3',
        ];
        
        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            $errors[] = 'Tipo de archivo no permitido: ' . $file->getMimeType();
        }
        
        // Check filename
        if (strlen($file->getClientOriginalName()) > 255) {
            $errors[] = 'El nombre del archivo es demasiado largo';
        }
        
        return $errors;
    }
    
    /**
     * Duplicate a file.
     */
    public function duplicateFile(File $originalFile): File
    {
        // Generate new filename
        $newFilename = Str::uuid() . '.' . $originalFile->extension;
        $newPath = 'files/' . $newFilename;
        
        // Copy physical file
        Storage::disk('public')->copy($originalFile->path, $newPath);
        
        // Create new file record
        $newFile = File::create([
            'filename' => $newFilename,
            'original_name' => 'Copia de ' . $originalFile->original_name,
            'path' => $newPath,
            'disk' => $originalFile->disk,
            'size' => $originalFile->size,
            'mime_type' => $originalFile->mime_type,
            'extension' => $originalFile->extension,
            'category' => $originalFile->category,
            'uploaded_by' => auth()->id(),
            'access_level' => $originalFile->access_level,
            'description' => $originalFile->description,
            'tags' => $originalFile->tags,
        ]);
        
        // Copy thumbnail if exists
        if ($originalFile->thumbnail_path) {
            $newThumbnailPath = 'thumbnails/' . pathinfo($newFilename, PATHINFO_FILENAME) . '_thumb.jpg';
            Storage::disk('public')->copy($originalFile->thumbnail_path, $newThumbnailPath);
            $newFile->update(['thumbnail_path' => $newThumbnailPath]);
        }
        
        return $newFile;
    }
}
