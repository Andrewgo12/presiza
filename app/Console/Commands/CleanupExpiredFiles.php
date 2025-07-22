<?php

namespace App\Console\Commands;

use App\Models\File;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupExpiredFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'files:cleanup-expired {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired files from storage and database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting cleanup of expired files...');

        $expiredFiles = File::where('expires_at', '<', now())
            ->whereNotNull('expires_at')
            ->get();

        if ($expiredFiles->isEmpty()) {
            $this->info('No expired files found.');
            return 0;
        }

        $this->info("Found {$expiredFiles->count()} expired files.");

        if ($this->option('dry-run')) {
            $this->warn('DRY RUN MODE - No files will be deleted');
            $this->table(
                ['ID', 'Name', 'Size', 'Expired At', 'Uploader'],
                $expiredFiles->map(function ($file) {
                    return [
                        $file->id,
                        $file->original_name,
                        $file->size_formatted,
                        $file->expires_at->format('Y-m-d H:i:s'),
                        $file->uploader->full_name ?? 'Unknown',
                    ];
                })->toArray()
            );
            return 0;
        }

        $deletedCount = 0;
        $failedCount = 0;

        foreach ($expiredFiles as $file) {
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
                $file->forceDelete();

                $deletedCount++;
                $this->line("✓ Deleted: {$file->original_name}");
            } catch (\Exception $e) {
                $failedCount++;
                $this->error("✗ Failed to delete {$file->original_name}: {$e->getMessage()}");
            }
        }

        $this->info("Cleanup completed:");
        $this->info("- Deleted: {$deletedCount} files");
        if ($failedCount > 0) {
            $this->warn("- Failed: {$failedCount} files");
        }

        return 0;
    }
}
