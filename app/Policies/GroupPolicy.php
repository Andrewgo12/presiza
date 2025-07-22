<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class GroupPolicy
{
    /**
     * Determine whether the user can view any groups.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine whether the user can view the group.
     */
    public function view(User $user, Group $group): bool
    {
        // Admins pueden ver cualquier grupo
        if ($user->isAdmin()) {
            return true;
        }

        // Grupos públicos pueden ser vistos por todos
        if ($group->type === 'public') {
            return true;
        }

        // Grupos privados solo por miembros
        if ($group->type === 'private') {
            return $group->hasMember($user);
        }

        // Grupos restringidos solo por miembros y ciertos roles
        if ($group->type === 'restricted') {
            return $group->hasMember($user) || in_array($user->role, ['admin', 'analyst', 'investigator']);
        }

        return false;
    }

    /**
     * Determine whether the user can create groups.
     */
    public function create(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine whether the user can update the group.
     */
    public function update(User $user, Group $group): bool
    {
        return $group->canManage($user);
    }

    /**
     * Determine whether the user can delete the group.
     */
    public function delete(User $user, Group $group): bool
    {
        // Solo el creador o admin pueden eliminar
        return $group->created_by === $user->id || $user->isAdmin();
    }

    /**
     * Determine whether the user can join the group.
     */
    public function join(User $user, Group $group): bool
    {
        // No puede unirse si ya es miembro
        if ($group->hasMember($user)) {
            return false;
        }

        // Solo grupos públicos permiten unirse libremente
        return $group->type === 'public';
    }

    /**
     * Determine whether the user can leave the group.
     */
    public function leave(User $user, Group $group): bool
    {
        // Debe ser miembro para poder salir
        if (!$group->hasMember($user)) {
            return false;
        }

        // El creador no puede salir del grupo
        if ($group->created_by === $user->id) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can invite others to the group.
     */
    public function invite(User $user, Group $group): bool
    {
        return $group->canManage($user);
    }

    /**
     * Determine whether the user can manage members.
     */
    public function manageMembers(User $user, Group $group): bool
    {
        return $group->canManage($user);
    }

    /**
     * Determine whether the user can send messages to the group.
     */
    public function sendMessage(User $user, Group $group): bool
    {
        // Debe ser miembro del grupo
        return $group->hasMember($user);
    }

    /**
     * Determine whether the user can restore the group.
     */
    public function restore(User $user, Group $group): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the group.
     */
    public function forceDelete(User $user, Group $group): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can export group data.
     */
    public function export(User $user): bool
    {
        return in_array($user->role, ['admin', 'analyst']);
    }
}
