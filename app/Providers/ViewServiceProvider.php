<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share role-based CSS classes with all views
        View::composer('*', function ($view) {
            $roleClass = '';
            $roleCss = [];
            
            if (Auth::check()) {
                $userRole = Auth::user()->role ?? 'user';
                $roleClass = 'role-' . strtolower($userRole);
                
                // Determine which CSS files to load based on role
                switch (strtolower($userRole)) {
                    case 'admin':
                        $roleCss[] = 'admin-dashboard.css';
                        break;
                    case 'medical':
                    case 'doctor':
                        $roleCss[] = 'medical-dashboard.css';
                        break;
                    case 'eps':
                    case 'analyst':
                        $roleCss[] = 'eps-dashboard.css';
                        break;
                    case 'systems':
                    case 'system':
                        $roleCss[] = 'systems-dashboard.css';
                        break;
                }
            }
            
            // Determine which CSS files to load based on current route/view
            $currentRoute = request()->route() ? request()->route()->getName() : '';
            $viewName = $view->getName();
            $viewCss = [];

            // Individual view CSS mapping for specific views - COMPLETE COVERAGE
            $viewMappings = [
                // Welcome/Landing
                'welcome' => 'welcome.css',

                // Dashboard
                'dashboard' => 'dashboard.css',
                'dashboard.index' => 'dashboard.css',

                // Evidence views
                'evidences.index' => 'evidences-index.css',
                'evidences.create' => 'evidences-create.css',
                'evidences.show' => 'evidences-show.css',
                'evidences.edit' => 'evidences-edit.css',

                // File views
                'files.index' => 'files-index.css',
                'files.create' => 'files-create.css',
                'files.show' => 'files-show.css',

                // Admin views - Complete Coverage
                'admin.dashboard' => 'admin-dashboard.css',
                'admin.users.index' => 'admin-users.css',
                'admin.users.create' => 'admin-users.css',
                'admin.users.edit' => 'admin-users.css',
                'admin.users.show' => 'admin-users.css',
                'admin.analytics' => 'admin-analytics.css',
                'admin.analytics.index' => 'admin-analytics.css',
                'admin.backups' => 'admin-backups.css',
                'admin.backups.index' => 'admin-backups.css',
                'admin.logs' => 'admin-logs.css',
                'admin.logs.index' => 'admin-logs.css',
                'admin.settings' => 'admin-settings.css',
                'admin.settings.index' => 'admin-settings.css',

                // Project views
                'projects.index' => 'projects-index.css',
                'projects.create' => 'projects-create.css',
                'projects.show' => 'projects-show.css',
                'projects.edit' => 'projects-edit.css',

                // Profile views
                'profile.edit' => 'profile-edit.css',

                // Analytics views
                'analytics.index' => 'analytics-index.css',

                // Search views
                'search.index' => 'search-index.css',

                // Time logs views
                'time-logs.index' => 'time-logs-index.css',
                'time-logs.create' => 'time-logs-create.css',

                // Groups views
                'groups.index' => 'groups-index.css',

                // Messages views
                'messages.index' => 'messages-index.css',

                // Milestones views
                'milestones.index' => 'milestones-index.css',

                // Notifications views
                'notifications.index' => 'notifications-index.css',
            ];

            // Check for exact route name match first
            if (isset($viewMappings[$currentRoute])) {
                $viewCss[] = $viewMappings[$currentRoute];
            }
            // Check for exact view name match
            elseif (isset($viewMappings[$viewName])) {
                $viewCss[] = $viewMappings[$viewName];
            }
            // Fallback to general category CSS for unmapped routes
            else {
                if (str_contains($currentRoute, 'evidence') || str_contains($viewName, 'evidence')) {
                    $viewCss[] = 'evidence-management.css';
                }

                if (str_contains($currentRoute, 'file') || str_contains($viewName, 'file')) {
                    $viewCss[] = 'file-management.css';
                }

                if (str_contains($currentRoute, 'user') || str_contains($viewName, 'user')) {
                    $viewCss[] = 'user-management.css';
                }

                if (str_contains($currentRoute, 'report') || str_contains($currentRoute, 'analytic') ||
                    str_contains($viewName, 'report') || str_contains($viewName, 'analytic')) {
                    $viewCss[] = 'reports-analytics.css';
                }
            }
            
            // Get user color scheme if authenticated
            $userColorScheme = null;
            if (Auth::check()) {
                $userColorScheme = Auth::user()->getRoleColorScheme();
            }

            $view->with([
                'roleClass' => $roleClass,
                'roleCss' => $roleCss,
                'viewCss' => $viewCss,
                'userRole' => Auth::check() ? Auth::user()->role : null,
                'userColorScheme' => $userColorScheme,
                'isAuthenticated' => Auth::check(),
                'currentUser' => Auth::user()
            ]);
        });
    }
}
