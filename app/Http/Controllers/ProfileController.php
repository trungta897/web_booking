<?php

namespace App\Http\Controllers;

use App\Http\Requests\EducationUpdateRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Services\UserService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ProfileController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display the user's profile form.
     */
    public function edit(): View
    {
        $user = Auth::user();
        $tutor = null;
        $subjects = [];

        if ($user->role === 'tutor' && $user->tutor) {
            $tutor = $user->tutor()->with('education', 'subjects')->first();
            $subjects = \App\Models\Subject::where('is_active', true)->orderBy('name')->get();

            if ($tutor) {
                // Defensive check to prevent error on count() if education is unexpectedly null.
                $educationCount = $tutor->education ? $tutor->education->count() : 0;
                Log::debug('Tutor data loaded for profile edit', [
                    'tutor_id' => $tutor->id,
                    'education_count' => $educationCount,
                ]);
            }
        }

        return view('profile.edit', compact('user', 'tutor', 'subjects'));
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // Validate and update the user's profile information.

        try {
            $user = Auth::user();

            $this->userService->updateProfile($user, $request->validated());

            // Clear any cached user data and reload relationships
            $user->refresh();
            if ($user->role === 'tutor' && $user->tutor) {
                $user->tutor->refresh();
                $user->tutor->load(['education', 'subjects']);
            }

            Log::info('Profile updated successfully', [
                'user_id' => $user->id,
                'education_count' => $user->tutor?->education?->count() ?? 0,
            ]);

            return redirect()->route('profile.edit')
                ->with('success', 'Profile updated successfully');
        } catch (Exception $e) {
            Log::error('Profile update failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Update the tutor's education information.
     */
    public function updateEducation(EducationUpdateRequest $request): RedirectResponse
    {
        try {
            $user = Auth::user();
            // The authorize method in EducationUpdateRequest already ensures user is a tutor.
            $this->userService->updateTutorEducation($user->tutor, $request->validated()['education'] ?? []);
            Log::info('Tutor education updated successfully.', ['user_id' => $user->id]);

            return redirect()->route('profile.edit')->with('success', __('tutors.education_updated_successfully'));
        } catch (Exception $e) {
            Log::error('Tutor education update failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return back()->withInput()->withErrors(['error' => __('common.error_occurred')]);
        }
    }

    /**
     * Update user password.
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
     * Delete the user's account.
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
     * Upload avatar.
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
     * Remove avatar.
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
