<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\PaymentService;
use App\Services\VnpayService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    protected VnpayService $vnpayService;

    public function __construct(PaymentService $paymentService, VnpayService $vnpayService)
    {
        $this->paymentService = $paymentService;
        $this->vnpayService = $vnpayService;
    }

    /**
     * Create Stripe payment intent
     */
    public function createIntent(Request $request, Booking $booking): JsonResponse
    {
        try {
            $this->authorizeBookingAccess($booking);
            $this->validateBookingForPayment($booking);

            $result = $this->paymentService->createStripePaymentIntent($booking);

            if (!$result->success) {
                return response()->json(['error' => $result->error], 400);
            }

            return response()->json([
                'clientSecret' => $result->clientSecret,
            ]);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Confirm Stripe payment
     */
    public function confirm(Request $request, Booking $booking): RedirectResponse
    {
        try {
            $this->authorizeBookingAccess($booking);

            $success = $this->paymentService->confirmStripePayment($booking);

            if ($success) {
                return redirect()->route('bookings.show', $booking)
                    ->with('success', __('booking.payment_completed_successfully'));
            }

            return redirect()->route('bookings.show', $booking)
                ->with('error', __('booking.payment_not_completed'));

        } catch (Exception $e) {
            return redirect()->route('bookings.show', $booking)
                ->with('error', __('booking.error_confirming_payment', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Handle Stripe webhook
     */
    public function handleWebhook(Request $request): Response
    {
        try {
            $this->paymentService->handleStripeWebhook($request);

            return response('Webhook received', 200);

        } catch (Exception $e) {
            return response('Webhook Error: '.$e->getMessage(), 400);
        }
    }

    /**
     * Create VNPay payment
     */
    public function createVnpayPayment(Request $request, Booking $booking): JsonResponse
    {
        try {
            $this->authorizeBookingAccess($booking);
            $this->validateBookingForPayment($booking);

            $result = $this->paymentService->createVnpayPayment($booking, $request->ip());

            if (!$result->success) {
                return response()->json(['error' => $result->error], 400);
            }

            return response()->json([
                'payment_url' => $result->paymentUrl,
            ]);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Handle VNPay return
     */
    public function vnpayReturn(Request $request): RedirectResponse
    {
        try {
            $result = $this->paymentService->handleVnpayReturn($request->all());

            if ($result['success']) {
                return redirect()->route('bookings.show', $result['booking'])
                    ->with('success', $result['message']);
            }

            return redirect()->route('bookings.show', $result['booking'])
                ->with('error', $result['message']);

        } catch (Exception $e) {
            return redirect()->route('home')
                ->with('error', __('booking.payment_processing_failed'));
        }
    }

    /**
     * Handle VNPay IPN
     */
    public function vnpayIpn(Request $request): Response
    {
        try {
            $this->paymentService->handleVnpayIpn($request->all());

            return response('OK', 200);

        } catch (Exception $e) {
            return response('ERROR', 400);
        }
    }

    /**
     * Process payment (unified endpoint)
     */
    public function processPayment(Request $request, Booking $booking): JsonResponse|RedirectResponse
    {
        $request->validate([
            'payment_method' => 'required|in:stripe,vnpay',
        ]);

        try {
            $this->authorizeBookingAccess($booking);
            $this->validateBookingForPayment($booking);

            if ($request->payment_method === 'stripe') {
                return $this->createIntent($request, $booking);
            } else {
                return $this->createVnpayPayment($request, $booking);
            }

        } catch (Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 400);
            }

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Get transaction history for booking
     */
    public function getTransactionHistory(Booking $booking): JsonResponse
    {
        try {
            $this->authorizeBookingAccess($booking);

            $transactions = $this->paymentService->getBookingTransactions($booking);

            return response()->json([
                'transactions' => $transactions,
            ]);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * View transaction history page
     */
    public function viewTransactionHistory(Booking $booking): View
    {
        $this->authorizeBookingAccess($booking);

        $transactions = $this->paymentService->getBookingTransactions($booking);

        return view('bookings.transactions', compact('booking', 'transactions'));
    }

    /**
     * Authorize booking access
     */
    protected function authorizeBookingAccess(Booking $booking): void
    {
        if ($booking->student_id !== Auth::id()) {
            throw new Exception(__('booking.unauthorized_access'));
        }
    }

    /**
     * Validate booking for payment
     */
    protected function validateBookingForPayment(Booking $booking): void
    {
        if ($booking->status !== 'accepted' || $booking->payment_status === 'paid') {
            throw new Exception(__('booking.invalid_booking_status_for_payment'));
        }
    }
}
