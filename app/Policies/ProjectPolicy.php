<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
{
    /**
     * Determine whether the user can view any projects.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine whether the user can view the project.
     */
    public function view(User $user, Project $project): bool
    {
        return $project->canBeAccessedBy($user);
    }

    /**
     * Determine whether the user can create projects.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'analyst']);
    }

    /**
     * Determine whether the user can update the project.
     */
    public function update(User $user, Project $project): bool
    {
        return $project->canBeEditedBy($user);
    }

    /**
     * Determine whether the user can delete the project.
     */
    public function delete(User $user, Project $project): bool
    {
        return $user->isAdmin() || $project->project_manager_id === $user->id;
    }

    /**
     * Determine whether the user can manage project members.
     */
    public function manageMembers(User $user, Project $project): bool
    {
        return $project->canBeEditedBy($user);
    }

    /**
     * Determine whether the user can view project reports.
     */
    public function viewReports(User $user, Project $project): bool
    {
        return $project->canBeAccessedBy($user);
    }

    /**
     * Determine whether the user can export project data.
     */
    public function export(User $user): bool
    {
        return in_array($user->role, ['admin', 'analyst']);
    }

    /**
     * Determine whether the user can restore the project.
     */
    public function restore(User $user, Project $project): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the project.
     */
    public function forceDelete(User $user, Project $project): bool
    {
        return $user->isAdmin();
    }
}
