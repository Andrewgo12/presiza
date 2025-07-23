<?php

namespace App\Jobs;

use App\Models\File;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProcessFileUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $file;

    /**
     * Create a new job instance.
     */
    public function __construct(File $file)
    {
        $this->file = $file;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info("Processing file upload for file ID: {$this->file->id}");

            // Generate thumbnail for images
            if ($this->file->is_image) {
                $this->generateThumbnail();
            }

            // Extract metadata
            $this->extractMetadata();

            // Scan for viruses (placeholder - would integrate with actual antivirus)
            $this->scanForViruses();

            // Update file processing status
            $this->file->update([
                'metadata' => array_merge($this->file->metadata ?? [], [
                    'processed_at' => now(),
                    'processing_status' => 'completed'
                ])
            ]);

            Log::info("File processing completed for file ID: {$this->file->id}");

        } catch (\Exception $e) {
            Log::error("File processing failed for file ID: {$this->file->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Update file with error status
            $this->file->update([
                'metadata' => array_merge($this->file->metadata ?? [], [
                    'processed_at' => now(),
                    'processing_status' => 'failed',
                    'processing_error' => $e->getMessage()
                ])
            ]);

            throw $e;
        }
    }

    /**
     * Generate thumbnail for image files.
     */
    protected function generateThumbnail(): void
    {
        if (!$this->file->is_image) {
            return;
        }

        try {
            // This is a placeholder - in a real application you would use
            // an image processing library like Intervention Image
            $thumbnailPath = 'thumbnails/' . pathinfo($this->file->path, PATHINFO_FILENAME) . '_thumb.jpg';
            
            // For now, just copy the original file as thumbnail
            // In production, you would resize the image
            if (Storage::disk($this->file->disk)->exists($this->file->path)) {
                Storage::disk($this->file->disk)->copy($this->file->path, $thumbnailPath);
                
                $this->file->update([
                    'thumbnail_path' => $thumbnailPath
                ]);
            }

        } catch (\Exception $e) {
            Log::warning("Thumbnail generation failed for file ID: {$this->file->id}", [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Extract file metadata.
     */
    protected function extractMetadata(): void
    {
        try {
            $metadata = $this->file->metadata ?? [];

            // Get file info
            if (Storage::disk($this->file->disk)->exists($this->file->path)) {
                $filePath = Storage::disk($this->file->disk)->path($this->file->path);
                
                // Basic file info
                $metadata['file_info'] = [
                    'size' => $this->file->size,
                    'mime_type' => $this->file->mime_type,
                    'extension' => $this->file->extension,
                    'created_at' => $this->file->created_at->toISOString(),
                ];

                // For images, extract EXIF data
                if ($this->file->is_image && function_exists('exif_read_data')) {
                    $exifData = @exif_read_data($filePath);
                    if ($exifData) {
                        $metadata['exif'] = array_filter($exifData, function($key) {
                            // Only keep safe EXIF data
                            return in_array($key, [
                                'DateTime', 'Make', 'Model', 'Software',
                                'ImageWidth', 'ImageLength', 'ColorSpace'
                            ]);
                        }, ARRAY_FILTER_USE_KEY);
                    }
                }

                // Calculate file hash for integrity checking
                $metadata['hash'] = [
                    'md5' => md5_file($filePath),
                    'sha256' => hash_file('sha256', $filePath),
                ];
            }

            $this->file->update(['metadata' => $metadata]);

        } catch (\Exception $e) {
            Log::warning("Metadata extraction failed for file ID: {$this->file->id}", [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Scan file for viruses (placeholder).
     */
    protected function scanForViruses(): void
    {
        try {
            // This is a placeholder for virus scanning
            // In production, you would integrate with ClamAV or similar
            
            $metadata = $this->file->metadata ?? [];
            $metadata['virus_scan'] = [
                'scanned_at' => now()->toISOString(),
                'status' => 'clean',
                'scanner' => 'placeholder',
            ];

            $this->file->update(['metadata' => $metadata]);

        } catch (\Exception $e) {
            Log::warning("Virus scan failed for file ID: {$this->file->id}", [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("ProcessFileUpload job failed for file ID: {$this->file->id}", [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);

        // Update file with failed status
        $this->file->update([
            'metadata' => array_merge($this->file->metadata ?? [], [
                'processed_at' => now(),
                'processing_status' => 'failed',
                'processing_error' => $exception->getMessage()
            ])
        ]);
    }
}
