<?php

namespace App\Console\Commands;

use App\Models\Evidence;
use App\Models\File;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateSystemReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:report {--format=table : Output format (table, json, csv)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a comprehensive system report';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating system report...');

        $stats = $this->gatherStatistics();
        $format = $this->option('format');

        switch ($format) {
            case 'json':
                $this->outputJson($stats);
                break;
            case 'csv':
                $this->outputCsv($stats);
                break;
            default:
                $this->outputTable($stats);
                break;
        }

        return 0;
    }

    private function gatherStatistics(): array
    {
        return [
            'users' => [
                'total' => User::count(),
                'active' => User::where('is_active', true)->count(),
                'inactive' => User::where('is_active', false)->count(),
                'admins' => User::where('role', 'admin')->count(),
                'analysts' => User::where('role', 'analyst')->count(),
                'investigators' => User::where('role', 'investigator')->count(),
                'regular_users' => User::where('role', 'user')->count(),
                'recent_logins' => User::where('last_login', '>=', now()->subDays(7))->count(),
            ],
            'files' => [
                'total' => File::count(),
                'total_size' => File::sum('size'),
                'public' => File::where('is_public', true)->count(),
                'private' => File::where('is_public', false)->count(),
                'by_category' => [
                    'documents' => File::where('category', 'document')->count(),
                    'images' => File::where('category', 'image')->count(),
                    'videos' => File::where('category', 'video')->count(),
                    'audio' => File::where('category', 'audio')->count(),
                    'archives' => File::where('category', 'archive')->count(),
                    'other' => File::where('category', 'other')->count(),
                ],
                'by_access_level' => [
                    'public' => File::where('access_level', 'public')->count(),
                    'internal' => File::where('access_level', 'internal')->count(),
                    'restricted' => File::where('access_level', 'restricted')->count(),
                    'confidential' => File::where('access_level', 'confidential')->count(),
                ],
                'recent_uploads' => File::where('created_at', '>=', now()->subDays(7))->count(),
                'expired' => File::where('expires_at', '<', now())->whereNotNull('expires_at')->count(),
            ],
            'evidences' => [
                'total' => Evidence::count(),
                'by_status' => [
                    'pending' => Evidence::where('status', 'pending')->count(),
                    'under_review' => Evidence::where('status', 'under_review')->count(),
                    'approved' => Evidence::where('status', 'approved')->count(),
                    'rejected' => Evidence::where('status', 'rejected')->count(),
                    'archived' => Evidence::where('status', 'archived')->count(),
                ],
                'by_priority' => [
                    'low' => Evidence::where('priority', 'low')->count(),
                    'medium' => Evidence::where('priority', 'medium')->count(),
                    'high' => Evidence::where('priority', 'high')->count(),
                    'critical' => Evidence::where('priority', 'critical')->count(),
                ],
                'by_category' => [
                    'security' => Evidence::where('category', 'security')->count(),
                    'investigation' => Evidence::where('category', 'investigation')->count(),
                    'compliance' => Evidence::where('category', 'compliance')->count(),
                    'audit' => Evidence::where('category', 'audit')->count(),
                    'incident' => Evidence::where('category', 'incident')->count(),
                    'other' => Evidence::where('category', 'other')->count(),
                ],
                'assigned' => Evidence::whereNotNull('assigned_to')->count(),
                'unassigned' => Evidence::whereNull('assigned_to')->count(),
                'overdue' => Evidence::where('status', 'pending')
                    ->where('created_at', '<', now()->subDays(7))
                    ->count(),
                'recent' => Evidence::where('created_at', '>=', now()->subDays(7))->count(),
            ],
            'system' => [
                'storage_used' => $this->getStorageUsage(),
                'database_size' => $this->getDatabaseSize(),
                'uptime' => $this->getSystemUptime(),
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
            ],
        ];
    }

    private function outputTable(array $stats): void
    {
        $this->info('=== SYSTEM REPORT ===');
        $this->newLine();

        // Users
        $this->info('👥 USERS');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Users', $stats['users']['total']],
                ['Active Users', $stats['users']['active']],
                ['Inactive Users', $stats['users']['inactive']],
                ['Administrators', $stats['users']['admins']],
                ['Analysts', $stats['users']['analysts']],
                ['Investigators', $stats['users']['investigators']],
                ['Regular Users', $stats['users']['regular_users']],
                ['Recent Logins (7 days)', $stats['users']['recent_logins']],
            ]
        );

        // Files
        $this->info('📁 FILES');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Files', $stats['files']['total']],
                ['Total Size', $this->formatBytes($stats['files']['total_size'])],
                ['Public Files', $stats['files']['public']],
                ['Private Files', $stats['files']['private']],
                ['Documents', $stats['files']['by_category']['documents']],
                ['Images', $stats['files']['by_category']['images']],
                ['Videos', $stats['files']['by_category']['videos']],
                ['Recent Uploads (7 days)', $stats['files']['recent_uploads']],
                ['Expired Files', $stats['files']['expired']],
            ]
        );

        // Evidences
        $this->info('🔍 EVIDENCES');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Evidences', $stats['evidences']['total']],
                ['Pending', $stats['evidences']['by_status']['pending']],
                ['Under Review', $stats['evidences']['by_status']['under_review']],
                ['Approved', $stats['evidences']['by_status']['approved']],
                ['Critical Priority', $stats['evidences']['by_priority']['critical']],
                ['Security Category', $stats['evidences']['by_category']['security']],
                ['Assigned', $stats['evidences']['assigned']],
                ['Overdue', $stats['evidences']['overdue']],
                ['Recent (7 days)', $stats['evidences']['recent']],
            ]
        );

        // System
        $this->info('⚙️ SYSTEM');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Storage Used', $stats['system']['storage_used']],
                ['PHP Version', $stats['system']['php_version']],
                ['Laravel Version', $stats['system']['laravel_version']],
            ]
        );
    }

    private function outputJson(array $stats): void
    {
        $this->line(json_encode($stats, JSON_PRETTY_PRINT));
    }

    private function outputCsv(array $stats): void
    {
        $filename = 'system_report_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $path = storage_path('app/reports/' . $filename);

        // Ensure directory exists
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $handle = fopen($path, 'w');
        
        // Write headers
        fputcsv($handle, ['Category', 'Metric', 'Value']);

        // Write data
        foreach ($stats as $category => $data) {
            $this->writeCsvData($handle, $category, $data);
        }

        fclose($handle);

        $this->info("CSV report saved to: {$path}");
    }

    private function writeCsvData($handle, string $category, array $data, string $prefix = ''): void
    {
        foreach ($data as $key => $value) {
            $metric = $prefix ? "{$prefix}.{$key}" : $key;
            
            if (is_array($value)) {
                $this->writeCsvData($handle, $category, $value, $metric);
            } else {
                fputcsv($handle, [ucfirst($category), $metric, $value]);
            }
        }
    }

    private function getStorageUsage(): string
    {
        $totalSize = 0;
        $files = Storage::disk('public')->allFiles();
        
        foreach ($files as $file) {
            $totalSize += Storage::disk('public')->size($file);
        }

        return $this->formatBytes($totalSize);
    }

    private function getDatabaseSize(): string
    {
        // This is a simplified version - actual implementation would depend on database type
        return 'N/A';
    }

    private function getSystemUptime(): string
    {
        // This is a placeholder - actual implementation would depend on system
        return 'N/A';
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes == 0) return '0 Bytes';
        
        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes) / log($k));
        
        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }
}
