<?php

namespace App\Services;

use App\Contracts\Services\TutorServiceInterface;
use App\Models\Review;
use App\Models\Tutor;
use App\Models\User;
use App\Repositories\TutorRepository;
use App\Repositories\UserRepository;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TutorService extends BaseService implements TutorServiceInterface
{
    protected TutorRepository $tutorRepository;

    protected UserRepository $userRepository;

    public function __construct(
        TutorRepository $tutorRepository,
        UserRepository $userRepository
    ) {
        $this->tutorRepository = $tutorRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Get tutors with filters and caching.
     */
    public function getTutorsWithFilters(array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        // Don't cache paginated results, but cache the underlying data
        $filtersForCache = $filters;
        unset($filtersForCache['page'], $filtersForCache['per_page']);
        $cacheKey = 'tutors_data_' . md5(serialize($filtersForCache));
        $page = $filters['page'] ?? 1;
        $perPage = $filters['per_page'] ?? 12;

        return CacheService::remember($cacheKey . '_page_' . $page . '_' . $perPage, CacheService::TTL_MEDIUM, function () use ($filters) {
            return $this->tutorRepository->getTutorsWithFilters($filters);
        });
    }

    /**
     * Get tutor details with caching.
     */
    public function getTutorDetails(int $tutorId): ?Tutor
    {
        $cacheKey = CacheService::tutorDetailsKey($tutorId);

        return CacheService::remember($cacheKey, CacheService::TTL_LONG, function () use ($tutorId) {
            return $this->tutorRepository->getTutorWithDetails($tutorId);
        });
    }

    /**
     * Toggle favorite tutor for user.
     */
    public function toggleFavoriteTutor(int $userId, int $tutorId): array
    {
        $isFavorite = $this->userRepository->toggleFavoriteTutor($userId, $tutorId);

        $this->logActivity('Favorite tutor toggled', [
            'user_id' => $userId,
            'tutor_id' => $tutorId,
            'is_favorite' => $isFavorite,
        ]);

        return ['is_favorite' => $isFavorite];
    }

    /**
     * Check tutor availability.
     */
    public function checkTutorAvailability(Tutor $tutor, string $day): array
    {
        $availability = $tutor->availability()
            ->where('day_of_week', strtolower($day))
            ->where('is_available', true)
            ->get();

        if ($availability->isEmpty()) {
            return ['available' => false];
        }

        $slots = [];
        foreach ($availability as $slot) {
            $slots[] = $slot->start_time . ' - ' . $slot->end_time;
        }

        return [
            'available' => true,
            'slots' => $slots,
        ];
    }

    /**
     * Create tutor review.
     */
    public function createTutorReview(Tutor $tutor, array $data): Review
    {
        return $this->executeTransaction(function () use ($tutor, $data) {
            // Validate review constraints
            $this->validateReviewConstraints($tutor, $data);

            // Create review
            $review = Review::create([
                'tutor_id' => $tutor->id,
                'student_id' => $data['student_id'],
                'booking_id' => $data['booking_id'],
                'rating' => $data['rating'],
                'comment' => $data['comment'],
            ]);

            // Clear tutor cache
            $this->clearTutorCache($tutor->id);

            $this->logActivity('Review created', [
                'review_id' => $review->id,
                'tutor_id' => $tutor->id,
                'rating' => $data['rating'],
            ]);

            return $review;
        });
    }

    /**
     * Update tutor availability.
     */
    public function updateTutorAvailability(Tutor $tutor, array $availabilityData): void
    {
        $this->executeTransaction(function () use ($tutor, $availabilityData) {
            // Delete existing availability
            $tutor->availability()->delete();

            // Create new availability records
            foreach ($availabilityData as $day => $slots) {
                if (!empty($slots)) {
                    foreach ($slots as $slot) {
                        $tutor->availability()->create([
                            'day_of_week' => $day,
                            'start_time' => $slot['start_time'],
                            'end_time' => $slot['end_time'],
                            'is_available' => true,
                        ]);
                    }
                }
            }

            // Clear tutor cache
            $this->clearTutorCache($tutor->id);

            $this->logActivity('Tutor availability updated', [
                'tutor_id' => $tutor->id,
            ]);
        });
    }

    /**
     * Get top rated tutors.
     */
    public function getTopRatedTutors(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        $cacheKey = 'top_rated_tutors_' . $limit;

        return Cache::remember($cacheKey, 7200, function () use ($limit) {
            return $this->tutorRepository->getTopRatedTutors($limit);
        });
    }

    /**
     * Search tutors.
     */
    public function searchTutors(array $criteria): \Illuminate\Pagination\LengthAwarePaginator
    {
        if (!empty($criteria['name'])) {
            return $this->tutorRepository->searchTutorsByName($criteria['name'], $criteria['per_page'] ?? 12);
        }

        if (!empty($criteria['subject_id'])) {
            return $this->tutorRepository->getTutorsBySubject($criteria['subject_id'], $criteria['per_page'] ?? 12);
        }

        return $this->getTutorsWithFilters($criteria);
    }

    /**
     * Get tutor statistics.
     */
    public function getTutorStatistics(int $tutorId): array
    {
        $cacheKey = 'tutor_stats_' . $tutorId;

        return Cache::remember($cacheKey, 1800, function () use ($tutorId) {
            $stats = $this->tutorRepository->getTutorStatistics($tutorId);

            // Format statistics for display
            if (!empty($stats)) {
                $stats['formatted_total_earnings'] = $this->formatCurrency($stats['total_earnings'] ?? 0);
                $stats['formatted_average_rating'] = number_format($stats['average_rating'] ?? 0, 1);
                $stats['formatted_response_rate'] = number_format($stats['response_rate'] ?? 0, 1) . '%';
            }

            return $stats;
        });
    }

    /**
     * Get available tutors for time slot.
     */
    public function getAvailableTutors(string $dayOfWeek, string $startTime, string $endTime): \Illuminate\Database\Eloquent\Collection
    {
        return $this->tutorRepository->getAvailableTutors($dayOfWeek, $startTime, $endTime);
    }

    /**
     * Validate review constraints.
     */
    protected function validateReviewConstraints(Tutor $tutor, array $data): void
    {
        // Check if booking exists and belongs to student
        $booking = \App\Models\Booking::where('id', $data['booking_id'])
            ->where('student_id', $data['student_id'])
            ->where('tutor_id', $tutor->id)
            ->where('status', 'completed')
            ->first();

        if (!$booking) {
            throw new Exception('Invalid booking for review');
        }

        // Check if review already exists
        if (Review::where('booking_id', $data['booking_id'])->exists()) {
            throw new Exception('Review already exists for this booking');
        }
    }

    /**
     * Clear tutor related cache.
     */
    protected function clearTutorCache(int $tutorId): void
    {
        CacheService::clearTutorCaches($tutorId);
    }

    /**
     * Handle errors specific to tutor service.
     */
    public function handleError(Exception $e, string $context = ''): void
    {
        Log::error("TutorService Error: {$context}", [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Get tutor profile data.
     */
    public function getTutorProfileData(Tutor $tutor): array
    {
        $tutor->load('user', 'subjects', 'education', 'reviews.student');

        return [
            'tutor' => $tutor,
            'statistics' => $this->getTutorStatistics($tutor->id),
            'recent_reviews' => $tutor->reviews()->with('student')->latest()->limit(5)->get(),
        ];
    }

    /**
     * Get edit profile data.
     */
    public function getEditProfileData(Tutor $tutor): array
    {
        $tutor->load('user', 'subjects', 'education');
        $subjects = \App\Models\Subject::where('is_active', true)->get();

        return [
            'tutor' => $tutor,
            'subjects' => $subjects,
        ];
    }

    /**
     * Create tutor profile.
     */
    public function createTutorProfile(User $user, array $data): Tutor
    {
        return $this->executeTransaction(function () use ($user, $data) {
            // Check if user already has tutor profile
            if ($user->tutor) {
                throw new Exception(__('User already has a tutor profile'));
            }

            // Create tutor
            $tutor = Tutor::create([
                'user_id' => $user->id,
                'hourly_rate' => $data['hourly_rate'],
                'experience_years' => $data['experience_years'],
                'bio' => $data['bio'],
                'specialization' => $data['specialization'] ?? null,
                'is_available' => $data['is_available'] ?? true,
            ]);

            // Sync subjects
            if (!empty($data['subjects'])) {
                $tutor->subjects()->sync($data['subjects']);
            }

            // Add education if provided
            if (!empty($data['education'])) {
                foreach ($data['education'] as $eduData) {
                    $tutor->education()->create($eduData);
                }
            }

            $this->logActivity('Tutor profile created', [
                'tutor_id' => $tutor->id,
                'user_id' => $user->id,
            ]);

            return $tutor;
        });
    }

    /**
     * Update tutor profile.
     */
    public function updateTutorProfile(Tutor $tutor, array $data): Tutor
    {
        return $this->executeTransaction(function () use ($tutor, $data) {
            // Update tutor data
            $tutorData = [
                'hourly_rate' => $data['hourly_rate'],
                'experience_years' => $data['experience_years'],
                'bio' => $data['bio'],
                'specialization' => $data['specialization'] ?? $tutor->specialization,
            ];

            if (isset($data['is_available'])) {
                $tutorData['is_available'] = $data['is_available'];
            }

            $tutor->update($tutorData);

            // Sync subjects
            if (!empty($data['subjects'])) {
                $tutor->subjects()->sync($data['subjects']);
            }

            // Update education
            if (isset($data['education'])) {
                $tutor->education()->delete();
                foreach ($data['education'] as $eduData) {
                    $tutor->education()->create($eduData);
                }
            }

            // Clear cache
            $this->clearTutorCache($tutor->id);

            $this->logActivity('Tutor profile updated', [
                'tutor_id' => $tutor->id,
            ]);

            return $tutor;
        });
    }

    /**
     * Delete tutor profile.
     */
    public function deleteTutorProfile(Tutor $tutor): bool
    {
        return $this->executeTransaction(function () use ($tutor) {
            $tutorId = $tutor->id;
            $userId = $tutor->user_id;

            // Delete related data
            $tutor->education()->delete();
            $tutor->subjects()->detach();
            $tutor->availability()->delete();
            $tutor->reviews()->delete();

            // Delete tutor
            $result = $tutor->delete();

            if ($result) {
                $this->clearTutorCache($tutorId);

                $this->logActivity('Tutor profile deleted', [
                    'tutor_id' => $tutorId,
                    'user_id' => $userId,
                ]);
            }

            return $result;
        });
    }

    /**
     * Get featured tutors.
     */
    public function getFeaturedTutors(int $limit = 6): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('featured_tutors', 3600, function () use ($limit) {
            return Tutor::with(['user', 'subjects'])
                ->withCount('reviews')
                ->withAvg('reviews', 'rating')
                ->having('reviews_count', '>', 0)
                ->orderBy('reviews_avg_rating', 'desc')
                ->take($limit)
                ->get();
        });
    }

    /**
     * Get user favorite tutors.
     */
    public function getUserFavoriteTutors(User $user): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $user->favoriteTutors()->with('user')->paginate(12);
    }

    /**
     * Toggle favorite.
     */
    public function toggleFavorite(User $user, int $tutorId): array
    {
        $tutor = Tutor::findOrFail($tutorId);

        $isFavorite = $user->favoriteTutors()->where('tutor_id', $tutorId)->exists();

        if ($isFavorite) {
            $user->favoriteTutors()->detach($tutorId);
            $message = __('Tutor removed from favorites');
        } else {
            $user->favoriteTutors()->attach($tutorId);
            $message = __('Tutor added to favorites');
        }

        $this->logActivity('Favorite toggled', [
            'user_id' => $user->id,
            'tutor_id' => $tutorId,
            'is_favorite' => !$isFavorite,
        ]);

        return [
            'is_favorite' => !$isFavorite,
            'message' => $message,
        ];
    }

    /**
     * Remove favorite.
     */
    public function removeFavorite(User $user, int $tutorId): bool
    {
        $result = $user->favoriteTutors()->detach($tutorId);

        if ($result) {
            $this->logActivity('Favorite removed', [
                'user_id' => $user->id,
                'tutor_id' => $tutorId,
            ]);
        }

        return $result > 0;
    }

    /**
     * Get tutors for subject with filters.
     */
    public function getTutorsForSubject(\App\Models\Subject $subject, array $filters = []): array
    {
        $query = $subject->tutors()
            ->with(['user', 'subjects'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');

        // Apply filters
        if (!empty($filters['search'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['min_rating'])) {
            $query->having('reviews_avg_rating', '>=', $filters['min_rating']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('hourly_rate', '<=', $filters['max_price']);
        }

        // Apply sorting
        $sort = $filters['sort'] ?? 'rating';
        $order = $filters['order'] ?? 'desc';

        switch ($sort) {
            case 'rating':
                $query->orderBy('reviews_avg_rating', $order);

                break;
            case 'price':
                $query->orderBy('hourly_rate', $order);

                break;
            case 'experience':
                $query->orderBy('experience_years', $order);

                break;
            default:
                $query->orderBy('created_at', $order);
        }

        $tutors = $query->paginate(10);

        return [
            'subject' => $subject,
            'tutors' => $tutors,
            'filters' => $filters,
        ];
    }

    /**
     * Get dashboard data for tutor.
     */
    public function getDashboardData(Tutor $tutor): array
    {
        $stats = $this->getTutorStatistics($tutor->id);

        // Get recent bookings
        $recentBookings = \App\Models\Booking::where('tutor_id', $tutor->id)
            ->with(['student', 'subject'])
            ->latest()
            ->limit(5)
            ->get();

        // Get upcoming sessions
        $upcomingSessions = \App\Models\Booking::where('tutor_id', $tutor->id)
            ->where('status', 'confirmed')
            ->where('start_time', '>', now())
            ->with(['student', 'subject'])
            ->orderBy('start_time')
            ->limit(10)
            ->get();

        // Get recent reviews
        $recentReviews = $tutor->reviews()
            ->with('student')
            ->latest()
            ->limit(5)
            ->get();

        // Count statistics for dashboard
        $totalBookings = \App\Models\Booking::where('tutor_id', $tutor->id)->count();
        $completedBookings = \App\Models\Booking::where('tutor_id', $tutor->id)
            ->where('status', 'completed')
            ->count();
        $pendingBookings = \App\Models\Booking::where('tutor_id', $tutor->id)
            ->where('status', 'pending')
            ->count();
        $upcomingBookings = \App\Models\Booking::where('tutor_id', $tutor->id)
            ->where('status', 'confirmed')
            ->where('start_time', '>', now())
            ->count();
        $totalStudents = \App\Models\Booking::where('tutor_id', $tutor->id)
            ->select('student_id')
            ->distinct()
            ->count();
        $totalEarnings = \App\Models\Booking::where('tutor_id', $tutor->id)
            ->where('payment_status', 'paid')
            ->sum('price');

        // Get calendar data for current month
        $calendarData = $this->getCalendarData($tutor);

        return [
            'tutor' => $tutor,
            'stats' => $stats,
            'recentBookings' => $recentBookings,
            'upcomingSessions' => $upcomingSessions,
            'recentReviews' => $recentReviews,
            'totalBookings' => $totalBookings,
            'completedBookings' => $completedBookings,
            'pendingBookings' => $pendingBookings,
            'upcomingBookings' => $upcomingBookings,
            'totalStudents' => $totalStudents,
            'totalEarnings' => $totalEarnings,
            'calendarData' => $calendarData,
        ];
    }

    /**
     * Get calendar data for tutor (current and next month).
     */
    public function getCalendarData(Tutor $tutor, \Carbon\Carbon $date = null): array
    {
        $currentDate = $date ?? now();
        $startOfMonth = $currentDate->copy()->startOfMonth();
        $endOfMonth = $currentDate->copy()->endOfMonth();
        $nextMonth = $currentDate->copy()->addMonth();
        $endOfNextMonth = $nextMonth->copy()->endOfMonth();

        // Get all bookings for current and next month
        $bookings = \App\Models\Booking::where('tutor_id', $tutor->id)
            ->whereIn('status', ['confirmed', 'pending'])
            ->whereBetween('start_time', [$startOfMonth, $endOfNextMonth])
            ->with(['student', 'subject'])
            ->orderBy('start_time')
            ->get();

        // Group bookings by date
        $bookingsByDate = [];
        foreach ($bookings as $booking) {
            $date = $booking->start_time->format('Y-m-d');
            if (!isset($bookingsByDate[$date])) {
                $bookingsByDate[$date] = [];
            }
            $bookingsByDate[$date][] = [
                'id' => $booking->id,
                'student_name' => $booking->student->name ?? 'N/A',
                'subject_name' => $booking->subject->name ?? 'N/A',
                'start_time' => $booking->start_time->format('H:i'),
                'end_time' => $booking->end_time->format('H:i'),
                'status' => $booking->status,
                'price' => $booking->price ?? 0,
            ];
        }

        // Get days with bookings for highlighting
        $daysWithBookings = array_keys($bookingsByDate);

        return [
            'current_month' => $currentDate->format('Y-m'),
            'current_month_name' => $currentDate->format('F Y'),
            'next_month' => $nextMonth->format('Y-m'),
            'next_month_name' => $nextMonth->format('F Y'),
            'bookings_by_date' => $bookingsByDate,
            'days_with_bookings' => $daysWithBookings,
            'calendar_weeks' => $this->generateCalendarWeeks($currentDate),
            'next_calendar_weeks' => $this->generateCalendarWeeks($nextMonth),
        ];
    }

    /**
     * Generate calendar weeks for a given month.
     */
    private function generateCalendarWeeks(\Carbon\Carbon $date): array
    {
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        // Start from Sunday of the week containing the first day of month
        $calendarStart = $startOfMonth->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);

        // End at Saturday of the week containing the last day of month
        $calendarEnd = $endOfMonth->copy()->endOfWeek(\Carbon\Carbon::SATURDAY);

        $weeks = [];
        $currentWeek = [];
        $currentDate = $calendarStart->copy();

        while ($currentDate->lte($calendarEnd)) {
            $currentWeek[] = [
                'date' => $currentDate->format('Y-m-d'),
                'day' => $currentDate->day,
                'is_current_month' => $currentDate->month === $date->month,
                'is_today' => $currentDate->isToday(),
                'is_past' => $currentDate->isPast(),
            ];

            if ($currentDate->dayOfWeek === \Carbon\Carbon::SATURDAY) {
                $weeks[] = $currentWeek;
                $currentWeek = [];
            }

            $currentDate->addDay();
        }

        // Add remaining days if week is not complete
        if (!empty($currentWeek)) {
            $weeks[] = $currentWeek;
        }

        return $weeks;
    }

    /**
     * Get bookings for specific date.
     */
    public function getBookingsForDate(Tutor $tutor, string $date): array
    {
        $startOfDay = \Carbon\Carbon::parse($date)->startOfDay();
        $endOfDay = \Carbon\Carbon::parse($date)->endOfDay();

        $bookings = \App\Models\Booking::where('tutor_id', $tutor->id)
            ->whereIn('status', ['confirmed', 'pending'])
            ->whereBetween('start_time', [$startOfDay, $endOfDay])
            ->with(['student', 'subject'])
            ->orderBy('start_time')
            ->get();

        return $bookings->map(function ($booking) {
            $durationInMinutes = $booking->start_time && $booking->end_time
                ? $booking->start_time->diffInMinutes($booking->end_time)
                : 0;

            return [
                'id' => $booking->id,
                'student_name' => $booking->student->name ?? 'N/A',
                'subject_name' => $booking->subject->name ?? 'N/A',
                'start_time' => $booking->start_time->format('H:i'),
                'end_time' => $booking->end_time->format('H:i'),
                'status' => $booking->status,
                'price' => $booking->price ?? 0,
                'duration' => $durationInMinutes . ' phút',
            ];
        })->toArray();
    }

    /**
     * Get availability data for tutor.
     */
    public function getAvailabilityData(Tutor $tutor): array
    {
        $tutor->load('availability');

        $daysOfWeek = [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ];

        $availabilityByDay = [];
        foreach ($daysOfWeek as $dayNum => $dayName) {
            $availabilityByDay[$dayNum] = [
                'day_name' => $dayName,
                'slots' => $tutor->availability->where('day_of_week', $dayNum)->values(),
            ];
        }

        return [
            'tutor' => $tutor,
            'availabilityByDay' => $availabilityByDay,
            'daysOfWeek' => $daysOfWeek,
        ];
    }
}
