<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Tutor;
use App\Models\User;
use App\Notifications\BookingCancelled;
use App\Notifications\BookingStatusChanged;
use App\Notifications\BookingStatusUpdated;
use App\Repositories\BookingRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class BookingService extends BaseService
{
    protected BookingRepository $bookingRepository;

    public function __construct()
    {
        $this->bookingRepository = new BookingRepository(new Booking);
    }

    /**
     * Get user bookings with filters
     */
    public function getUserBookings(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = $user->role === 'tutor'
            ? Booking::where('tutor_id', $user->tutor->id)
            : Booking::where('student_id', $user->id);

        return $query->with(['tutor.user', 'student', 'subject'])
            ->latest()
            ->paginate(10);
    }

    /**
     * Get create booking form data
     */
    public function getCreateBookingData(Tutor $tutor): array
    {
        $subjects = $tutor->subjects;

        // Set default start and end times in GMT+7 (Asia/Ho_Chi_Minh)
        $nowInHanoi = Carbon::now('Asia/Ho_Chi_Minh');
        $defaultStart = $nowInHanoi->copy()->addHours(2);
        $defaultEnd = $nowInHanoi->copy()->addHours(4);

        // For display format (dd-mm-yyyy)
        $oldStartDateDisplay = old('start_date_display', $defaultStart->format('d-m-Y'));
        $oldEndDateDisplay = old('end_date_display', $defaultEnd->format('d-m-Y'));

        // For hidden backend format (yyyy-mm-dd)
        $oldStartDate = old('start_date', $defaultStart->format('Y-m-d'));
        $oldStartTime = old('start_time_only', $defaultStart->format('H:i'));
        $oldEndDate = old('end_date', $defaultEnd->format('Y-m-d'));
        $oldEndTime = old('end_time_only', $defaultEnd->format('H:i'));

        // Combined datetime values for JavaScript
        $oldStartDateTime = old('start_time', $defaultStart->format('Y-m-d\TH:i'));
        $oldEndDateTime = old('end_time', $defaultEnd->format('Y-m-d\TH:i'));

        return [
            'subjects' => $subjects,
            'oldStartDateDisplay' => $oldStartDateDisplay,
            'oldEndDateDisplay' => $oldEndDateDisplay,
            'oldStartDate' => $oldStartDate,
            'oldStartTime' => $oldStartTime,
            'oldEndDate' => $oldEndDate,
            'oldEndTime' => $oldEndTime,
            'oldStartDateTime' => $oldStartDateTime,
            'oldEndDateTime' => $oldEndDateTime,
        ];
    }

    /**
     * Create booking with validation
     */
    public function createBooking(array $data, Tutor $tutor, User $student): Booking
    {
        return $this->executeTransaction(function () use ($data, $tutor, $student) {
            // Check if student has any pending bookings with this tutor
            $hasPendingBooking = Booking::where('student_id', $student->id)
                ->where('tutor_id', $tutor->id)
                ->where('status', Booking::STATUS_PENDING)
                ->exists();

            if ($hasPendingBooking) {
                throw new Exception(__('booking.validation.pending_booking_exists'));
            }

            // Calculate price based on duration and tutor's hourly rate
            $startTime = Carbon::parse($data['start_time']);
            $endTime = Carbon::parse($data['end_time']);
            $duration = $endTime->diffInMinutes($startTime);
            $hours = $duration / 60;
            $price = $hours * $tutor->hourly_rate;

            // Create booking
            $booking = new Booking;
            $booking->student_id = $student->id;
            $booking->tutor_id = $tutor->id;
            $booking->subject_id = $data['subject_id'];
            $booking->start_time = $data['start_time'];
            $booking->end_time = $data['end_time'];
            $booking->notes = $data['notes'] ?? null;
            $booking->price = $price;
            $booking->status = Booking::STATUS_PENDING;
            $booking->save();

            // Send notification to tutor
            $tutor->user->notify(new BookingStatusChanged($booking));

            $this->logActivity('Booking created', [
                'booking_id' => $booking->id,
                'tutor_id' => $tutor->id,
                'student_id' => $student->id,
            ]);

            return $booking;
        });
    }

    /**
     * Get booking details with related data
     */
    public function getBookingDetails(Booking $booking): Booking
    {
        return $booking->load(['tutor.user', 'student', 'subject', 'review']);
    }

    /**
     * Update booking status with validation
     */
    public function updateBookingStatus(Booking $booking, array $data): bool
    {
        return $this->executeTransaction(function () use ($booking, $data) {
            // Additional validation for status changes
            if (isset($data['status'])) {
                if ($data['status'] === Booking::STATUS_ACCEPTED && ! $booking->isPending()) {
                    throw new Exception('Only pending bookings can be accepted.');
                }

                if ($data['status'] === Booking::STATUS_CANCELLED && ! $booking->canBeCancelled()) {
                    throw new Exception('This booking cannot be cancelled.');
                }
            }

            $booking->update($data);

            // Send notification to student
            if (isset($data['status'])) {
                if ($data['status'] === Booking::STATUS_REJECTED) {
                    $booking->student->notify(new BookingCancelled($booking, 'tutor'));
                } else {
                    $booking->student->notify(new BookingStatusUpdated($booking));
                }
            }

            $this->logActivity('Booking status updated', [
                'booking_id' => $booking->id,
                'status' => $data['status'] ?? 'N/A',
            ]);

            return true;
        });
    }

    /**
     * Cancel booking with notification
     */
    public function cancelBooking(Booking $booking, User $user): bool
    {
        return $this->executeTransaction(function () use ($booking, $user) {
            if (! $booking->canBeCancelled()) {
                throw new Exception('This booking cannot be cancelled.');
            }

            $validated = request()->validate([
                'cancellation_reason' => 'required|string|max:100',
                'cancellation_description' => 'nullable|string|max:500',
            ]);

            $booking->update([
                'status' => Booking::STATUS_CANCELLED,
                'cancellation_reason' => $validated['cancellation_reason'],
                'cancellation_description' => $validated['cancellation_description'] ?? null,
            ]);

            // Send notification to the other party
            if ($user->id === $booking->student_id) {
                // Student cancelled, notify tutor
                $booking->tutor->user->notify(new BookingCancelled($booking, 'student'));
            } else {
                // Tutor cancelled, notify student
                $booking->student->notify(new BookingCancelled($booking, 'tutor'));
            }

            $this->logActivity('Booking cancelled', [
                'booking_id' => $booking->id,
                'cancelled_by' => $user->role,
            ]);

            return true;
        });
    }

    /**
     * Get student profile data for tutor
     */
    public function getStudentProfileData(Booking $booking): array
    {
        $student = $booking->student;

        // Get all bookings between this tutor and student
        $allBookings = Booking::where('tutor_id', $booking->tutor_id)
            ->where('student_id', $student->id)
            ->with(['subject'])
            ->orderBy('start_time', 'desc')
            ->get();

        // Get student's reviews for this tutor
        $reviews = \App\Models\Review::where('student_id', $student->id)
            ->whereHas('booking', function ($query) use ($booking) {
                $query->where('tutor_id', $booking->tutor_id);
            })
            ->with(['booking.subject'])
            ->latest()
            ->get();

        return [
            'booking' => $booking,
            'student' => $student,
            'allBookings' => $allBookings,
            'reviews' => $reviews,
        ];
    }

    /**
     * Get user transactions
     */
    public function getUserTransactions(User $user): LengthAwarePaginator
    {
        $query = $user->role === 'tutor'
            ? Booking::where('tutor_id', $user->tutor->id)
            : Booking::where('student_id', $user->id);

        return $query->with(['tutor.user', 'student', 'subject'])
            ->whereIn('status', ['completed', 'paid'])
            ->latest()
            ->paginate(10);
    }

    /**
     * Get upcoming bookings for tutor
     */
    public function getUpcomingBookingsForTutor(int $tutorId): Collection
    {
        return Booking::where('tutor_id', $tutorId)
            ->where('status', 'confirmed')
            ->where('start_time', '>', Carbon::now())
            ->with(['student', 'subject'])
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Get tutor earnings data
     */
    public function getTutorEarnings(int $tutorId, ?int $year = null, ?int $month = null): array
    {
        $totalEarnings = Booking::where('tutor_id', $tutorId)
            ->where('status', 'completed')
            ->where('payment_status', 'paid')
            ->sum('price');

        $monthlyEarnings = 0;
        if ($year && $month) {
            $monthlyEarnings = Booking::where('tutor_id', $tutorId)
                ->where('status', 'completed')
                ->where('payment_status', 'paid')
                ->whereYear('start_time', $year)
                ->whereMonth('start_time', $month)
                ->sum('price');
        }

        return [
            'total_earnings' => $totalEarnings,
            'monthly_earnings' => $monthlyEarnings,
            'formatted_total' => number_format($totalEarnings, 0, ',', '.').' VNĐ',
            'formatted_monthly' => number_format($monthlyEarnings, 0, ',', '.').' VNĐ',
        ];
    }

    /**
     * Get bookings needing review
     */
    public function getBookingsNeedingReview(int $studentId): Collection
    {
        return Booking::where('student_id', $studentId)
            ->where('status', 'completed')
            ->whereDoesntHave('review')
            ->with(['tutor.user', 'subject'])
            ->orderBy('end_time', 'desc')
            ->get();
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
        if ($newStatus === 'accepted' && ! $booking->isPending()) {
            throw new Exception('Only pending bookings can be accepted');
        }

        if ($newStatus === 'cancelled' && ! $booking->canBeCancelled()) {
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
