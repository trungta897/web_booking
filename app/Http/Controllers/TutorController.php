<?php

namespace App\Http\Controllers;

use App\Models\Tutor;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class TutorController extends Controller
{
    public function index(Request $request)
    {
        $cacheKey = 'tutors_' . md5($request->fullUrl());

        if (Cache::has($cacheKey)) {
            $data = Cache::get($cacheKey);
            return view('tutors.index', $data);
        }

        $query = Tutor::with(['user', 'subjects', 'reviews'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');

        // Filter by subject
        if ($request->filled('subject')) {
            $query->join('subject_tutor', 'tutors.id', '=', 'subject_tutor.tutor_id')
                  ->where('subject_tutor.subject_id', $request->subject)
                  ->select('tutors.*');
        }

        // Filter by price range
        if ($request->filled('price_range')) {
            $range = explode('-', $request->price_range);
            if (count($range) === 2) {
                $query->whereBetween('hourly_rate', [$range[0], $range[1]]);
            } else {
                $query->where('hourly_rate', '>=', substr($request->price_range, 0, -1));
            }
        }

        // Filter by minimum rating
        if ($request->filled('rating')) {
            $query->having('reviews_avg_rating', '>=', $request->rating);
        }

        $tutors = $query->paginate(12);
        $subjects = Subject::all();

        // Cache the data, not the view
        Cache::put($cacheKey, [
            'tutors' => $tutors,
            'subjects' => $subjects
        ], 3600);

        return view('tutors.index', compact('tutors', 'subjects'));
    }

    public function show(Tutor $tutor)
    {
        $cacheKey = 'tutor_' . $tutor->id;

        if (Cache::has($cacheKey)) {
            $data = Cache::get($cacheKey);
            return view('tutors.show', $data);
        }

        $tutor->load(['user', 'subjects', 'education', 'reviews.user']);
        $tutor->loadCount('reviews');
        $tutor->loadAvg('reviews', 'rating');

        // Cache the data, not the view
        Cache::put($cacheKey, [
            'tutor' => $tutor
        ], 3600);

        return view('tutors.show', compact('tutor'));
    }

    public function toggleFavorite(Tutor $tutor)
    {
        $user = Auth::user();

        if ($user->favoriteTutors()->where('tutor_id', $tutor->id)->exists()) {
            $user->favoriteTutors()->detach($tutor->id);
            $isFavorite = false;
        } else {
            $user->favoriteTutors()->attach($tutor->id);
            $isFavorite = true;
        }

        return response()->json(['is_favorite' => $isFavorite]);
    }

    public function checkAvailability(Tutor $tutor, $day)
    {
        $availability = $tutor->availability()
            ->where('day_of_week', strtolower($day))
            ->where('is_available', true)
            ->get();

        if ($availability->isEmpty()) {
            return response()->json([
                'available' => false
            ]);
        }

        $slots = [];
        foreach ($availability as $slot) {
            $slots[] = $slot->start_time . ' - ' . $slot->end_time;
        }

        return response()->json([
            'available' => true,
            'slots' => $slots
        ]);
    }
}
