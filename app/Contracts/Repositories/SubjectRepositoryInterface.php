<?php

namespace App\Contracts\Repositories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Collection;

interface SubjectRepositoryInterface extends RepositoryInterface
{
    /**
     * Get active subjects.
     */
    public function getActiveSubjects(): Collection;

    /**
     * Get subjects with tutor count.
     */
    public function getSubjectsWithTutorCount(): Collection;

    /**
     * Get popular subjects.
     */
    public function getPopularSubjects(int $limit = 10): Collection;

    /**
     * Search subjects by name.
     */
    public function searchByName(string $name): Collection;

    /**
     * Get subject statistics.
     */
    public function getSubjectStatistics(): array;
}
