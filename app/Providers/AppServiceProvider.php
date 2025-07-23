<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Pagination\Paginator;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set default string length for MySQL
        Schema::defaultStringLength(191);
        
        // Use Bootstrap for pagination
        Paginator::useBootstrapFive();
        
        // Share common data with all views
        View::composer('*', function ($view) {
            if (auth()->check()) {
                $view->with('currentUser', auth()->user());
                $view->with('unreadNotifications', auth()->user()->unreadNotifications()->count());
            }
        });
        
        // Define global gates
        Gate::define('admin-access', function (User $user) {
            return $user->role === 'admin';
        });
        
        Gate::define('analyst-access', function (User $user) {
            return in_array($user->role, ['admin', 'analyst']);
        });
        
        Gate::define('investigator-access', function (User $user) {
            return in_array($user->role, ['admin', 'analyst', 'investigator']);
        });
    }
}
