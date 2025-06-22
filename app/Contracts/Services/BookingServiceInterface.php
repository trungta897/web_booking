<?php

namespace App\Contracts\Services;

use App\Models\Booking;
use Illuminate\Pagination\LengthAwarePaginator;

interface BookingServiceInterface extends ServiceInterface
{
    /**
     * Create new booking
     */
    public function createBooking(array $data): Booking;

    /**
     * Update booking status
     */
    public function updateBookingStatus(int $bookingId, string $status, ?string $rejectionReason = null): bool;

    /**
     * Cancel booking
     */
    public function cancelBooking(int $bookingId, int $cancelledBy, ?string $reason = null): bool;

    /**
     * Get user bookings with filters
     */
    public function getUserBookings(int $userId, string $role, array $filters = []): LengthAwarePaginator;

    /**
     * Get tutor earnings
     */
    public function getTutorEarnings(int $tutorId, ?int $year = null, ?int $month = null): array;

    /**
     * Validate booking constraints
     */
    public function validateBookingConstraints(array $data): void;
}
