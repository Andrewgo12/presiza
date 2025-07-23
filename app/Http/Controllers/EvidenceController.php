<?php

namespace App\Http\Controllers;

use App\Models\Evidence;
use App\Models\File;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EvidenceController extends Controller
{
    /**
     * Display a listing of evidences.
     */
    public function index(Request $request)
    {
        $query = Evidence::with(['submitter', 'assignee'])
            ->withCount('files');
        
        // Aplicar filtros basados en el rol del usuario
        if (Auth::user()->role !== 'admin') {
            $query->where(function($q) {
                $q->where('submitted_by', Auth::id())
                  ->orWhere('assigned_to', Auth::id());
            });
        }
        
        // Filtros de búsqueda
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        
        if ($request->filled('assigned_to')) {
            if ($request->assigned_to === 'me') {
                $query->where('assigned_to', Auth::id());
            } else {
                $query->where('assigned_to', $request->assigned_to);
            }
        }
        
        $evidences = $query->latest()->paginate(20)->withQueryString();
        
        // Obtener estadísticas
        $stats = $this->getStats();
        
        // Obtener usuarios para filtros
        $users = User::where('is_active', true)
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name']);
        
        return view('evidences.index', compact('evidences', 'stats', 'users'));
    }
    
    /**
     * Show the form for creating a new evidence.
     */
    public function create()
    {
        // Obtener archivos disponibles del usuario
        $available_files = File::where('uploaded_by', Auth::id())
            ->latest()
            ->get();
        
        // Obtener usuarios para asignación
        $users = User::where('is_active', true)
            ->where('id', '!=', Auth::id())
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'role']);
        
        return view('evidences.create', compact('available_files', 'users'));
    }
    
    /**
     * Store a newly created evidence.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|in:security,investigation,compliance,audit,incident,other',
            'priority' => 'required|in:low,medium,high,critical',
            'status' => 'required|in:pending,under_review,approved,rejected,archived',
            'assigned_to' => 'nullable|exists:users,id',
            'incident_date' => 'nullable|date',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'files' => 'nullable|array',
            'files.*' => 'exists:files,id',
            'metadata_keys' => 'nullable|array',
            'metadata_values' => 'nullable|array'
        ]);
        
        DB::beginTransaction();
        
        try {
            // Preparar metadatos
            $metadata = null;
            if ($request->metadata_keys && $request->metadata_values) {
                $metadata = [];
                foreach ($request->metadata_keys as $index => $key) {
                    if (!empty($key) && !empty($request->metadata_values[$index])) {
                        $metadata[$key] = $request->metadata_values[$index];
                    }
                }
            }
            
            // Crear evidencia
            $evidence = Evidence::create([
                'title' => $request->title,
                'description' => $request->description,
                'category' => $request->category,
                'priority' => $request->priority,
                'status' => $request->status,
                'submitted_by' => Auth::id(),
                'assigned_to' => $request->assigned_to,
                'incident_date' => $request->incident_date,
                'location' => $request->location,
                'notes' => $request->notes,
                'metadata' => $metadata
            ]);
            
            // Asociar archivos
            if ($request->files) {
                $evidence->files()->attach($request->files);
            }
            
            // Registrar en historial
            $evidence->history()->create([
                'user_id' => Auth::id(),
                'action' => 'created',
                'new_values' => $evidence->toArray(),
                'notes' => 'Evidencia creada'
            ]);
            
            DB::commit();
            
            return redirect()->route('evidences.show', $evidence)
                ->with('success', 'Evidencia creada exitosamente.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Error al crear la evidencia: ' . $e->getMessage());
        }
    }
    
    /**
     * Display the specified evidence.
     */
    public function show(Evidence $evidence)
    {
        $this->authorize('view', $evidence);
        
        $evidence->load([
            'submitter',
            'assignee',
            'files',
            'evaluations.evaluator',
            'history.user'
        ]);
        
        return view('evidences.show', compact('evidence'));
    }
    
    /**
     * Show the form for editing the specified evidence.
     */
    public function edit(Evidence $evidence)
    {
        $this->authorize('update', $evidence);
        
        $evidence->load('files');
        
        // Obtener archivos disponibles del usuario
        $available_files = File::where('uploaded_by', Auth::id())
            ->latest()
            ->get();
        
        // Obtener usuarios para asignación
        $users = User::where('is_active', true)
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'role']);
        
        return view('evidences.edit', compact('evidence', 'available_files', 'users'));
    }
    
    /**
     * Update the specified evidence.
     */
    public function update(Request $request, Evidence $evidence)
    {
        $this->authorize('update', $evidence);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|in:security,investigation,compliance,audit,incident,other',
            'priority' => 'required|in:low,medium,high,critical',
            'assigned_to' => 'nullable|exists:users,id',
            'incident_date' => 'nullable|date',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'files' => 'nullable|array',
            'files.*' => 'exists:files,id'
        ]);
        
        DB::beginTransaction();
        
        try {
            $oldValues = $evidence->toArray();
            
            $evidence->update([
                'title' => $request->title,
                'description' => $request->description,
                'category' => $request->category,
                'priority' => $request->priority,
                'assigned_to' => $request->assigned_to,
                'incident_date' => $request->incident_date,
                'location' => $request->location,
                'notes' => $request->notes
            ]);
            
            // Actualizar archivos asociados
            if ($request->has('files')) {
                $evidence->files()->sync($request->files);
            }
            
            // Registrar en historial
            $evidence->history()->create([
                'user_id' => Auth::id(),
                'action' => 'updated',
                'old_values' => $oldValues,
                'new_values' => $evidence->fresh()->toArray(),
                'notes' => 'Evidencia actualizada'
            ]);
            
            DB::commit();
            
            return redirect()->route('evidences.show', $evidence)
                ->with('success', 'Evidencia actualizada exitosamente.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Error al actualizar la evidencia: ' . $e->getMessage());
        }
    }
    
    /**
     * Remove the specified evidence.
     */
    public function destroy(Evidence $evidence)
    {
        $this->authorize('delete', $evidence);
        
        $evidence->delete();
        
        return redirect()->route('evidences.index')
            ->with('success', 'Evidencia eliminada exitosamente.');
    }
    
    /**
     * Update evidence status.
     */
    public function updateStatus(Request $request, Evidence $evidence)
    {
        $this->authorize('updateStatus', $evidence);
        
        $request->validate([
            'status' => 'required|in:pending,under_review,approved,rejected,archived',
            'notes' => 'nullable|string'
        ]);
        
        $oldStatus = $evidence->status;
        
        $evidence->update([
            'status' => $request->status
        ]);
        
        // Registrar cambio en historial
        $evidence->history()->create([
            'user_id' => Auth::id(),
            'action' => 'status_changed',
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => $request->status],
            'notes' => $request->notes ?? "Estado cambiado de {$oldStatus} a {$request->status}"
        ]);
        
        return back()->with('success', 'Estado de la evidencia actualizado.');
    }
    
    /**
     * Assign evidence to user.
     */
    public function assign(Request $request, Evidence $evidence)
    {
        $this->authorize('assign', $evidence);
        
        $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'notes' => 'nullable|string'
        ]);
        
        $oldAssignee = $evidence->assigned_to;
        
        $evidence->update([
            'assigned_to' => $request->assigned_to
        ]);
        
        // Registrar en historial
        $evidence->history()->create([
            'user_id' => Auth::id(),
            'action' => 'assigned',
            'old_values' => ['assigned_to' => $oldAssignee],
            'new_values' => ['assigned_to' => $request->assigned_to],
            'notes' => $request->notes ?? 'Evidencia reasignada'
        ]);
        
        return back()->with('success', 'Evidencia asignada exitosamente.');
    }
    
    /**
     * Add evaluation to evidence.
     */
    public function evaluate(Request $request, Evidence $evidence)
    {
        $this->authorize('evaluate', $evidence);
        
        $request->validate([
            'rating' => 'nullable|integer|min:1|max:5',
            'comment' => 'required|string',
            'recommendation' => 'required|in:approve,reject,needs_revision,escalate'
        ]);
        
        $evidence->evaluations()->create([
            'evaluator_id' => Auth::id(),
            'rating' => $request->rating,
            'comment' => $request->comment,
            'recommendation' => $request->recommendation
        ]);
        
        // Registrar en historial
        $evidence->history()->create([
            'user_id' => Auth::id(),
            'action' => 'evaluated',
            'new_values' => [
                'rating' => $request->rating,
                'recommendation' => $request->recommendation
            ],
            'notes' => 'Evaluación agregada: ' . $request->comment
        ]);
        
        return back()->with('success', 'Evaluación agregada exitosamente.');
    }
    
    /**
     * Approve evidence.
     */
    public function approve(Evidence $evidence)
    {
        $this->authorize('updateStatus', $evidence);

        $oldStatus = $evidence->status;

        $evidence->update([
            'status' => 'approved'
        ]);

        // Registrar cambio en historial
        $evidence->history()->create([
            'user_id' => Auth::id(),
            'action' => 'approved',
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => 'approved'],
            'notes' => 'Evidencia aprobada por administrador'
        ]);

        return back()->with('success', 'Evidencia aprobada exitosamente.');
    }

    /**
     * Reject evidence.
     */
    public function reject(Evidence $evidence)
    {
        $this->authorize('updateStatus', $evidence);

        $oldStatus = $evidence->status;

        $evidence->update([
            'status' => 'rejected'
        ]);

        // Registrar cambio en historial
        $evidence->history()->create([
            'user_id' => Auth::id(),
            'action' => 'rejected',
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => 'rejected'],
            'notes' => 'Evidencia rechazada por administrador'
        ]);

        return back()->with('success', 'Evidencia rechazada.');
    }

    /**
     * Get evidence statistics.
     */
    private function getStats()
    {
        $baseQuery = Evidence::query();

        // Filtrar por rol del usuario
        if (Auth::user()->role !== 'admin') {
            $baseQuery->where(function($q) {
                $q->where('submitted_by', Auth::id())
                  ->orWhere('assigned_to', Auth::id());
            });
        }

        return [
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'under_review' => (clone $baseQuery)->where('status', 'under_review')->count(),
            'approved' => (clone $baseQuery)->where('status', 'approved')->count(),
            'critical' => (clone $baseQuery)->where('priority', 'critical')->count()
        ];
    }
}
