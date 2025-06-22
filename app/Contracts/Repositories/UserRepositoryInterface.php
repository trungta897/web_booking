<?php

namespace App\Contracts\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface extends RepositoryInterface
{
    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User;

    /**
     * Get users by role
     */
    public function getUsersByRole(string $role): Collection;

    /**
     * Get active users
     */
    public function getActiveUsers(): Collection;

    /**
     * Toggle favorite tutor for user
     */
    public function toggleFavoriteTutor(int $userId, int $tutorId): bool;

    /**
     * Get user's favorite tutors
     */
    public function getUserFavoriteTutors(int $userId): Collection;

    /**
     * Get user statistics
     */
    public function getUserStatistics(int $userId): array;
}
