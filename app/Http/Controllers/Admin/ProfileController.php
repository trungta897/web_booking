<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function show()
    {
        $admin = Auth::user();

        return view('admin.profile.show', compact('admin'));
    }

    public function suspend(Request $request)
    {
        $admin = Auth::user();

        // Basic check: Prevent self-suspension if they are the only admin
        if ($admin->role === 'admin') {
            $otherAdmins = User::where('role', 'admin')->where('id', '!=', $admin->id)->where('account_status', 'active')->count();
            if ($otherAdmins === 0) {
                return redirect()->route('admin.profile.show')->with('error', 'You cannot suspend your own account as you are the only active admin.');
            }
        }

        $admin->account_status = 'suspended';
        $admin->save();

        Auth::logout(); // Log the admin out after suspension
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Your account has been suspended.');
    }
}
