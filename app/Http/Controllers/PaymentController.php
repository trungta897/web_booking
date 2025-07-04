<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\PaymentService;
use App\Services\VnpayService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;
    protected VnpayService $vnpayService;

    public function __construct(PaymentService $paymentService, VnpayService $vnpayService)
    {
        $this->paymentService = $paymentService;
        $this->vnpayService = $vnpayService;
    }

    /**
     * Process payment (unified endpoint)
     */
    public function processPayment(Request $request, Booking $booking): JsonResponse
    {
        try {
            // Validate request
            $request->validate([
                'payment_method' => 'required|in:stripe,vnpay',
            ]);

            // Log the request for debugging
            Log::info('Payment processing started', [
                'booking_id' => $booking->id,
                'user_id' => Auth::id(),
                'payment_method' => $request->payment_method,
                'booking_status' => $booking->status,
                'payment_status' => $booking->payment_status,
            ]);

            // Check if user can access this booking
            $this->checkBookingAccess($booking);

            // Check if user can make payment for this booking
            $this->checkPaymentPermission($booking);

            // Validate booking status for payment
            $this->validateBookingForPayment($booking);

            // Process based on payment method
            if ($request->payment_method === 'vnpay') {
                return $this->handleVnpayPayment($booking, $request);
            } else {
                return $this->handleStripePayment($booking, $request);
            }

        } catch (Exception $e) {
            Log::error('Payment processing failed', [
                'booking_id' => $booking->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->handlePaymentError($e);
        }
    }

    /**
     * Create Stripe payment intent
     */
    public function createIntent(Request $request, Booking $booking): JsonResponse
    {
        try {
            $this->checkBookingAccess($booking);
            $this->checkPaymentPermission($booking);
            $this->validateBookingForPayment($booking);

            $result = $this->paymentService->createStripePaymentIntent($booking);

            if (!$result->success) {
                return response()->json(['error' => $result->error], 400);
            }

            return response()->json([
                'clientSecret' => $result->clientSecret,
            ]);

        } catch (Exception $e) {
            return $this->handlePaymentError($e);
        }
    }

    /**
     * Create VNPay payment
     */
    public function createVnpayPayment(Request $request, Booking $booking): JsonResponse
    {
        try {
            $this->checkBookingAccess($booking);
            $this->checkPaymentPermission($booking);
            $this->validateBookingForPayment($booking);

            $result = $this->paymentService->createVnpayPayment($booking, $request->ip());

            if (!$result->success) {
                return response()->json(['error' => $result->error], 400);
            }

            return response()->json([
                'payment_url' => $result->paymentUrl,
            ]);

        } catch (Exception $e) {
            return $this->handlePaymentError($e);
        }
    }

    /**
     * Confirm Stripe payment
     */
    public function confirm(Request $request, Booking $booking): RedirectResponse
    {
        try {
            $this->checkBookingAccess($booking);

            $success = $this->paymentService->confirmStripePayment($booking);

            if ($success) {
                return redirect()->route('bookings.show', $booking)
                    ->with('success', __('booking.payment_completed_successfully'));
            }

            return redirect()->route('bookings.show', $booking)
                ->with('error', __('booking.payment_not_completed'));

        } catch (Exception $e) {
            return redirect()->route('bookings.show', $booking)
                ->with('error', __('booking.error_confirming_payment', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Handle VNPay return
     */
    public function vnpayReturn(Request $request): RedirectResponse
    {
        try {
            // Check for specific cancellation or failure response codes
            $responseCode = $request->get('vnp_ResponseCode');
            $txnRef = $request->get('vnp_TxnRef');

            // Log the return data for debugging
            Log::info('VNPay return received', [
                'response_code' => $responseCode,
                'txn_ref' => $txnRef,
                'all_params' => $request->all(),
            ]);

            // Handle cancellation or specific error codes
            if ($responseCode === '24') { // User cancelled
                return redirect()->route('student.dashboard')
                    ->with('info', 'Bạn đã hủy thanh toán. Bạn có thể thử lại sau.');
            }

            // Try to find booking by txn_ref to get proper redirect
            $booking = null;
            if ($txnRef) {
                $booking = \App\Models\Booking::where('vnpay_txn_ref', $txnRef)->first();
            }

            $result = $this->paymentService->handleVnpayReturn($request->all());

            if ($result['success']) {
                return redirect()->route('bookings.show', $result['booking'])
                    ->with('success', $result['message']);
            }

            // If we have a booking, redirect to booking page
            if (isset($result['booking'])) {
                return redirect()->route('bookings.show', $result['booking'])
                    ->with('error', $result['message']);
            }

            // If we found booking by txn_ref, redirect to booking page
            if ($booking) {
                return redirect()->route('bookings.show', $booking)
                    ->with('error', $result['message'] ?? 'Thanh toán không thành công.');
            }

            // Fallback to student dashboard with error message
            return redirect()->route('student.dashboard')
                ->with('error', $result['message'] ?? 'Có lỗi xảy ra trong quá trình thanh toán.');

        } catch (Exception $e) {
            Log::error('VNPay return handling failed', [
                'request_data' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Always redirect to student dashboard on error
            return redirect()->route('student.dashboard')
                ->with('error', 'Có lỗi xảy ra trong quá trình xử lý thanh toán. Vui lòng thử lại sau.');
        }
    }

    /**
     * Handle VNPay IPN
     */
    public function vnpayIpn(Request $request): Response
    {
        try {
            $this->paymentService->handleVnpayIpn($request->all());
            return response('OK', 200);
        } catch (Exception $e) {
            Log::error('VNPay IPN handling failed', [
                'request_data' => $request->all(),
                'error' => $e->getMessage(),
            ]);
            return response('ERROR', 400);
        }
    }

    /**
     * Handle Stripe webhook
     */
    public function handleWebhook(Request $request): Response
    {
        try {
            $this->paymentService->handleStripeWebhook($request);
            return response('Webhook received', 200);
        } catch (Exception $e) {
            Log::error('Stripe webhook handling failed', [
                'error' => $e->getMessage(),
            ]);
            return response('Webhook Error: '.$e->getMessage(), 400);
        }
    }

    /**
     * Get transaction history for booking
     */
    public function getTransactionHistory(Booking $booking): JsonResponse
    {
        try {
            $this->checkBookingAccess($booking);

            $transactions = $this->paymentService->getBookingTransactions($booking);

            return response()->json([
                'transactions' => $transactions,
            ]);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * View transaction history page
     */
    public function viewTransactionHistory(Booking $booking): View
    {
        $this->checkBookingAccess($booking);

        $transactions = $this->paymentService->getBookingTransactions($booking);

        return view('bookings.transactions', compact('booking', 'transactions'));
    }

    /**
     * Process refund for booking
     */
    public function processRefund(Request $request, Booking $booking): RedirectResponse
    {
        try {
            $this->checkBookingAccess($booking);
            $this->checkRefundPermission($booking);

            $validated = $request->validate([
                'refund_reason' => 'required|string|max:255',
                'refund_description' => 'nullable|string|max:500',
            ]);

            // Check if booking can be refunded
            if ($booking->payment_status !== 'paid') {
                throw new Exception(__('booking.errors.booking_not_paid_refund'));
            }

            // Process refund
            $result = $this->paymentService->refundPayment(
                $booking,
                $booking->price,
                $validated['refund_reason']
            );

            if ($result['success']) {
                // Update booking status to cancelled with refund info
                $booking->update([
                    'status' => 'cancelled',
                    'cancellation_reason' => 'tutor_unavailable',
                    'cancellation_description' => $validated['refund_description'] ?? null,
                    'payment_status' => 'refunded',
                ]);

                // Send notifications to student
                $booking->student->notify(new \App\Notifications\BookingCancelled($booking, 'tutor'));
                $booking->student->notify(new \App\Notifications\PaymentRefunded($booking, $validated['refund_reason']));

                Log::info('Refund processed successfully', [
                    'booking_id' => $booking->id,
                    'tutor_id' => Auth::user()->tutor->id ?? null,
                    'amount' => $booking->price,
                    'reason' => $validated['refund_reason'],
                ]);

                return redirect()->route('bookings.show', $booking)
                    ->with('success', 'Hoàn tiền thành công. Học viên sẽ nhận được tiền hoàn trong vòng 3-5 ngày làm việc.');
            }

            return redirect()->route('bookings.show', $booking)
                ->with('error', $result['message'] ?? 'Không thể xử lý hoàn tiền. Vui lòng thử lại sau.');

        } catch (Exception $e) {
            Log::error('Refund processing failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('bookings.show', $booking)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show refund confirmation page
     */
    public function confirmRefund(Booking $booking): View
    {
        $this->checkBookingAccess($booking);
        $this->checkRefundPermission($booking);

        if ($booking->payment_status !== 'paid') {
            abort(422, 'Booking is not paid and cannot be refunded');
        }

        return view('bookings.refund-confirm', compact('booking'));
    }

    // ========== DEMO AND TEST METHODS ==========

    public function showVnpayDemo(): View
    {
        return view('vnpay-demo');
    }

    public function createDemoVnpay(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:10000|max:50000000',
            'order_info' => 'required|string|max:255',
        ]);

        try {
            $demoData = [
                'vnp_Version' => '2.1.0',
                'vnp_Command' => 'pay',
                'vnp_TmnCode' => config('services.vnpay.tmn_code'),
                'vnp_Amount' => $request->amount * 100,
                'vnp_CurrCode' => 'VND',
                'vnp_TxnRef' => 'DEMO_' . time(),
                'vnp_OrderInfo' => $request->order_info,
                'vnp_OrderType' => 'demo',
                'vnp_Locale' => app()->getLocale() === 'vi' ? 'vn' : 'en',
                'vnp_ReturnUrl' => route('vnpay.result'),
                'vnp_IpAddr' => $request->ip(),
                'vnp_CreateDate' => now()->format('YmdHis'),
                'vnp_ExpireDate' => now()->addMinutes(30)->format('YmdHis'),
            ];

            $vnpUrl = $this->buildVnpayUrl($demoData);

            return response()->json([
                'success' => true,
                'payment_url' => $vnpUrl,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function showVnpayTest(): View
    {
        return view('test-vnpay');
    }

    public function createTestVnpay(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:1000',
            'order_info' => 'required|string|max:255',
        ]);

        try {
            $testData = [
                'vnp_Version' => '2.1.0',
                'vnp_Command' => 'pay',
                'vnp_TmnCode' => config('services.vnpay.tmn_code'),
                'vnp_Amount' => $request->amount * 100,
                'vnp_CurrCode' => 'VND',
                'vnp_TxnRef' => 'TEST_' . time(),
                'vnp_OrderInfo' => $request->order_info,
                'vnp_OrderType' => 'test',
                'vnp_Locale' => app()->getLocale() === 'vi' ? 'vn' : 'en',
                'vnp_ReturnUrl' => route('vnpay.result'),
                'vnp_IpAddr' => $request->ip(),
                'vnp_CreateDate' => now()->format('YmdHis'),
                'vnp_ExpireDate' => now()->addMinutes(30)->format('YmdHis'),
            ];

            $vnpUrl = $this->buildVnpayUrl($testData);

            return response()->json([
                'success' => true,
                'payment_url' => $vnpUrl,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function showVnpayResult(Request $request): View
    {
        $params = $request->all();

        $result = [
            'success' => false,
            'message' => 'Unknown result',
            'data' => $params
        ];

        if (isset($params['vnp_ResponseCode'])) {
            if ($params['vnp_ResponseCode'] === '00') {
                $result['success'] = true;
                $result['message'] = 'Giao dịch thành công';
            } else {
                $result['message'] = $this->getVnpayResponseMessage($params['vnp_ResponseCode']);
            }
        }

        return view('vnpay-result', compact('result', 'params'));
    }

    // ========== PRIVATE HELPER METHODS ==========

    /**
     * Check if user can access the booking
     */
    private function checkBookingAccess(Booking $booking): void
    {
        $user = Auth::user();

        if (!$user) {
            throw new Exception(__('booking.errors.unauthorized_access'), 401);
        }

        $isStudent = $booking->student_id === $user->id;
        $isTutor = $booking->tutor && $booking->tutor->user_id === $user->id;
        $isAdmin = $user->role === 'admin';

        if (!$isStudent && !$isTutor && !$isAdmin) {
            throw new Exception(__('booking.errors.unauthorized_access'), 403);
        }
    }

    /**
     * Check if user can make payment for this booking
     */
    private function checkPaymentPermission(Booking $booking): void
    {
        $user = Auth::user();

        // Only the student who made the booking can pay
        if ($booking->student_id !== $user->id) {
            throw new Exception(__('booking.errors.only_student_can_pay'), 403);
        }
    }

    /**
     * Check if user can process refund for this booking
     */
    private function checkRefundPermission(Booking $booking): void
    {
        $user = Auth::user();

        // Only the tutor of the booking can process refund
        if (!$user->tutor || $booking->tutor_id !== $user->tutor->id) {
            throw new Exception(__('booking.errors.only_tutor_can_refund'), 403);
        }
    }

    /**
     * Validate booking status for payment
     */
    private function validateBookingForPayment(Booking $booking): void
    {
        if ($booking->status === 'cancelled') {
            throw new Exception(__('booking.errors.booking_cancelled_payment'), 422);
        }

        if ($booking->payment_status === 'paid') {
            throw new Exception(__('booking.info.already_paid'), 422);
        }

        // Kiểm tra xem có transaction đã hoàn thành không
        if ($booking->completedTransactions()->exists()) {
            throw new Exception(__('booking.info.already_paid'), 422);
        }

        if ($booking->status !== 'accepted') {
            throw new Exception(__('booking.errors.booking_not_accepted_payment'), 422);
        }

        // Kiểm tra xem có giao dịch pending nào không
        $pendingTransaction = $booking->transactions()
            ->where('status', 'pending')
            ->where('type', 'payment')
            ->where('created_at', '>', now()->subMinutes(30)) // Chỉ kiểm tra 30 phút gần đây
            ->first();

        if ($pendingTransaction) {
            throw new Exception('Có một giao dịch đang được xử lý. Vui lòng đợi hoặc thử lại sau.', 422);
        }
    }

    /**
     * Handle VNPay payment processing
     */
    private function handleVnpayPayment(Booking $booking, Request $request): JsonResponse
    {
        $result = $this->paymentService->createVnpayPayment($booking, $request->ip());

        if (!$result->success) {
            throw new Exception($result->error);
        }

        return response()->json([
            'success' => true,
            'payment_url' => $result->paymentUrl,
        ]);
    }

    /**
     * Handle Stripe payment processing
     */
    private function handleStripePayment(Booking $booking, Request $request): JsonResponse
    {
        $result = $this->paymentService->createStripePaymentIntent($booking);

        if (!$result->success) {
            throw new Exception($result->error);
        }

        return response()->json([
            'success' => true,
            'clientSecret' => $result->clientSecret,
        ]);
    }

    /**
     * Handle payment errors with appropriate HTTP status codes
     */
    private function handlePaymentError(Exception $e): JsonResponse
    {
        $statusCode = $this->getErrorStatusCode($e);

        return response()->json([
            'error' => $e->getMessage(),
            'code' => $e->getCode(),
        ], $statusCode);
    }

    /**
     * Determine appropriate HTTP status code for error
     */
    private function getErrorStatusCode(Exception $e): int
    {
        $code = $e->getCode();

        if ($code === 401) return 401; // Unauthorized
        if ($code === 403) return 403; // Forbidden
        if ($code === 422) return 422; // Unprocessable Entity

        // Check error message for specific cases
        $message = strtolower($e->getMessage());

        if (str_contains($message, 'unauthorized') || str_contains($message, 'không có quyền')) {
            return 403;
        }

        if (str_contains($message, 'cancelled') || str_contains($message, 'paid') || str_contains($message, 'accepted')) {
            return 422;
        }

        return 400; // Bad Request (default)
    }

    /**
     * Build VNPay URL
     */
    private function buildVnpayUrl(array $data): string
    {
        ksort($data);
        $query = '';
        $hashdata = '';
        $i = 0;

        foreach ($data as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . '=' . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . '=' . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . '=' . urlencode($value) . '&';
        }

        $vnpUrl = config('services.vnpay.url') . '?' . $query;

        if (config('services.vnpay.hash_secret')) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, config('services.vnpay.hash_secret'));
            $vnpUrl .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        return $vnpUrl;
    }

    /**
     * Get VNPay response message
     */
    private function getVnpayResponseMessage($responseCode): string
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
}
