<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
{
    /**
     * Display a listing of groups.
     */
    public function index(Request $request)
    {
        $query = Group::with(['creator', 'members'])
            ->withCount('members');
        
        // Filtros de búsqueda
        if ($request->filled('search')) {
            $query->search($request->search);
        }
        
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        if ($request->filled('my_groups')) {
            $query->whereUserIsMember(Auth::id());
        }
        
        $groups = $query->active()->latest()->paginate(12)->withQueryString();
        
        // Obtener grupos del usuario
        $userGroups = Auth::user()->groups()->active()->get();
        
        return view('groups.index', compact('groups', 'userGroups'));
    }
    
    /**
     * Show the form for creating a new group.
     */
    public function create()
    {
        return view('groups.create');
    }
    
    /**
     * Store a newly created group.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:groups,name',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:public,private,restricted',
        ]);
        
        DB::beginTransaction();
        
        try {
            $group = Group::create([
                'name' => $request->name,
                'description' => $request->description,
                'type' => $request->type,
                'created_by' => Auth::id(),
                'is_active' => true,
            ]);
            
            // Agregar al creador como admin del grupo
            $group->addMember(Auth::user(), 'admin');
            
            DB::commit();
            
            return redirect()->route('groups.show', $group)
                ->with('success', 'Grupo creado exitosamente.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Error al crear el grupo: ' . $e->getMessage());
        }
    }
    
    /**
     * Display the specified group.
     */
    public function show(Group $group)
    {
        $group->load(['creator', 'members', 'messages.sender']);
        
        // Verificar si el usuario puede ver el grupo
        if ($group->type === 'private' && !$group->hasMember(Auth::user()) && !Auth::user()->isAdmin()) {
            abort(403, 'No tienes acceso a este grupo privado.');
        }
        
        // Obtener mensajes recientes del grupo
        $recentMessages = $group->messages()
            ->with('sender')
            ->latest()
            ->take(10)
            ->get();
        
        $isMember = $group->hasMember(Auth::user());
        $canManage = $group->canManage(Auth::user());
        
        return view('groups.show', compact('group', 'recentMessages', 'isMember', 'canManage'));
    }
    
    /**
     * Show the form for editing the specified group.
     */
    public function edit(Group $group)
    {
        if (!$group->canManage(Auth::user())) {
            abort(403, 'No tienes permisos para editar este grupo.');
        }
        
        return view('groups.edit', compact('group'));
    }
    
    /**
     * Update the specified group.
     */
    public function update(Request $request, Group $group)
    {
        if (!$group->canManage(Auth::user())) {
            abort(403, 'No tienes permisos para editar este grupo.');
        }
        
        $request->validate([
            'name' => 'required|string|max:255|unique:groups,name,' . $group->id,
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:public,private,restricted',
        ]);
        
        $group->update([
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
        ]);
        
        return redirect()->route('groups.show', $group)
            ->with('success', 'Grupo actualizado exitosamente.');
    }
    
    /**
     * Remove the specified group.
     */
    public function destroy(Group $group)
    {
        if (!$group->canManage(Auth::user())) {
            abort(403, 'No tienes permisos para eliminar este grupo.');
        }
        
        $group->delete();
        
        return redirect()->route('groups.index')
            ->with('success', 'Grupo eliminado exitosamente.');
    }
    
    /**
     * Join a group.
     */
    public function join(Request $request, Group $group)
    {
        if ($group->type === 'private') {
            return back()->with('error', 'No puedes unirte a un grupo privado sin invitación.');
        }
        
        if ($group->hasMember(Auth::user())) {
            return back()->with('info', 'Ya eres miembro de este grupo.');
        }
        
        $group->addMember(Auth::user());
        
        return back()->with('success', 'Te has unido al grupo exitosamente.');
    }
    
    /**
     * Leave a group.
     */
    public function leave(Group $group)
    {
        if (!$group->hasMember(Auth::user())) {
            return back()->with('error', 'No eres miembro de este grupo.');
        }
        
        // No permitir que el creador abandone el grupo
        if ($group->created_by === Auth::id()) {
            return back()->with('error', 'El creador del grupo no puede abandonarlo. Transfiere la propiedad primero.');
        }
        
        $group->removeMember(Auth::user());
        
        return redirect()->route('groups.index')
            ->with('success', 'Has abandonado el grupo exitosamente.');
    }
    
    /**
     * Invite user to group.
     */
    public function invite(Request $request, Group $group)
    {
        if (!$group->canManage(Auth::user())) {
            abort(403, 'No tienes permisos para invitar usuarios a este grupo.');
        }
        
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);
        
        $user = User::findOrFail($request->user_id);
        
        if ($group->hasMember($user)) {
            return back()->with('error', 'El usuario ya es miembro del grupo.');
        }
        
        $group->addMember($user);
        
        // Aquí podrías enviar una notificación al usuario invitado
        
        return back()->with('success', "Usuario {$user->full_name} agregado al grupo exitosamente.");
    }
    
    /**
     * Update member role.
     */
    public function updateMember(Request $request, Group $group, User $user)
    {
        if (!$group->canManage(Auth::user())) {
            abort(403, 'No tienes permisos para gestionar miembros de este grupo.');
        }
        
        $request->validate([
            'role' => 'required|in:admin,moderator,member',
        ]);
        
        if (!$group->hasMember($user)) {
            return back()->with('error', 'El usuario no es miembro del grupo.');
        }
        
        $group->updateMemberRole($user, $request->role);
        
        return back()->with('success', "Rol de {$user->full_name} actualizado exitosamente.");
    }
    
    /**
     * Remove member from group.
     */
    public function removeMember(Group $group, User $user)
    {
        if (!$group->canManage(Auth::user())) {
            abort(403, 'No tienes permisos para gestionar miembros de este grupo.');
        }
        
        if (!$group->hasMember($user)) {
            return back()->with('error', 'El usuario no es miembro del grupo.');
        }
        
        // No permitir remover al creador del grupo
        if ($group->created_by === $user->id) {
            return back()->with('error', 'No puedes remover al creador del grupo.');
        }
        
        $group->removeMember($user);
        
        return back()->with('success', "{$user->full_name} ha sido removido del grupo.");
    }
    
    /**
     * Search groups.
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json(['results' => []]);
        }
        
        $groups = Group::search($query)
            ->active()
            ->public()
            ->limit(10)
            ->get(['id', 'name', 'description', 'type']);
        
        return response()->json([
            'results' => $groups->map(function ($group) {
                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'description' => $group->description,
                    'type' => $group->type,
                    'url' => route('groups.show', $group),
                ];
            }),
        ]);
    }
    
    /**
     * Export groups data.
     */
    public function export(Request $request)
    {
        $this->authorize('export', Group::class);
        
        $groups = Group::with(['creator', 'members'])
            ->withCount('members')
            ->get();
        
        $filename = 'groups_export_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function () use ($groups) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'ID', 'Nombre', 'Descripción', 'Tipo', 'Creador', 
                'Miembros', 'Activo', 'Creado'
            ]);
            
            // Data
            foreach ($groups as $group) {
                fputcsv($file, [
                    $group->id,
                    $group->name,
                    $group->description,
                    $group->type_display_name,
                    $group->creator->full_name ?? 'N/A',
                    $group->members_count,
                    $group->is_active ? 'Sí' : 'No',
                    $group->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
