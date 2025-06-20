<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Tutor;
use App\Models\Subject;
use App\Notifications\BookingStatusChanged;
use App\Notifications\BookingStatusUpdated;
use App\Notifications\BookingCancelled;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $user = Auth::user();
        $query = $user->role === 'tutor'
            ? Booking::where('tutor_id', $user->tutor->id)
            : Booking::where('student_id', $user->id);

        $bookings = $query->with(['tutor', 'student', 'subject'])
            ->latest()
            ->paginate(10);

        return view('bookings.index', compact('bookings'));
    }

    public function create(Tutor $tutor)
    {
        $subjects = $tutor->subjects;

        // Set default start and end times in GMT+7 (Asia/Ho_Chi_Minh)
        $nowInHanoi = Carbon::now('Asia/Ho_Chi_Minh');
        $defaultStart = $nowInHanoi->copy()->addHours(2); // 2 hours ahead to ensure it's safe
        $defaultEnd = $nowInHanoi->copy()->addHours(4); // 4 hours ahead

        // Separate date and time for better d-m-y format handling
        $defaultStartDate = $defaultStart->format('Y-m-d');
        $defaultStartTime = $defaultStart->format('H:i');
        $defaultEndDate = $defaultEnd->format('Y-m-d');
        $defaultEndTime = $defaultEnd->format('H:i');

                // Handle old values from validation errors
        // For display format (dd-mm-yyyy)
        $oldStartDateDisplay = old('start_date_display', $defaultStart->format('d-m-Y'));
        $oldEndDateDisplay = old('end_date_display', $defaultEnd->format('d-m-Y'));

        // For hidden backend format (yyyy-mm-dd)
        $oldStartDate = old('start_date', $defaultStartDate);
        $oldStartTime = old('start_time_only', $defaultStartTime);
        $oldEndDate = old('end_date', $defaultEndDate);
        $oldEndTime = old('end_time_only', $defaultEndTime);

        // Also provide the combined datetime values for JavaScript
        $oldStartDateTime = old('start_time', $defaultStart->format('Y-m-d\TH:i'));
        $oldEndDateTime = old('end_time', $defaultEnd->format('Y-m-d\TH:i'));

                return view('bookings.create', compact(
            'tutor',
            'subjects',
            'oldStartDateDisplay',
            'oldEndDateDisplay',
            'oldStartDate',
            'oldStartTime',
            'oldEndDate',
            'oldEndTime',
            'oldStartDateTime',
            'oldEndDateTime'
        ));
    }

    public function store(Request $request, Tutor $tutor)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'subject_id' => 'required|exists:subjects,id',
                'start_time' => [
                    'required',
                    'date',
                    'after:now',
                    function ($attribute, $value, $fail) {
                        $startTime = Carbon::parse($value, 'Asia/Ho_Chi_Minh');
                        $now = Carbon::now('Asia/Ho_Chi_Minh');

                        if ($startTime->lt($now->copy()->addMinutes(30))) {
                            $fail(__('booking.validation.booking_advance_notice'));
                        }
                    },
                ],
                'end_time' => [
                    'required',
                    'date',
                    'after:start_time',
                    function ($attribute, $value, $fail) use ($request) {
                        $startTime = Carbon::parse($request->start_time, 'Asia/Ho_Chi_Minh');
                        $endTime = Carbon::parse($value, 'Asia/Ho_Chi_Minh');
                        if ($endTime->diffInHours($startTime) > 4) {
                            $fail(__('booking.validation.max_duration'));
                        }
                    },
                ],
                'notes' => 'nullable|string|max:500',
            ]);

            // Check if student has any pending bookings with this tutor
            $hasPendingBooking = Booking::where('student_id', Auth::id())
                ->where('tutor_id', $tutor->id)
                ->where('status', Booking::STATUS_PENDING)
                ->exists();

            if ($hasPendingBooking) {
                return back()->withErrors(['booking' => __('booking.validation.pending_booking_exists')]);
            }

            // Calculate price based on duration and tutor's hourly rate
            $startTime = Carbon::parse($validated['start_time']);
            $endTime = Carbon::parse($validated['end_time']);
            $duration = $endTime->diffInMinutes($startTime);
            $hours = $duration / 60;
            $price = $hours * $tutor->hourly_rate;

            // Create booking with explicit tutor_id
            $booking = new Booking();
            $booking->student_id = Auth::id();
            $booking->tutor_id = $tutor->id;
            $booking->subject_id = $validated['subject_id'];
            $booking->start_time = $validated['start_time'];
            $booking->end_time = $validated['end_time'];
            $booking->notes = $validated['notes'];
            $booking->price = $price;
            $booking->status = Booking::STATUS_PENDING;
            $booking->save();

            // Send notification to tutor
            $tutor->user->notify(new BookingStatusChanged($booking));

            DB::commit();

            return redirect()->route('bookings.index')
                ->with('success', __('booking.success.booking_requested'))
                ->with('new_booking_id', $booking->id);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Booking creation failed: ' . $e->getMessage());
            return back()->withErrors(['error' => __('booking.errors.booking_failed')]);
        }
    }

    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);

        $booking->load(['tutor.user', 'student', 'subject', 'review']);

        return view('bookings.show', compact('booking'));
    }

    public function update(Request $request, Booking $booking)
    {
        $this->authorize('update', $booking);

        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'status' => ['required', 'in:' . implode(',', [
                    Booking::STATUS_ACCEPTED,
                    Booking::STATUS_REJECTED,
                    Booking::STATUS_CANCELLED
                ])],
                'meeting_link' => 'nullable|url|max:255',
                'rejection_reason' => 'nullable|string|max:100',
                'rejection_description' => 'nullable|string|max:500',
            ]);

            // Additional validation for status changes
            if ($validated['status'] === Booking::STATUS_ACCEPTED && !$booking->isPending()) {
                return back()->withErrors(['status' => 'Only pending bookings can be accepted.']);
            }

            if ($validated['status'] === Booking::STATUS_CANCELLED && !$booking->canBeCancelled()) {
                return back()->withErrors(['status' => 'This booking cannot be cancelled.']);
            }

            $booking->update($validated);

            // Send notification to student
            if ($validated['status'] === Booking::STATUS_REJECTED) {
                // Tutor rejected, notify student
                $booking->student->notify(new BookingCancelled($booking, 'tutor'));
            } else {
                $booking->student->notify(new BookingStatusUpdated($booking));
            }

            DB::commit();

            return redirect()->route('bookings.show', $booking)
                ->with('success', 'Booking status updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Booking update failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to update booking. Please try again.']);
        }
    }

    public function destroy(Booking $booking)
    {
        $this->authorize('delete', $booking);

        try {
            DB::beginTransaction();

            if (!$booking->canBeCancelled()) {
                return back()->withErrors(['error' => 'This booking cannot be cancelled.']);
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

            // Don't delete the booking, just mark as cancelled
            // $booking->delete();

            // Send notification to the other party
            if (Auth::user()->id === $booking->student_id) {
                // Student cancelled, notify tutor
                $booking->tutor->user->notify(new BookingCancelled($booking, 'student'));
            } else {
                // Tutor cancelled, notify student
                $booking->student->notify(new BookingCancelled($booking, 'tutor'));
            }

            DB::commit();

            return redirect()->route('bookings.index')
                ->with('success', __('booking.success.booking_cancelled'));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Booking cancellation failed: ' . $e->getMessage());
            return back()->withErrors(['error' => __('booking.errors.cancellation_failed')]);
        }
    }

    /**
     * Show student profile for tutors
     *
     * @param Booking $booking
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showStudentProfile(Booking $booking)
    {
        // Only allow tutors to view student profiles for their bookings
        if (Auth::user()->role !== 'tutor' || Auth::user()->id !== $booking->tutor->user->id) {
            abort(403, 'Unauthorized to view this student profile.');
        }

        $student = $booking->student;

        // Get all bookings between this tutor and student
        $allBookings = Booking::where('tutor_id', $booking->tutor_id)
            ->where('student_id', $student->id)
            ->with(['subject'])
            ->orderBy('start_time', 'desc')
            ->get();

        // Get student's reviews for this tutor
        $reviews = \App\Models\Review::where('student_id', $student->id)
            ->whereHas('booking', function($query) use ($booking) {
                $query->where('tutor_id', $booking->tutor_id);
            })
            ->with(['booking.subject'])
            ->latest()
            ->get();

        return view('bookings.student-profile', compact('booking', 'student', 'allBookings', 'reviews'));
    }

    /**
     * Redirect to the payment page for a booking
     *
     * @param Booking $booking
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function payment(Booking $booking)
    {
        $this->authorize('view', $booking);

        // Check if the booking is in a valid state for payment
        if (!$booking->isAccepted() || $booking->isPaid()) {
            return redirect()->route('bookings.show', $booking)
                ->with('error', 'This booking is not eligible for payment.');
        }

        return view('bookings.payment', compact('booking'));
    }
}
