<?php

namespace App\Contracts\Services;

use App\Models\Booking;
use App\Services\PaymentResult;

interface PaymentServiceInterface extends ServiceInterface
{
    /**
     * Create Stripe payment intent.
     */
    public function createStripePaymentIntent(Booking $booking): PaymentResult;

    /**
     * Confirm Stripe payment.
     */
    public function confirmStripePayment(Booking $booking, array $paymentData = []): bool;

    /**
     * Create VNPay payment.
     */
    public function createVnpayPayment(Booking $booking, string $returnUrl): PaymentResult;

    /**
     * Handle VNPay return.
     */
    public function handleVnpayReturn(array $params): array;

    /**
     * Refund payment.
     */
    public function refundPayment(Booking $booking, float $amount, string $reason): array;

    /**
     * Get payment history for booking.
     */
    public function getPaymentHistory(Booking $booking): array;

    /**
     * Handle Stripe webhook.
     */
    public function handleStripeWebhook(\Illuminate\Http\Request $request): void;

    /**
     * Handle VNPay IPN.
     */
    public function handleVnpayIpn(array $ipnData): void;

    /**
     * Get booking transactions with summary.
     */
    public function getBookingTransactions(Booking $booking): array;
}
