<?php

namespace App\Services;

use App\Contracts\Services\TutorServiceInterface;
use App\Models\Tutor;
use App\Models\User;
use App\Models\Review;
use App\Repositories\TutorRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Cache;
use Exception;

class TutorService extends BaseService implements TutorServiceInterface
{
    protected TutorRepository $tutorRepository;
    protected UserRepository $userRepository;

    public function __construct()
    {
        $this->tutorRepository = new TutorRepository(new Tutor());
        $this->userRepository = new UserRepository(new User());
    }

    /**
     * Get tutors with filters and caching
     */
    public function getTutorsWithFilters(array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $cacheKey = 'tutors_' . md5(serialize($filters));

        return Cache::remember($cacheKey, 3600, function () use ($filters) {
            return $this->tutorRepository->getTutorsWithFilters($filters);
        });
    }

    /**
     * Get tutor details with caching
     */
    public function getTutorDetails(int $tutorId): ?Tutor
    {
        $cacheKey = 'tutor_details_' . $tutorId;

        return Cache::remember($cacheKey, 3600, function () use ($tutorId) {
            return $this->tutorRepository->getTutorWithDetails($tutorId);
        });
    }

    /**
     * Toggle favorite tutor for user
     */
    public function toggleFavoriteTutor(int $userId, int $tutorId): array
    {
        $isFavorite = $this->userRepository->toggleFavoriteTutor($userId, $tutorId);

        $this->logActivity('Favorite tutor toggled', [
            'user_id' => $userId,
            'tutor_id' => $tutorId,
            'is_favorite' => $isFavorite
        ]);

        return ['is_favorite' => $isFavorite];
    }

    /**
     * Check tutor availability
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
            'slots' => $slots
        ];
    }

    /**
     * Create tutor review
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
                'rating' => $data['rating']
            ]);

            return $review;
        });
    }

    /**
     * Update tutor availability
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
                            'is_available' => true
                        ]);
                    }
                }
            }

            // Clear tutor cache
            $this->clearTutorCache($tutor->id);

            $this->logActivity('Tutor availability updated', [
                'tutor_id' => $tutor->id
            ]);
        });
    }

    /**
     * Get top rated tutors
     */
    public function getTopRatedTutors(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        $cacheKey = 'top_rated_tutors_' . $limit;

        return Cache::remember($cacheKey, 7200, function () use ($limit) {
            return $this->tutorRepository->getTopRatedTutors($limit);
        });
    }

    /**
     * Search tutors
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
     * Get tutor statistics
     */
    public function getTutorStatistics(int $tutorId): array
    {
        $cacheKey = 'tutor_stats_' . $tutorId;

        return Cache::remember($cacheKey, 1800, function () use ($tutorId) {
            $stats = $this->tutorRepository->getTutorStatistics($tutorId);

            // Format statistics for display
            if (!empty($stats)) {
                $stats['formatted_total_earnings'] = $this->formatCurrency($stats['total_earnings']);
                $stats['formatted_average_rating'] = number_format($stats['average_rating'], 1);
                $stats['formatted_response_rate'] = number_format($stats['response_rate'], 1) . '%';
            }

            return $stats;
        });
    }

    /**
     * Get available tutors for time slot
     */
    public function getAvailableTutors(string $dayOfWeek, string $startTime, string $endTime): \Illuminate\Database\Eloquent\Collection
    {
        return $this->tutorRepository->getAvailableTutors($dayOfWeek, $startTime, $endTime);
    }

    /**
     * Validate review constraints
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
     * Clear tutor related cache
     */
    protected function clearTutorCache(int $tutorId): void
    {
        Cache::forget('tutor_details_' . $tutorId);
        Cache::forget('tutor_stats_' . $tutorId);

        // Clear tutors list cache (basic cache clearing)
        $patterns = ['tutors_*', 'top_rated_tutors_*'];
        foreach ($patterns as $pattern) {
            Cache::flush(); // In production, use more specific cache clearing
        }
    }

    /**
     * Handle errors specific to tutor service
     */
    public function handleError(\Exception $e, string $context = ''): void
    {
        $this->logError($context ?: 'Tutor service error occurred', $e);
        throw $e;
    }
}
