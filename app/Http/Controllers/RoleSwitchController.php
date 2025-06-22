<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RoleSwitchController extends Controller
{
    /**
     * Valid roles that can be switched to
     */
    protected const VALID_ROLES = ['admin', 'tutor', 'student'];

    /**
     * Switch user to a different role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $role
     * @return \Illuminate\Http\Response
     */
    public function switchToRole(Request $request, string $role)
    {
        $user = Auth::user();

        // Security check: Only admins can switch roles
        if ($user->role !== 'admin') {
            Log::warning("Non-admin user {$user->id} attempted to switch roles", [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'requested_role' => $role
            ]);
            return redirect()->back()->with('error', __('common.access_denied'));
        }

        // Validate the requested role
        if (!in_array($role, self::VALID_ROLES)) {
            Log::warning("Invalid role switch attempt by user {$user->id}", [
                'user_id' => $user->id,
                'requested_role' => $role
            ]);
            return redirect()->back()->with('error', __('common.invalid_role'));
        }

        // If trying to switch to current actual role, just clear session
        if ($role === $user->role) {
            $request->session()->forget(['original_role', 'current_role']);
            Log::info("Admin user {$user->id} returned to original role");
            return redirect()->route('admin.dashboard')->with('success', __('common.viewing_normal_role'));
        }

        // Store original role in session if not already stored
        if (!$request->session()->has('original_role')) {
            $request->session()->put('original_role', $user->role);
        }

        // Store current role in session
        $request->session()->put('current_role', $role);

        Log::info("Admin user {$user->id} switched to role: {$role}");

        // Determine where to redirect based on the new role
        $redirectRoute = match($role) {
            'admin' => 'admin.dashboard',
            'tutor' => 'dashboard',
            'student' => 'dashboard',
            default => 'dashboard'
        };

        $message = __('common.switched_to_role', ['role' => ucfirst($role)]);

        return redirect()->route($redirectRoute)->with('success', $message);
    }

    /**
     * Switch back to original role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function switchBack(Request $request)
    {
        $user = Auth::user();

        // Security check: Only session-switching users can switch back
        if (!$request->session()->has('current_role')) {
            return redirect()->back()->with('error', __('common.not_switching_roles'));
        }

        // Security check: Only admins can use role switching
        if ($user->role !== 'admin') {
            Log::warning("Non-admin user {$user->id} attempted to switch back roles", [
                'user_id' => $user->id,
                'user_role' => $user->role
            ]);
            return redirect()->back()->with('error', __('common.access_denied'));
        }

        $originalRole = $request->session()->get('original_role', $user->role);
        $currentRole = $request->session()->get('current_role');

        // Clear role switching session data
        $request->session()->forget(['original_role', 'current_role']);

        Log::info("User {$user->id} switched back from role '{$currentRole}' to original role: {$originalRole}");

        // Determine redirect route based on original role
        $redirectRoute = match($originalRole) {
            'admin' => 'admin.dashboard',
            'tutor' => 'dashboard',
            'student' => 'dashboard',
            default => 'admin.dashboard' // Default to admin since only admins can switch
        };

        return redirect()->route($redirectRoute)->with('success', __('common.returned_to_original_role'));
    }
}
