<?php

namespace App\Http\Controllers;

use App\Models\Tutor;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\Booking;
use App\Models\Review;

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
            $query->whereHas('subjects', function ($q) use ($request) {
                $q->where('subjects.id', $request->subject);
            });
            // dd($query->toSql(), $query->getBindings()); // Temporary debug line
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

        // Filter by location
        if ($request->filled('location')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('users.address', 'like', '%' . $request->location . '%');
            });
        }

        // Filter by availability on a specific day
        if ($request->filled('day_of_week')) {
            $query->whereHas('availability', function($q) use ($request) {
                $q->where('day_of_week', $request->day_of_week)
                  ->where('is_available', true);
            });
        }

        // Filter by experience level
        if ($request->filled('experience')) {
            $query->where('experience_years', '>=', $request->experience);
        }

        // Sort options
        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'price_low':
                    $query->orderBy('hourly_rate', 'asc');
                    break;
                case 'price_high':
                    $query->orderBy('hourly_rate', 'desc');
                    break;
                case 'rating':
                    $query->orderBy('reviews_avg_rating', 'desc');
                    break;
                case 'experience':
                    $query->orderBy('experience_years', 'desc');
                    break;
                default:
                    $query->latest();
            }
        } else {
            // Default sorting
            $query->latest();
        }

        $tutors = $query->paginate(12);
        $allSubjects = Subject::all();
        $pageTitle = 'Find Your Perfect Tutor';
        $filteredSubject = null;

        if ($request->filled('subject')) {
            $filteredSubject = Subject::find($request->subject);
            if ($filteredSubject) {
                $pageTitle = 'Tutors for ' . $filteredSubject->name;
            }
        }

        // Cache the data, not the view
        Cache::put($cacheKey, [
            'tutors' => $tutors,
            'subjects' => $allSubjects,
            'pageTitle' => $pageTitle,
            'filteredSubject' => $filteredSubject
        ], 3600);

        return view('tutors.index', [
            'tutors' => $tutors,
            'subjects' => $allSubjects,
            'pageTitle' => $pageTitle,
            'filteredSubject' => $filteredSubject
        ]);
    }

    public function show(Tutor $tutor)
    {
        $cacheKey = 'tutor_' . $tutor->id;

        if (Cache::has($cacheKey)) {
            $data = Cache::get($cacheKey);
            return view('tutors.show', $data);
        }

        $tutor->load(['user', 'subjects', 'education', 'reviews.student']);
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

    public function storeReview(Request $request, Tutor $tutor)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:500',
            'booking_id' => 'required|exists:bookings,id',
        ]);

        // Check if the booking belongs to the current user and the tutor
        $booking = Booking::where('id', $validated['booking_id'])
            ->where('student_id', Auth::id())
            ->where('tutor_id', $tutor->id)
            ->where('status', 'completed')
            ->firstOrFail();

        // Check if a review already exists for this booking
        if (Review::where('booking_id', $booking->id)->exists()) {
            return back()->withErrors(['booking_id' => 'You have already reviewed this session.']);
        }

        $review = Review::create([
            'tutor_id' => $tutor->id,
            'student_id' => Auth::id(),
            'booking_id' => $booking->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
        ]);

        // Clear tutor cache
        Cache::forget('tutor_' . $tutor->id);

        return redirect()->route('tutors.show', $tutor)
            ->with('success', 'Your review has been submitted successfully.');
    }

    public function updateReview(Request $request, Review $review)
    {
        // Check if the user is authorized to update this review
        if (Auth::id() !== $review->student_id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:500',
        ]);

        $review->update($validated);

        // Clear tutor cache
        Cache::forget('tutor_' . $review->tutor_id);

        return redirect()->route('tutors.show', $review->tutor)
            ->with('success', 'Your review has been updated successfully.');
    }

    public function destroyReview(Review $review)
    {
        // Check if the user is authorized to delete this review
        if (Auth::id() !== $review->student_id) {
            abort(403, 'Unauthorized action.');
        }

        $tutorId = $review->tutor_id;
        $review->delete();

        // Clear tutor cache
        Cache::forget('tutor_' . $tutorId);

        return redirect()->route('tutors.show', $tutorId)
            ->with('success', 'Your review has been deleted successfully.');
    }

    /**
     * Display the tutor's availability management page
     *
     * @return \Illuminate\View\View
     */
    public function availability()
    {
        $user = Auth::user();
        $tutor = $user->tutor;
        $availabilities = $tutor->availability;

        return view('tutors.availability', compact('tutor', 'availabilities'));
    }

    /**
     * Update the tutor's availability settings
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateAvailability(Request $request)
    {
        $user = Auth::user();
        $tutor = $user->tutor;

        // Update tutor's overall availability
        $tutor->update([
            'is_available' => $request->has('is_available')
        ]);

        // Process each day's availability
        foreach ([0, 1, 2, 3, 4, 5, 6] as $day) {
            $isAvailable = isset($request->days[$day]['is_available']);

            // Get or create the availability for this day
            $availability = $tutor->availability()->firstOrNew([
                'day_of_week' => $day
            ]);

            // Update availability settings
            $availability->is_available = $isAvailable;

            if ($isAvailable) {
                $availability->start_time = $request->days[$day]['start_time'] ?? '09:00:00';
                $availability->end_time = $request->days[$day]['end_time'] ?? '17:00:00';
            }

            $availability->save();
        }

        return redirect()->route('tutors.availability')
            ->with('success', 'Your availability has been updated successfully.');
    }
}
