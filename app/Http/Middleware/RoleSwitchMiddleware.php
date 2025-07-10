<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleSwitchMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  $role  The role that should be allowed to access this route
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        // Check if the user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // CASE 1: Admin with role switching
        // If user is admin and has a current_role in session, check against that instead
        if ($user->role === 'admin' && session()->has('current_role')) {
            $currentRole = session()->get('current_role');

            // If the current role matches the required role, allow access
            // This allows admins to access routes of other roles when they're "switching"
            if ($currentRole === $role) {
                return $next($request);
            }
        }

        // CASE 2: User with the correct role
        // The user actually has the role required for this route
        if ($user->role === $role) {
            return $next($request);
        }

        // CASE 3: Access denied
        // The user doesn't have the required role and isn't an admin switching roles
        session()->flash('error', 'You do not have permission to access this page.');

        return redirect()->route('home')->with('error', 'Unauthorized action.');
    }
}
