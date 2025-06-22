<?php

namespace App\Contracts\Services;

use App\Models\Booking;

interface PaymentServiceInterface extends ServiceInterface
{
    /**
     * Create Stripe payment intent
     */
    public function createStripePaymentIntent(Booking $booking): array;

    /**
     * Confirm Stripe payment
     */
    public function confirmStripePayment(string $paymentIntentId, Booking $booking): array;

    /**
     * Create VNPay payment
     */
    public function createVnpayPayment(Booking $booking, string $returnUrl): string;

    /**
     * Handle VNPay return
     */
    public function handleVnpayReturn(array $params): array;

    /**
     * Refund payment
     */
    public function refundPayment(Booking $booking, float $amount, string $reason): array;

    /**
     * Get payment history for booking
     */
    public function getPaymentHistory(Booking $booking): array;
}
