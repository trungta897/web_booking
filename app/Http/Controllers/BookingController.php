<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingRequest;
use App\Models\Booking;
use App\Models\Tutor;
use App\Services\BookingService;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BookingController extends Controller
{
    use AuthorizesRequests;

    protected BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * Display user's bookings
     */
    public function index(): View
    {
        $user = Auth::user();
        $bookings = $this->bookingService->getUserBookings($user);

        return view('bookings.index', compact('bookings'));
    }

    /**
     * Show create booking form
     */
    public function create(Tutor $tutor): View
    {
        $formData = $this->bookingService->getCreateBookingData($tutor);

        return view('bookings.create', array_merge($formData, ['tutor' => $tutor]));
    }

    /**
     * Store new booking
     */
    public function store(BookingRequest $request, Tutor $tutor): RedirectResponse
    {
        try {
            $booking = $this->bookingService->createBooking($request->validated(), $tutor, Auth::user());

            return redirect()->route('bookings.index')
                ->with('success', __('booking.success.booking_requested'))
                ->with('new_booking_id', $booking->id);

        } catch (Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display booking details
     */
    public function show(Booking $booking): View
    {
        $this->authorize('view', $booking);

        $booking = $this->bookingService->getBookingDetails($booking);

        return view('bookings.show', compact('booking'));
    }

    /**
     * Update booking status
     */
    public function update(BookingRequest $request, Booking $booking): RedirectResponse
    {
        $this->authorize('update', $booking);

        try {
            $this->bookingService->updateBookingStatus($booking, $request->validated());

            return back()->with('success', __('booking.success.status_updated'));

        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Cancel booking
     */
    public function destroy(Booking $booking): RedirectResponse
    {
        $this->authorize('delete', $booking);

        try {
            $this->bookingService->cancelBooking($booking, Auth::user());

            return redirect()->route('bookings.index')
                ->with('success', __('booking.success.booking_cancelled'));

        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display student profile for tutor
     */
    public function showStudentProfile(Booking $booking): View
    {
        $this->authorize('view', $booking);

        if (Auth::user()->role !== 'tutor') {
            abort(403, 'Only tutors can view student profiles');
        }

        $studentData = $this->bookingService->getStudentProfileData($booking);

        return view('bookings.student-profile', $studentData);
    }

    /**
     * Show payment page
     */
    public function payment(Booking $booking): View|RedirectResponse
    {
        $this->authorize('view', $booking);

        if ($booking->payment_status === 'paid') {
            return redirect()->route('bookings.show', $booking)
                ->with('info', __('booking.info.already_paid'));
        }

        return view('bookings.payment', compact('booking'));
    }

    /**
     * Display transactions page
     */
    public function transactions(): View
    {
        $user = Auth::user();
        $transactions = $this->bookingService->getUserTransactions($user);

        return view('bookings.transactions', compact('transactions'));
    }
}
