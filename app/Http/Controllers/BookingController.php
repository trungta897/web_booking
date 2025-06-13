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

class BookingController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $user = Auth::user();
        $bookings = $user->role === 'tutor'
            ? Booking::where('tutor_id', $user->tutor->id)
            : Booking::where('student_id', $user->id);

        $bookings = $bookings->with(['tutor', 'student', 'subject'])
            ->latest()
            ->paginate(10);

        return view('bookings.index', compact('bookings'));
    }

    public function create(Tutor $tutor)
    {
        $subjects = $tutor->subjects;
        $availability = $tutor->availability;

        // Set default start and end times in GMT+7 (Asia/Ho_Chi_Minh)
        $nowInHanoi = Carbon::now('Asia/Ho_Chi_Minh');
        $defaultStartTime = $nowInHanoi->format('Y-m-d\TH:i');
        $defaultEndTime = $nowInHanoi->copy()->addHours(2)->format('Y-m-d\TH:i');

        return view('bookings.create', compact('tutor', 'subjects', 'availability', 'defaultStartTime', 'defaultEndTime'));
    }

    public function store(Request $request, Tutor $tutor)
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'notes' => 'nullable|string|max:500',
        ]);

        // Check if the time slot is available
        if (!$tutor->isTimeSlotAvailable($validated['start_time'], $validated['end_time'])) {
            return back()->withErrors(['time_slot' => 'This time slot is not available.']);
        }

        // Calculate price based on duration and tutor's hourly rate
        $duration = strtotime($validated['end_time']) - strtotime($validated['start_time']);
        $hours = $duration / 3600;
        $price = $hours * $tutor->hourly_rate;

        $booking = Booking::create([
            'student_id' => Auth::id(),
            'tutor_id' => $tutor->id,
            'subject_id' => $validated['subject_id'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'notes' => $validated['notes'],
            'price' => $price,
            'status' => 'pending',
        ]);

        // Send notification to tutor
        $tutor->user->notify(new BookingStatusChanged($booking));

        return redirect()->route('bookings.index')
            ->with('success', 'Booking request sent successfully.')
            ->with('new_booking_id', $booking->id);
    }

    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);

        return view('bookings.show', compact('booking'));
    }

    public function update(Request $request, Booking $booking)
    {
        $this->authorize('update', $booking);

        $validated = $request->validate([
            'status' => ['required', 'in:accepted,rejected,cancelled'],
        ]);

        $booking->update($validated);

        // Send notification to student
        $booking->student->notify(new BookingStatusUpdated($booking));

        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Booking status updated successfully.');
    }

    public function destroy(Booking $booking)
    {
        $this->authorize('delete', $booking);

        $booking->update(['status' => 'cancelled']);
        $booking->delete();

        // Send notification to tutor
        $booking->tutor->user->notify(new BookingStatusChanged($booking));

        return redirect()->route('bookings.index')
            ->with('success', 'Booking cancelled successfully.');
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
        if ($booking->status !== 'accepted' || $booking->payment_status === 'paid') {
            return redirect()->route('bookings.show', $booking)
                ->with('error', 'This booking is not eligible for payment.');
        }

        return view('bookings.payment', compact('booking'));
    }
}
