<?php

namespace App\Services;

use App\Contracts\Services\BookingServiceInterface;
use App\Models\Booking;
use App\Models\User;
use App\Models\Tutor;
use App\Repositories\BookingRepository;
use App\Notifications\BookingStatusChanged;
use App\Notifications\BookingStatusUpdated;
use App\Notifications\BookingCancelled;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Exception;

class BookingService extends BaseService implements BookingServiceInterface
{
    protected BookingRepository $bookingRepository;

    public function __construct()
    {
        $this->bookingRepository = new BookingRepository(new Booking());
    }

    /**
     * Create a new booking
     */
    public function createBooking(array $data): Booking
    {
        return $this->executeTransaction(function () use ($data) {
            $tutor = Tutor::findOrFail($data['tutor_id']);

            // Validate booking constraints
            $this->validateBookingConstraints($data);

            // Calculate price
            $price = $this->calculateBookingPrice($tutor, $data['start_time'], $data['end_time']);

            // Create booking
            $booking = Booking::create([
                'student_id' => $data['student_id'] ?? Auth::id(),
                'tutor_id' => $tutor->id,
                'subject_id' => $data['subject_id'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'notes' => $data['notes'] ?? null,
                'price' => $price,
                'status' => 'pending',
            ]);

            // Send notification to tutor
            $tutor->user->notify(new BookingStatusChanged($booking));

            $this->logActivity('Booking created', [
                'booking_id' => $booking->id,
                'tutor_id' => $tutor->id,
                'student_id' => $booking->student_id
            ]);

            return $booking;
        });
    }

    /**
     * Update booking status
     */
    public function updateBookingStatus(int $bookingId, string $status, ?string $rejectionReason = null): bool
    {
        return $this->executeTransaction(function () use ($bookingId, $status, $rejectionReason) {
            $booking = Booking::findOrFail($bookingId);
            $oldStatus = $booking->status;

            // Validate status change
            $this->validateStatusChange($booking, $status);

            // Update booking
            $updateData = ['status' => $status];
            if ($rejectionReason && $status === 'rejected') {
                $updateData['rejection_reason'] = $rejectionReason;
            }

            $result = $booking->update($updateData);

            // Send notifications based on status
            $this->sendStatusChangeNotification($booking, $oldStatus, $status);

            $this->logActivity('Booking status updated', [
                'booking_id' => $booking->id,
                'old_status' => $oldStatus,
                'new_status' => $status
            ]);

            return $result;
        });
    }

    /**
     * Cancel booking
     */
    public function cancelBooking(int $bookingId, int $cancelledBy, ?string $reason = null): bool
    {
        return $this->executeTransaction(function () use ($bookingId, $cancelledBy, $reason) {
            $booking = Booking::findOrFail($bookingId);
            $cancelledByUser = User::findOrFail($cancelledBy);

            if (!$booking->canBeCancelled()) {
                throw new Exception('This booking cannot be cancelled');
            }

            $result = $booking->update([
                'status' => 'cancelled',
                'cancellation_reason' => $reason,
                'cancelled_by' => $cancelledByUser->role,
                'cancelled_at' => Carbon::now(),
            ]);

            // Send cancellation notification
            if ($cancelledByUser->role === 'tutor') {
                $booking->student->notify(new BookingCancelled($booking, $cancelledByUser));
            } else {
                $booking->tutor->user->notify(new BookingCancelled($booking, $cancelledByUser));
            }

            $this->logActivity('Booking cancelled', [
                'booking_id' => $booking->id,
                'cancelled_by' => $cancelledByUser->role,
                'reason' => $reason
            ]);

            return $result;
        });
    }

    /**
     * Get user bookings with filters
     */
    public function getUserBookings(int $userId, string $role, array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->bookingRepository->getBookingsForUser($userId, $role, $filters);
    }

    /**
     * Get upcoming bookings for tutor
     */
    public function getUpcomingBookingsForTutor(int $tutorId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->bookingRepository->getUpcomingBookingsForTutor($tutorId);
    }

    /**
     * Get tutor earnings data
     */
    public function getTutorEarnings(int $tutorId, ?int $year = null, ?int $month = null): array
    {
        $totalEarnings = $this->bookingRepository->getTutorTotalEarnings($tutorId);

        $monthlyEarnings = 0;
        if ($year && $month) {
            $monthlyEarnings = $this->bookingRepository->getTutorMonthlyEarnings($tutorId, $year, $month);
        }

        return [
            'total_earnings' => $totalEarnings,
            'monthly_earnings' => $monthlyEarnings,
            'formatted_total' => $this->formatCurrency($totalEarnings),
            'formatted_monthly' => $this->formatCurrency($monthlyEarnings),
        ];
    }

    /**
     * Get bookings needing review
     */
    public function getBookingsNeedingReview(int $studentId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->bookingRepository->getBookingsNeedingReview($studentId);
    }

    /**
     * Validate booking constraints
     */
    public function validateBookingConstraints(array $data): void
    {
        $tutor = Tutor::findOrFail($data['tutor_id']);
        $studentId = $data['student_id'] ?? Auth::id();

        // Check if student has pending booking with tutor
        if ($this->bookingRepository->hasPendingBookingWithTutor($studentId, $tutor->id)) {
            throw new Exception(__('booking.validation.pending_booking_exists'));
        }

        // Validate booking time
        $startTime = Carbon::parse($data['start_time'], 'Asia/Ho_Chi_Minh');
        $now = Carbon::now('Asia/Ho_Chi_Minh');

        if ($startTime->lt($now->copy()->addMinutes(30))) {
            throw new Exception(__('booking.validation.booking_advance_notice'));
        }

        // Validate duration
        $endTime = Carbon::parse($data['end_time'], 'Asia/Ho_Chi_Minh');
        if ($endTime->diffInHours($startTime) > 4) {
            throw new Exception(__('booking.validation.max_duration'));
        }
    }

    /**
     * Calculate booking price
     */
    protected function calculateBookingPrice(Tutor $tutor, string $startTime, string $endTime): float
    {
        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);
        $duration = $end->diffInMinutes($start);
        $hours = $duration / 60;

        return $hours * $tutor->hourly_rate;
    }

    /**
     * Validate status change
     */
    protected function validateStatusChange(Booking $booking, string $newStatus): void
    {
        if ($newStatus === 'accepted' && !$booking->isPending()) {
            throw new Exception('Only pending bookings can be accepted');
        }

        if ($newStatus === 'cancelled' && !$booking->canBeCancelled()) {
            throw new Exception('This booking cannot be cancelled');
        }
    }

    /**
     * Send status change notification
     */
    protected function sendStatusChangeNotification(Booking $booking, string $oldStatus, string $newStatus): void
    {
        if ($newStatus === 'rejected') {
            $booking->student->notify(new BookingStatusUpdated($booking));
        } elseif ($newStatus === 'accepted') {
            $booking->student->notify(new BookingStatusUpdated($booking));
        } elseif ($newStatus === 'cancelled') {
            // Determine who cancelled based on auth user
            $cancelledBy = Auth::user()->role === 'tutor' ? 'tutor' : 'student';
            $booking->student->notify(new BookingCancelled($booking, $cancelledBy));
        }
    }
}
