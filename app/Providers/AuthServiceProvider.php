<?php

namespace App\Providers;

use App\Models\Evidence;
use App\Models\File;
use App\Models\Group;
use App\Models\Message;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\TimeLog;
use App\Models\User;
use App\Policies\EvidencePolicy;
use App\Policies\FilePolicy;
use App\Policies\GroupPolicy;
use App\Policies\MessagePolicy;
use App\Policies\ProjectPolicy;
use App\Policies\MilestonePolicy;
use App\Policies\TimeLogPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        File::class => FilePolicy::class,
        Evidence::class => EvidencePolicy::class,
        Group::class => GroupPolicy::class,
        Message::class => MessagePolicy::class,
        Project::class => ProjectPolicy::class,
        ProjectMilestone::class => MilestonePolicy::class,
        TimeLog::class => TimeLogPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define custom gates
        Gate::define('manage-users', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('view-analytics', function (User $user) {
            return in_array($user->role, ['admin', 'analyst']);
        });

        Gate::define('manage-system', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('evaluate-evidences', function (User $user) {
            return in_array($user->role, ['admin', 'analyst', 'investigator']);
        });

        Gate::define('assign-evidences', function (User $user) {
            return in_array($user->role, ['admin', 'analyst']);
        });

        Gate::define('export-data', function (User $user) {
            return in_array($user->role, ['admin', 'analyst']);
        });

        Gate::define('manage-groups', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('send-system-messages', function (User $user) {
            return $user->isAdmin();
        });

        // Project management gates
        Gate::define('manage-projects', function (User $user) {
            return in_array($user->role, ['admin', 'manager']);
        });

        Gate::define('view-all-projects', function (User $user) {
            return $user->hasRole('admin');
        });

        Gate::define('manage-project-members', function (User $user, Project $project) {
            return $user->hasRole('admin') || $project->project_manager_id === $user->id;
        });

        Gate::define('approve-time-logs', function (User $user) {
            return in_array($user->role, ['admin', 'manager']);
        });

        Gate::define('view-team-analytics', function (User $user) {
            return in_array($user->role, ['admin', 'manager']);
        });

        Gate::define('export-project-data', function (User $user) {
            return in_array($user->role, ['admin', 'manager']);
        });

        Gate::define('manage-milestones', function (User $user, Project $project) {
            if ($user->hasRole('admin')) {
                return true;
            }

            if ($user->hasRole('manager') && $project->project_manager_id === $user->id) {
                return true;
            }

            $projectUser = $project->users()->where('users.id', $user->id)->first();
            return $projectUser && $projectUser->pivot->can_manage_milestones;
        });

        Gate::define('view-project-reports', function (User $user, Project $project) {
            if ($user->hasRole(['admin', 'manager'])) {
                return true;
            }

            $projectUser = $project->users()->where('users.id', $user->id)->first();
            return $projectUser && $projectUser->pivot->can_view_reports;
        });

        Gate::define('viewProfile', function (User $user, User $targetUser) {
            // Users can view their own profile
            if ($user->id === $targetUser->id) {
                return true;
            }

            // Admin can view any profile
            if ($user->hasRole('admin')) {
                return true;
            }

            // Managers can view profiles of users in their projects
            if ($user->hasRole('manager')) {
                return $user->projects()
                           ->whereHas('users', function ($q) use ($targetUser) {
                               $q->where('users.id', $targetUser->id);
                           })
                           ->exists();
            }

            // Users can view profiles of teammates in shared projects
            return $user->projects()
                       ->whereHas('users', function ($q) use ($targetUser) {
                           $q->where('users.id', $targetUser->id);
                       })
                       ->exists();
        });

        // Super admin gate (for future use)
        Gate::define('super-admin', function (User $user) {
            return $user->email === 'admin@company.com' && $user->isAdmin();
        });
    }
}
