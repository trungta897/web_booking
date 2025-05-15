<?php

namespace App\Http\Controllers;

use App\Models\Tutor;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PageController extends Controller
{
    public function index()
    {
        // Get featured tutors (high rating, with reviews)
        $featuredTutors = Cache::remember('featured_tutors', 3600, function () {
            return Tutor::with(['user', 'subjects'])
                ->withCount('reviews')
                ->withAvg('reviews', 'rating')
                ->having('reviews_count', '>', 0)
                ->orderBy('reviews_avg_rating', 'desc')
                ->take(6)
                ->get();
        });

        // Get popular subjects
        $popularSubjects = Cache::remember('popular_subjects', 3600, function () {
            return Subject::withCount('tutors')
                ->orderBy('tutors_count', 'desc')
                ->take(8)
                ->get();
        });

        return view('welcome', compact('featuredTutors', 'popularSubjects'));
    }

    public function howItWorks()
    {
        return view('pages.how-it-works');
    }

    public function pricing()
    {
        return view('pages.pricing');
    }
}
