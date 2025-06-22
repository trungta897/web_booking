<?php

namespace App\Contracts\Services;

use App\Models\Review;
use App\Models\Tutor;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface TutorServiceInterface extends ServiceInterface
{
    /**
     * Get tutors with filters and caching
     */
    public function getTutorsWithFilters(array $filters = []): LengthAwarePaginator;

    /**
     * Get tutor details with caching
     */
    public function getTutorDetails(int $tutorId): ?Tutor;

    /**
     * Toggle favorite tutor for user
     */
    public function toggleFavoriteTutor(int $userId, int $tutorId): array;

    /**
     * Check tutor availability
     */
    public function checkTutorAvailability(Tutor $tutor, string $day): array;

    /**
     * Create tutor review
     */
    public function createTutorReview(Tutor $tutor, array $data): Review;

    /**
     * Update tutor availability
     */
    public function updateTutorAvailability(Tutor $tutor, array $availabilityData): void;

    /**
     * Get top rated tutors
     */
    public function getTopRatedTutors(int $limit = 10): Collection;

    /**
     * Search tutors
     */
    public function searchTutors(array $criteria): LengthAwarePaginator;

    /**
     * Get tutor statistics
     */
    public function getTutorStatistics(int $tutorId): array;

    /**
     * Get available tutors for time slot
     */
    public function getAvailableTutors(string $dayOfWeek, string $startTime, string $endTime): Collection;
}
