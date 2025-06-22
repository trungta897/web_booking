<?php

namespace App\Services;

use App\Contracts\Services\PaymentServiceInterface;
use App\Models\Booking;
use App\Models\Transaction;
use App\Services\VnpayService;
use App\Notifications\PaymentReceived;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;
use Exception;

class PaymentService extends BaseService implements PaymentServiceInterface
{
    protected StripeClient $stripe;
    protected VnpayService $vnpayService;

    public function __construct(VnpayService $vnpayService)
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
        $this->vnpayService = $vnpayService;
    }

    /**
     * Create Stripe payment intent
     */
    public function createStripePaymentIntent(Booking $booking): array
    {
        try {
            $amount = $booking->price * 100; // Convert to cents

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

            $this->logActivity('Stripe payment intent created', [
                'booking_id' => $booking->id,
                'payment_intent_id' => $paymentIntent->id,
                'amount' => $amount
            ]);

            return [
                'success' => true,
                'client_secret' => $paymentIntent->client_secret,
            ];
        } catch (ApiErrorException $e) {
            $this->logError('Stripe payment intent creation failed', $e, [
                'booking_id' => $booking->id
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Confirm Stripe payment
     */
    public function confirmStripePayment(string $paymentIntentId, Booking $booking): array
    {
        try {
            $paymentIntent = $this->stripe->paymentIntents->retrieve($booking->payment_intent_id);

            if ($paymentIntent->status === 'succeeded') {
                return $this->executeTransaction(function () use ($booking) {
                    $booking->update(['payment_status' => 'paid']);

                    // Create transaction record
                    Transaction::create([
                        'booking_id' => $booking->id,
                        'user_id' => $booking->student_id,
                        'amount' => $booking->price,
                        'currency' => 'USD',
                        'payment_method' => Transaction::PAYMENT_METHOD_STRIPE,
                        'type' => Transaction::TYPE_PAYMENT,
                        'status' => Transaction::STATUS_COMPLETED,
                        'transaction_id' => $booking->payment_intent_id,
                    ]);

                    // Notify tutor
                    $booking->tutor->user->notify(new PaymentReceived($booking));

                    $this->logActivity('Stripe payment confirmed', [
                        'booking_id' => $booking->id,
                        'amount' => $booking->price
                    ]);

                    return ['success' => true, 'message' => 'Payment completed successfully'];
                });
            }

            return ['success' => false, 'message' => 'Payment not completed'];
        } catch (Exception $e) {
            $this->logError('Stripe payment confirmation failed', $e, [
                'booking_id' => $booking->id
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Create VNPay payment URL
     */
    public function createVnpayPayment(Booking $booking, string $returnUrl): string
    {
        $paymentUrl = $this->vnpayService->createPaymentUrl($booking, $returnUrl);

        $this->logActivity('VNPay payment URL created', [
            'booking_id' => $booking->id,
            'vnpay_txn_ref' => $booking->vnpay_txn_ref
        ]);

        return $paymentUrl;
    }

    /**
     * Handle VNPay return
     */
    public function handleVnpayReturn(array $params): array
    {
        try {
            $result = $this->vnpayService->handlePaymentResult($params);

            if ($result['success']) {
                $booking = $result['booking'];

                $this->logActivity('VNPay payment confirmed', [
                    'booking_id' => $booking->id,
                    'txn_ref' => $params['vnp_TxnRef'] ?? null
                ]);

                return $result;
            }

            return $result;
        } catch (Exception $e) {
            $this->logError('VNPay return handling failed', $e, [
                'params' => $params
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get payment methods for booking
     */
    public function getAvailablePaymentMethods(Booking $booking): array
    {
        $methods = [];

        // Check if Stripe is configured
        if (config('services.stripe.secret')) {
            $methods[] = [
                'method' => 'stripe',
                'name' => 'Credit/Debit Card',
                'description' => 'Pay with your credit or debit card',
                'icon' => 'credit-card'
            ];
        }

        // Check if VNPay is configured
        if (config('services.vnpay.tmn_code')) {
            $methods[] = [
                'method' => 'vnpay',
                'name' => 'VNPay',
                'description' => 'Pay with Vietnamese banks',
                'icon' => 'bank'
            ];
        }

        return $methods;
    }

    /**
     * Get transaction history for booking
     */
    public function getTransactionHistory(Booking $booking): \Illuminate\Database\Eloquent\Collection
    {
        return Transaction::where('booking_id', $booking->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get payment history for booking
     */
    public function getPaymentHistory(Booking $booking): array
    {
        $transactions = $this->getTransactionHistory($booking);

        return [
            'transactions' => $transactions,
            'total_paid' => $transactions->where('type', Transaction::TYPE_PAYMENT)
                ->where('status', Transaction::STATUS_COMPLETED)
                ->sum('amount'),
            'total_refunded' => $transactions->where('type', Transaction::TYPE_REFUND)
                ->where('status', Transaction::STATUS_REFUNDED)
                ->sum('amount'),
            'payment_status' => $booking->payment_status,
            'formatted' => [
                'total_paid' => $this->formatCurrency($transactions->where('type', Transaction::TYPE_PAYMENT)->sum('amount')),
                'total_refunded' => $this->formatCurrency(abs($transactions->where('type', Transaction::TYPE_REFUND)->sum('amount')))
            ]
        ];
    }

    /**
     * Refund payment
     */
    public function refundPayment(Booking $booking, float $amount, string $reason): array
    {
        try {
            if ($booking->payment_status !== 'paid') {
                throw new Exception('Booking is not paid, cannot refund');
            }

            return $this->executeTransaction(function () use ($booking, $reason) {
                // Process refund based on payment method
                $transaction = Transaction::where('booking_id', $booking->id)
                    ->where('status', Transaction::STATUS_COMPLETED)
                    ->first();

                if (!$transaction) {
                    throw new Exception('No completed transaction found');
                }

                $refundResult = $this->processRefund($transaction, $reason);

                if ($refundResult['success']) {
                    $booking->update(['payment_status' => 'refunded']);

                    // Create refund transaction record
                    Transaction::create([
                        'booking_id' => $booking->id,
                        'user_id' => $booking->student_id,
                        'amount' => -$booking->price, // Negative amount for refund
                        'currency' => $transaction->currency,
                        'payment_method' => $transaction->payment_method,
                        'type' => Transaction::TYPE_REFUND,
                        'status' => Transaction::STATUS_REFUNDED,
                        'transaction_id' => $refundResult['refund_id'] ?? null,
                    ]);

                    $this->logActivity('Payment refunded', [
                        'booking_id' => $booking->id,
                        'amount' => $booking->price,
                        'reason' => $reason
                    ]);
                }

                return $refundResult;
            });
        } catch (Exception $e) {
            $this->logError('Refund processing failed', $e, [
                'booking_id' => $booking->id
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process refund based on payment method
     */
    protected function processRefund(Transaction $transaction, string $reason = null): array
    {
        switch ($transaction->payment_method) {
            case 'stripe':
                return $this->processStripeRefund($transaction, $reason);
            case 'vnpay':
                return $this->processVnpayRefund($transaction, $reason);
            default:
                throw new Exception('Unsupported payment method for refund');
        }
    }

    /**
     * Process Stripe refund
     */
    protected function processStripeRefund(Transaction $transaction, string $reason = null): array
    {
        try {
            $refund = $this->stripe->refunds->create([
                'payment_intent' => $transaction->transaction_id,
                'amount' => $transaction->amount * 100, // Convert to cents
                'reason' => 'requested_by_customer',
                'metadata' => [
                    'booking_id' => $transaction->booking_id,
                    'reason' => $reason
                ]
            ]);

            return [
                'success' => true,
                'refund_id' => $refund->id,
                'message' => 'Refund processed successfully'
            ];
        } catch (ApiErrorException $e) {
            throw new Exception('Stripe refund failed: ' . $e->getMessage());
        }
    }

    /**
     * Process VNPay refund (placeholder - implement based on VNPay API)
     */
    protected function processVnpayRefund(Transaction $transaction, string $reason = null): array
    {
        // VNPay refund implementation would go here
        // This is a placeholder as VNPay refund requires specific API integration

        return [
            'success' => false,
            'message' => 'VNPay refunds must be processed manually through VNPay portal'
        ];
    }
}
