<?php

namespace App\Services;

use App\Contracts\Services\PaymentServiceInterface;
use App\Models\Booking;
use App\Models\Transaction;
use App\Notifications\PaymentReceived;
use Exception;
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
     * Create Stripe payment intent.
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
     * Confirm Stripe payment with payment intent.
     */
    public function confirmStripePayment(Booking $booking, array $paymentData = []): bool
    {
        return $this->executeTransaction(function () use ($booking) {
            if ($booking->isPaid()) {
                return true;
            }

            // 🎯 THANH TOÁN THÀNH CÔNG - CẬP NHẬT BOOLEAN LOGIC
            $booking->update([
                'is_confirmed' => true, // ✅ Đã chấp nhận VÀ đã thanh toán = sẵn sàng học
                'payment_method' => 'stripe',
                'payment_at' => now(),
            ]);

            // Auto-apply commission calculation
            try {
                $payoutService = app(PayoutService::class);
                $payoutService->applyCommissionToBooking($booking);
            } catch (Exception $e) {
                $this->logActivity('Commission calculation failed for Stripe payment', [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Create transaction record - This should be handled by a dedicated Stripe transaction handler if needed
            // For now, we assume the webhook is the source of truth and creates the transaction.
            // $this->createTransactionRecord($booking, 'stripe', 'paid');

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
     * Handle Stripe webhook.
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
                $booking = Booking::where('payment_intent_id', $paymentIntent->id)->first();

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
     * Handle VNPay IPN.
     */
    public function handleVnpayIpn(array $ipnData): void
    {
        $result = $this->vnpayService->handlePaymentResult($ipnData);

        if ($result['success']) {
            $this->sendPaymentNotifications($result['booking']);
            $this->logActivity('VNPay IPN processed successfully', [
                'booking_id' => $result['booking']->id,
                'vnpay_transaction_no' => $ipnData['vnp_TransactionNo'] ?? null,
            ]);
        } else {
            $this->logActivity('VNPay IPN processing failed', [
                'vnp_TxnRef' => $ipnData['vnp_TxnRef'] ?? 'N/A',
                'error' => $result['message'],
            ]);

            // Throw an exception to signal failure to the VNPay server if needed.
            // This ensures VNPay might retry sending the IPN.
            throw new Exception($result['message']);
        }
    }

    /**
     * Create transaction record.
     */
    /*
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
    */

    /**
     * Send payment notifications.
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
     * Create VNPay payment.
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
     * Handle VNPay return.
     */
    public function handleVnpayReturn(array $params): array
    {
        return $this->vnpayService->handlePaymentResult($params);
    }

    /**
     * Get payment methods for booking.
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
     * Create initial transaction for booking.
     */
    public function createInitialTransaction(Booking $booking, string $paymentMethod): Transaction
    {
        return Transaction::create([
            'booking_id' => $booking->id,
            'type' => 'payment',
            'amount' => $booking->price,
            'currency' => 'VND',
            'payment_method' => $paymentMethod,
            'status' => Transaction::STATUS_PENDING,
            'payment_at' => null, // Will be set when payment is completed
        ]);
    }

    /**
     * Complete payment process.
     */
    public function completePayment(Booking $booking, array $transactionData): bool
    {
        return $this->executeTransaction(function () use ($booking, $transactionData) {
            // Update booking payment status using boolean field
            if ($booking && !$booking->isPaid()) {
                $booking->update([
                    'payment_at' => now(), // Use payment_at instead of payment_status
                ]);
            }

            // Create transaction record
            Transaction::create([
                'booking_id' => $booking->id,
                'user_id' => $booking->student_id,
                'amount' => $booking->price,
                'currency' => 'VND',
                'payment_method' => 'stripe', // Assuming stripe for now
                'type' => Transaction::TYPE_PAYMENT,
                'status' => Transaction::STATUS_COMPLETED,
                'transaction_id' => $transactionData['id'] ?? null,
                'processed_at' => now(),
            ]);

            $this->logActivity('Payment completed', [
                'booking_id' => $booking->id,
                'amount' => $booking->price,
            ]);

            return true;
        });
    }

    /**
     * Get payment details for booking.
     */
    public function getPaymentDetails(Booking $booking): array
    {
        $transactions = $booking->transactions()
            ->whereIn('status', [Transaction::STATUS_PENDING, Transaction::STATUS_COMPLETED])
            ->orderBy('created_at', 'desc')
            ->get();

        $totalPaid = $booking->transactions()
            ->where('status', Transaction::STATUS_COMPLETED)
            ->sum('amount');

        $totalRefunded = $booking->transactions()
            ->where('status', Transaction::STATUS_REFUNDED)
            ->sum('amount');

        return [
            'transactions' => $transactions,
            'total_paid' => $totalPaid,
            'total_refunded' => $totalRefunded,
            'payment_status' => $booking->getPaymentStatusAttribute(), // Use accessor instead of direct column
            'formatted' => [
                'total_paid' => formatCurrency($totalPaid),
                'total_refunded' => formatCurrency($totalRefunded),
            ],
        ];
    }

    /**
     * Get refund details for booking.
     */
    public function getRefundDetails(Booking $booking): array
    {
        $transactions = $booking->transactions()
            ->where('status', Transaction::STATUS_COMPLETED)
            ->with(['booking.subject'])
            ->orderBy('created_at', 'desc')
            ->get();

        $totalPaid = $booking->transactions()
            ->where('status', Transaction::STATUS_COMPLETED)
            ->sum('amount');

        $totalRefunded = $booking->transactions()
            ->where('status', Transaction::STATUS_REFUNDED)
            ->sum('amount');

        return [
            'transactions' => $transactions,
            'total_paid' => $totalPaid,
            'total_refunded' => $totalRefunded,
            'payment_status' => $booking->getPaymentStatusAttribute(), // Use accessor instead of direct column
            'formatted' => [
                'total_paid' => formatCurrency($totalPaid),
                'total_refunded' => formatCurrency($totalRefunded),
            ],
        ];
    }

    /**
     * Refund payment.
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

            // Validate refund amount
            $totalPaid = $this->getTotalPaidAmount($booking);
            $totalRefunded = $this->getTotalRefundedAmount($booking);
            $availableForRefund = $totalPaid - abs($totalRefunded);

            if ($amount > $availableForRefund) {
                throw new Exception("Refund amount ({$amount}) exceeds available amount ({$availableForRefund})");
            }

            return $this->executeTransaction(function () use ($booking, $amount, $reason) {
                // Process refund based on payment method
                $transaction = Transaction::where('booking_id', $booking->id)
                    ->where('status', Transaction::STATUS_COMPLETED)
                    ->where('type', Transaction::TYPE_PAYMENT)
                    ->first();

                if (!$transaction) {
                    throw new Exception('No completed payment transaction found');
                }

                $refundResult = $this->processRefund($transaction, $reason, $amount);

                if ($refundResult['success']) {
                    // Determine refund type
                    $isPartialRefund = $amount < $booking->price;
                    $refundType = $isPartialRefund ? Transaction::TYPE_PARTIAL_REFUND : Transaction::TYPE_REFUND;

                    // Update booking payment status
                    $newPaymentStatus = $isPartialRefund ? 'partial_refunded' : 'refunded';
                    $booking->update(['payment_status' => $newPaymentStatus]);

                    // Create refund transaction record
                    Transaction::create([
                        'booking_id' => $booking->id,
                        'user_id' => $booking->student_id,
                        'amount' => -$amount, // Negative amount for refund
                        'currency' => $transaction->currency ?? 'VND',
                        'payment_method' => $transaction->payment_method,
                        'type' => $refundType,
                        'status' => Transaction::STATUS_COMPLETED,
                        'transaction_id' => $refundResult['refund_id'] ?? null,
                        'processed_at' => now(),
                        'metadata' => [
                            'refund_reason' => $reason,
                            'original_transaction_id' => $transaction->id,
                            'refund_type' => $isPartialRefund ? 'partial' : 'full',
                        ],
                    ]);

                    $this->logActivity('Payment refunded', [
                        'booking_id' => $booking->id,
                        'amount' => $amount,
                        'type' => $refundType,
                        'reason' => $reason,
                    ]);
                }

                return $refundResult;
            });
        } catch (Exception $e) {
            $this->logError('Refund processing failed', $e, [
                'booking_id' => $booking->id,
                'amount' => $amount,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process refund based on payment method.
     *
     * @param Transaction $transaction
     * @param string|null $reason
     * @param float|null $amount
     * @return array{success: bool, refund_id?: string, message?: string}
     */
    protected function processRefund(Transaction $transaction, ?string $reason = null, ?float $amount = null): array
    {
        $refundAmount = $amount ?? $transaction->amount;

        switch ($transaction->payment_method) {
            case 'stripe':
                return $this->processStripeRefund($transaction, $reason, $refundAmount);
            case 'vnpay':
                return $this->processVnpayRefund($transaction, $reason, $refundAmount);
            default:
                throw new Exception('Unsupported payment method for refund');
        }
    }

    /**
     * Process Stripe refund.
     *
     * @param Transaction $transaction
     * @param string|null $reason
     * @param float $amount
     * @return array{success: bool, refund_id?: string, message?: string}
     */
    protected function processStripeRefund(Transaction $transaction, ?string $reason = null, ?float $amount = null): array
    {
        if (!$this->stripe) {
            throw new Exception('Stripe not configured');
        }

        $refundAmount = $amount ?? $transaction->amount;

        try {
            $refund = $this->stripe->refunds->create([
                'payment_intent' => $transaction->transaction_id,
                'amount' => $refundAmount * 100, // Convert to cents
                'reason' => 'requested_by_customer',
                'metadata' => [
                    'booking_id' => $transaction->booking_id,
                    'reason' => $reason,
                    'refund_amount' => $refundAmount,
                ],
            ]);

            return [
                'success' => true,
                'refund_id' => $refund->id,
                'message' => 'Refund processed successfully',
            ];
        } catch (ApiErrorException $e) {
            throw new Exception('Stripe refund failed: ' . $e->getMessage());
        }
    }

    /**
     * Process VNPay refund (throws exception as VNPay requires manual processing).
     *
     * @param Transaction $transaction
     * @param string|null $reason
     * @param float|null $amount
     * @return array{success: bool, message: string}
     * @throws Exception
     */
    protected function processVnpayRefund(Transaction $transaction, ?string $reason = null, ?float $amount = null): array
    {
        // VNPay refunds require manual processing through VNPay portal
        // Throw an exception to indicate this cannot be processed automatically

        $message = app()->getLocale() === 'vi'
            ? 'Hoàn tiền VNPay cần được xử lý thủ công qua cổng thanh toán VNPay. Vui lòng liên hệ admin để được hỗ trợ.'
            : 'VNPay refunds must be processed manually through VNPay portal. Please contact admin for assistance.';

        throw new Exception($message);
    }

    /**
     * Get total paid amount for booking.
     */
    private function getTotalPaidAmount(Booking $booking): float
    {
        return Transaction::where('booking_id', $booking->id)
            ->where('type', Transaction::TYPE_PAYMENT)
            ->where('status', Transaction::STATUS_COMPLETED)
            ->sum('amount');
    }

    /**
     * Get total refunded amount for booking.
     */
    private function getTotalRefundedAmount(Booking $booking): float
    {
        return Transaction::where('booking_id', $booking->id)
            ->whereIn('type', [Transaction::TYPE_REFUND, Transaction::TYPE_PARTIAL_REFUND])
            ->where('status', Transaction::STATUS_COMPLETED)
            ->sum('amount'); // This will be negative, so we need abs() when displaying
    }

    /**
     * Get booking transactions.
     */
    public function getBookingTransactions(Booking $booking): array
    {
        $transactions = $booking->transactions()
            ->orderBy('created_at', 'desc')
            ->get();

        $totalPaid = $booking->transactions()
            ->where('status', Transaction::STATUS_COMPLETED)
            ->where('type', Transaction::TYPE_PAYMENT)
            ->sum('amount');

        $totalRefunded = abs($booking->transactions()
            ->whereIn('type', [Transaction::TYPE_REFUND, Transaction::TYPE_PARTIAL_REFUND])
            ->where('status', Transaction::STATUS_COMPLETED)
            ->sum('amount'));

        return [
            'transactions' => $transactions,
            'total_paid' => $totalPaid,
            'total_refunded' => $totalRefunded,
            'net_amount' => $totalPaid - $totalRefunded,
            'payment_status' => $booking->getPaymentStatusAttribute(),
            'formatted' => [
                'total_paid' => formatCurrency($totalPaid),
                'total_refunded' => formatCurrency($totalRefunded),
                'net_amount' => formatCurrency($totalPaid - $totalRefunded),
            ],
        ];
    }

    /**
     * Get payment history for booking.
     */
    public function getPaymentHistory(Booking $booking): array
    {
        return $this->getBookingTransactions($booking);
    }
}
