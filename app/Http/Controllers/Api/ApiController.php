<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Project;
use App\Models\Evidence;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['ping']);
    }

    /**
     * API health check.
     */
    public function ping()
    {
        return response()->json([
            'status' => 'ok',
            'message' => 'API is working',
            'timestamp' => now()->toISOString(),
            'version' => '1.0.0'
        ]);
    }

    /**
     * Get dashboard statistics.
     */
    public function dashboardStats()
    {
        $user = Auth::user();
        
        $stats = [
            'user' => [
                'evidences_count' => $user->evidences()->count(),
                'projects_count' => $user->projects()->count(),
                'notifications_count' => $user->notifications()->unread()->count(),
                'time_logs_count' => $user->timeLogs()->count(),
            ],
            'system' => [
                'total_users' => User::count(),
                'active_projects' => Project::where('status', 'in_progress')->count(),
                'pending_evidences' => Evidence::where('status', 'pending')->count(),
                'recent_activities' => $this->getRecentActivities(),
            ]
        ];

        return response()->json($stats);
    }

    /**
     * Get unread notifications count.
     */
    public function unreadNotificationsCount()
    {
        $count = Auth::user()->notifications()->unread()->count();
        
        return response()->json([
            'count' => $count,
            'has_unread' => $count > 0
        ]);
    }

    /**
     * Get notifications.
     */
    public function notifications(Request $request)
    {
        $user = Auth::user();
        $perPage = $request->get('per_page', 10);
        
        $notifications = $user->notifications()
                             ->latest()
                             ->paginate($perPage);

        return response()->json($notifications);
    }

    /**
     * Mark notification as read.
     */
    public function markNotificationAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read',
            'notification' => $notification
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllNotificationsAsRead()
    {
        Auth::user()->notifications()->unread()->update(['read_at' => now()]);

        return response()->json([
            'message' => 'All notifications marked as read'
        ]);
    }

    /**
     * Search across the system.
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        $type = $request->get('type', 'all');
        $limit = $request->get('limit', 10);

        if (!$query) {
            return response()->json([
                'results' => [],
                'total' => 0
            ]);
        }

        $results = [];

        if ($type === 'all' || $type === 'users') {
            $users = User::where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })->take($limit)->get(['id', 'first_name', 'last_name', 'email', 'avatar']);

            $results['users'] = $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'type' => 'user',
                    'title' => $user->full_name,
                    'subtitle' => $user->email,
                    'url' => route('users.show', $user),
                    'avatar' => $user->avatar_url
                ];
            });
        }

        if ($type === 'all' || $type === 'projects') {
            $projects = Project::where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })->take($limit)->get(['id', 'name', 'description', 'status']);

            $results['projects'] = $projects->map(function ($project) {
                return [
                    'id' => $project->id,
                    'type' => 'project',
                    'title' => $project->name,
                    'subtitle' => $project->description,
                    'url' => route('projects.show', $project),
                    'status' => $project->status
                ];
            });
        }

        if ($type === 'all' || $type === 'evidences') {
            $evidences = Evidence::where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhere('case_number', 'like', "%{$query}%");
            })->take($limit)->get(['id', 'title', 'description', 'case_number', 'status']);

            $results['evidences'] = $evidences->map(function ($evidence) {
                return [
                    'id' => $evidence->id,
                    'type' => 'evidence',
                    'title' => $evidence->title,
                    'subtitle' => $evidence->case_number,
                    'url' => route('evidences.show', $evidence),
                    'status' => $evidence->status
                ];
            });
        }

        $total = collect($results)->sum(function ($items) {
            return count($items);
        });

        return response()->json([
            'results' => $results,
            'total' => $total,
            'query' => $query
        ]);
    }

    /**
     * Get user profile data.
     */
    public function profile()
    {
        $user = Auth::user();
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->full_name,
                'email' => $user->email,
                'avatar' => $user->avatar_url,
                'role' => $user->role,
                'department' => $user->department,
                'position' => $user->position,
                'is_active' => $user->is_active,
                'last_login' => $user->last_login,
                'created_at' => $user->created_at,
            ],
            'stats' => [
                'evidences_count' => $user->evidences()->count(),
                'projects_count' => $user->projects()->count(),
                'time_logs_hours' => $user->timeLogs()->sum('hours'),
                'notifications_unread' => $user->notifications()->unread()->count(),
            ]
        ]);
    }

    /**
     * Update user preferences.
     */
    public function updatePreferences(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'notification_settings' => 'array',
            'privacy_settings' => 'array',
            'theme' => 'string|in:light,dark,auto',
            'language' => 'string|in:es,en',
        ]);

        if (isset($validated['notification_settings'])) {
            $user->notification_settings = array_merge(
                $user->notification_settings ?? [],
                $validated['notification_settings']
            );
        }

        if (isset($validated['privacy_settings'])) {
            $user->privacy_settings = array_merge(
                $user->privacy_settings ?? [],
                $validated['privacy_settings']
            );
        }

        $user->save();

        return response()->json([
            'message' => 'Preferences updated successfully',
            'user' => $user->fresh()
        ]);
    }

    /**
     * Get recent activities.
     */
    private function getRecentActivities()
    {
        $activities = [];

        // Recent evidences
        $recentEvidences = Evidence::with('submittedBy')
                                 ->latest()
                                 ->take(3)
                                 ->get();

        foreach ($recentEvidences as $evidence) {
            $activities[] = [
                'type' => 'evidence_created',
                'title' => 'Nueva evidencia creada',
                'description' => $evidence->title,
                'user' => $evidence->submittedBy->full_name ?? 'Usuario desconocido',
                'time' => $evidence->created_at->diffForHumans(),
                'url' => route('evidences.show', $evidence)
            ];
        }

        // Recent projects
        $recentProjects = Project::with('projectManager')
                                ->latest()
                                ->take(2)
                                ->get();

        foreach ($recentProjects as $project) {
            $activities[] = [
                'type' => 'project_created',
                'title' => 'Nuevo proyecto creado',
                'description' => $project->name,
                'user' => $project->projectManager->full_name ?? 'Usuario desconocido',
                'time' => $project->created_at->diffForHumans(),
                'url' => route('projects.show', $project)
            ];
        }

        // Sort by time
        usort($activities, function ($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });

        return array_slice($activities, 0, 5);
    }

    /**
     * Get system status.
     */
    public function systemStatus()
    {
        return response()->json([
            'status' => 'operational',
            'database' => $this->checkDatabase(),
            'storage' => $this->checkStorage(),
            'cache' => $this->checkCache(),
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Check database connectivity.
     */
    private function checkDatabase()
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'ok', 'message' => 'Database connection successful'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Database connection failed'];
        }
    }

    /**
     * Check storage accessibility.
     */
    private function checkStorage()
    {
        try {
            $testFile = storage_path('app/test.txt');
            file_put_contents($testFile, 'test');
            unlink($testFile);
            return ['status' => 'ok', 'message' => 'Storage is writable'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Storage is not writable'];
        }
    }

    /**
     * Check cache functionality.
     */
    private function checkCache()
    {
        try {
            cache()->put('test_key', 'test_value', 60);
            $value = cache()->get('test_key');
            cache()->forget('test_key');
            
            if ($value === 'test_value') {
                return ['status' => 'ok', 'message' => 'Cache is working'];
            } else {
                return ['status' => 'error', 'message' => 'Cache is not working'];
            }
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Cache error: ' . $e->getMessage()];
        }
    }
}
