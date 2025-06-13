<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
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

        request()->session()->regenerate();

        $user = Auth::user(); // Get the authenticated user via Auth facade

        if ($user && $user->role === 'admin') {
            return redirect()->intended(route('admin.dashboard', absolute: false));
        } elseif ($user && $user->role === 'tutor') {
            // Optionally, redirect tutors to their specific dashboard if it exists
            // and if you have a separate role check for 'tutor.dashboard' or similar
            // For now, let's assume tutors also go to the generic 'dashboard' or you can refine this.
            return redirect()->intended(route('tutor.dashboard', absolute: false)); // Or 'dashboard'
        } else {
            // For students or any other roles
            return redirect()->intended(route('home'));
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        request()->session()->invalidate();

        request()->session()->regenerateToken();

        return redirect('/');
    }
}
