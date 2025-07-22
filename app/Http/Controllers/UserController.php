<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);
        
        $query = User::query();
        
        // Filtros de búsqueda
        if ($request->filled('search')) {
            $query->search($request->search);
        }
        
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        
        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }
        
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }
        
        $users = $query->latest()->paginate(20)->withQueryString();
        
        // Obtener departamentos únicos para filtros
        $departments = User::distinct()->pluck('department')->filter()->sort();
        
        // Estadísticas
        $stats = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
            'admins' => User::where('role', 'admin')->count(),
            'recent_logins' => User::where('last_login', '>=', now()->subDays(7))->count(),
        ];
        
        return view('users.index', compact('users', 'departments', 'stats'));
    }
    
    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $this->authorize('create', User::class);
        
        return view('users.create');
    }
    
    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $this->authorize('create', User::class);
        
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,analyst,investigator,user',
            'department' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        $userData = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'department' => $request->department,
            'position' => $request->position,
            'is_active' => true,
            'email_verified_at' => now(),
        ];
        
        // Manejar avatar
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $userData['avatar'] = $avatarPath;
        }
        
        $user = User::create($userData);
        
        return redirect()->route('users.show', $user)
            ->with('success', 'Usuario creado exitosamente.');
    }
    
    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);
        
        $user->load(['files', 'evidences', 'assignedEvidences', 'groups']);
        
        // Estadísticas del usuario
        $stats = [
            'files_count' => $user->files()->count(),
            'evidences_submitted' => $user->evidences()->count(),
            'evidences_assigned' => $user->assignedEvidences()->count(),
            'groups_count' => $user->groups()->count(),
            'recent_activity' => $user->evidences()->where('created_at', '>=', now()->subDays(30))->count(),
        ];
        
        return view('users.show', compact('user', 'stats'));
    }
    
    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);
        
        return view('users.edit', compact('user'));
    }
    
    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);
        
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|in:admin,analyst,investigator,user',
            'department' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
        ]);
        
        $userData = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'role' => $request->role,
            'department' => $request->department,
            'position' => $request->position,
        ];
        
        // Solo admins pueden cambiar el estado activo
        if (Auth::user()->isAdmin()) {
            $userData['is_active'] = $request->boolean('is_active');
        }
        
        // Manejar avatar
        if ($request->hasFile('avatar')) {
            // Eliminar avatar anterior
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $userData['avatar'] = $avatarPath;
        }
        
        $user->update($userData);
        
        return redirect()->route('users.show', $user)
            ->with('success', 'Usuario actualizado exitosamente.');
    }
    
    /**
     * Update user password.
     */
    public function updatePassword(Request $request, User $user)
    {
        $this->authorize('update', $user);
        
        $request->validate([
            'current_password' => 'required_if:user_id,' . Auth::id(),
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        // Si el usuario está cambiando su propia contraseña, verificar la actual
        if ($user->id === Auth::id()) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'La contraseña actual es incorrecta.']);
            }
        }
        
        $user->update([
            'password' => Hash::make($request->password),
        ]);
        
        return back()->with('success', 'Contraseña actualizada exitosamente.');
    }
    
    /**
     * Toggle user active status.
     */
    public function toggleStatus(User $user)
    {
        $this->authorize('update', $user);
        
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Solo los administradores pueden cambiar el estado de los usuarios.');
        }
        
        $user->update([
            'is_active' => !$user->is_active,
        ]);
        
        $status = $user->is_active ? 'activado' : 'desactivado';
        
        return back()->with('success', "Usuario {$status} exitosamente.");
    }
    
    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);
        
        if ($user->id === Auth::id()) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta.');
        }
        
        // Eliminar avatar si existe
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }
        
        $user->delete();
        
        return redirect()->route('users.index')
            ->with('success', 'Usuario eliminado exitosamente.');
    }
    
    /**
     * Show user profile.
     */
    public function profile()
    {
        $user = Auth::user();
        $user->load(['files', 'evidences', 'assignedEvidences', 'groups']);
        
        $stats = [
            'files_count' => $user->files()->count(),
            'evidences_submitted' => $user->evidences()->count(),
            'evidences_assigned' => $user->assignedEvidences()->count(),
            'groups_count' => $user->groups()->count(),
            'pending_evidences' => $user->assignedEvidences()->where('status', 'pending')->count(),
        ];
        
        return view('users.profile', compact('user', 'stats'));
    }
    
    /**
     * Update user profile.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'department' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        $userData = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'department' => $request->department,
            'position' => $request->position,
        ];
        
        // Manejar avatar
        if ($request->hasFile('avatar')) {
            // Eliminar avatar anterior
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $userData['avatar'] = $avatarPath;
        }
        
        $user->update($userData);
        
        return back()->with('success', 'Perfil actualizado exitosamente.');
    }
    
    /**
     * Search users.
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json(['results' => []]);
        }
        
        $users = User::search($query)
            ->active()
            ->limit(10)
            ->get(['id', 'first_name', 'last_name', 'email', 'role', 'avatar']);
        
        return response()->json([
            'results' => $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->full_name,
                    'email' => $user->email,
                    'role' => $user->role_display_name,
                    'avatar' => $user->avatar_url,
                    'url' => route('users.show', $user),
                ];
            }),
        ]);
    }
    
    /**
     * Export users data.
     */
    public function export(Request $request)
    {
        $this->authorize('export', User::class);
        
        $users = User::with(['files', 'evidences'])->get();
        
        $filename = 'users_export_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function () use ($users) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'ID', 'Nombre', 'Apellido', 'Email', 'Rol', 'Departamento', 
                'Posición', 'Activo', 'Archivos', 'Evidencias', 'Último Login', 'Creado'
            ]);
            
            // Data
            foreach ($users as $user) {
                fputcsv($file, [
                    $user->id,
                    $user->first_name,
                    $user->last_name,
                    $user->email,
                    $user->role_display_name,
                    $user->department,
                    $user->position,
                    $user->is_active ? 'Sí' : 'No',
                    $user->files->count(),
                    $user->evidences->count(),
                    $user->last_login?->format('Y-m-d H:i:s') ?? 'Nunca',
                    $user->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
