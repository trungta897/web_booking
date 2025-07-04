<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class VnpayService
{
    protected $vnpTmnCode;

    protected $vnpHashSecret;

    protected $vnpUrl;

    protected $vnpReturnUrl;

    public function __construct()
    {
        $this->vnpTmnCode = config('services.vnpay.tmn_code');
        $this->vnpHashSecret = config('services.vnpay.hash_secret');
        $this->vnpUrl = config('services.vnpay.url');
        $this->vnpReturnUrl = config('services.vnpay.return_url');
    }

    /**
     * Tạo URL thanh toán VNPay
     */
    public function createPaymentUrl(Booking $booking, $ipAddr = null)
    {
        // Tạo transaction reference
        $txnRef = 'BOOKING_'.$booking->id.'_'.time();

        // Cập nhật booking với txn_ref
        $booking->update([
            'vnpay_txn_ref' => $txnRef,
            'payment_method' => 'vnpay',
        ]);

        // Tạo transaction record
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

        // Calculate VND amount for VNPay
        $vndAmount = $this->calculateVndAmount($booking);

        // Tham số VNPay
        $vnpData = [
            'vnp_Version' => '2.1.0',
            'vnp_Command' => 'pay',
            'vnp_TmnCode' => $this->vnpTmnCode,
            'vnp_Amount' => $vndAmount * 100, // VNPay tính bằng VND * 100
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
    public function verifyIpn($vnpData)
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
    public function handlePaymentResult($vnpData)
    {
        try {
            // Verify security hash
            if (! $this->verifyIpn($vnpData)) {
                Log::error('VNPay IPN verification failed', $vnpData);

                return ['success' => false, 'message' => 'Invalid signature'];
            }

            $txnRef = $vnpData['vnp_TxnRef'];
            $responseCode = $vnpData['vnp_ResponseCode'];
            $transactionNo = $vnpData['vnp_TransactionNo'] ?? null;
            $amount = $vnpData['vnp_Amount'] / 100; // Convert back to VND

            // Tìm booking
            $booking = Booking::where('vnpay_txn_ref', $txnRef)->first();
            if (! $booking) {
                Log::error('Booking not found for VNPay txn_ref: '.$txnRef);

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

                Log::info('VNPay payment successful', [
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

                Log::warning('VNPay payment failed', [
                    'booking_id' => $booking->id,
                    'response_code' => $responseCode,
                    'txn_ref' => $txnRef,
                    'response_message' => $this->getResponseMessage($responseCode),
                ]);

                return [
                    'success' => false,
                    'booking' => $booking,
                    'message' => $this->getResponseMessage($responseCode),
                ];
            }
        } catch (\Exception $e) {
            Log::error('VNPay payment processing error: '.$e->getMessage(), [
                'vnp_data' => $vnpData,
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Payment processing error',
            ];
        }
    }

    /**
     * Chuyển đổi mã phản hồi VNPay thành thông báo
     */
    protected function getResponseMessage($responseCode)
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
    public function createRefundRequest(Booking $booking, $amount = null)
    {
        $refundAmount = $amount ?? $booking->price;

        // Tạo transaction record cho refund
        $transaction = Transaction::create([
            'booking_id' => $booking->id,
            'user_id' => $booking->student_id,
            'transaction_id' => 'REFUND_'.$booking->id.'_'.time(),
            'payment_method' => Transaction::PAYMENT_METHOD_VNPAY,
            'type' => $amount < $booking->price ? Transaction::TYPE_PARTIAL_REFUND : Transaction::TYPE_REFUND,
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
        Log::info('VNPay refund request created', [
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
        $amount = $booking->price;

        // Smart detection: If currency is VND but amount is small (< 1000),
        // it's likely USD amount saved with wrong currency
        if ($currency === 'VND' && $amount < 1000) {
            // This is likely USD amount with wrong currency label
            return $amount * 25000; // Convert USD to VND
        }

        // Case 1: Currency is VND (real VND amounts)
        if ($currency === 'VND') {
            return $amount; // Already VND
        }

        // Case 2: Currency is USD (legacy)
        if ($currency === 'USD') {
            return $amount * 25000; // Convert USD to VND
        }

        // Default: assume VND
        return $amount;
    }
}
