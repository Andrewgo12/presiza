<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\TimeLog;
use App\Models\Milestone;
use App\Models\User;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    /**
     * Global search functionality.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = $request->get('q', '');
        $type = $request->get('type', 'all');
        $limit = $request->get('limit', 10);
        
        if (empty($query)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'results' => [],
                    'total' => 0,
                    'query' => $query
                ]);
            }
            
            return view('search.index', [
                'results' => [],
                'query' => $query,
                'total' => 0
            ]);
        }
        
        try {
            $results = [];
            $total = 0;
            
            if ($type === 'all' || $type === 'projects') {
                $projectResults = $this->searchProjects($query, $limit);
                $results['projects'] = $projectResults;
                $total += count($projectResults);
            }
            
            if ($type === 'all' || $type === 'time-logs') {
                $timeLogResults = $this->searchTimeLogs($query, $limit);
                $results['time_logs'] = $timeLogResults;
                $total += count($timeLogResults);
            }
            
            if ($type === 'all' || $type === 'milestones') {
                $milestoneResults = $this->searchMilestones($query, $limit);
                $results['milestones'] = $milestoneResults;
                $total += count($milestoneResults);
            }
            
            if ($type === 'all' || $type === 'users') {
                $userResults = $this->searchUsers($query, $limit);
                $results['users'] = $userResults;
                $total += count($userResults);
            }
            
            if ($type === 'all' || $type === 'files') {
                $fileResults = $this->searchFiles($query, $limit);
                $results['files'] = $fileResults;
                $total += count($fileResults);
            }
            
            if ($request->expectsJson()) {
                return response()->json([
                    'results' => $results,
                    'total' => $total,
                    'query' => $query,
                    'type' => $type
                ]);
            }
            
            return view('search.index', compact('results', 'query', 'total', 'type'));
            
        } catch (\Exception $e) {
            Log::error('Search error: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Error en la búsqueda',
                    'results' => [],
                    'total' => 0
                ], 500);
            }
            
            return view('search.index', [
                'results' => [],
                'query' => $query,
                'total' => 0,
                'error' => 'Error en la búsqueda'
            ]);
        }
    }

    /**
     * Search projects.
     */
    public function projects(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $limit = $request->get('limit', 10);
        
        try {
            $results = $this->searchProjects($query, $limit);
            
            return response()->json([
                'success' => true,
                'results' => $results,
                'total' => count($results)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Project search error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Error al buscar proyectos',
                'results' => []
            ], 500);
        }
    }

    /**
     * Search time logs.
     */
    public function timeLogs(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $limit = $request->get('limit', 10);
        
        try {
            $results = $this->searchTimeLogs($query, $limit);
            
            return response()->json([
                'success' => true,
                'results' => $results,
                'total' => count($results)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Time log search error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Error al buscar registros de tiempo',
                'results' => []
            ], 500);
        }
    }

    /**
     * Search milestones.
     */
    public function milestones(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $limit = $request->get('limit', 10);
        
        try {
            $results = $this->searchMilestones($query, $limit);
            
            return response()->json([
                'success' => true,
                'results' => $results,
                'total' => count($results)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Milestone search error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Error al buscar milestones',
                'results' => []
            ], 500);
        }
    }

    /**
     * Global search API endpoint.
     */
    public function global(Request $request): JsonResponse
    {
        return $this->index($request);
    }

    /**
     * Get search suggestions.
     */
    public function suggestions(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([
                'suggestions' => []
            ]);
        }
        
        try {
            $user = Auth::user();
            $suggestions = [];
            
            // Project suggestions
            $projects = Project::where('name', 'like', "%{$query}%")
                             ->whereHas('users', function ($q) use ($user) {
                                 $q->where('users.id', $user->id);
                             })
                             ->limit(5)
                             ->get(['id', 'name']);
            
            foreach ($projects as $project) {
                $suggestions[] = [
                    'type' => 'project',
                    'id' => $project->id,
                    'title' => $project->name,
                    'subtitle' => 'Proyecto',
                    'url' => route('projects.show', $project)
                ];
            }
            
            // Milestone suggestions
            $milestones = Milestone::where('name', 'like', "%{$query}%")
                                 ->whereHas('project.users', function ($q) use ($user) {
                                     $q->where('users.id', $user->id);
                                 })
                                 ->with('project:id,name')
                                 ->limit(5)
                                 ->get(['id', 'name', 'project_id']);
            
            foreach ($milestones as $milestone) {
                $suggestions[] = [
                    'type' => 'milestone',
                    'id' => $milestone->id,
                    'title' => $milestone->name,
                    'subtitle' => 'Milestone - ' . $milestone->project->name,
                    'url' => route('projects.milestones.show', [$milestone->project_id, $milestone])
                ];
            }
            
            // User suggestions (if admin or can view users)
            if ($user->hasRole('admin') || $user->can('viewAny', User::class)) {
                $users = User::where(function ($q) use ($query) {
                            $q->where('first_name', 'like', "%{$query}%")
                              ->orWhere('last_name', 'like', "%{$query}%")
                              ->orWhere('email', 'like', "%{$query}%");
                        })
                        ->limit(3)
                        ->get(['id', 'first_name', 'last_name', 'email']);
                
                foreach ($users as $searchUser) {
                    $suggestions[] = [
                        'type' => 'user',
                        'id' => $searchUser->id,
                        'title' => $searchUser->full_name,
                        'subtitle' => 'Usuario - ' . $searchUser->email,
                        'url' => route('activity.user', $searchUser)
                    ];
                }
            }
            
            return response()->json([
                'suggestions' => $suggestions
            ]);
            
        } catch (\Exception $e) {
            Log::error('Search suggestions error: ' . $e->getMessage());
            
            return response()->json([
                'suggestions' => [],
                'error' => 'Error al obtener sugerencias'
            ], 500);
        }
    }

    /**
     * Search projects with user access control.
     */
    private function searchProjects(string $query, int $limit): array
    {
        $user = Auth::user();
        
        $projects = Project::where(function ($q) use ($query) {
                        $q->where('name', 'like', "%{$query}%")
                          ->orWhere('description', 'like', "%{$query}%")
                          ->orWhere('client_name', 'like', "%{$query}%");
                    })
                    ->whereHas('users', function ($q) use ($user) {
                        $q->where('users.id', $user->id);
                    })
                    ->with(['projectManager:id,first_name,last_name'])
                    ->limit($limit)
                    ->get();
        
        return $projects->map(function ($project) {
            return [
                'id' => $project->id,
                'title' => $project->name,
                'description' => $project->description,
                'status' => $project->status,
                'manager' => $project->projectManager?->full_name,
                'url' => route('projects.show', $project),
                'type' => 'project'
            ];
        })->toArray();
    }

    /**
     * Search time logs with user access control.
     */
    private function searchTimeLogs(string $query, int $limit): array
    {
        $user = Auth::user();
        
        $timeLogs = TimeLog::where(function ($q) use ($query) {
                        $q->where('task_description', 'like', "%{$query}%")
                          ->orWhere('notes', 'like', "%{$query}%");
                    })
                    ->where(function ($q) use ($user) {
                        $q->where('user_id', $user->id)
                          ->orWhereHas('project.users', function ($subQ) use ($user) {
                              $subQ->where('users.id', $user->id);
                          });
                    })
                    ->with(['project:id,name', 'user:id,first_name,last_name'])
                    ->orderBy('created_at', 'desc')
                    ->limit($limit)
                    ->get();
        
        return $timeLogs->map(function ($timeLog) {
            return [
                'id' => $timeLog->id,
                'title' => $timeLog->task_description,
                'project' => $timeLog->project->name,
                'user' => $timeLog->user->full_name,
                'hours' => $timeLog->hours,
                'date' => $timeLog->date->format('d/m/Y'),
                'url' => route('time-logs.show', $timeLog),
                'type' => 'time_log'
            ];
        })->toArray();
    }

    /**
     * Search milestones with user access control.
     */
    private function searchMilestones(string $query, int $limit): array
    {
        $user = Auth::user();
        
        $milestones = Milestone::where(function ($q) use ($query) {
                          $q->where('name', 'like', "%{$query}%")
                            ->orWhere('description', 'like', "%{$query}%");
                      })
                      ->whereHas('project.users', function ($q) use ($user) {
                          $q->where('users.id', $user->id);
                      })
                      ->with(['project:id,name'])
                      ->limit($limit)
                      ->get();
        
        return $milestones->map(function ($milestone) {
            return [
                'id' => $milestone->id,
                'title' => $milestone->name,
                'description' => $milestone->description,
                'project' => $milestone->project->name,
                'status' => $milestone->status,
                'progress' => $milestone->progress_percentage,
                'url' => route('projects.milestones.show', [$milestone->project_id, $milestone]),
                'type' => 'milestone'
            ];
        })->toArray();
    }

    /**
     * Search users (admin only or with proper permissions).
     */
    private function searchUsers(string $query, int $limit): array
    {
        $user = Auth::user();
        
        if (!$user->hasRole('admin') && !$user->can('viewAny', User::class)) {
            return [];
        }
        
        $users = User::where(function ($q) use ($query) {
                     $q->where('first_name', 'like', "%{$query}%")
                       ->orWhere('last_name', 'like', "%{$query}%")
                       ->orWhere('email', 'like', "%{$query}%");
                 })
                 ->limit($limit)
                 ->get();
        
        return $users->map(function ($searchUser) {
            return [
                'id' => $searchUser->id,
                'title' => $searchUser->full_name,
                'email' => $searchUser->email,
                'role' => $searchUser->role,
                'url' => route('activity.user', $searchUser),
                'type' => 'user'
            ];
        })->toArray();
    }

    /**
     * Search files with user access control.
     */
    private function searchFiles(string $query, int $limit): array
    {
        $user = Auth::user();
        
        if (!class_exists(File::class)) {
            return [];
        }
        
        $files = File::where(function ($q) use ($query) {
                     $q->where('name', 'like', "%{$query}%")
                       ->orWhere('description', 'like', "%{$query}%");
                 })
                 ->where(function ($q) use ($user) {
                     $q->where('user_id', $user->id)
                       ->orWhere('is_public', true);
                 })
                 ->limit($limit)
                 ->get();
        
        return $files->map(function ($file) {
            return [
                'id' => $file->id,
                'title' => $file->name,
                'description' => $file->description,
                'size' => $file->size_formatted,
                'type' => $file->mime_type,
                'url' => route('files.show', $file),
                'type' => 'file'
            ];
        })->toArray();
    }
}
