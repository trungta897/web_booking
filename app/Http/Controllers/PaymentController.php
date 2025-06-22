<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\User;
use App\Models\Transaction;
use App\Notifications\PaymentReceived;
use App\Services\VnpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class PaymentController extends Controller
{
    protected $stripe;
    protected $vnpayService;

    public function __construct(VnpayService $vnpayService)
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
        $this->vnpayService = $vnpayService;
    }

    /**
     * Create a payment intent for a booking
     *
     * @param Request $request
     * @param Booking $booking
     * @return \Illuminate\Http\JsonResponse
     */
    public function createIntent(Request $request, Booking $booking)
    {
        // Check if the booking belongs to the authenticated user
        if ($booking->student_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if the booking is in a valid state for payment
        if ($booking->status !== 'accepted' || $booking->payment_status === 'paid') {
            return response()->json(['error' => 'Invalid booking status for payment'], 400);
        }

        try {
            // Create a payment intent
            $amount = $booking->price * 100; // Convert to cents for Stripe
            $paymentIntent = $this->stripe->paymentIntents->create([
                'amount' => (int)$amount,
                'currency' => 'usd',
                'metadata' => [
                    'booking_id' => $booking->id,
                    'student_id' => $booking->student_id,
                    'tutor_id' => $booking->tutor_id,
                ],
            ]);

            // Update booking with payment intent ID
            $booking->update([
                'payment_intent_id' => $paymentIntent->id,
                'payment_status' => 'pending',
            ]);

            return response()->json([
                'clientSecret' => $paymentIntent->client_secret,
            ]);
        } catch (ApiErrorException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Handle successful payment confirmation
     *
     * @param Request $request
     * @param Booking $booking
     * @return \Illuminate\Http\RedirectResponse
     */
    public function confirm(Request $request, Booking $booking)
    {
        // Check if the booking belongs to the authenticated user
        if ($booking->student_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        try {
            // Retrieve the payment intent
            $paymentIntent = $this->stripe->paymentIntents->retrieve($booking->payment_intent_id);

            // If payment succeeded, update booking status
            if ($paymentIntent->status === 'succeeded') {
                $booking->update([
                    'payment_status' => 'paid',
                ]);

                // Notify the tutor about the payment
                $booking->tutor->user->notify(new PaymentReceived($booking));

                return redirect()->route('bookings.show', $booking)
                    ->with('success', 'Payment completed successfully.');
            }

            return redirect()->route('bookings.show', $booking)
                ->with('error', 'Payment has not been completed. Please try again.');
        } catch (ApiErrorException $e) {
            return redirect()->route('bookings.show', $booking)
                ->with('error', 'Error confirming payment: ' . $e->getMessage());
        }
    }

    /**
     * Handle Stripe webhook events
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sigHeader, $endpointSecret
            );
        } catch (\Exception $e) {
            return response('Webhook Error: ' . $e->getMessage(), 400);
        }

        // Handle specific event types
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $this->handleSuccessfulPayment($event->data->object);
                break;
            case 'payment_intent.payment_failed':
                $this->handleFailedPayment($event->data->object);
                break;
        }

        return response('Webhook received', 200);
    }

    /**
     * Handle successful payment webhook
     *
     * @param object $paymentIntent
     * @return void
     */
    protected function handleSuccessfulPayment($paymentIntent)
    {
        $bookingId = $paymentIntent->metadata->booking_id;
        $booking = Booking::find($bookingId);

        if ($booking) {
            $booking->update([
                'payment_status' => 'paid',
            ]);

            // Notify the tutor about the payment
            $booking->tutor->user->notify(new PaymentReceived($booking));
        }
    }

    /**
     * Handle failed payment webhook
     *
     * @param object $paymentIntent
     * @return void
     */
    protected function handleFailedPayment($paymentIntent)
    {
        $bookingId = $paymentIntent->metadata->booking_id;
        $booking = Booking::find($bookingId);

        if ($booking) {
            $booking->update([
                'payment_status' => 'failed',
            ]);
        }
    }

    /**
     * Create VNPay payment URL
     *
     * @param Request $request
     * @param Booking $booking
     * @return \Illuminate\Http\JsonResponse
     */
    public function createVnpayPayment(Request $request, Booking $booking)
    {
        // Check if the booking belongs to the authenticated user
        if ($booking->student_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if the booking is in a valid state for payment
        if ($booking->status !== 'accepted' || $booking->payment_status === 'paid') {
            return response()->json(['error' => 'Invalid booking status for payment'], 400);
        }

        try {
            $paymentUrl = $this->vnpayService->createPaymentUrl($booking, $request->ip());

            return response()->json([
                'payment_url' => $paymentUrl,
                'success' => true
            ]);
        } catch (\Exception $e) {
            Log::error('VNPay payment creation failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create payment'], 500);
        }
    }

    /**
     * Handle VNPay return URL
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function vnpayReturn(Request $request)
    {
        $vnpData = $request->all();
        $result = $this->vnpayService->handlePaymentResult($vnpData);

        if ($result['success']) {
            // Notify the tutor about the payment
            $booking = $result['booking'];
            $booking->tutor->user->notify(new PaymentReceived($booking));

            return redirect()->route('bookings.show', $booking)
                ->with('success', __('booking.payment_success', ['method' => 'VNPay']));
        } else {
            $booking = $result['booking'] ?? null;
            $redirectRoute = $booking ? route('bookings.show', $booking) : route('bookings.index');

            return redirect($redirectRoute)
                ->with('error', $result['message']);
        }
    }

    /**
     * Handle VNPay IPN (Instant Payment Notification)
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function vnpayIpn(Request $request)
    {
        $vnpData = $request->all();
        $result = $this->vnpayService->handlePaymentResult($vnpData);

        if ($result['success']) {
            return response('RspCode=00&Message=Confirm Success', 200);
        } else {
            return response('RspCode=99&Message=Confirm Fail', 400);
        }
    }

    /**
     * Process payment based on selected method
     *
     * @param Request $request
     * @param Booking $booking
     * @return \Illuminate\Http\JsonResponse
     */
    public function processPayment(Request $request, Booking $booking)
    {
        $request->validate([
            'payment_method' => 'required|in:stripe,vnpay',
        ]);

        // Check authorization
        if ($booking->student_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check booking status
        if ($booking->status !== 'accepted' || $booking->payment_status === 'paid') {
            return response()->json(['error' => 'Invalid booking status for payment'], 400);
        }

        $paymentMethod = $request->input('payment_method');

        try {
            if ($paymentMethod === 'vnpay') {
                $paymentUrl = $this->vnpayService->createPaymentUrl($booking, $request->ip());
                return response()->json([
                    'redirect_url' => $paymentUrl,
                    'payment_method' => 'vnpay'
                ]);
            } elseif ($paymentMethod === 'stripe') {
                // Existing Stripe logic
                $amount = $booking->price * 100;
                $paymentIntent = $this->stripe->paymentIntents->create([
                    'amount' => (int)$amount,
                    'currency' => 'usd',
                    'metadata' => [
                        'booking_id' => $booking->id,
                        'student_id' => $booking->student_id,
                        'tutor_id' => $booking->tutor_id,
                    ],
                ]);

                $booking->update([
                    'payment_intent_id' => $paymentIntent->id,
                    'payment_status' => 'pending',
                    'payment_method' => 'stripe',
                ]);

                return response()->json([
                    'clientSecret' => $paymentIntent->client_secret,
                    'payment_method' => 'stripe'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Payment processing failed: ' . $e->getMessage());
            return response()->json(['error' => 'Payment processing failed'], 500);
        }

        return response()->json(['error' => 'Invalid payment method'], 400);
    }

    /**
     * Get transaction history for a booking
     *
     * @param Booking $booking
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTransactionHistory(Booking $booking)
    {
        if ($booking->student_id !== Auth::id() && $booking->tutor->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $transactions = $booking->transactions()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['transactions' => $transactions]);
    }

    /**
     * View transaction history page for a booking
     *
     * @param Booking $booking
     * @return \Illuminate\View\View
     */
    public function viewTransactionHistory(Booking $booking)
    {
        if ($booking->student_id !== Auth::id() && $booking->tutor->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $transactions = $booking->transactions()
            ->orderBy('created_at', 'desc')
            ->get();

        return view('bookings.transactions', compact('booking', 'transactions'));
    }
}
