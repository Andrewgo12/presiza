<?php

namespace App\Policies;

use App\Models\Milestone;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MilestonePolicy
{
    /**
     * Determine whether the user can view any milestones.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'manager', 'developer']);
    }

    /**
     * Determine whether the user can view the milestone.
     */
    public function view(User $user, Milestone $milestone): bool
    {
        // Admin can view all milestones
        if ($user->hasRole('admin')) {
            return true;
        }

        // Check if user is part of the project
        return $milestone->project->users()->where('users.id', $user->id)->exists();
    }

    /**
     * Determine whether the user can create milestones.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'manager']);
    }

    /**
     * Determine whether the user can update the milestone.
     */
    public function update(User $user, Milestone $milestone): bool
    {
        // Admin can update all milestones
        if ($user->hasRole('admin')) {
            return true;
        }

        // Project manager can update milestones in their projects
        if ($user->hasRole('manager') && $milestone->project->project_manager_id === $user->id) {
            return true;
        }

        // Check if user has milestone management permissions in the project
        $projectUser = $milestone->project->users()
                                         ->where('users.id', $user->id)
                                         ->first();

        if ($projectUser && $projectUser->pivot->can_manage_milestones) {
            return true;
        }

        // Assigned user can update their own milestone
        return $milestone->assigned_to === $user->id;
    }

    /**
     * Determine whether the user can delete the milestone.
     */
    public function delete(User $user, Milestone $milestone): bool
    {
        // Admin can delete all milestones
        if ($user->hasRole('admin')) {
            return true;
        }

        // Project manager can delete milestones in their projects
        if ($user->hasRole('manager') && $milestone->project->project_manager_id === $user->id) {
            return true;
        }

        // Cannot delete completed milestones unless admin
        if ($milestone->status === Milestone::STATUS_COMPLETED && !$user->hasRole('admin')) {
            return false;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the milestone.
     */
    public function restore(User $user, Milestone $milestone): bool
    {
        return $user->hasRole(['admin', 'manager']);
    }

    /**
     * Determine whether the user can permanently delete the milestone.
     */
    public function forceDelete(User $user, Milestone $milestone): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can assign the milestone to someone.
     */
    public function assign(User $user, Milestone $milestone): bool
    {
        // Admin can assign any milestone
        if ($user->hasRole('admin')) {
            return true;
        }

        // Project manager can assign milestones in their projects
        if ($user->hasRole('manager') && $milestone->project->project_manager_id === $user->id) {
            return true;
        }

        // Check if user has milestone management permissions in the project
        $projectUser = $milestone->project->users()
                                         ->where('users.id', $user->id)
                                         ->first();

        return $projectUser && $projectUser->pivot->can_manage_milestones;
    }

    /**
     * Determine whether the user can update milestone progress.
     */
    public function updateProgress(User $user, Milestone $milestone): bool
    {
        // Admin can update any milestone progress
        if ($user->hasRole('admin')) {
            return true;
        }

        // Project manager can update progress in their projects
        if ($user->hasRole('manager') && $milestone->project->project_manager_id === $user->id) {
            return true;
        }

        // Assigned user can update their milestone progress
        if ($milestone->assigned_to === $user->id) {
            return true;
        }

        // Check if user has milestone management permissions in the project
        $projectUser = $milestone->project->users()
                                         ->where('users.id', $user->id)
                                         ->first();

        return $projectUser && $projectUser->pivot->can_manage_milestones;
    }

    /**
     * Determine whether the user can mark milestone as completed.
     */
    public function complete(User $user, Milestone $milestone): bool
    {
        // Cannot complete already completed milestones
        if ($milestone->status === Milestone::STATUS_COMPLETED) {
            return false;
        }

        // Admin can complete any milestone
        if ($user->hasRole('admin')) {
            return true;
        }

        // Project manager can complete milestones in their projects
        if ($user->hasRole('manager') && $milestone->project->project_manager_id === $user->id) {
            return true;
        }

        // Assigned user can complete their milestone
        if ($milestone->assigned_to === $user->id) {
            return true;
        }

        // Check if user has milestone management permissions in the project
        $projectUser = $milestone->project->users()
                                         ->where('users.id', $user->id)
                                         ->first();

        return $projectUser && $projectUser->pivot->can_manage_milestones;
    }

    /**
     * Determine whether the user can reorder milestones.
     */
    public function reorder(User $user, Milestone $milestone): bool
    {
        // Admin can reorder any milestone
        if ($user->hasRole('admin')) {
            return true;
        }

        // Project manager can reorder milestones in their projects
        if ($user->hasRole('manager') && $milestone->project->project_manager_id === $user->id) {
            return true;
        }

        // Check if user has milestone management permissions in the project
        $projectUser = $milestone->project->users()
                                         ->where('users.id', $user->id)
                                         ->first();

        return $projectUser && $projectUser->pivot->can_manage_milestones;
    }

    /**
     * Determine whether the user can view milestone reports.
     */
    public function viewReports(User $user, Milestone $milestone): bool
    {
        // Admin can view all reports
        if ($user->hasRole('admin')) {
            return true;
        }

        // Project manager can view reports for their projects
        if ($user->hasRole('manager') && $milestone->project->project_manager_id === $user->id) {
            return true;
        }

        // Check if user has report viewing permissions in the project
        $projectUser = $milestone->project->users()
                                         ->where('users.id', $user->id)
                                         ->first();

        return $projectUser && $projectUser->pivot->can_view_reports;
    }

    /**
     * Determine whether the user can export milestone data.
     */
    public function export(User $user, Milestone $milestone): bool
    {
        return $this->viewReports($user, $milestone);
    }

    /**
     * Determine whether the user can manage milestone dependencies.
     */
    public function manageDependencies(User $user, Milestone $milestone): bool
    {
        // Admin can manage dependencies for any milestone
        if ($user->hasRole('admin')) {
            return true;
        }

        // Project manager can manage dependencies in their projects
        if ($user->hasRole('manager') && $milestone->project->project_manager_id === $user->id) {
            return true;
        }

        // Check if user has milestone management permissions in the project
        $projectUser = $milestone->project->users()
                                         ->where('users.id', $user->id)
                                         ->first();

        return $projectUser && $projectUser->pivot->can_manage_milestones;
    }

    /**
     * Determine whether the user can manage milestone budget.
     */
    public function manageBudget(User $user, Milestone $milestone): bool
    {
        // Only admin and project managers can manage budget
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('manager') && $milestone->project->project_manager_id === $user->id;
    }
}
