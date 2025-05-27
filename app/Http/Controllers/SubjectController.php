<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::withCount('tutors')->get();
        return view('subjects.index', compact('subjects'));
    }

    public function listTutorsForSubject(Subject $subject)
    {
        $tutors = $subject->tutors()
            ->with(['user', 'subjects']) // Eager load user and subjects
            ->withCount('reviews')        // Get count of reviews
            ->withAvg('reviews', 'rating') // Get average rating
            ->paginate(10);

        return view('subjects.tutors', compact('subject', 'tutors'));
    }
}
