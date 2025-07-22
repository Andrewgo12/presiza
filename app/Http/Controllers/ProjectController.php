<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects.
     */
    public function index(Request $request)
    {
        $query = Project::with(['manager', 'members'])
            ->withCount(['members', 'evidences', 'milestones']);
        
        // Filtros de búsqueda
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhere('client_name', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        
        if ($request->filled('manager')) {
            $query->where('project_manager_id', $request->manager);
        }
        
        if ($request->filled('my_projects')) {
            $query->where(function ($q) {
                $q->where('project_manager_id', Auth::id())
                  ->orWhereHas('members', function ($memberQuery) {
                      $memberQuery->where('user_id', Auth::id());
                  });
            });
        }
        
        $projects = $query->active()->latest()->paginate(12)->withQueryString();
        
        // Obtener managers para filtros
        $managers = User::whereIn('role', ['admin', 'analyst'])
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name']);
        
        // Estadísticas
        $stats = [
            'total' => Project::active()->count(),
            'in_progress' => Project::active()->where('status', 'in_progress')->count(),
            'completed' => Project::active()->where('status', 'completed')->count(),
            'overdue' => Project::active()->where('deadline', '<', now())
                ->whereNotIn('status', ['completed', 'cancelled'])->count(),
            'my_projects' => Project::where('project_manager_id', Auth::id())
                ->orWhereHas('members', function ($q) {
                    $q->where('user_id', Auth::id());
                })->count(),
        ];
        
        return view('projects.index', compact('projects', 'managers', 'stats'));
    }
    
    /**
     * Show the form for creating a new project.
     */
    public function create()
    {
        $this->authorize('create', Project::class);
        
        $managers = User::whereIn('role', ['admin', 'analyst'])
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name']);
        
        $groups = Group::active()->orderBy('name')->get(['id', 'name']);
        
        return view('projects.create', compact('managers', 'groups'));
    }
    
    /**
     * Store a newly created project.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Project::class);
        
        $request->validate([
            'name' => 'required|string|max:255|unique:projects,name',
            'description' => 'nullable|string|max:2000',
            'status' => 'required|in:planning,in_progress,on_hold,completed,cancelled',
            'priority' => 'required|in:low,medium,high,critical',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'deadline' => 'nullable|date',
            'budget' => 'nullable|numeric|min:0',
            'client_name' => 'nullable|string|max:255',
            'project_manager_id' => 'required|exists:users,id',
            'group_id' => 'nullable|exists:groups,id',
            'repository_url' => 'nullable|url',
            'documentation_url' => 'nullable|url',
        ]);
        
        DB::beginTransaction();
        
        try {
            $project = Project::create([
                'name' => $request->name,
                'description' => $request->description,
                'status' => $request->status,
                'priority' => $request->priority,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'deadline' => $request->deadline,
                'budget' => $request->budget,
                'client_name' => $request->client_name,
                'project_manager_id' => $request->project_manager_id,
                'group_id' => $request->group_id,
                'repository_url' => $request->repository_url,
                'documentation_url' => $request->documentation_url,
                'is_active' => true,
            ]);
            
            // Agregar al manager como miembro del proyecto
            $project->addMember(
                User::find($request->project_manager_id), 
                'project_manager',
                $request->manager_hourly_rate ?? 0
            );
            
            DB::commit();
            
            return redirect()->route('projects.show', $project)
                ->with('success', 'Proyecto creado exitosamente.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Error al crear el proyecto: ' . $e->getMessage());
        }
    }
    
    /**
     * Display the specified project.
     */
    public function show(Project $project)
    {
        $this->authorize('view', $project);
        
        $project->load([
            'manager', 
            'members', 
            'evidences.submitter', 
            'milestones.assignedUser',
            'timeLogs.user'
        ]);
        
        // Obtener estadísticas del proyecto
        $stats = [
            'total_members' => $project->members()->count(),
            'total_evidences' => $project->evidences()->count(),
            'pending_evidences' => $project->evidences()->where('status', 'pending')->count(),
            'total_milestones' => $project->milestones()->count(),
            'completed_milestones' => $project->milestones()->where('status', 'completed')->count(),
            'total_hours' => $project->total_hours_logged,
            'budget_used' => $project->budget_used,
            'progress' => $project->completion_rate,
        ];
        
        // Actividad reciente
        $recentActivity = collect()
            ->merge($project->evidences()->latest()->take(5)->get())
            ->merge($project->timeLogs()->latest()->take(5)->get())
            ->sortByDesc('created_at')
            ->take(10);
        
        return view('projects.show', compact('project', 'stats', 'recentActivity'));
    }
    
    /**
     * Show the form for editing the specified project.
     */
    public function edit(Project $project)
    {
        $this->authorize('update', $project);
        
        $managers = User::whereIn('role', ['admin', 'analyst'])
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name']);
        
        $groups = Group::active()->orderBy('name')->get(['id', 'name']);
        
        return view('projects.edit', compact('project', 'managers', 'groups'));
    }
    
    /**
     * Update the specified project.
     */
    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);
        
        $request->validate([
            'name' => 'required|string|max:255|unique:projects,name,' . $project->id,
            'description' => 'nullable|string|max:2000',
            'status' => 'required|in:planning,in_progress,on_hold,completed,cancelled',
            'priority' => 'required|in:low,medium,high,critical',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'deadline' => 'nullable|date',
            'budget' => 'nullable|numeric|min:0',
            'client_name' => 'nullable|string|max:255',
            'project_manager_id' => 'required|exists:users,id',
            'group_id' => 'nullable|exists:groups,id',
            'repository_url' => 'nullable|url',
            'documentation_url' => 'nullable|url',
        ]);
        
        $project->update([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status,
            'priority' => $request->priority,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'deadline' => $request->deadline,
            'budget' => $request->budget,
            'client_name' => $request->client_name,
            'project_manager_id' => $request->project_manager_id,
            'group_id' => $request->group_id,
            'repository_url' => $request->repository_url,
            'documentation_url' => $request->documentation_url,
        ]);
        
        return redirect()->route('projects.show', $project)
            ->with('success', 'Proyecto actualizado exitosamente.');
    }
    
    /**
     * Remove the specified project.
     */
    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);
        
        $project->delete();
        
        return redirect()->route('projects.index')
            ->with('success', 'Proyecto eliminado exitosamente.');
    }
    
    /**
     * Add member to project.
     */
    public function addMember(Request $request, Project $project)
    {
        $this->authorize('update', $project);
        
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:project_manager,senior_developer,developer,designer,tester,analyst',
            'hourly_rate' => 'nullable|numeric|min:0',
        ]);
        
        $user = User::findOrFail($request->user_id);
        
        if ($project->hasMember($user)) {
            return back()->with('error', 'El usuario ya es miembro del proyecto.');
        }
        
        $project->addMember($user, $request->role, $request->hourly_rate ?? 0);
        
        return back()->with('success', "Usuario {$user->full_name} agregado al proyecto.");
    }
    
    /**
     * Remove member from project.
     */
    public function removeMember(Project $project, User $user)
    {
        $this->authorize('update', $project);
        
        if (!$project->hasMember($user)) {
            return back()->with('error', 'El usuario no es miembro del proyecto.');
        }
        
        // No permitir remover al project manager
        if ($project->project_manager_id === $user->id) {
            return back()->with('error', 'No puedes remover al gerente del proyecto.');
        }
        
        $project->removeMember($user);
        
        return back()->with('success', "{$user->full_name} ha sido removido del proyecto.");
    }
    
    /**
     * Export projects data.
     */
    public function export(Request $request)
    {
        $this->authorize('export', Project::class);
        
        $projects = Project::with(['manager', 'members'])
            ->withCount(['members', 'evidences', 'milestones'])
            ->get();
        
        $filename = 'projects_export_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function () use ($projects) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'ID', 'Nombre', 'Estado', 'Prioridad', 'Gerente', 'Miembros', 
                'Progreso', 'Presupuesto', 'Fecha Inicio', 'Fecha Fin', 'Creado'
            ]);
            
            // Data
            foreach ($projects as $project) {
                fputcsv($file, [
                    $project->id,
                    $project->name,
                    $project->status_display_name,
                    $project->priority_display_name,
                    $project->manager->full_name ?? 'N/A',
                    $project->members_count,
                    $project->progress_percentage . '%',
                    $project->budget ? '$' . number_format($project->budget, 2) : 'N/A',
                    $project->start_date?->format('Y-m-d') ?? 'N/A',
                    $project->end_date?->format('Y-m-d') ?? 'N/A',
                    $project->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
