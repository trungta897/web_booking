<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\UserService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display the user's profile form
     */
    public function edit(): View
    {
        $user = Auth::user();

        return view('profile.edit', compact('user'));
    }

    /**
     * Update the user's profile information
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        try {
            $this->userService->updateProfile(Auth::user(), $request->validated());

            return redirect()->route('profile.edit')
                ->with('success', __('Profile updated successfully'));

        } catch (Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'current_password' => 'required|current_password',
                'password' => 'required|min:8|confirmed',
            ]);

            $this->userService->updatePassword(Auth::user(), $validated);

            return back()->with('success', __('Password updated successfully'));

        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Delete the user's account
     */
    public function destroy(Request $request): RedirectResponse
    {
        try {
            $request->validate([
                'password' => 'required|current_password',
            ]);

            $this->userService->deleteAccount(Auth::user());

            return redirect('/')->with('success', __('Account deleted successfully'));

        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Upload avatar
     */
    public function uploadAvatar(Request $request): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $this->userService->uploadAvatar(Auth::user(), $validated['avatar']);

            return back()->with('success', __('Avatar uploaded successfully'));

        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove avatar
     */
    public function removeAvatar(): RedirectResponse
    {
        try {
            $this->userService->removeAvatar(Auth::user());

            return back()->with('success', __('Avatar removed successfully'));

        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
