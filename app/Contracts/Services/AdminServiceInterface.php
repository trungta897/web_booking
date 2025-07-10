<?php

namespace App\Contracts\Services;

use Illuminate\Pagination\LengthAwarePaginator;

interface AdminServiceInterface extends ServiceInterface
{
    /**
     * Get comprehensive dashboard statistics.
     */
    public function getDashboardStats(): array;

    /**
     * Get all users with filters and pagination.
     */
    public function getAllUsers(array $filters = []): LengthAwarePaginator;

    /**
     * Get all bookings with filters and pagination.
     */
    public function getAllBookings(array $filters = []): LengthAwarePaginator;

    /**
     * Get all tutors with filters and pagination.
     */
    public function getAllTutors(array $filters = []): LengthAwarePaginator;

    /**
     * Toggle user suspension status.
     */
    public function toggleUserSuspension(int $userId): bool;

    /**
     * Delete user account.
     */
    public function deleteUser(int $userId): bool;

    /**
     * Get revenue statistics.
     */
    public function getRevenueStatistics(array $filters = []): array;

    /**
     * Export data to CSV.
     */
    public function exportData(string $type, array $filters = []): string;
}
