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
    public function switchToRole(Request $request, $role)
    {
        $user = Auth::user();

        // Security check: Only admins can switch roles
        if ($user->role !== 'admin') {
            Log::warning("Non-admin user {$user->id} attempted to switch roles", [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'requested_role' => $role
            ]);
            return redirect()->back()->with('error', 'Only administrators can switch roles.');
        }

        // Validate the requested role
        if (!in_array($role, self::VALID_ROLES)) {
            Log::warning("Invalid role switch attempt by user {$user->id}", [
                'user_id' => $user->id,
                'requested_role' => $role
            ]);
            return redirect()->back()->with('error', 'Invalid role specified.');
        }

        // If trying to switch to current actual role, just clear session
        if ($role === $user->role) {
            $request->session()->forget(['original_role', 'current_role']);
            return redirect()->route('admin.dashboard')->with('success', 'Viewing as your normal role.');
        }

        // Store original role in session if not already stored
        if (!$request->session()->has('original_role')) {
            $request->session()->put('original_role', $user->role);
        }

        // Store current role in session
        $request->session()->put('current_role', $role);

        Log::info("Admin user {$user->id} switched to role: {$role}");

        // Determine where to redirect based on the new role
        if ($role === 'admin') {
            return redirect()->route('admin.dashboard')->with('success', 'Switched to admin role.');
        } elseif ($role === 'tutor') {
            return redirect()->route('dashboard')->with('success', 'Switched to tutor role.');
        } else {
            return redirect()->route('dashboard')->with('success', 'Switched to student role.');
        }
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
        $originalRole = $request->session()->get('original_role', 'admin');

        // Security check: Only session-switching users can switch back
        if (!$request->session()->has('current_role')) {
            return redirect()->back()->with('error', 'You are not currently switching roles.');
        }

        // Clear role switching session data
        $request->session()->forget(['original_role', 'current_role']);

        Log::info("User {$user->id} switched back to original role: {$originalRole}");

        // Redirect based on original role
        if ($originalRole === 'admin') {
            return redirect()->route('admin.dashboard')->with('success', 'Returned to original role.');
        } else {
            return redirect()->route('dashboard')->with('success', 'Returned to original role.');
        }
    }
}
