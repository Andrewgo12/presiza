<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
// use Intervention\Image\Facades\Image;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update the user's avatar.
     */
    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        $user = Auth::user();

        // Delete old avatar if exists
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Process and store new avatar
        $image = $request->file('avatar');
        $filename = 'avatar_' . $user->id . '_' . time() . '.' . $image->getClientOriginalExtension();

        // Store the image directly (without resizing for now)
        $path = $image->storeAs('avatars', $filename, 'public');

        // Update user avatar path
        $user->update(['avatar' => $path]);

        return back()->with('success', 'Avatar actualizado exitosamente.');
    }

    /**
     * Update the user's notification settings.
     */
    public function updateNotifications(Request $request): RedirectResponse
    {
        $request->validate([
            'email_notifications' => ['boolean'],
            'push_notifications' => ['boolean'],
            'evidence_notifications' => ['boolean'],
            'message_notifications' => ['boolean'],
            'project_notifications' => ['boolean'],
        ]);

        $user = Auth::user();
        
        $settings = [
            'email_notifications' => $request->boolean('email_notifications'),
            'push_notifications' => $request->boolean('push_notifications'),
            'evidence_notifications' => $request->boolean('evidence_notifications'),
            'message_notifications' => $request->boolean('message_notifications'),
            'project_notifications' => $request->boolean('project_notifications'),
        ];

        $user->update(['notification_settings' => $settings]);

        return back()->with('success', 'Configuración de notificaciones actualizada.');
    }

    /**
     * Update the user's privacy settings.
     */
    public function updatePrivacy(Request $request): RedirectResponse
    {
        $request->validate([
            'profile_visibility' => ['required', 'in:public,private,team'],
            'show_email' => ['boolean'],
            'show_department' => ['boolean'],
            'show_position' => ['boolean'],
            'allow_messages' => ['boolean'],
        ]);

        $user = Auth::user();
        
        $settings = [
            'profile_visibility' => $request->profile_visibility,
            'show_email' => $request->boolean('show_email'),
            'show_department' => $request->boolean('show_department'),
            'show_position' => $request->boolean('show_position'),
            'allow_messages' => $request->boolean('allow_messages'),
        ];

        $user->update(['privacy_settings' => $settings]);

        return back()->with('success', 'Configuración de privacidad actualizada.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Delete avatar if exists
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Search users for mentions, assignments, etc.
     */
    public function searchUsers(Request $request)
    {
        $query = $request->get('q');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $users = User::where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->select('id', 'first_name', 'last_name', 'email', 'avatar', 'department', 'position')
            ->limit(10)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->full_name,
                    'email' => $user->email,
                    'avatar' => $user->avatar ? Storage::url($user->avatar) : null,
                    'department' => $user->department,
                    'position' => $user->position,
                ];
            });

        return response()->json($users);
    }
}
