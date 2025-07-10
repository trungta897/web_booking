<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Transaction;
use App\Services\LogService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class VnpayService
{
    protected string $vnpTmnCode;

    protected string $vnpHashSecret;

    protected string $vnpUrl;

    protected string $vnpReturnUrl;

    public function __construct()
    {
        $this->vnpTmnCode = config('services.vnpay.tmn_code');
        $this->vnpHashSecret = config('services.vnpay.hash_secret');
        $this->vnpUrl = config('services.vnpay.url');
        $this->vnpReturnUrl = config('services.vnpay.return_url');

        // Validate essential VNPay configurations
        $this->validateConfiguration();
    }

    /**
     * Validate VNPay configuration
     */
    private function validateConfiguration(): void
    {
        $errors = [];

        if (empty($this->vnpTmnCode)) {
            $errors[] = 'VNPAY_TMN_CODE is not configured';
        }

        if (empty($this->vnpHashSecret)) {
            $errors[] = 'VNPAY_HASH_SECRET is not configured';
        }

        if (empty($this->vnpUrl)) {
            $errors[] = 'VNPAY_URL is not configured';
        }

        if (empty($this->vnpReturnUrl)) {
            $errors[] = 'VNPAY_RETURN_URL is not configured';
        } elseif (str_contains($this->vnpReturnUrl, 'localhost')) {
            if (app()->environment('production')) {
                // Block localhost in production
                $errors[] = 'VNPAY_RETURN_URL cannot use localhost in production - VNPay needs a public URL';
            } else {
                // Allow localhost in development but log warning
                Log::warning('VNPay using localhost URL in development environment', [
                    'return_url' => $this->vnpReturnUrl,
                    'environment' => app()->environment(),
                    'note' => 'Real VNPay callbacks will not work with localhost URLs'
                ]);
            }
        }

        if (!empty($errors)) {
            Log::error('VNPay configuration errors', ['errors' => $errors]);
            throw new \Exception('VNPay configuration error: ' . implode(', ', $errors));
        }
    }

    /**
     * Tạo URL thanh toán VNPay
     */
    public function createPaymentUrl(Booking $booking, ?string $ipAddr = null): string
    {
        // Kiểm tra và cleanup pending transactions cũ trước
        $this->cleanupOldPendingTransactions($booking);

        // Kiểm tra xem có pending transaction còn active không (tạo trong 2 phút qua)
        $recentPendingTransaction = Transaction::where('booking_id', $booking->id)
            ->where('type', Transaction::TYPE_PAYMENT)
            ->where('status', Transaction::STATUS_PENDING)
            ->where('created_at', '>', now()->subMinutes(2))
            ->first();

        if ($recentPendingTransaction) {
            // Sử dụng lại transaction hiện tại
            $txnRef = $recentPendingTransaction->transaction_id;

            LogService::vnpay('Reusing existing pending transaction', [
                'booking_id' => $booking->id,
                'transaction_id' => $recentPendingTransaction->id,
                'txn_ref' => $txnRef,
            ]);
        } else {
            // Tạo transaction reference mới
            $txnRef = 'BOOKING_'.$booking->id.'_'.time();

            // Tạo transaction record mới
            Transaction::create([
                'booking_id' => $booking->id,
                'user_id' => $booking->student_id,
                'transaction_id' => $txnRef,
                'payment_method' => Transaction::PAYMENT_METHOD_VNPAY,
                'type' => Transaction::TYPE_PAYMENT,
                'amount' => $booking->price,
                'currency' => $booking->currency ?? 'VND',
                'status' => Transaction::STATUS_PENDING,
            ]);

            LogService::vnpay('Created new pending transaction', [
                'booking_id' => $booking->id,
                'txn_ref' => $txnRef,
            ]);
        }

        // Cập nhật booking với txn_ref
        $booking->update([
            'vnpay_txn_ref' => $txnRef,
            'payment_method' => 'vnpay',
        ]);

        // Calculate VND amount for VNPay
        $vndAmount = $this->calculateVndAmount($booking);

        // Convert to VNPay format (VND * 100 for "xu" unit)
        $vnpayAmount = (int) ($vndAmount * 100);

        // Log detailed amount calculation for debugging
        LogService::vnpay('Payment URL creation', [
            'booking_id' => $booking->id,
            'booking_price' => $booking->price,
            'booking_currency' => $booking->currency ?? 'VND',
            'calculated_vnd_amount' => $vndAmount,
            'vnpay_amount_xu' => $vnpayAmount,
            'vnpay_amount_formatted' => number_format($vnpayAmount, 0, '.', ','),
            'is_amount_valid' => ($vnpayAmount >= 5000 && $vnpayAmount < 1000000000),
            'amount_in_millions' => round($vnpayAmount / 1000000, 2),
        ]);

        // Validate amount range for VNPay
        if ($vnpayAmount < 5000) {
            LogService::vnpay('VNPay amount too small', [
                'booking_id' => $booking->id,
                'vnpay_amount' => $vnpayAmount,
                'minimum_required' => 5000,
            ], 'error');
            throw new \Exception('Số tiền thanh toán quá nhỏ (tối thiểu 5,000 VND)');
        }

        if ($vnpayAmount >= 1000000000) {
            LogService::vnpay('VNPay amount too large', [
                'booking_id' => $booking->id,
                'vnpay_amount' => $vnpayAmount,
                'maximum_allowed' => 999999999,
                'amount_in_billions' => round($vnpayAmount / 1000000000, 2),
            ], 'error');
            throw new \Exception('Số tiền thanh toán quá lớn (tối đa dưới 1 tỷ VND)');
        }

        // Tham số VNPay
        $vnpData = [
            'vnp_Version' => '2.1.0',
            'vnp_Command' => 'pay',
            'vnp_TmnCode' => $this->vnpTmnCode,
            'vnp_Amount' => $vnpayAmount, // Amount in "xu" (VND * 100)
            'vnp_CurrCode' => 'VND',
            'vnp_TxnRef' => $txnRef,
            'vnp_OrderInfo' => 'Thanh toan hoc phi - '.$booking->subject->name.' - '.$booking->tutor->user->name,
            'vnp_OrderType' => 'other',
            'vnp_Locale' => app()->getLocale() === 'vi' ? 'vn' : 'en',
            'vnp_ReturnUrl' => $this->vnpReturnUrl,
            'vnp_IpAddr' => $ipAddr ?? request()->ip(),
            'vnp_CreateDate' => Carbon::now()->format('YmdHis'),
            'vnp_ExpireDate' => Carbon::now()->addMinutes(30)->format('YmdHis'),
        ];

        // Sắp xếp và tạo query string
        ksort($vnpData);
        $query = '';
        $i = 0;
        $hashdata = '';

        foreach ($vnpData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&'.urlencode($key).'='.urlencode($value);
            } else {
                $hashdata .= urlencode($key).'='.urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key).'='.urlencode($value).'&';
        }

        $vnpUrl = $this->vnpUrl.'?'.$query;

        if (isset($this->vnpHashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $this->vnpHashSecret);
            $vnpUrl .= 'vnp_SecureHash='.$vnpSecureHash;
        }

        return $vnpUrl;
    }

    /**
     * Xác thực IPN từ VNPay
     */
    public function verifyIpn(array $vnpData): bool
    {
        $vnpSecureHash = $vnpData['vnp_SecureHash'] ?? '';
        unset($vnpData['vnp_SecureHash']);

        ksort($vnpData);
        $hashData = '';
        $i = 0;

        foreach ($vnpData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&'.urlencode($key).'='.urlencode($value);
            } else {
                $hashData .= urlencode($key).'='.urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $this->vnpHashSecret);

        return $secureHash === $vnpSecureHash;
    }

    /**
     * Validate IPN data from VNPay (alias for verifyIpn)
     */
    public function validateIPN(array $ipnData): bool
    {
        return $this->verifyIpn($ipnData);
    }

    /**
     * Xử lý kết quả thanh toán từ VNPay
     */
    public function handlePaymentResult(array $vnpData): array
    {
        try {
            // Verify security hash
            if (! $this->verifyIpn($vnpData)) {
                LogService::vnpay('IPN verification failed', $vnpData, 'error');

                return ['success' => false, 'message' => 'Invalid signature'];
            }

            $txnRef = $vnpData['vnp_TxnRef'];
            $responseCode = $vnpData['vnp_ResponseCode'];
            $transactionNo = $vnpData['vnp_TransactionNo'] ?? null;
            $amount = $vnpData['vnp_Amount'] / 100; // Convert back to VND

            // Tìm booking
            $booking = Booking::where('vnpay_txn_ref', $txnRef)->first();
            if (! $booking) {
                LogService::vnpay('Booking not found for txn_ref: '.$txnRef, ['txn_ref' => $txnRef], 'error');

                return ['success' => false, 'message' => 'Booking not found'];
            }

            // Tìm transaction
            $transaction = Transaction::where('transaction_id', $txnRef)->first();

            if ($responseCode === '00') {
                // Thanh toán thành công
                $booking->update([
                    'payment_status' => 'paid',
                    'payment_method' => 'vnpay',
                    'payment_at' => Carbon::now(),
                    'payment_metadata' => array_merge($booking->payment_metadata ?? [], [
                        'vnpay_transaction_no' => $transactionNo,
                        'vnpay_response_time' => Carbon::now(),
                        'vnpay_amount' => $amount,
                        'vnpay_bank_code' => $vnpData['vnp_BankCode'] ?? null,
                        'vnpay_card_type' => $vnpData['vnp_CardType'] ?? null,
                    ]),
                ]);

                if ($transaction) {
                    $transaction->update([
                        'status' => Transaction::STATUS_COMPLETED,
                        'gateway_response' => $vnpData,
                        'processed_at' => Carbon::now(),
                        'gateway_transaction_id' => $transactionNo,
                        'metadata' => [
                            'vnpay_transaction_no' => $transactionNo,
                            'vnpay_bank_code' => $vnpData['vnp_BankCode'] ?? null,
                            'vnpay_card_type' => $vnpData['vnp_CardType'] ?? null,
                            'vnpay_pay_date' => $vnpData['vnp_PayDate'] ?? null,
                        ],
                    ]);
                }

                LogService::vnpay('Payment successful', [
                    'booking_id' => $booking->id,
                    'txn_ref' => $txnRef,
                    'amount' => $amount,
                    'transaction_no' => $transactionNo,
                ]);

                return [
                    'success' => true,
                    'booking' => $booking,
                    'message' => 'Payment successful',
                ];
            } else {
                // Thanh toán thất bại
                $booking->update(['payment_status' => 'failed']);

                if ($transaction) {
                    $transaction->update([
                        'status' => Transaction::STATUS_FAILED,
                        'gateway_response' => $vnpData,
                        'processed_at' => Carbon::now(),
                        'metadata' => [
                            'vnpay_response_code' => $responseCode,
                            'vnpay_response_message' => $this->getResponseMessage($responseCode),
                        ],
                    ]);
                }

                LogService::vnpay('Payment failed', [
                    'booking_id' => $booking->id,
                    'response_code' => $responseCode,
                    'txn_ref' => $txnRef,
                    'response_message' => $this->getResponseMessage($responseCode),
                ], 'warning');

                return [
                    'success' => false,
                    'booking' => $booking,
                    'message' => $this->getResponseMessage($responseCode),
                ];
            }
        } catch (\Exception $e) {
            LogService::vnpay('Payment processing error: '.$e->getMessage(), [
                'vnp_data' => $vnpData,
                'trace' => $e->getTraceAsString(),
            ], 'error');

            return [
                'success' => false,
                'message' => 'Payment processing error',
            ];
        }
    }

    /**
     * Chuyển đổi mã phản hồi VNPay thành thông báo
     */
    protected function getResponseMessage(string $responseCode): string
    {
        $messages = [
            '00' => 'Giao dịch thành công',
            '07' => 'Trừ tiền thành công. Giao dịch bị nghi ngờ (liên quan tới lừa đảo, giao dịch bất thường)',
            '09' => 'Giao dịch không thành công do: Thẻ/Tài khoản của khách hàng chưa đăng ký dịch vụ InternetBanking tại ngân hàng',
            '10' => 'Giao dịch không thành công do: Khách hàng xác thực thông tin thẻ/tài khoản không đúng quá 3 lần',
            '11' => 'Giao dịch không thành công do: Đã hết hạn chờ thanh toán',
            '12' => 'Giao dịch không thành công do: Thẻ/Tài khoản của khách hàng bị khóa',
            '13' => 'Giao dịch không thành công do Quý khách nhập sai mật khẩu xác thực giao dịch (OTP)',
            '24' => 'Giao dịch không thành công do: Khách hàng hủy giao dịch',
            '51' => 'Giao dịch không thành công do: Tài khoản của quý khách không đủ số dư để thực hiện giao dịch',
            '65' => 'Giao dịch không thành công do: Tài khoản của Quý khách đã vượt quá hạn mức giao dịch trong ngày',
            '75' => 'Ngân hàng thanh toán đang bảo trì',
            '79' => 'Giao dịch không thành công do: KH nhập sai mật khẩu thanh toán quá số lần quy định',
            '99' => 'Các lỗi khác',
        ];

        return $messages[$responseCode] ?? 'Lỗi không xác định';
    }

    /**
     * Tạo refund request (VNPay không hỗ trợ API refund tự động)
     */
    public function createRefundRequest(Booking $booking, ?float $amount = null): Transaction
    {
        $refundAmount = $amount ?? $booking->price;

        // Tạo transaction record cho refund
        $transaction = Transaction::create([
            'booking_id' => $booking->id,
            'user_id' => $booking->student_id,
            'transaction_id' => 'REFUND_'.$booking->id.'_'.time(),
            'payment_method' => Transaction::PAYMENT_METHOD_VNPAY,
            'type' => ($amount !== null && $amount < $booking->price) ? Transaction::TYPE_PARTIAL_REFUND : Transaction::TYPE_REFUND,
            'amount' => $refundAmount,
            'currency' => $booking->currency ?? 'VND',
            'status' => Transaction::STATUS_PENDING,
            'metadata' => [
                'original_booking_id' => $booking->id,
                'original_txn_ref' => $booking->vnpay_txn_ref,
                'refund_reason' => 'Customer request',
            ],
        ]);

        // VNPay yêu cầu refund thủ công qua portal
        LogService::vnpay('Refund request created', [
            'booking_id' => $booking->id,
            'transaction_id' => $transaction->id,
            'amount' => $refundAmount,
        ]);

        return $transaction;
    }

    /**
     * Calculate VND amount from booking price
     */
    private function calculateVndAmount(Booking $booking): float
    {
        $currency = $booking->currency ?? 'VND';
        $amount = (float) $booking->price;

        // Log for debugging
        LogService::vnpay('Amount calculation', [
            'booking_id' => $booking->id,
            'original_amount' => $amount,
            'currency' => $currency,
            'price_type' => gettype($booking->price),
        ]);

        // Simplified logic:
        // If currency is USD, convert to VND
        // Otherwise, assume it's already VND
        if ($currency === 'USD') {
            $vndAmount = $amount * 25000; // Convert USD to VND
            LogService::vnpay('Converting USD to VND', [
                'usd_amount' => $amount,
                'vnd_amount' => $vndAmount,
                'exchange_rate' => 25000,
            ]);
            return $vndAmount;
        }

        // For VND currency or no currency specified, use amount as-is
        // The amount should already be in VND
        LogService::vnpay('Using amount as VND', [
            'vnd_amount' => $amount,
            'currency' => $currency,
        ]);

        return $amount;
    }

    /**
     * Cleanup old pending transactions for a booking
     */
    private function cleanupOldPendingTransactions(Booking $booking): void
    {
        // Tìm các pending transactions cũ hơn 5 phút
        $oldPendingTransactions = Transaction::where('booking_id', $booking->id)
            ->where('type', Transaction::TYPE_PAYMENT)
            ->where('status', Transaction::STATUS_PENDING)
            ->where('created_at', '<', now()->subMinutes(5))
            ->get();

        if ($oldPendingTransactions->isNotEmpty()) {
            LogService::vnpay('Cleaning up old pending transactions', [
                'booking_id' => $booking->id,
                'count' => $oldPendingTransactions->count(),
                'transaction_ids' => $oldPendingTransactions->pluck('id')->toArray(),
            ]);

            // Mark old pending transactions as failed
            foreach ($oldPendingTransactions as $transaction) {
                $transaction->update([
                    'status' => Transaction::STATUS_FAILED,
                    'metadata' => array_merge($transaction->metadata ?? [], [
                        'failure_reason' => 'timeout_cleanup',
                        'cleaned_up_at' => now()->toISOString(),
                    ]),
                ]);
            }
        }
    }
}
