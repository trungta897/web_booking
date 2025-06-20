<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Tutor;
use App\Models\Subject;
use App\Notifications\BookingStatusChanged;
use App\Notifications\BookingStatusUpdated;
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
        $defaultStart = $nowInHanoi->copy()->addHour();
        $defaultEnd = $nowInHanoi->copy()->addHours(3);

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
                        $startTime = Carbon::parse($value);
                        $now = Carbon::now();

                        if ($startTime->lt($now->copy()->addMinutes(60))) {
                            $fail('Booking must be at least 1 hour in advance.');
                        }
                    },
                ],
                'end_time' => [
                    'required',
                    'date',
                    'after:start_time',
                    function ($attribute, $value, $fail) use ($request) {
                        $startTime = Carbon::parse($request->start_time);
                        $endTime = Carbon::parse($value);
                        if ($endTime->diffInHours($startTime) > 4) {
                            $fail('Booking duration cannot exceed 4 hours.');
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
                return back()->withErrors(['booking' => 'You already have a pending booking with this tutor.']);
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
                ->with('success', 'Booking request sent successfully.')
                ->with('new_booking_id', $booking->id);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Booking creation failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to create booking. Please try again.']);
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
            $booking->student->notify(new BookingStatusUpdated($booking));

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

            $booking->update(['status' => Booking::STATUS_CANCELLED]);
            $booking->delete();

            // Send notification to tutor
            $booking->tutor->user->notify(new BookingStatusChanged($booking));

            DB::commit();

            return redirect()->route('bookings.index')
                ->with('success', 'Booking cancelled successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Booking cancellation failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to cancel booking. Please try again.']);
        }
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
