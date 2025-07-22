<?php

namespace App\Policies;

use App\Models\TimeLog;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TimeLogPolicy
{
    /**
     * Determine whether the user can view any time logs.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine whether the user can view the time log.
     */
    public function view(User $user, TimeLog $timeLog): bool
    {
        // User can view their own time logs
        if ($timeLog->user_id === $user->id) {
            return true;
        }

        // Admin can view all time logs
        if ($user->isAdmin()) {
            return true;
        }

        // Project manager can view time logs for their projects
        if ($timeLog->project->project_manager_id === $user->id) {
            return true;
        }

        // Analysts can view time logs
        if ($user->role === 'analyst') {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create time logs.
     */
    public function create(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine whether the user can update the time log.
     */
    public function update(User $user, TimeLog $timeLog): bool
    {
        return $timeLog->canBeEditedBy($user);
    }

    /**
     * Determine whether the user can delete the time log.
     */
    public function delete(User $user, TimeLog $timeLog): bool
    {
        // Only the user who created the time log can delete it (if not approved)
        if ($timeLog->user_id === $user->id && !$timeLog->is_approved) {
            return true;
        }

        // Admin can delete any time log
        if ($user->isAdmin()) {
            return true;
        }

        // Project manager can delete time logs for their projects
        if ($timeLog->project->project_manager_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can approve the time log.
     */
    public function approve(User $user, TimeLog $timeLog): bool
    {
        return $timeLog->canBeApprovedBy($user);
    }

    /**
     * Determine whether the user can export time logs.
     */
    public function export(User $user): bool
    {
        return in_array($user->role, ['admin', 'analyst']);
    }
}
