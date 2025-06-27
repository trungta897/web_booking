<?php

namespace App\Services;

use App\Contracts\Services\PaymentServiceInterface;
use App\Models\Booking;
use App\Models\Transaction;
use App\Notifications\PaymentReceived;
use App\Services\PaymentResult;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class PaymentService extends BaseService implements PaymentServiceInterface
{
    protected StripeClient $stripe;

    protected VnpayService $vnpayService;

    public function __construct(VnpayService $vnpayService)
    {
        $stripeSecret = config('services.stripe.secret');
        if ($stripeSecret) {
            $this->stripe = new StripeClient($stripeSecret);
        }
        $this->vnpayService = $vnpayService;
    }

    /**
     * Create Stripe payment intent
     */
    public function createStripePaymentIntent(Booking $booking): PaymentResult
    {
        try {
            if (!$this->stripe) {
                return new PaymentResult(false, error: 'Stripe not configured');
            }

            $amount = $booking->price * 100; // Convert to cents

            $paymentIntent = $this->stripe->paymentIntents->create([
                'amount' => (int) $amount,
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
                'amount' => $amount,
            ]);

            return new PaymentResult(true, $paymentIntent->client_secret);
        } catch (ApiErrorException $e) {
            $this->logError('Stripe payment intent creation failed', $e, [
                'booking_id' => $booking->id,
            ]);

            return new PaymentResult(false, error: $e->getMessage());
        }
    }

    /**
     * Confirm Stripe payment with payment intent
     */
    public function confirmStripePayment(Booking $booking, array $paymentData = []): bool
    {
        return $this->executeTransaction(function () use ($booking) {
            if ($booking->payment_status === 'paid') {
                return true;
            }

            // Mark booking as paid
            $booking->update([
                'payment_status' => 'paid',
                'payment_method' => 'stripe',
                'payment_at' => now(),
            ]);

            // Create transaction record
            $this->createTransactionRecord($booking, 'stripe', 'paid');

            // Send notifications
            $this->sendPaymentNotifications($booking);

            $this->logActivity('Stripe payment confirmed', [
                'booking_id' => $booking->id,
                'amount' => $booking->price,
            ]);

            return true;
        });
    }

    /**
     * Handle Stripe webhook
     */
    public function handleStripeWebhook(Request $request): void
    {
        if (!$this->stripe) {
            throw new Exception('Stripe not configured');
        }

        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $signature,
                config('services.stripe.webhook_secret')
            );

            if ($event->type === 'payment_intent.succeeded') {
                $paymentIntent = $event->data->object;

                // Find booking by payment intent ID
                $booking = Booking::where('stripe_payment_intent_id', $paymentIntent->id)->first();

                if ($booking && $booking->payment_status !== 'paid') {
                    $this->confirmStripePayment($booking);
                }
            }

            $this->logActivity('Stripe webhook handled', [
                'event_type' => $event->type,
            ]);

        } catch (Exception $e) {
            $this->logActivity('Stripe webhook error', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle VNPay IPN
     */
    public function handleVnpayIpn(array $ipnData): void
    {
        if (! $this->vnpayService->validateIPN($ipnData)) {
            throw new Exception('Invalid VNPay IPN');
        }

        $bookingId = $ipnData['vnp_TxnRef'] ?? null;
        $booking = Booking::find($bookingId);

        if (! $booking) {
            throw new Exception('Booking not found');
        }

        if ($ipnData['vnp_ResponseCode'] === '00') {
            // Payment successful
            if ($booking->payment_status !== 'paid') {
                $booking->update([
                    'payment_status' => 'paid',
                    'payment_method' => 'vnpay',
                    'payment_at' => now(),
                    'vnpay_transaction_id' => $ipnData['vnp_TransactionNo'] ?? null,
                ]);

                $this->createTransactionRecord($booking, 'vnpay', 'paid');
                $this->sendPaymentNotifications($booking);
            }
        } else {
            // Payment failed
            $booking->update(['payment_status' => 'failed']);
            $this->createTransactionRecord($booking, 'vnpay', 'failed');
        }

        $this->logActivity('VNPay IPN processed', [
            'booking_id' => $booking->id,
            'response_code' => $ipnData['vnp_ResponseCode'],
        ]);
    }

    /**
     * Get booking transactions
     */
    public function getBookingTransactions(Booking $booking): Collection
    {
        return $booking->transactions()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Create transaction record
     */
    protected function createTransactionRecord(Booking $booking, string $method, string $status): void
    {
        Transaction::create([
            'booking_id' => $booking->id,
            'user_id' => $booking->student_id,
            'amount' => $booking->price,
            'currency' => 'VND',
            'payment_method' => $method,
            'type' => Transaction::TYPE_PAYMENT,
            'status' => $status === 'paid' ? Transaction::STATUS_COMPLETED : Transaction::STATUS_FAILED,
            'transaction_id' => uniqid($method.'_'),
            'processed_at' => $status === 'paid' ? now() : null,
        ]);
    }

    /**
     * Send payment notifications
     */
    protected function sendPaymentNotifications(Booking $booking): void
    {
        try {
            // Notify student
            $booking->student->notify(new PaymentReceived($booking));

            // Notify tutor
            $booking->tutor->user->notify(new PaymentReceived($booking));

        } catch (Exception $e) {
            // Log error but don't throw - payment was successful
            $this->logActivity('Payment notification error', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Create VNPay payment
     */
    public function createVnpayPayment(Booking $booking, string $ipAddr = null): PaymentResult
    {
        try {
            $paymentUrl = $this->vnpayService->createPaymentUrl($booking, $ipAddr);

            $this->logActivity('VNPay payment URL created', [
                'booking_id' => $booking->id,
                'vnpay_txn_ref' => $booking->vnpay_txn_ref,
            ]);

            return new PaymentResult(true, paymentUrl: $paymentUrl);
        } catch (Exception $e) {
            return new PaymentResult(false, error: $e->getMessage());
        }
    }

    /**
     * Handle VNPay return
     *
     * @param array $params
     * @return array{success: bool, booking?: Booking, message?: string, error?: string}
     */
    public function handleVnpayReturn(array $params): array
    {
        try {
            $result = $this->vnpayService->handlePaymentResult($params);

            if ($result['success']) {
                $booking = $result['booking'];

                $this->logActivity('VNPay payment confirmed', [
                    'booking_id' => $booking->id,
                    'txn_ref' => $params['vnp_TxnRef'] ?? null,
                ]);

                return $result;
            }

            return $result;
        } catch (Exception $e) {
            $this->logError('VNPay return handling failed', $e, [
                'params' => $params,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get payment methods for booking
     *
     * @param Booking $booking
     * @return array<int, array{method: string, name: string, description: string, icon: string}>
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
                'icon' => 'credit-card',
            ];
        }

        // Check if VNPay is configured
        if (config('services.vnpay.tmn_code')) {
            $methods[] = [
                'method' => 'vnpay',
                'name' => 'VNPay',
                'description' => 'Pay with Vietnamese banks',
                'icon' => 'bank',
            ];
        }

        return $methods;
    }

    /**
     * Get transaction history for booking
     */
    public function getTransactionHistory(Booking $booking): Collection
    {
        return Transaction::where('booking_id', $booking->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get payment history for booking
     *
     * @param Booking $booking
     * @return array{transactions: Collection, total_paid: float, total_refunded: float, payment_status: string, formatted: array{total_paid: string, total_refunded: string}}
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
                'total_refunded' => $this->formatCurrency(abs($transactions->where('type', Transaction::TYPE_REFUND)->sum('amount'))),
            ],
        ];
    }

    /**
     * Refund payment
     *
     * @param Booking $booking
     * @param float $amount
     * @param string $reason
     * @return array{success: bool, error?: string, refund_id?: string, message?: string}
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

                if (! $transaction) {
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
                        'currency' => $transaction->currency ?? 'VND',
                        'payment_method' => $transaction->payment_method,
                        'type' => Transaction::TYPE_REFUND,
                        'status' => Transaction::STATUS_REFUNDED,
                        'transaction_id' => $refundResult['refund_id'] ?? null,
                    ]);

                    $this->logActivity('Payment refunded', [
                        'booking_id' => $booking->id,
                        'amount' => $booking->price,
                        'reason' => $reason,
                    ]);
                }

                return $refundResult;
            });
        } catch (Exception $e) {
            $this->logError('Refund processing failed', $e, [
                'booking_id' => $booking->id,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process refund based on payment method
     *
     * @param Transaction $transaction
     * @param string|null $reason
     * @return array{success: bool, refund_id?: string, message?: string}
     */
    protected function processRefund(Transaction $transaction, ?string $reason = null): array
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
     *
     * @param Transaction $transaction
     * @param string|null $reason
     * @return array{success: bool, refund_id?: string, message?: string}
     */
    protected function processStripeRefund(Transaction $transaction, ?string $reason = null): array
    {
        if (!$this->stripe) {
            throw new Exception('Stripe not configured');
        }

        try {
            $refund = $this->stripe->refunds->create([
                'payment_intent' => $transaction->transaction_id,
                'amount' => $transaction->amount * 100, // Convert to cents
                'reason' => 'requested_by_customer',
                'metadata' => [
                    'booking_id' => $transaction->booking_id,
                    'reason' => $reason,
                ],
            ]);

            return [
                'success' => true,
                'refund_id' => $refund->id,
                'message' => 'Refund processed successfully',
            ];
        } catch (ApiErrorException $e) {
            throw new Exception('Stripe refund failed: '.$e->getMessage());
        }
    }

    /**
     * Process VNPay refund (placeholder - implement based on VNPay API)
     *
     * @param Transaction $transaction
     * @param string|null $reason
     * @return array{success: bool, message: string}
     */
    protected function processVnpayRefund(Transaction $transaction, ?string $reason = null): array
    {
        // VNPay refund implementation would go here
        // This is a placeholder as VNPay refund requires specific API integration

        return [
            'success' => false,
            'message' => 'VNPay refunds must be processed manually through VNPay portal',
        ];
    }
}
