<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewRequest;
use App\Models\Review;
use App\Models\Tutor;
use App\Services\TutorService;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TutorController extends Controller
{
    use AuthorizesRequests;

    protected TutorService $tutorService;

    public function __construct(TutorService $tutorService)
    {
        $this->tutorService = $tutorService;
    }

    /**
     * Display tutor dashboard
     */
    public function dashboard(): View|RedirectResponse
    {
        $user = Auth::user();

        if (! $user->tutor) {
            return redirect()->route('profile.edit')
                ->with('error', __('Please complete your tutor profile first'));
        }

        $dashboardData = $this->tutorService->getDashboardData($user->tutor);

        return view('tutors.dashboard', $dashboardData);
    }

    /**
     * Display tutors listing with filters
     */
    public function index(Request $request): View
    {
        $filters = $request->only([
            'subject', 'price_range', 'rating', 'location',
            'day_of_week', 'experience', 'sort',
        ]);

        $tutors = $this->tutorService->getTutorsWithFilters($filters);

        // Get all subjects for the filter dropdown
        $subjects = \App\Models\Subject::where('is_active', true)->orderBy('name')->get();

        return view('tutors.index', [
            'tutors' => $tutors,
            'subjects' => $subjects
        ]);
    }

    /**
     * Display tutor profile
     */
    public function show(Tutor $tutor): View
    {
        $tutorData = $this->tutorService->getTutorDetails($tutor->id);

        return view('tutors.show', [
            'tutor' => $tutorData
        ]);
    }

    /**
     * Toggle favorite tutor
     */
    public function toggleFavorite(Tutor $tutor): JsonResponse
    {
        try {
            $result = $this->tutorService->toggleFavoriteTutor(Auth::id(), $tutor->id);

            return response()->json([
                'success' => true,
                'is_favorited' => $result['is_favorite'],
                'message' => $result['is_favorite']
                    ? __('Tutor added to favorites')
                    : __('Tutor removed from favorites'),
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Check tutor availability for a specific day
     */
    public function checkAvailability(Tutor $tutor, string $day): JsonResponse
    {
        try {
            $availability = $this->tutorService->checkTutorAvailability($tutor, $day);

            return response()->json([
                'success' => true,
                'availability' => $availability,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Store tutor review
     */
    public function storeReview(ReviewRequest $request, Tutor $tutor): RedirectResponse
    {
        try {
            $data = $request->validated();
            $data['student_id'] = Auth::id();

            $this->tutorService->createTutorReview($tutor, $data);

            return back()->with('success', __('Review submitted successfully'));

        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Update review
     */
    public function updateReview(ReviewRequest $request, Review $review): RedirectResponse
    {
        try {
            $this->authorize('update', $review);

            $review->update($request->validated());

            return back()->with('success', __('Review updated successfully'));

        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Delete review
     */
    public function destroyReview(Review $review): RedirectResponse
    {
        try {
            $this->authorize('delete', $review);

            $review->delete();

            return back()->with('success', __('Review deleted successfully'));

        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display availability management page
     */
    public function availability(): View|RedirectResponse
    {
        $user = Auth::user();

        if (! $user->tutor) {
            return redirect()->route('profile.edit')
                ->with('error', __('Please complete your tutor profile first'));
        }

        $availabilityData = $this->tutorService->getAvailabilityData($user->tutor);

        return view('tutors.availability', $availabilityData);
    }

    /**
     * Update tutor availability
     */
    public function updateAvailability(Request $request): RedirectResponse
    {
        $request->validate([
            'availability' => 'required|array',
            'availability.*.day_of_week' => 'required|integer|between:0,6',
            'availability.*.is_available' => 'required|boolean',
            'availability.*.start_time' => 'nullable|date_format:H:i',
            'availability.*.end_time' => 'nullable|date_format:H:i|after:availability.*.start_time',
        ]);

        try {
            $this->tutorService->updateTutorAvailability(Auth::user()->tutor, $request->availability);

            return back()->with('success', __('Availability updated successfully'));

        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
