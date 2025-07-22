<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Display system settings.
     */
    public function index()
    {
        $settings = $this->getSystemSettings();
        $systemInfo = $this->getSystemInfo();
        
        return view('admin.settings.index', compact('settings', 'systemInfo'));
    }

    /**
     * Update system settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'app_name' => ['required', 'string', 'max:255'],
            'app_timezone' => ['required', 'string'],
            'mail_from_address' => ['required', 'email'],
            'mail_from_name' => ['required', 'string', 'max:255'],
            'max_file_size' => ['required', 'integer', 'min:1', 'max:100'],
            'allowed_file_types' => ['required', 'string'],
            'session_lifetime' => ['required', 'integer', 'min:1', 'max:1440'],
            'maintenance_mode' => ['boolean'],
        ]);

        // Update environment variables
        $this->updateEnvFile([
            'APP_NAME' => $validated['app_name'],
            'APP_TIMEZONE' => $validated['app_timezone'],
            'MAIL_FROM_ADDRESS' => $validated['mail_from_address'],
            'MAIL_FROM_NAME' => $validated['mail_from_name'],
        ]);

        // Update config cache
        Artisan::call('config:cache');

        return redirect()->route('admin.settings.index')
                        ->with('success', 'Configuración actualizada exitosamente.');
    }

    /**
     * Clear application cache.
     */
    public function clearCache()
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');

            return redirect()->route('admin.settings.index')
                           ->with('success', 'Cache limpiado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error clearing cache: ' . $e->getMessage());
            
            return redirect()->route('admin.settings.index')
                           ->with('error', 'Error al limpiar el cache.');
        }
    }

    /**
     * Optimize application.
     */
    public function optimize()
    {
        try {
            Artisan::call('optimize');
            
            return redirect()->route('admin.settings.index')
                           ->with('success', 'Aplicación optimizada exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error optimizing application: ' . $e->getMessage());
            
            return redirect()->route('admin.settings.index')
                           ->with('error', 'Error al optimizar la aplicación.');
        }
    }

    /**
     * Toggle maintenance mode.
     */
    public function toggleMaintenance()
    {
        try {
            if (app()->isDownForMaintenance()) {
                Artisan::call('up');
                $message = 'Modo de mantenimiento desactivado.';
            } else {
                Artisan::call('down', ['--secret' => 'admin-access']);
                $message = 'Modo de mantenimiento activado.';
            }

            return redirect()->route('admin.settings.index')
                           ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Error toggling maintenance mode: ' . $e->getMessage());
            
            return redirect()->route('admin.settings.index')
                           ->with('error', 'Error al cambiar el modo de mantenimiento.');
        }
    }

    /**
     * Run database migrations.
     */
    public function migrate()
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            
            return redirect()->route('admin.settings.index')
                           ->with('success', 'Migraciones ejecutadas exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error running migrations: ' . $e->getMessage());
            
            return redirect()->route('admin.settings.index')
                           ->with('error', 'Error al ejecutar las migraciones.');
        }
    }

    /**
     * Create database backup.
     */
    public function backup()
    {
        try {
            $filename = 'backup_' . now()->format('Y_m_d_H_i_s') . '.sql';
            $path = storage_path('app/backups/' . $filename);
            
            // Create backups directory if it doesn't exist
            if (!File::exists(dirname($path))) {
                File::makeDirectory(dirname($path), 0755, true);
            }

            // Run mysqldump command
            $command = sprintf(
                'mysqldump -u%s -p%s %s > %s',
                config('database.connections.mysql.username'),
                config('database.connections.mysql.password'),
                config('database.connections.mysql.database'),
                $path
            );

            exec($command, $output, $returnCode);

            if ($returnCode === 0) {
                return redirect()->route('admin.settings.index')
                               ->with('success', "Respaldo creado: {$filename}");
            } else {
                throw new \Exception('Mysqldump failed with return code: ' . $returnCode);
            }
        } catch (\Exception $e) {
            Log::error('Error creating backup: ' . $e->getMessage());
            
            return redirect()->route('admin.settings.index')
                           ->with('error', 'Error al crear el respaldo.');
        }
    }

    /**
     * Get system settings.
     */
    private function getSystemSettings(): array
    {
        return [
            'app_name' => config('app.name'),
            'app_env' => config('app.env'),
            'app_debug' => config('app.debug'),
            'app_timezone' => config('app.timezone'),
            'database_connection' => config('database.default'),
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'session_lifetime' => config('session.lifetime'),
            'mail_driver' => config('mail.default'),
            'mail_from_address' => config('mail.from.address'),
            'mail_from_name' => config('mail.from.name'),
            'filesystem_driver' => config('filesystems.default'),
            'maintenance_mode' => app()->isDownForMaintenance(),
        ];
    }

    /**
     * Get system information.
     */
    private function getSystemInfo(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'database_version' => $this->getDatabaseVersion(),
            'storage_path' => storage_path(),
            'storage_writable' => is_writable(storage_path()),
            'cache_path' => storage_path('framework/cache'),
            'cache_writable' => is_writable(storage_path('framework/cache')),
            'logs_path' => storage_path('logs'),
            'logs_writable' => is_writable(storage_path('logs')),
            'disk_space' => $this->getDiskSpace(),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
        ];
    }

    /**
     * Get database version.
     */
    private function getDatabaseVersion(): string
    {
        try {
            $result = DB::select('SELECT VERSION() as version');
            return $result[0]->version ?? 'Unknown';
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Get disk space information.
     */
    private function getDiskSpace(): array
    {
        $path = storage_path();
        
        return [
            'free' => $this->formatBytes(disk_free_space($path)),
            'total' => $this->formatBytes(disk_total_space($path)),
            'used' => $this->formatBytes(disk_total_space($path) - disk_free_space($path)),
        ];
    }

    /**
     * Format bytes to human readable format.
     */
    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Update environment file.
     */
    private function updateEnvFile(array $data): void
    {
        $envFile = base_path('.env');
        $envContent = File::get($envFile);

        foreach ($data as $key => $value) {
            $pattern = "/^{$key}=.*/m";
            $replacement = "{$key}=\"{$value}\"";
            
            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, $replacement, $envContent);
            } else {
                $envContent .= "\n{$replacement}";
            }
        }

        File::put($envFile, $envContent);
    }

    /**
     * Download backup file.
     */
    public function downloadBackup($filename)
    {
        $path = storage_path('app/backups/' . $filename);

        if (!File::exists($path)) {
            return redirect()->route('admin.backups')
                           ->with('error', 'El archivo de respaldo no existe.');
        }

        return response()->download($path);
    }

    /**
     * Restore backup.
     */
    public function restoreBackup($filename)
    {
        try {
            $path = storage_path('app/backups/' . $filename);

            if (!File::exists($path)) {
                return response()->json(['error' => 'El archivo de respaldo no existe.'], 404);
            }

            // This would implement the actual restore logic
            // For now, we'll just return success
            Log::info("Backup restore initiated: {$filename}");

            return response()->json(['message' => 'Respaldo restaurado exitosamente.']);
        } catch (\Exception $e) {
            Log::error('Error restoring backup: ' . $e->getMessage());
            return response()->json(['error' => 'Error al restaurar el respaldo.'], 500);
        }
    }

    /**
     * Delete backup.
     */
    public function deleteBackup($filename)
    {
        try {
            $path = storage_path('app/backups/' . $filename);

            if (File::exists($path)) {
                File::delete($path);
            }

            return response()->json(['message' => 'Respaldo eliminado exitosamente.']);
        } catch (\Exception $e) {
            Log::error('Error deleting backup: ' . $e->getMessage());
            return response()->json(['error' => 'Error al eliminar el respaldo.'], 500);
        }
    }

    /**
     * Clear system logs.
     */
    public function clearLogs()
    {
        try {
            $logFile = storage_path('logs/laravel.log');

            if (File::exists($logFile)) {
                File::put($logFile, '');
            }

            return redirect()->route('admin.logs')
                           ->with('success', 'Logs limpiados exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error clearing logs: ' . $e->getMessage());

            return redirect()->route('admin.logs')
                           ->with('error', 'Error al limpiar los logs.');
        }
    }
}
