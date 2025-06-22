<?php

namespace App\Http\Controllers;

use App\Http\Requests\TutorProfileRequest;
use App\Models\Subject;
use App\Models\Tutor;
use App\Services\TutorService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TutorProfileController extends Controller
{
    protected TutorService $tutorService;

    public function __construct(TutorService $tutorService)
    {
        $this->tutorService = $tutorService;
    }

    /**
     * Display tutor profile
     */
    public function show(): View
    {
        $user = Auth::user();
        $tutor = $user->tutor;

        if (! $tutor instanceof Tutor) {
            return view('tutors.profile.show', compact('tutor'));
        }

        $profileData = $this->tutorService->getTutorProfileData($tutor);

        return view('tutors.profile.show', $profileData);
    }

    /**
     * Show edit profile form
     */
    public function edit(): View|RedirectResponse
    {
        $user = Auth::user();
        $tutor = $user->tutor;

        if (! $tutor instanceof Tutor) {
            return redirect()->route('profile.edit')
                ->with('info', __('Please create your tutor profile first'));
        }

        $editData = $this->tutorService->getEditProfileData($tutor);

        return view('tutors.profile.edit', $editData);
    }

    /**
     * Show create profile form
     */
    public function create(): View
    {
        $subjects = Subject::where('is_active', true)->get();

        return view('tutors.profile.create', compact('subjects'));
    }

    /**
     * Store new tutor profile
     */
    public function store(TutorProfileRequest $request): RedirectResponse
    {
        try {
            $this->tutorService->createTutorProfile(Auth::user(), $request->validated());

            return redirect()->route('tutor.profile.show')
                ->with('success', __('Tutor profile created successfully'));

        } catch (Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Update tutor profile
     */
    public function update(TutorProfileRequest $request): RedirectResponse
    {
        try {
            $tutor = Auth::user()->tutor()->first();

            if (! $tutor) {
                return redirect()->route('profile.edit')
                    ->with('error', __('Tutor profile not found'));
            }

            $this->tutorService->updateTutorProfile($tutor, $request->validated());

            return redirect()->route('tutor.profile.show')
                ->with('success', __('Profile updated successfully'));

        } catch (Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Delete tutor profile
     */
    public function destroy(): RedirectResponse
    {
        try {
            $tutor = Auth::user()->tutor()->first();

            if (! $tutor) {
                return redirect()->route('tutor.dashboard')
                    ->with('error', __('No tutor profile found'));
            }

            $this->tutorService->deleteTutorProfile($tutor);

            return redirect()->route('tutor.dashboard')
                ->with('success', __('Tutor profile deleted successfully'));

        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
