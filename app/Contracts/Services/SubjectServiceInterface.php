<?php

namespace App\Contracts\Services;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface SubjectServiceInterface extends ServiceInterface
{
    /**
     * Get all active subjects with caching.
     */
    public function getAllActiveSubjects(): Collection;

    /**
     * Get tutors for subject.
     */
    public function getTutorsForSubject(int $subjectId, array $filters = []): LengthAwarePaginator;

    /**
     * Search subjects.
     */
    public function searchSubjects(string $query): Collection;

    /**
     * Create new subject.
     */
    public function createSubject(array $data): Subject;

    /**
     * Update subject.
     */
    public function updateSubject(int $subjectId, array $data): Subject;

    /**
     * Delete subject.
     */
    public function deleteSubject(int $subjectId): bool;

    /**
     * Get subject analytics.
     */
    public function getSubjectAnalytics(): array;
}
