<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MilestoneController extends Controller
{
    /**
     * Display a listing of milestones for a project.
     */
    public function index(Request $request, Project $project)
    {
        $this->authorize('view', $project);
        
        $query = $project->milestones()->with(['assignedUser']);
        
        // Filtros
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }
        
        $milestones = $query->ordered()->get();
        
        // Obtener miembros del proyecto para asignación
        $projectMembers = $project->members()->get(['id', 'first_name', 'last_name']);
        
        return view('milestones.index', compact('project', 'milestones', 'projectMembers'));
    }
    
    /**
     * Show the form for creating a new milestone.
     */
    public function create(Project $project)
    {
        $this->authorize('update', $project);
        
        $projectMembers = $project->members()->get(['id', 'first_name', 'last_name']);
        
        return view('milestones.create', compact('project', 'projectMembers'));
    }
    
    /**
     * Store a newly created milestone.
     */
    public function store(Request $request, Project $project)
    {
        $this->authorize('update', $project);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'priority' => 'required|in:low,medium,high,critical',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'estimated_hours' => 'nullable|numeric|min:0',
            'order' => 'nullable|integer|min:0',
        ]);
        
        // Verificar que el usuario asignado sea miembro del proyecto
        if ($request->assigned_to && !$project->hasMember(User::find($request->assigned_to))) {
            return back()->withInput()
                ->with('error', 'El usuario asignado debe ser miembro del proyecto.');
        }
        
        $milestone = $project->milestones()->create([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status,
            'priority' => $request->priority,
            'due_date' => $request->due_date,
            'assigned_to' => $request->assigned_to,
            'estimated_hours' => $request->estimated_hours,
            'order' => $request->order ?? $project->milestones()->max('order') + 1,
        ]);
        
        return redirect()->route('projects.milestones.show', [$project, $milestone])
            ->with('success', 'Milestone creado exitosamente.');
    }
    
    /**
     * Display the specified milestone.
     */
    public function show(Project $project, ProjectMilestone $milestone)
    {
        $this->authorize('view', $project);
        
        $milestone->load(['assignedUser', 'evidences.submitter']);
        
        // Estadísticas del milestone
        $stats = [
            'total_evidences' => $milestone->evidences()->count(),
            'pending_evidences' => $milestone->evidences()->where('status', 'pending')->count(),
            'approved_evidences' => $milestone->evidences()->where('status', 'approved')->count(),
            'progress' => $milestone->completion_rate,
        ];
        
        return view('milestones.show', compact('project', 'milestone', 'stats'));
    }
    
    /**
     * Show the form for editing the specified milestone.
     */
    public function edit(Project $project, ProjectMilestone $milestone)
    {
        $this->authorize('update', $project);
        
        $projectMembers = $project->members()->get(['id', 'first_name', 'last_name']);
        
        return view('milestones.edit', compact('project', 'milestone', 'projectMembers'));
    }
    
    /**
     * Update the specified milestone.
     */
    public function update(Request $request, Project $project, ProjectMilestone $milestone)
    {
        $this->authorize('update', $project);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'priority' => 'required|in:low,medium,high,critical',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'estimated_hours' => 'nullable|numeric|min:0',
            'progress_percentage' => 'nullable|integer|min:0|max:100',
            'order' => 'nullable|integer|min:0',
        ]);
        
        // Verificar que el usuario asignado sea miembro del proyecto
        if ($request->assigned_to && !$project->hasMember(User::find($request->assigned_to))) {
            return back()->withInput()
                ->with('error', 'El usuario asignado debe ser miembro del proyecto.');
        }
        
        $milestone->update([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status,
            'priority' => $request->priority,
            'due_date' => $request->due_date,
            'assigned_to' => $request->assigned_to,
            'estimated_hours' => $request->estimated_hours,
            'progress_percentage' => $request->progress_percentage,
            'order' => $request->order,
        ]);
        
        // Si se marca como completado, actualizar fecha
        if ($request->status === 'completed' && !$milestone->completed_at) {
            $milestone->markAsCompleted();
        }
        
        return redirect()->route('projects.milestones.show', [$project, $milestone])
            ->with('success', 'Milestone actualizado exitosamente.');
    }
    
    /**
     * Remove the specified milestone.
     */
    public function destroy(Project $project, ProjectMilestone $milestone)
    {
        $this->authorize('update', $project);
        
        $milestone->delete();
        
        return redirect()->route('projects.milestones.index', $project)
            ->with('success', 'Milestone eliminado exitosamente.');
    }
    
    /**
     * Update milestone progress.
     */
    public function updateProgress(Request $request, Project $project, ProjectMilestone $milestone)
    {
        $this->authorize('update', $milestone);
        
        $request->validate([
            'progress_percentage' => 'required|integer|min:0|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        $milestone->updateProgress($request->progress_percentage);
        
        if ($request->notes) {
            // Aquí podrías agregar una nota al historial del milestone
        }
        
        return back()->with('success', 'Progreso actualizado exitosamente.');
    }
    
    /**
     * Mark milestone as completed.
     */
    public function markCompleted(Project $project, ProjectMilestone $milestone)
    {
        $this->authorize('update', $milestone);
        
        $milestone->markAsCompleted();
        
        return back()->with('success', 'Milestone marcado como completado.');
    }
    
    /**
     * Reorder milestones.
     */
    public function reorder(Request $request, Project $project)
    {
        $this->authorize('update', $project);
        
        $request->validate([
            'milestones' => 'required|array',
            'milestones.*' => 'exists:project_milestones,id',
        ]);
        
        foreach ($request->milestones as $index => $milestoneId) {
            ProjectMilestone::where('id', $milestoneId)
                ->where('project_id', $project->id)
                ->update(['order' => $index + 1]);
        }
        
        return response()->json(['success' => true]);
    }
}
