<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\User;
use App\Notifications\PaymentReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class PaymentController extends Controller
{
    protected $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
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
}
