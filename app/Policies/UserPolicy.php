<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any users.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'analyst']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // Admins pueden ver cualquier usuario
        if ($user->isAdmin()) {
            return true;
        }

        // Los usuarios pueden ver su propio perfil
        if ($user->id === $model->id) {
            return true;
        }

        // Analistas pueden ver otros usuarios
        if ($user->role === 'analyst') {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create users.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Admins pueden actualizar cualquier usuario
        if ($user->isAdmin()) {
            return true;
        }

        // Los usuarios pueden actualizar su propio perfil
        if ($user->id === $model->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Solo admins pueden eliminar usuarios
        if (!$user->isAdmin()) {
            return false;
        }

        // No puede eliminarse a sí mismo
        if ($user->id === $model->id) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can change roles.
     */
    public function changeRole(User $user, User $model): bool
    {
        // Solo admins pueden cambiar roles
        if (!$user->isAdmin()) {
            return false;
        }

        // No puede cambiar su propio rol
        if ($user->id === $model->id) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can toggle active status.
     */
    public function toggleStatus(User $user, User $model): bool
    {
        // Solo admins pueden cambiar el estado
        if (!$user->isAdmin()) {
            return false;
        }

        // No puede desactivarse a sí mismo
        if ($user->id === $model->id) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can reset passwords.
     */
    public function resetPassword(User $user, User $model): bool
    {
        // Admins pueden resetear cualquier contraseña
        if ($user->isAdmin()) {
            return true;
        }

        // Los usuarios pueden cambiar su propia contraseña
        if ($user->id === $model->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view user activity.
     */
    public function viewActivity(User $user, User $model): bool
    {
        // Admins pueden ver actividad de cualquier usuario
        if ($user->isAdmin()) {
            return true;
        }

        // Los usuarios pueden ver su propia actividad
        if ($user->id === $model->id) {
            return true;
        }

        // Analistas pueden ver actividad de otros usuarios
        if ($user->role === 'analyst') {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can impersonate other users.
     */
    public function impersonate(User $user, User $model): bool
    {
        // Solo admins pueden impersonar
        if (!$user->isAdmin()) {
            return false;
        }

        // No puede impersonar a sí mismo
        if ($user->id === $model->id) {
            return false;
        }

        // No puede impersonar a otros admins
        if ($model->isAdmin()) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can export user data.
     */
    public function export(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can manage user permissions.
     */
    public function managePermissions(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view user statistics.
     */
    public function viewStats(User $user): bool
    {
        return in_array($user->role, ['admin', 'analyst']);
    }
}
