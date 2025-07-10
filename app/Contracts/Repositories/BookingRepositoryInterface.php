<?php

namespace App\Contracts\Repositories;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface BookingRepositoryInterface extends RepositoryInterface
{
    /**
     * Get bookings for user with filters.
     */
    public function getBookingsForUser(int $userId, string $role, array $filters = []): LengthAwarePaginator;

    /**
     * Check if user has pending booking with tutor.
     */
    public function hasPendingBookingWithTutor(int $studentId, int $tutorId): bool;

    /**
     * Get upcoming bookings for tutor.
     */
    public function getUpcomingBookingsForTutor(int $tutorId): Collection;

    /**
     * Get tutor total earnings.
     */
    public function getTutorTotalEarnings(int $tutorId): float;

    /**
     * Get tutor monthly earnings.
     */
    public function getTutorMonthlyEarnings(int $tutorId, int $year, int $month): float;

    /**
     * Get bookings needing review.
     */
    public function getBookingsNeedingReview(int $studentId): Collection;

    /**
     * Search bookings with filters.
     */
    public function searchBookings(array $filters): LengthAwarePaginator;
}
