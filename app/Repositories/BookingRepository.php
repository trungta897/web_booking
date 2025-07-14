<?php

namespace App\Repositories;

use App\Contracts\Repositories\BookingRepositoryInterface;
use App\Models\Booking;
use App\Models\Tutor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class BookingRepository extends BaseRepository implements BookingRepositoryInterface
{
    public function __construct(Booking $booking)
    {
        parent::__construct($booking);
    }

    /**
     * Get bookings for a user (student or tutor).
     */
    public function getBookingsForUser(int $userId, string $role, array $filters = []): LengthAwarePaginator
    {
        $query = $role === 'tutor'
            ? $this->query()->whereHas('tutor', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            : $this->query()->where('student_id', $userId);

        // Apply filters using boolean logic
        if (isset($filters['status'])) {
            switch ($filters['status']) {
                case 'pending':
                    $query->pending();
                    break;
                case 'confirmed':
                case 'accepted':
                    $query->confirmed();
                    break;
                case 'cancelled':
                    $query->cancelled();
                    break;
                case 'completed':
                    $query->completed();
                    break;
            }
        }

        if (isset($filters['subject_id'])) {
            $query->where('subject_id', $filters['subject_id']);
        }

        return $query->with(['tutor.user', 'student', 'subject'])
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Check if student has pending booking with tutor.
     */
    public function hasPendingBookingWithTutor(int $studentId, int $tutorId): bool
    {
        return $this->query()->where('student_id', $studentId)
            ->where('tutor_id', $tutorId)
            ->pending() // Use scope instead of where('status', 'pending')
            ->exists();
    }

    /**
     * Get bookings by status.
     */
    public function getBookingsByStatus(string $status, int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->query();

        // Use boolean scopes instead of status column
        switch ($status) {
            case 'pending':
                $query->pending();
                break;
            case 'confirmed':
            case 'accepted':
                $query->confirmed();
                break;
            case 'cancelled':
                $query->cancelled();
                break;
            case 'completed':
                $query->completed();
                break;
        }

        return $query->with(['tutor.user', 'student', 'subject'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get upcoming bookings for tutor.
     */
    public function getUpcomingBookingsForTutor(int $tutorId): Collection
    {
        return $this->query()->where('tutor_id', $tutorId)
            ->confirmed() // Use scope instead of where('status', 'accepted')
            ->where('start_time', '>', Carbon::now())
            ->with(['student', 'subject'])
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Get completed bookings for student.
     */
    public function getCompletedBookingsForStudent(int $studentId): Collection
    {
        return $this->query()->where('student_id', $studentId)
            ->completed() // Use scope instead of where('status', 'completed')
            ->with(['tutor.user', 'subject'])
            ->orderBy('end_time', 'desc')
            ->get();
    }

    /**
     * Get bookings within date range.
     */
    public function getBookingsInDateRange(Carbon $startDate, Carbon $endDate): Collection
    {
        return $this->query()->whereBetween('start_time', [$startDate, $endDate])
            ->with(['tutor.user', 'student', 'subject'])
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Get bookings for tutor in date range.
     */
    public function getTutorBookingsInDateRange(int $tutorId, Carbon $startDate, Carbon $endDate): Collection
    {
        return $this->query()->where('tutor_id', $tutorId)
            ->whereBetween('start_time', [$startDate, $endDate])
            ->with(['student', 'subject'])
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Calculate total earnings for tutor.
     */
    public function getTutorTotalEarnings(int $tutorId): float
    {
        return $this->query()->where('tutor_id', $tutorId)
            ->whereNotNull('payment_at') // Use payment_at instead of payment_status = 'paid'
            ->sum('price');
    }

    /**
     * Get monthly earnings for tutor.
     */
    public function getTutorMonthlyEarnings(int $tutorId, int $year, int $month): float
    {
        return $this->query()->where('tutor_id', $tutorId)
            ->whereNotNull('payment_at') // Use payment_at instead of payment_status = 'paid'
            ->whereYear('end_time', $year)
            ->whereMonth('end_time', $month)
            ->sum('price');
    }

    /**
     * Get bookings that need review.
     */
    public function getBookingsNeedingReview(int $studentId): Collection
    {
        return $this->query()->where('student_id', $studentId)
            ->where('is_completed', true)
            ->whereDoesntHave('review')
            ->with(['tutor.user', 'subject'])
            ->orderBy('end_time', 'desc')
            ->get();
    }

    /**
     * Search bookings.
     */
    public function searchBookings(array $filters): LengthAwarePaginator
    {
        $query = $this->query()->with(['tutor.user', 'student', 'subject']);

        // Use boolean scopes instead of status column
        if (isset($filters['status'])) {
            switch ($filters['status']) {
                case 'pending':
                    $query->pending();
                    break;
                case 'confirmed':
                case 'accepted':
                    $query->confirmed();
                    break;
                case 'cancelled':
                    $query->cancelled();
                    break;
                case 'completed':
                    $query->completed();
                    break;
            }
        }

        if (isset($filters['subject_id'])) {
            $query->where('subject_id', $filters['subject_id']);
        }

        if (isset($filters['date_from'])) {
            $query->where('start_time', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('start_time', '<=', $filters['date_to']);
        }

        if (isset($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (isset($filters['tutor_id'])) {
            $query->where('tutor_id', $filters['tutor_id']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Get all bookings for a student.
     */
    public function getStudentBookings(int $studentId): Collection
    {
        return $this->query()->where('student_id', $studentId)
            ->with(['tutor.user', 'subject'])
            ->orderBy('start_time', 'desc')
            ->get();
    }
}
