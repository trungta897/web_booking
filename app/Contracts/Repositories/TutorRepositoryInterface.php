<?php

namespace App\Contracts\Repositories;

use App\Models\Tutor;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface TutorRepositoryInterface extends RepositoryInterface
{
    /**
     * Get tutors with filters and pagination
     */
    public function getTutorsWithFilters(array $filters = []): LengthAwarePaginator;

    /**
     * Get tutor with detailed information
     */
    public function getTutorWithDetails(int $tutorId): ?Tutor;

    /**
     * Get top rated tutors
     */
    public function getTopRatedTutors(int $limit = 10): Collection;

    /**
     * Search tutors by name
     */
    public function searchTutorsByName(string $name, int $perPage = 12): LengthAwarePaginator;

    /**
     * Get available tutors for time slot
     */
    public function getAvailableTutors(string $dayOfWeek, string $startTime, string $endTime): Collection;

    /**
     * Get tutor statistics
     */
    public function getTutorStatistics(int $tutorId): array;

    /**
     * Calculate tutor response rate
     */
    public function calculateResponseRate(int $tutorId): float;

    /**
     * Get tutors by subject
     */
    public function getTutorsBySubject(int $subjectId, int $perPage = 12): LengthAwarePaginator;
}
