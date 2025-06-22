<?php

namespace App\Repositories;

use App\Contracts\Repositories\TutorRepositoryInterface;
use App\Models\Tutor;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class TutorRepository extends BaseRepository implements TutorRepositoryInterface
{
    public function __construct(Tutor $tutor)
    {
        parent::__construct($tutor);
    }

    /**
     * Get tutors with filters and sorting
     */
    public function getTutorsWithFilters(array $filters = []): LengthAwarePaginator
    {
        $query = $this->query()->with(['user', 'subjects', 'reviews'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');

        // Apply filters
        $query = $this->applyFilters($query, $filters);

        // Apply sorting
        $query = $this->applySorting($query, $filters['sort'] ?? null);

        return $query->paginate($filters['per_page'] ?? 12);
    }

    /**
     * Apply filters to query
     */
    protected function applyFilters(Builder $query, array $filters): Builder
    {
        // Filter by subject
        if (!empty($filters['subject'])) {
            $query->whereHas('subjects', function ($q) use ($filters) {
                $q->where('subjects.id', $filters['subject']);
            });
        }

        // Filter by price range
        if (!empty($filters['price_range'])) {
            $range = explode('-', $filters['price_range']);
            if (count($range) === 2) {
                $query->whereBetween('hourly_rate', [$range[0], $range[1]]);
            } else {
                $query->where('hourly_rate', '>=', substr($filters['price_range'], 0, -1));
            }
        }

        // Filter by minimum rating
        if (!empty($filters['rating'])) {
            $query->having('reviews_avg_rating', '>=', $filters['rating']);
        }

        // Filter by location
        if (!empty($filters['location'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('users.address', 'like', '%' . $filters['location'] . '%');
            });
        }

        // Filter by availability on a specific day
        if (!empty($filters['day_of_week'])) {
            $query->whereHas('availability', function($q) use ($filters) {
                $q->where('day_of_week', $filters['day_of_week'])
                  ->where('is_available', true);
            });
        }

        // Filter by experience level
        if (!empty($filters['experience'])) {
            $query->where('experience_years', '>=', $filters['experience']);
        }

        return $query;
    }

    /**
     * Apply sorting to query
     */
    protected function applySorting(Builder $query, ?string $sort): Builder
    {
        switch ($sort) {
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

        return $query;
    }

    /**
     * Get tutor with full details
     */
    public function getTutorWithDetails(int $id): ?Tutor
    {
        return $this->query()->with([
            'user',
            'subjects',
            'education',
            'reviews.student',
            'availability'
        ])
        ->withCount('reviews')
        ->withAvg('reviews', 'rating')
        ->find($id);
    }

    /**
     * Get top rated tutors
     */
    public function getTopRatedTutors(int $limit = 10): Collection
    {
        return $this->query()->with(['user', 'subjects'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->having('reviews_count', '>=', 5)
            ->orderBy('reviews_avg_rating', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get tutors by subject
     */
    public function getTutorsBySubject(int $subjectId, int $perPage = 12): LengthAwarePaginator
    {
        return $this->query()->whereHas('subjects', function ($q) use ($subjectId) {
            $q->where('subjects.id', $subjectId);
        })
        ->with(['user', 'subjects', 'reviews'])
        ->withCount('reviews')
        ->withAvg('reviews', 'rating')
        ->paginate($perPage);
    }

    /**
     * Search tutors by name
     */
    public function searchTutorsByName(string $name, int $perPage = 12): LengthAwarePaginator
    {
        return $this->query()->whereHas('user', function ($q) use ($name) {
            $q->where('name', 'like', '%' . $name . '%');
        })
        ->with(['user', 'subjects', 'reviews'])
        ->withCount('reviews')
        ->withAvg('reviews', 'rating')
        ->paginate($perPage);
    }

    /**
     * Get available tutors for specific time slot
     */
    public function getAvailableTutors(string $dayOfWeek, string $startTime, string $endTime): Collection
    {
        return $this->query()->whereHas('availability', function ($q) use ($dayOfWeek, $startTime, $endTime) {
            $q->where('day_of_week', $dayOfWeek)
              ->where('is_available', true)
              ->where('start_time', '<=', $startTime)
              ->where('end_time', '>=', $endTime);
        })
        ->with(['user', 'subjects'])
        ->get();
    }

    /**
     * Get tutor statistics
     */
    public function getTutorStatistics(int $tutorId): array
    {
        $tutor = $this->findById($tutorId);

        if (!$tutor) {
            return [];
        }

        return [
            'total_bookings' => $tutor->bookings()->count(),
            'completed_bookings' => $tutor->bookings()->where('status', 'completed')->count(),
            'total_earnings' => $tutor->bookings()->where('status', 'completed')->where('payment_status', 'paid')->sum('price'),
            'average_rating' => $tutor->reviews()->avg('rating'),
            'total_reviews' => $tutor->reviews()->count(),
            'response_rate' => $this->calculateResponseRate($tutorId),
        ];
    }

    /**
     * Calculate tutor response rate
     */
    public function calculateResponseRate(int $tutorId): float
    {
        $tutor = $this->findById($tutorId);
        if (!$tutor) {
            return 0;
        }

        $totalRequests = $tutor->bookings()->count();
        $respondedRequests = $tutor->bookings()
            ->whereIn('status', ['accepted', 'rejected'])->count();

        return $totalRequests > 0 ? ($respondedRequests / $totalRequests) * 100 : 0;
    }
}
