<?php

namespace App\Services;

use App\Models\Subject;
use App\Repositories\SubjectRepository;
use App\Repositories\TutorRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Exception;

class SubjectService extends BaseService
{
    protected SubjectRepository $subjectRepository;
    protected TutorRepository $tutorRepository;

    public function __construct()
    {
        $this->subjectRepository = new SubjectRepository(new Subject());
        $this->tutorRepository = new TutorRepository(new \App\Models\Tutor());
    }

    /**
     * Get all active subjects with caching
     */
    public function getAllActiveSubjects(): Collection
    {
        return Cache::remember('subjects.active', 3600, function () {
            return $this->subjectRepository->getActiveSubjects();
        });
    }

    /**
     * Get subjects with tutor count
     */
    public function getSubjectsWithTutorCount(): Collection
    {
        return Cache::remember('subjects.with_tutor_count', 1800, function () {
            return $this->subjectRepository->getSubjectsWithTutorCount();
        });
    }

    /**
     * Get popular subjects
     */
    public function getPopularSubjects(int $limit = 10): Collection
    {
        return Cache::remember("subjects.popular.{$limit}", 3600, function () use ($limit) {
            return $this->subjectRepository->getPopularSubjects($limit);
        });
    }

    /**
     * Get tutors for a specific subject
     */
    public function getTutorsForSubject(int $subjectId, array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $subject = $this->subjectRepository->findById($subjectId);

        if (!$subject) {
            throw new Exception(__('Subject not found'));
        }

        // Add subject filter to existing filters
        $filters['subject'] = $subjectId;

        return $this->tutorRepository->getTutorsWithFilters($filters);
    }

    /**
     * Search subjects by name
     */
    public function searchSubjects(string $query): Collection
    {
        if (empty(trim($query))) {
            return collect();
        }

        return $this->subjectRepository->searchByName($query);
    }

    /**
     * Get subject with statistics
     */
    public function getSubjectWithStats(int $subjectId): array
    {
        $subject = $this->subjectRepository->findById($subjectId);

        if (!$subject) {
            throw new Exception(__('Subject not found'));
        }

        $stats = $this->subjectRepository->getSpecificSubjectStatistics($subjectId);

        return [
            'subject' => $subject,
            'statistics' => $stats,
            'formatted_average_price' => $this->formatCurrency($stats['average_price'] ?? 0),
            'formatted_average_rating' => number_format($stats['average_rating'] ?? 0, 1)
        ];
    }

    /**
     * Create new subject
     */
    public function createSubject(array $data): Subject
    {
        return $this->executeTransaction(function () use ($data) {
            $subject = $this->subjectRepository->create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'icon' => $data['icon'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            // Clear relevant caches
            $this->clearSubjectCaches();

            $this->logActivity('Subject created', [
                'subject_id' => $subject->id,
                'name' => $subject->name
            ]);

            return $subject;
        });
    }

    /**
     * Update subject
     */
    public function updateSubject(int $subjectId, array $data): Subject
    {
        return $this->executeTransaction(function () use ($subjectId, $data) {
            $subject = $this->subjectRepository->findByIdOrFail($subjectId);

            $this->subjectRepository->update($subject->id, $data);

            // Clear relevant caches
            $this->clearSubjectCaches();

            $this->logActivity('Subject updated', [
                'subject_id' => $subject->id,
                'name' => $subject->name
            ]);

            return $subject->fresh();
        });
    }

    /**
     * Delete subject
     */
    public function deleteSubject(int $subjectId): bool
    {
        return $this->executeTransaction(function () use ($subjectId) {
            $subject = $this->subjectRepository->findByIdOrFail($subjectId);

            // Check if subject has active tutors
            if ($subject->tutors()->count() > 0) {
                throw new Exception(__('Cannot delete subject that has active tutors'));
            }

            $result = $this->subjectRepository->delete($subject->id);

            if ($result) {
                // Clear relevant caches
                $this->clearSubjectCaches();

                $this->logActivity('Subject deleted', [
                    'subject_id' => $subject->id,
                    'name' => $subject->name
                ]);
            }

            return $result;
        });
    }

    /**
     * Toggle subject status
     */
    public function toggleSubjectStatus(int $subjectId): Subject
    {
        return $this->executeTransaction(function () use ($subjectId) {
            $subject = $this->subjectRepository->findByIdOrFail($subjectId);

            $newStatus = !$subject->is_active;

            $this->subjectRepository->update($subject->id, ['is_active' => $newStatus]);

            // Clear relevant caches
            $this->clearSubjectCaches();

            $this->logActivity('Subject status toggled', [
                'subject_id' => $subject->id,
                'new_status' => $newStatus ? 'active' : 'inactive'
            ]);

            return $subject->fresh();
        });
    }

    /**
     * Get subject analytics
     */
    public function getSubjectAnalytics(): array
    {
        return Cache::remember('subjects.analytics', 3600, function () {
            return array_merge(
                $this->subjectRepository->getSubjectStatistics(),
                [
                    'subjects_with_tutors' => $this->subjectRepository->getSubjectsWithTutors()->count(),
                    'popular_subjects' => $this->getPopularSubjects(5),
                ]
            );
        });
    }

    /**
     * Clear subject-related caches
     */
    protected function clearSubjectCaches(): void
    {
        Cache::forget('subjects.active');
        Cache::forget('subjects.with_tutor_count');
        Cache::forget('subjects.analytics');

        // Clear popular subjects cache for different limits
        for ($i = 5; $i <= 20; $i += 5) {
            Cache::forget("subjects.popular.{$i}");
        }
    }
}
