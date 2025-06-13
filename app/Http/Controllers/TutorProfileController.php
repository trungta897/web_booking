<?php

namespace App\Http\Controllers;

use App\Models\Tutor;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TutorProfileController extends Controller
{
    public function show()
    {
        $tutor = Auth::user()->tutor;
        if (!$tutor) {
            return view('tutor.profile.show', compact('tutor'));
        }
        $tutor->load('user', 'subjects', 'education');
        return view('tutor.profile.show', compact('tutor'));
    }

    public function edit()
    {
        $tutor = Auth::user()->tutor;
        if (!$tutor) {
            abort(404, 'Tutor profile not found. Please create one.');
        }
        $tutor->load('user', 'subjects', 'education');
        $subjects = Subject::all();
        return view('tutor.profile.edit', compact('tutor', 'subjects'));
    }

    public function update(Request $request)
    {
        $tutor = Auth::user()->tutor;
        if (!$tutor) {
            abort(404, 'Tutor profile not found.');
        }

        $validated = $request->validate([
            'hourly_rate' => ['required', 'numeric', 'min:0'],
            'experience_years' => ['required', 'integer', 'min:0'],
            'bio' => ['required', 'string', 'max:1000'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'is_available' => ['sometimes', 'boolean'],
            'subjects' => ['required', 'array'],
            'subjects.*' => ['exists:subjects,id'],
            'education' => ['sometimes', 'array'],
            'education.*.degree' => ['required_with:education', 'string', 'max:255'],
            'education.*.institution' => ['required_with:education', 'string', 'max:255'],
            'education.*.field_of_study' => ['nullable', 'string', 'max:255'],
            'education.*.start_year' => ['required_with:education', 'numeric', 'min:1900', 'max:' . date('Y')],
            'education.*.end_year' => ['nullable', 'numeric', 'min:1900', 'max:' . (date('Y') + 10)],
            'education.*.description' => ['nullable', 'string', 'max:500'],
        ]);

        $tutorData = [
            'hourly_rate' => $validated['hourly_rate'],
            'experience_years' => $validated['experience_years'],
            'bio' => $validated['bio'],
            'specialization' => $validated['specialization'] ?? $tutor->specialization,
        ];
        if ($request->has('is_available')) {
            $tutorData['is_available'] = $request->boolean('is_available');
        }

        $tutor->update($tutorData);

        if ($request->has('subjects')) {
            $tutor->subjects()->sync($validated['subjects']);
        }

        if ($request->has('education')) {
            $tutor->education()->delete();
            foreach ($validated['education'] as $eduData) {
                $tutor->education()->create($eduData);
            }
        }

        return redirect()->route('tutor.profile.show')
            ->with('success', 'Profile updated successfully.');
    }
}
