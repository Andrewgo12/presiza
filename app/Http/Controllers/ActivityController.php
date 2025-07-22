<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;

class ActivityController extends Controller
{
    /**
     * Display a listing of activities.
     */
    public function index(Request $request): View
    {
        try {
            $query = Activity::with(['subject', 'causer'])
                            ->orderBy('created_at', 'desc');
            
            // Filter by user if specified
            if ($request->filled('user_id')) {
                $query->where('causer_id', $request->user_id)
                      ->where('causer_type', User::class);
            }
            
            // Filter by subject type
            if ($request->filled('subject_type')) {
                $subjectType = $request->subject_type;
                $query->where('subject_type', 'like', "%{$subjectType}%");
            }
            
            // Filter by date range
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
            
            // Filter by activity description
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                      ->orWhere('properties->attributes', 'like', "%{$search}%")
                      ->orWhere('properties->old', 'like', "%{$search}%");
                });
            }
            
            // Only show activities the user has permission to see
            $user = Auth::user();
            if (!$user->hasRole('admin')) {
                // Regular users can only see activities related to their projects
                $userProjectIds = $user->projects()->pluck('projects.id');
                $query->where(function ($q) use ($user, $userProjectIds) {
                    $q->where('causer_id', $user->id)
                      ->orWhere(function ($subQ) use ($userProjectIds) {
                          $subQ->where('subject_type', Project::class)
                               ->whereIn('subject_id', $userProjectIds);
                      });
                });
            }
            
            $activities = $query->paginate(50)->withQueryString();
            
            // Get filter options
            $users = User::select('id', 'first_name', 'last_name')
                        ->orderBy('first_name')
                        ->get();
            
            $subjectTypes = Activity::select('subject_type')
                                  ->distinct()
                                  ->whereNotNull('subject_type')
                                  ->pluck('subject_type')
                                  ->map(function ($type) {
                                      return [
                                          'value' => $type,
                                          'label' => class_basename($type)
                                      ];
                                  });
            
            $stats = [
                'total_today' => Activity::whereDate('created_at', today())->count(),
                'total_week' => Activity::whereBetween('created_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ])->count(),
                'total_month' => Activity::whereMonth('created_at', now()->month)
                                       ->whereYear('created_at', now()->year)
                                       ->count(),
                'most_active_user' => $this->getMostActiveUser(),
            ];
            
            return view('activity.index', compact(
                'activities',
                'users',
                'subjectTypes',
                'stats'
            ));
            
        } catch (\Exception $e) {
            Log::error('Error loading activities: ' . $e->getMessage());
            return view('activity.index', [
                'activities' => collect(),
                'users' => collect(),
                'subjectTypes' => collect(),
                'stats' => ['total_today' => 0, 'total_week' => 0, 'total_month' => 0, 'most_active_user' => null]
            ])->with('error', 'Error al cargar las actividades.');
        }
    }

    /**
     * Display activities for a specific project.
     */
    public function project(Request $request, Project $project): View
    {
        try {
            // Check if user can view this project
            $this->authorize('view', $project);
            
            $query = Activity::with(['causer'])
                            ->where(function ($q) use ($project) {
                                $q->where('subject_type', Project::class)
                                  ->where('subject_id', $project->id)
                                  ->orWhere('properties->project_id', $project->id);
                            })
                            ->orderBy('created_at', 'desc');
            
            // Filter by date range
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
            
            // Filter by user
            if ($request->filled('user_id')) {
                $query->where('causer_id', $request->user_id);
            }
            
            $activities = $query->paginate(30)->withQueryString();
            
            // Get project team members for filter
            $teamMembers = $project->users()
                                 ->select('users.id', 'users.first_name', 'users.last_name')
                                 ->orderBy('users.first_name')
                                 ->get();
            
            $stats = [
                'total_activities' => Activity::where('subject_type', Project::class)
                                            ->where('subject_id', $project->id)
                                            ->count(),
                'activities_today' => Activity::where('subject_type', Project::class)
                                            ->where('subject_id', $project->id)
                                            ->whereDate('created_at', today())
                                            ->count(),
                'most_active_member' => $this->getMostActiveProjectMember($project),
                'recent_milestone' => $project->milestones()
                                            ->orderBy('updated_at', 'desc')
                                            ->first(),
            ];
            
            return view('activity.project', compact(
                'project',
                'activities',
                'teamMembers',
                'stats'
            ));
            
        } catch (\Exception $e) {
            Log::error('Error loading project activities: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar las actividades del proyecto.');
        }
    }

    /**
     * Display activities for a specific user.
     */
    public function user(Request $request, User $user): View
    {
        try {
            // Check if current user can view this user's profile
            $this->authorize('viewProfile', $user);
            
            $query = Activity::with(['subject'])
                            ->where('causer_id', $user->id)
                            ->where('causer_type', User::class)
                            ->orderBy('created_at', 'desc');
            
            // Filter by date range
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
            
            // Filter by subject type
            if ($request->filled('subject_type')) {
                $query->where('subject_type', 'like', "%{$request->subject_type}%");
            }
            
            $activities = $query->paginate(30)->withQueryString();
            
            // Get user's projects for context
            $userProjects = $user->projects()
                               ->select('projects.id', 'projects.name', 'projects.status')
                               ->orderBy('projects.name')
                               ->get();
            
            $stats = [
                'total_activities' => Activity::where('causer_id', $user->id)->count(),
                'activities_today' => Activity::where('causer_id', $user->id)
                                            ->whereDate('created_at', today())
                                            ->count(),
                'activities_week' => Activity::where('causer_id', $user->id)
                                           ->whereBetween('created_at', [
                                               now()->startOfWeek(),
                                               now()->endOfWeek()
                                           ])
                                           ->count(),
                'most_active_project' => $this->getUserMostActiveProject($user),
            ];
            
            return view('activity.user', compact(
                'user',
                'activities',
                'userProjects',
                'stats'
            ));
            
        } catch (\Exception $e) {
            Log::error('Error loading user activities: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar las actividades del usuario.');
        }
    }

    /**
     * Get activity data for charts (API endpoint).
     */
    public function chartData(Request $request): JsonResponse
    {
        try {
            $period = $request->get('period', 'week'); // week, month, year
            $userId = $request->get('user_id');
            $projectId = $request->get('project_id');
            
            $query = Activity::query();
            
            // Apply filters
            if ($userId) {
                $query->where('causer_id', $userId);
            }
            
            if ($projectId) {
                $query->where(function ($q) use ($projectId) {
                    $q->where('subject_type', Project::class)
                      ->where('subject_id', $projectId)
                      ->orWhere('properties->project_id', $projectId);
                });
            }
            
            // Group by time period
            switch ($period) {
                case 'week':
                    $data = $query->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                                 ->whereBetween('created_at', [
                                     now()->startOfWeek(),
                                     now()->endOfWeek()
                                 ])
                                 ->groupBy('date')
                                 ->orderBy('date')
                                 ->get();
                    break;
                    
                case 'month':
                    $data = $query->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                                 ->whereMonth('created_at', now()->month)
                                 ->whereYear('created_at', now()->year)
                                 ->groupBy('date')
                                 ->orderBy('date')
                                 ->get();
                    break;
                    
                case 'year':
                    $data = $query->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                                 ->whereYear('created_at', now()->year)
                                 ->groupBy('month')
                                 ->orderBy('month')
                                 ->get();
                    break;
                    
                default:
                    throw new \InvalidArgumentException('Período no válido');
            }
            
            return response()->json([
                'success' => true,
                'data' => $data,
                'period' => $period
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting activity chart data: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los datos del gráfico',
                'data' => []
            ], 500);
        }
    }

    /**
     * Get the most active user.
     */
    private function getMostActiveUser(): ?User
    {
        try {
            $userId = Activity::selectRaw('causer_id, COUNT(*) as activity_count')
                            ->where('causer_type', User::class)
                            ->whereNotNull('causer_id')
                            ->whereBetween('created_at', [
                                now()->startOfMonth(),
                                now()->endOfMonth()
                            ])
                            ->groupBy('causer_id')
                            ->orderBy('activity_count', 'desc')
                            ->value('causer_id');
            
            return $userId ? User::find($userId) : null;
            
        } catch (\Exception $e) {
            Log::error('Error getting most active user: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get the most active member of a project.
     */
    private function getMostActiveProjectMember(Project $project): ?User
    {
        try {
            $userId = Activity::selectRaw('causer_id, COUNT(*) as activity_count')
                            ->where('causer_type', User::class)
                            ->whereNotNull('causer_id')
                            ->where(function ($q) use ($project) {
                                $q->where('subject_type', Project::class)
                                  ->where('subject_id', $project->id)
                                  ->orWhere('properties->project_id', $project->id);
                            })
                            ->whereBetween('created_at', [
                                now()->startOfMonth(),
                                now()->endOfMonth()
                            ])
                            ->groupBy('causer_id')
                            ->orderBy('activity_count', 'desc')
                            ->value('causer_id');
            
            return $userId ? User::find($userId) : null;
            
        } catch (\Exception $e) {
            Log::error('Error getting most active project member: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get user's most active project.
     */
    private function getUserMostActiveProject(User $user): ?Project
    {
        try {
            $projectId = Activity::selectRaw('subject_id, COUNT(*) as activity_count')
                               ->where('causer_id', $user->id)
                               ->where('subject_type', Project::class)
                               ->whereNotNull('subject_id')
                               ->whereBetween('created_at', [
                                   now()->startOfMonth(),
                                   now()->endOfMonth()
                               ])
                               ->groupBy('subject_id')
                               ->orderBy('activity_count', 'desc')
                               ->value('subject_id');
            
            return $projectId ? Project::find($projectId) : null;
            
        } catch (\Exception $e) {
            Log::error('Error getting user most active project: ' . $e->getMessage());
            return null;
        }
    }
}
