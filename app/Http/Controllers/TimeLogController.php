<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\TimeLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TimeLogController extends Controller
{
    /**
     * Display a listing of time logs.
     */
    public function index(Request $request)
    {
        $query = TimeLog::with(['user', 'project', 'milestone', 'approver']);
        
        // Filtros
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }
        
        if ($request->filled('approved')) {
            if ($request->approved === 'yes') {
                $query->approved();
            } elseif ($request->approved === 'no') {
                $query->pending();
            }
        }
        
        if ($request->filled('billable')) {
            $query->where('is_billable', $request->billable === 'yes');
        }
        
        // Solo mostrar logs del usuario actual si no es admin/manager
        if (!Auth::user()->isAdmin() && !in_array(Auth::user()->role, ['analyst'])) {
            $query->byUser(Auth::id());
        }
        
        $timeLogs = $query->latest('date')->latest('created_at')->paginate(20)->withQueryString();
        
        // Obtener proyectos y usuarios para filtros
        $projects = Project::active()->orderBy('name')->get(['id', 'name']);
        $users = User::active()->orderBy('first_name')->get(['id', 'first_name', 'last_name']);
        
        // Estadísticas
        $stats = [
            'total_hours' => $query->sum('hours'),
            'billable_hours' => $query->where('is_billable', true)->sum('hours'),
            'total_amount' => $query->sum(DB::raw('hours * hourly_rate')),
            'pending_approval' => TimeLog::pending()->count(),
        ];
        
        return view('time-logs.index', compact('timeLogs', 'projects', 'users', 'stats'));
    }
    
    /**
     * Show the form for creating a new time log.
     */
    public function create(Request $request)
    {
        $projects = Auth::user()->isAdmin() 
            ? Project::active()->with('milestones')->get()
            : Auth::user()->projects()->active()->with('milestones')->get();
        
        $selectedProject = null;
        $milestones = collect();
        
        if ($request->filled('project_id')) {
            $selectedProject = $projects->find($request->project_id);
            $milestones = $selectedProject?->milestones ?? collect();
        }
        
        return view('time-logs.create', compact('projects', 'selectedProject', 'milestones'));
    }
    
    /**
     * Store a newly created time log.
     */
    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'milestone_id' => 'nullable|exists:project_milestones,id',
            'task_description' => 'required|string|max:500',
            'date' => 'required|date|before_or_equal:today',
            'hours' => 'required_without_all:start_time,end_time|numeric|min:0.25|max:24',
            'start_time' => 'required_without:hours|date_format:H:i',
            'end_time' => 'required_with:start_time|date_format:H:i|after:start_time',
            'is_billable' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        // Verificar que el usuario sea miembro del proyecto
        $project = Project::findOrFail($request->project_id);
        if (!$project->hasMember(Auth::user()) && !Auth::user()->isAdmin()) {
            return back()->withInput()
                ->with('error', 'No tienes acceso a este proyecto.');
        }
        
        // Verificar que el milestone pertenezca al proyecto
        if ($request->milestone_id) {
            $milestone = ProjectMilestone::findOrFail($request->milestone_id);
            if ($milestone->project_id !== $project->id) {
                return back()->withInput()
                    ->with('error', 'El milestone no pertenece al proyecto seleccionado.');
            }
        }
        
        $timeLogData = [
            'user_id' => Auth::id(),
            'project_id' => $request->project_id,
            'milestone_id' => $request->milestone_id,
            'task_description' => $request->task_description,
            'date' => $request->date,
            'is_billable' => $request->boolean('is_billable', true),
            'notes' => $request->notes,
        ];
        
        // Calcular horas si se proporcionaron tiempos
        if ($request->start_time && $request->end_time) {
            $startTime = \Carbon\Carbon::createFromFormat('H:i', $request->start_time);
            $endTime = \Carbon\Carbon::createFromFormat('H:i', $request->end_time);
            
            $timeLogData['start_time'] = $startTime;
            $timeLogData['end_time'] = $endTime;
            $timeLogData['hours'] = $endTime->diffInMinutes($startTime) / 60;
        } else {
            $timeLogData['hours'] = $request->hours;
        }
        
        $timeLog = TimeLog::create($timeLogData);
        
        return redirect()->route('time-logs.show', $timeLog)
            ->with('success', 'Registro de tiempo creado exitosamente.');
    }
    
    /**
     * Display the specified time log.
     */
    public function show(TimeLog $timeLog)
    {
        $this->authorize('view', $timeLog);
        
        $timeLog->load(['user', 'project', 'milestone', 'approver']);
        
        return view('time-logs.show', compact('timeLog'));
    }
    
    /**
     * Show the form for editing the specified time log.
     */
    public function edit(TimeLog $timeLog)
    {
        $this->authorize('update', $timeLog);
        
        $projects = Auth::user()->isAdmin() 
            ? Project::active()->with('milestones')->get()
            : Auth::user()->projects()->active()->with('milestones')->get();
        
        $milestones = $timeLog->project->milestones;
        
        return view('time-logs.edit', compact('timeLog', 'projects', 'milestones'));
    }
    
    /**
     * Update the specified time log.
     */
    public function update(Request $request, TimeLog $timeLog)
    {
        $this->authorize('update', $timeLog);
        
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'milestone_id' => 'nullable|exists:project_milestones,id',
            'task_description' => 'required|string|max:500',
            'date' => 'required|date|before_or_equal:today',
            'hours' => 'required_without_all:start_time,end_time|numeric|min:0.25|max:24',
            'start_time' => 'required_without:hours|date_format:H:i',
            'end_time' => 'required_with:start_time|date_format:H:i|after:start_time',
            'is_billable' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        $updateData = [
            'project_id' => $request->project_id,
            'milestone_id' => $request->milestone_id,
            'task_description' => $request->task_description,
            'date' => $request->date,
            'is_billable' => $request->boolean('is_billable', true),
            'notes' => $request->notes,
        ];
        
        // Calcular horas si se proporcionaron tiempos
        if ($request->start_time && $request->end_time) {
            $startTime = \Carbon\Carbon::createFromFormat('H:i', $request->start_time);
            $endTime = \Carbon\Carbon::createFromFormat('H:i', $request->end_time);
            
            $updateData['start_time'] = $startTime;
            $updateData['end_time'] = $endTime;
            $updateData['hours'] = $endTime->diffInMinutes($startTime) / 60;
        } else {
            $updateData['hours'] = $request->hours;
            $updateData['start_time'] = null;
            $updateData['end_time'] = null;
        }
        
        $timeLog->update($updateData);
        
        return redirect()->route('time-logs.show', $timeLog)
            ->with('success', 'Registro de tiempo actualizado exitosamente.');
    }
    
    /**
     * Remove the specified time log.
     */
    public function destroy(TimeLog $timeLog)
    {
        $this->authorize('delete', $timeLog);
        
        $timeLog->delete();
        
        return redirect()->route('time-logs.index')
            ->with('success', 'Registro de tiempo eliminado exitosamente.');
    }
    
    /**
     * Approve time log.
     */
    public function approve(TimeLog $timeLog)
    {
        $this->authorize('approve', $timeLog);
        
        $timeLog->approve(Auth::user());
        
        return back()->with('success', 'Registro de tiempo aprobado exitosamente.');
    }
    
    /**
     * Bulk approve time logs.
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'time_log_ids' => 'required|array',
            'time_log_ids.*' => 'exists:time_logs,id',
        ]);
        
        $timeLogs = TimeLog::whereIn('id', $request->time_log_ids)->get();
        
        foreach ($timeLogs as $timeLog) {
            if (Auth::user()->can('approve', $timeLog)) {
                $timeLog->approve(Auth::user());
            }
        }
        
        return back()->with('success', 'Registros de tiempo aprobados exitosamente.');
    }
    
    /**
     * Export time logs.
     */
    public function export(Request $request)
    {
        $query = TimeLog::with(['user', 'project', 'milestone']);
        
        // Aplicar filtros
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }
        
        $timeLogs = $query->get();
        
        $filename = 'time_logs_export_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function () use ($timeLogs) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'Fecha', 'Usuario', 'Proyecto', 'Milestone', 'Descripción', 
                'Horas', 'Facturable', 'Tarifa', 'Total', 'Aprobado', 'Notas'
            ]);
            
            // Data
            foreach ($timeLogs as $timeLog) {
                fputcsv($file, [
                    $timeLog->date->format('Y-m-d'),
                    $timeLog->user->full_name,
                    $timeLog->project->name,
                    $timeLog->milestone->name ?? 'N/A',
                    $timeLog->task_description,
                    $timeLog->hours,
                    $timeLog->is_billable ? 'Sí' : 'No',
                    '$' . number_format($timeLog->hourly_rate, 2),
                    '$' . number_format($timeLog->total_amount, 2),
                    $timeLog->is_approved ? 'Sí' : 'No',
                    $timeLog->notes ?? '',
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
