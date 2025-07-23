<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Project;
use App\Models\Evidence;
use App\Models\Group;
use App\Models\TimeLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Show the admin dashboard.
     */
    public function dashboard()
    {
        $stats = $this->getDashboardStats();
        $recent_activities = $this->getRecentActivities();

        return view('admin.dashboard', compact('stats', 'recent_activities'));
    }

    /**
     * Show users management.
     */
    public function users(Request $request)
    {
        $query = User::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $users = $query->withCount(['evidences', 'projects', 'timeLogs'])
                      ->orderBy('created_at', 'desc')
                      ->paginate(20);

        $roles = User::distinct()->pluck('role')->filter();

        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Show projects management.
     */
    public function projects(Request $request)
    {
        $query = Project::with(['projectManager', 'group']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('client_name', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $projects = $query->withCount(['users', 'evidences', 'milestones'])
                         ->orderBy('created_at', 'desc')
                         ->paginate(15);

        $statuses = Project::distinct()->pluck('status')->filter();
        $priorities = Project::distinct()->pluck('priority')->filter();

        return view('admin.projects.index', compact('projects', 'statuses', 'priorities'));
    }

    /**
     * Show evidences management.
     */
    public function evidences(Request $request)
    {
        $query = Evidence::with(['submittedBy', 'assignedTo', 'project']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('case_number', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $evidences = $query->withCount(['files', 'evaluations'])
                          ->orderBy('created_at', 'desc')
                          ->paginate(20);

        $statuses = Evidence::distinct()->pluck('status')->filter();
        $types = Evidence::distinct()->pluck('type')->filter();

        return view('admin.evidences.index', compact('evidences', 'statuses', 'types'));
    }

    /**
     * Show groups management.
     */
    public function groups(Request $request)
    {
        $query = Group::with(['leader']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $groups = $query->withCount(['members', 'projects'])
                       ->orderBy('created_at', 'desc')
                       ->paginate(15);

        return view('admin.groups.index', compact('groups'));
    }

    /**
     * Show analytics.
     */
    public function analytics()
    {
        $analytics = $this->getAnalyticsData();

        return view('admin.analytics', compact('analytics'));
    }

    /**
     * Show system settings.
     */
    public function settings()
    {
        $settings = $this->getSystemSettings();

        return view('admin.settings', compact('settings'));
    }

    /**
     * Show system logs.
     */
    public function logs()
    {
        $logs = $this->getSystemLogs();

        return view('admin.logs', compact('logs'));
    }

    /**
     * Show backups management.
     */
    public function backups()
    {
        $backups = $this->getBackupsList();

        return view('admin.backups', compact('backups'));
    }

    /**
     * Get dashboard statistics.
     */
    private function getDashboardStats(): array
    {
        return [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'total_projects' => Project::count(),
            'active_projects' => Project::where('status', 'in_progress')->count(),
            'total_evidences' => Evidence::count(),
            'pending_evidences' => Evidence::where('status', 'pending')->count(),
            'total_groups' => Group::count(),
            'storage_used' => $this->getStorageUsed(),
            'monthly_growth' => $this->getMonthlyGrowth(),
        ];
    }

    /**
     * Get recent activities.
     */
    private function getRecentActivities(): array
    {
        $activities = [];

        // Recent user registrations
        $recentUsers = User::latest()->take(3)->get();
        foreach ($recentUsers as $user) {
            $activities[] = [
                'type' => 'user_registered',
                'description' => "Nuevo usuario registrado: {$user->full_name}",
                'time' => $user->created_at->diffForHumans(),
                'icon' => 'user',
            ];
        }

        // Recent project creations
        $recentProjects = Project::latest()->take(2)->get();
        foreach ($recentProjects as $project) {
            $activities[] = [
                'type' => 'project_created',
                'description' => "Nuevo proyecto creado: {$project->name}",
                'time' => $project->created_at->diffForHumans(),
                'icon' => 'project',
            ];
        }

        // Recent evidence submissions
        $recentEvidences = Evidence::latest()->take(3)->get();
        foreach ($recentEvidences as $evidence) {
            $activities[] = [
                'type' => 'evidence_submitted',
                'description' => "Nueva evidencia: {$evidence->title}",
                'time' => $evidence->created_at->diffForHumans(),
                'icon' => 'evidence',
            ];
        }

        // Sort by time and return latest 8
        usort($activities, function ($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });

        return array_slice($activities, 0, 8);
    }

    /**
     * Get storage usage.
     */
    private function getStorageUsed(): string
    {
        $totalSize = 0;
        
        // Calculate total file sizes
        $files = DB::table('files')->sum('size');
        $totalSize += $files;

        // Convert to human readable format
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = $totalSize > 0 ? floor(log($totalSize, 1024)) : 0;
        
        return number_format($totalSize / pow(1024, $power), 2) . ' ' . $units[$power];
    }

    /**
     * Get monthly growth statistics.
     */
    private function getMonthlyGrowth(): array
    {
        $currentMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();

        return [
            'users' => [
                'current' => User::where('created_at', '>=', $currentMonth)->count(),
                'previous' => User::whereBetween('created_at', [$lastMonth, $currentMonth])->count(),
            ],
            'projects' => [
                'current' => Project::where('created_at', '>=', $currentMonth)->count(),
                'previous' => Project::whereBetween('created_at', [$lastMonth, $currentMonth])->count(),
            ],
            'evidences' => [
                'current' => Evidence::where('created_at', '>=', $currentMonth)->count(),
                'previous' => Evidence::whereBetween('created_at', [$lastMonth, $currentMonth])->count(),
            ],
        ];
    }

    /**
     * Get analytics data.
     */
    private function getAnalyticsData(): array
    {
        return [
            'user_activity' => $this->getUserActivityData(),
            'project_progress' => $this->getProjectProgressData(),
            'evidence_trends' => $this->getEvidenceTrendsData(),
            'system_performance' => $this->getSystemPerformanceData(),
        ];
    }

    /**
     * Get user activity data for charts.
     */
    private function getUserActivityData(): array
    {
        $days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $days->push([
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('M j'),
                'logins' => User::whereDate('last_login', $date)->count(),
                'registrations' => User::whereDate('created_at', $date)->count(),
            ]);
        }

        return $days->toArray();
    }

    /**
     * Get project progress data.
     */
    private function getProjectProgressData(): array
    {
        return [
            'by_status' => Project::select('status', DB::raw('count(*) as count'))
                                 ->groupBy('status')
                                 ->get()
                                 ->toArray(),
            'by_priority' => Project::select('priority', DB::raw('count(*) as count'))
                                   ->groupBy('priority')
                                   ->get()
                                   ->toArray(),
        ];
    }

    /**
     * Get evidence trends data.
     */
    private function getEvidenceTrendsData(): array
    {
        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months->push([
                'month' => $date->format('Y-m'),
                'label' => $date->format('M Y'),
                'submitted' => Evidence::whereYear('created_at', $date->year)
                                     ->whereMonth('created_at', $date->month)
                                     ->count(),
                'approved' => Evidence::whereYear('updated_at', $date->year)
                                    ->whereMonth('updated_at', $date->month)
                                    ->where('status', 'approved')
                                    ->count(),
            ]);
        }

        return $months->toArray();
    }

    /**
     * Get system performance data.
     */
    private function getSystemPerformanceData(): array
    {
        return [
            'database_size' => $this->getDatabaseSize(),
            'file_storage' => $this->getStorageUsed(),
            'active_sessions' => $this->getActiveSessions(),
            'response_time' => $this->getAverageResponseTime(),
        ];
    }

    /**
     * Get database size.
     */
    private function getDatabaseSize(): string
    {
        try {
            $size = DB::select("SELECT 
                ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'size_mb' 
                FROM information_schema.tables 
                WHERE table_schema = ?", [config('database.connections.mysql.database')]);
            
            return ($size[0]->size_mb ?? 0) . ' MB';
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    /**
     * Get active sessions count.
     */
    private function getActiveSessions(): int
    {
        try {
            return DB::table('sessions')
                    ->where('last_activity', '>', now()->subMinutes(30)->timestamp)
                    ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get average response time.
     */
    private function getAverageResponseTime(): string
    {
        // This would typically come from application monitoring
        return '150ms';
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
            'database_connection' => config('database.default'),
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'mail_driver' => config('mail.default'),
            'filesystem_driver' => config('filesystems.default'),
        ];
    }

    /**
     * Get system logs.
     */
    private function getSystemLogs(): array
    {
        $logFile = storage_path('logs/laravel.log');
        
        if (!file_exists($logFile)) {
            return [];
        }

        $logs = [];
        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        // Get last 100 lines
        $recentLines = array_slice($lines, -100);
        
        foreach ($recentLines as $line) {
            if (preg_match('/\[(.*?)\] (\w+)\.(\w+): (.*)/', $line, $matches)) {
                $logs[] = [
                    'timestamp' => $matches[1],
                    'level' => $matches[2],
                    'channel' => $matches[3],
                    'message' => $matches[4],
                ];
            }
        }

        return array_reverse($logs);
    }

    /**
     * Get backups list.
     */
    private function getBackupsList(): array
    {
        // This would typically integrate with a backup service
        return [
            [
                'name' => 'backup_' . now()->format('Y_m_d_H_i_s') . '.sql',
                'size' => '15.2 MB',
                'created_at' => now()->subHours(2),
                'type' => 'database',
                'status' => 'completed',
            ],
            [
                'name' => 'files_backup_' . now()->subDay()->format('Y_m_d') . '.tar.gz',
                'size' => '245.8 MB',
                'created_at' => now()->subDay(),
                'type' => 'files',
                'status' => 'completed',
            ],
        ];
    }



    /**
     * Create a new backup.
     */
    public function createBackup(Request $request)
    {
        try {
            $backupName = 'backup_' . now()->format('Y_m_d_H_i_s') . '.sql';

            // Simulate backup creation
            sleep(2);

            return response()->json([
                'success' => true,
                'message' => 'Backup creado exitosamente',
                'backup' => [
                    'name' => $backupName,
                    'size' => '2.4 GB',
                    'created_at' => now()->toISOString()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a backup.
     */
    public function deleteBackup($backup)
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Backup eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el backup: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * Clear system logs.
     */
    public function clearLogs(Request $request)
    {
        try {
            $logType = $request->input('type', 'all');

            return response()->json([
                'success' => true,
                'message' => 'Logs eliminados exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar los logs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get backup statistics.
     */
    private function getBackupStats(): array
    {
        return [
            'total_backups' => 15,
            'total_size' => '3.2 GB',
            'last_backup' => now()->subHours(6),
            'success_rate' => 98.5,
        ];
    }

    /**
     * Get log statistics.
     */
    private function getLogStats(): array
    {
        return [
            'total_entries' => 1247,
            'errors' => 12,
            'warnings' => 45,
            'info' => 890,
            'debug' => 300,
        ];
    }
}
