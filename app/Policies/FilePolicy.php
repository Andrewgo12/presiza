<?php

namespace App\Policies;

use App\Models\File;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FilePolicy
{
    /**
     * Determine whether the user can view any files.
     */
    public function viewAny(User $user): bool
    {
        return true; // Todos los usuarios pueden ver la lista de archivos
    }

    /**
     * Determine whether the user can view the file.
     */
    public function view(User $user, File $file): bool
    {
        // Admins pueden ver cualquier archivo
        if ($user->isAdmin()) {
            return true;
        }

        // El propietario puede ver su archivo
        if ($file->uploaded_by === $user->id) {
            return true;
        }

        // Archivos públicos pueden ser vistos por todos
        if ($file->is_public && $file->access_level === 'public') {
            return true;
        }

        // Archivos internos pueden ser vistos por usuarios autenticados
        if ($file->access_level === 'internal') {
            return true;
        }

        // Archivos restringidos solo por analistas e investigadores
        if ($file->access_level === 'restricted') {
            return in_array($user->role, ['admin', 'analyst', 'investigator']);
        }

        // Archivos confidenciales solo por admins y analistas
        if ($file->access_level === 'confidential') {
            return in_array($user->role, ['admin', 'analyst']);
        }

        return false;
    }

    /**
     * Determine whether the user can create files.
     */
    public function create(User $user): bool
    {
        return $user->is_active; // Solo usuarios activos pueden crear archivos
    }

    /**
     * Determine whether the user can update the file.
     */
    public function update(User $user, File $file): bool
    {
        // Admins pueden actualizar cualquier archivo
        if ($user->isAdmin()) {
            return true;
        }

        // El propietario puede actualizar su archivo
        if ($file->uploaded_by === $user->id) {
            return true;
        }

        // Analistas pueden actualizar archivos no confidenciales
        if ($user->role === 'analyst' && $file->access_level !== 'confidential') {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the file.
     */
    public function delete(User $user, File $file): bool
    {
        // Admins pueden eliminar cualquier archivo
        if ($user->isAdmin()) {
            return true;
        }

        // El propietario puede eliminar su archivo si no es confidencial
        if ($file->uploaded_by === $user->id && $file->access_level !== 'confidential') {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can download the file.
     */
    public function download(User $user, File $file): bool
    {
        // Usar la misma lógica que view
        return $this->view($user, $file);
    }

    /**
     * Determine whether the user can share the file.
     */
    public function share(User $user, File $file): bool
    {
        // Solo se pueden compartir archivos que el usuario puede ver
        if (!$this->view($user, $file)) {
            return false;
        }

        // No se pueden compartir archivos confidenciales a menos que sea admin
        if ($file->access_level === 'confidential' && !$user->isAdmin()) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can restore the file.
     */
    public function restore(User $user, File $file): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the file.
     */
    public function forceDelete(User $user, File $file): bool
    {
        return $user->isAdmin();
    }
}
