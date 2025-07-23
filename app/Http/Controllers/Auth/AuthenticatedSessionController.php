<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        // Update last login timestamp
        $user->updateLastLogin();

        // Get role-specific redirect URL
        $redirectUrl = $this->getRoleBasedRedirect($user);

        return redirect()->intended($redirectUrl);
    }

    /**
     * Get role-based redirect URL after login.
     */
    private function getRoleBasedRedirect(User $user): string
    {
        return match($user->role) {
            User::ROLE_ADMIN => route('admin.dashboard'),
            User::ROLE_MEDICAL => route('dashboard'),
            User::ROLE_EPS => route('dashboard'),
            User::ROLE_SYSTEMS => route('dashboard'),
            default => route('dashboard')
        };
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
