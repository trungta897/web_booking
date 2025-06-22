<?php

namespace App\Repositories;

use App\Contracts\Repositories\SubjectRepositoryInterface;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class SubjectRepository extends BaseRepository implements SubjectRepositoryInterface
{
    public function __construct(Subject $subject)
    {
        parent::__construct($subject);
    }

    /**
     * Get all active subjects
     */
    public function getActiveSubjects(): Collection
    {
        return $this->query()->active()
            ->orderBy('name')
            ->get();
    }

    /**
     * Get subjects with tutor count
     */
    public function getSubjectsWithTutorCount(): Collection
    {
        return $this->query()->withCount('tutors')
            ->active()
            ->orderBy('name')
            ->get();
    }

    /**
     * Get popular subjects
     */
    public function getPopularSubjects(int $limit = 10): Collection
    {
        return $this->query()->withCount(['tutors', 'bookings'])
            ->active()
            ->orderBy('bookings_count', 'desc')
            ->orderBy('tutors_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Search subjects by name
     */
    public function searchByName(string $name): Collection
    {
        return $this->query()->where('name', 'like', '%' . $name . '%')
            ->active()
            ->orderBy('name')
            ->get();
    }

    /**
     * Get subjects with their tutors
     */
    public function getSubjectsWithTutors(): Collection
    {
        return $this->query()->with(['tutors.user'])
            ->withCount('tutors')
            ->active()
            ->having('tutors_count', '>', 0)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get general subject statistics
     */
    public function getSubjectStatistics(): array
    {
        return [
            'total_subjects' => $this->count(),
            'active_subjects' => $this->query()->active()->count(),
            'inactive_subjects' => $this->query()->inactive()->count(),
            'average_tutors_per_subject' => $this->all()->avg(function($subject) {
                return $subject->tutors()->count();
            })
        ];
    }

    /**
     * Get specific subject statistics
     */
    public function getSpecificSubjectStatistics(int $subjectId): array
    {
        $subject = $this->findById($subjectId);

        if (!$subject) {
            return [];
        }

        return [
            'total_tutors' => $subject->tutors()->count(),
            'active_tutors' => $subject->tutors()->whereHas('user', function($q) {
                $q->where('account_status', 'active');
            })->count(),
            'total_bookings' => $subject->bookings()->count(),
            'completed_bookings' => $subject->bookings()->where('status', 'completed')->count(),
            'average_rating' => $subject->tutors()->with('reviews')->get()
                ->flatMap->reviews->avg('rating'),
            'average_price' => $subject->tutors()->avg('hourly_rate'),
        ];
    }
}
