<?php

namespace App\Http\Controllers;

use App\Models\TutorProfile;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TutorProfileController extends Controller
{
    public function show()
    {
        $tutorProfile = Auth::user()->tutorProfile;
        return view('tutor.profile.show', compact('tutorProfile'));
    }

    public function edit()
    {
        $tutorProfile = Auth::user()->tutorProfile;
        $subjects = Subject::all();
        return view('tutor.profile.edit', compact('tutorProfile', 'subjects'));
    }

    public function update(Request $request)
    {
        $tutorProfile = Auth::user()->tutorProfile;

        $validated = $request->validate([
            'hourly_rate' => ['required', 'numeric', 'min:0'],
            'experience_years' => ['required', 'integer', 'min:0'],
            'bio' => ['required', 'string', 'max:1000'],
            'subjects' => ['required', 'array'],
            'subjects.*' => ['exists:subjects,id'],
            'education' => ['required', 'array'],
            'education.*.degree' => ['required', 'string', 'max:255'],
            'education.*.institution' => ['required', 'string', 'max:255'],
            'education.*.field_of_study' => ['nullable', 'string', 'max:255'],
            'education.*.start_year' => ['required', 'numeric', 'min:1900', 'max:' . date('Y')],
            'education.*.end_year' => ['nullable', 'numeric', 'min:1900', 'max:' . (date('Y') + 10)],
            'education.*.description' => ['nullable', 'string', 'max:500'],
        ]);

        $tutorProfile->update([
            'hourly_rate' => $validated['hourly_rate'],
            'experience_years' => $validated['experience_years'],
            'bio' => $validated['bio'],
        ]);

        // Update subjects
        $tutorProfile->subjects()->sync($validated['subjects']);

        // Update education
        $tutorProfile->education()->delete();
        foreach ($validated['education'] as $education) {
            $tutorProfile->education()->create($education);
        }

        return redirect()->route('tutor.profile.show')
            ->with('success', 'Profile updated successfully.');
    }
}
