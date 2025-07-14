<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingRequest;
use App\Models\Booking;
use App\Models\Tutor;
use App\Services\BookingService;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
     * Display user's bookings.
     */
    public function index(): View
    {
        $user = Auth::user();
        $bookings = $this->bookingService->getUserBookings($user);

        return view('bookings.index', compact('bookings'));
    }

    /**
     * Show create booking form.
     */
    public function create(Tutor $tutor): View
    {
        $formData = $this->bookingService->getCreateBookingData($tutor);

        return view('bookings.create', array_merge($formData, ['tutor' => $tutor]));
    }

    /**
     * Store new booking.
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
     * Display booking details.
     */
    public function show(Booking $booking): View
    {
        $this->authorize('view', $booking);

        $booking = $this->bookingService->getBookingDetails($booking);

        return view('bookings.show', compact('booking'));
    }

    /**
     * Update booking status.
     */
    public function update(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('update', $booking);

        try {
            // Validate input
            $validated = $request->validate([
                'action' => 'required|in:accept,reject', // Đổi từ 'status' thành 'action'
                'rejection_reason' => 'nullable|string|max:100',
                'rejection_description' => 'nullable|string|max:500',
            ]);

            // Kiểm tra xem người dùng có phải là tutor không
            if (Auth::user()->role !== 'tutor') {
                return back()->withErrors(['error' => 'Only tutors can update booking status']);
            }

            // Kiểm tra xem người dùng có phải là tutor của booking này không
            if (Auth::user()->tutor->id !== $booking->tutor_id) {
                return back()->withErrors(['error' => 'You can only update your own bookings']);
            }

            // 🎯 SỬA LOGIC XÁC NHẬN - Chỉ sử dụng boolean fields
            if ($validated['action'] === 'accept') {
                $booking->update([
                    // Boolean logic: Booking được chấp nhận nhưng chưa confirmed (chưa thanh toán)
                    'is_confirmed' => false, // Sẽ thành true khi thanh toán xong
                    'is_cancelled' => false,
                    'is_completed' => false,
                    'accepted_at' => now(), // 🎯 THÊM: Track thời điểm tutor accept
                ]);
                
                // Gửi thông báo cho student
                $booking->student->notify(new \App\Notifications\BookingStatusChanged($booking));
                
            } elseif ($validated['action'] === 'reject') {
                $booking->update([
                    'rejection_reason' => $validated['rejection_reason'],
                    'rejection_description' => $validated['rejection_description'],
                    // Boolean logic: Booking bị từ chối = cancelled
                    'is_confirmed' => false,
                    'is_cancelled' => true,
                    'is_completed' => false,
                ]);
                
                // Gửi thông báo cho student
                $booking->student->notify(new \App\Notifications\BookingStatusChanged($booking));
            }

            // Thông báo thành công
            $message = $validated['action'] === 'accept'
                ? __('booking.success.booking_accepted')
                : __('booking.success.booking_rejected');

            return back()->with('success', $message);
        } catch (Exception $e) {
            // Log lỗi để debug
            Log::error('Booking update error: ' . $e->getMessage(), [
                'booking_id' => $booking->id,
                'user_id' => Auth::id(),
                'data' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Cancel booking.
     */
    public function destroy(Booking $booking): RedirectResponse
    {
        $this->authorize('delete', $booking);

        try {
            $this->bookingService->cancelBooking($booking, Auth::user());

            // Redirect based on user role
            $user = Auth::user();
            if ($user->role === 'student') {
                return redirect()->route('student.dashboard')
                    ->with('success', __('booking.success.booking_cancelled'));
            } else {
                return redirect()->route('tutor.dashboard')
                    ->with('success', __('booking.success.booking_cancelled'));
            }
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display student profile for tutor.
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
     * Show payment page.
     */
    public function payment(Booking $booking): View|RedirectResponse
    {
        $this->authorize('view', $booking);

        if ($booking->is_cancelled) {
            return redirect()->route('bookings.show', $booking)
                ->with('error', __('booking.errors.booking_cancelled_payment'));
        }

        // 🔐 KIỂM TRA CHẶT CHẼ: ĐÃ THANH TOÁN CHƯA?
        if ($booking->is_confirmed || $booking->isPaid()) {
            return redirect()->route('bookings.show', $booking)
                ->with('success', '✅ Booking này đã được thanh toán hoàn tất. Bạn có thể xem lịch sử giao dịch.')
                ->with('info', __('booking.info.already_paid'));
        }

        // Double check với transaction database
        if ($booking->transactions()->where('type', 'payment')->where('status', 'completed')->exists()) {
            return redirect()->route('bookings.show', $booking)
                ->with('success', '✅ Booking này đã có giao dịch thanh toán hoàn thành. Không thể thanh toán lại.')
                ->with('info', 'Giao dịch đã hoàn tất.');
        }

        if ($booking->isPending()) {
            return redirect()->route('bookings.show', $booking)
                ->with('error', __('booking.errors.booking_not_accepted_payment'));
        }

        return view('bookings.payment', compact('booking'));
    }

    /**
     * Display transactions page.
     */
    public function transactions(): View
    {
        $user = Auth::user();
        $transactions = $this->bookingService->getUserTransactions($user);

        return view('bookings.transactions', compact('transactions'));
    }
}
