<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use App\Notifications\PaymentReceived;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PaymentController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createPaymentIntent(Booking $booking)
    {
        $this->authorize('pay', $booking);

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $booking->price * 100, // Convert to cents
                'currency' => 'usd',
                'metadata' => [
                    'booking_id' => $booking->id,
                    'student_id' => $booking->student_id,
                    'tutor_id' => $booking->tutor_id,
                ],
            ]);

            return response()->json([
                'clientSecret' => $paymentIntent->client_secret,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        if ($event->type === 'payment_intent.succeeded') {
            $paymentIntent = $event->data->object;
            $booking = Booking::where('payment_intent_id', $paymentIntent->id)->first();

            if ($booking) {
                $booking->update([
                    'payment_status' => 'paid',
                ]);

                // Send notification to tutor
                $booking->tutor->user->notify(new PaymentReceived($booking));
            }
        }

        return response()->json(['status' => 'success']);
    }
}
