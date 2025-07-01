<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Booking;
use App\Notifications\PaymentRefunded;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdminRefundController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::where('payment_method', 'vnpay')
            ->where('type', 'refund')
            ->with(['booking.student', 'booking.tutor.user']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $refunds = $query->orderBy('created_at', 'desc')->paginate(20);

        // Calculate stats
        $stats = [
            'pending' => Transaction::where('payment_method', 'vnpay')
                ->where('type', 'refund')
                ->where('status', 'pending')
                ->count(),
            'processing' => Transaction::where('payment_method', 'vnpay')
                ->where('type', 'refund')
                ->where('status', 'processing')
                ->count(),
            'completed' => Transaction::where('payment_method', 'vnpay')
                ->where('type', 'refund')
                ->where('status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->count(),
            'total_amount' => Transaction::where('payment_method', 'vnpay')
                ->where('type', 'refund')
                ->where('status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->sum('amount')
        ];

        return view('admin.refunds', compact('refunds', 'stats'));
    }

    public function startProcessing(Request $request, $bookingId)
    {
        try {
            $booking = Booking::findOrFail($bookingId);

            $refundTransaction = Transaction::where('booking_id', $bookingId)
                ->where('payment_method', 'vnpay')
                ->where('type', 'refund')
                ->where('status', 'pending')
                ->firstOrFail();

            $originalTransaction = Transaction::where('booking_id', $bookingId)
                ->where('payment_method', 'vnpay')
                ->where('type', 'payment')
                ->where('status', 'completed')
                ->first();

            // Update to processing
            $refundTransaction->update([
                'status' => 'processing',
                'metadata' => array_merge($refundTransaction->metadata ?? [], [
                    'processing_started_at' => Carbon::now(),
                    'admin_notes' => 'Started manual processing via admin panel'
                ])
            ]);

            // Log detailed instructions
            Log::info('VNPay manual refund started', [
                'booking_id' => $booking->id,
                'student' => $booking->student->name,
                'amount' => $refundTransaction->amount,
                'original_txn_ref' => $booking->vnpay_txn_ref,
                'original_gateway_txn' => $originalTransaction->gateway_transaction_id ?? null,
                'instructions' => [
                    'Login to VNPay Merchant Portal',
                    'Go to Transaction Management > Refund',
                    'Find transaction: ' . ($originalTransaction->gateway_transaction_id ?? $booking->vnpay_txn_ref),
                    'Process refund amount: ' . number_format($refundTransaction->amount, 0, ',', '.') . ' VND',
                    'After completing, run: php artisan vnpay:refund complete --booking=' . $bookingId . ' --txn=REFUND_TXN_ID'
                ]
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đã bắt đầu xử lý hoàn tiền. Kiểm tra log để xem hướng dẫn chi tiết.'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to start refund processing', [
                'booking_id' => $bookingId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function complete(Request $request, $bookingId)
    {
        $request->validate([
            'vnpay_refund_txn' => 'required|string',
            'admin_notes' => 'nullable|string'
        ]);

        try {
            $booking = Booking::findOrFail($bookingId);

            $refundTransaction = Transaction::where('booking_id', $bookingId)
                ->where('payment_method', 'vnpay')
                ->where('type', 'refund')
                ->whereIn('status', ['pending', 'processing'])
                ->firstOrFail();

            // Update refund transaction
            $refundTransaction->update([
                'status' => 'completed',
                'gateway_transaction_id' => $request->vnpay_refund_txn,
                'processed_at' => Carbon::now(),
                'metadata' => array_merge($refundTransaction->metadata ?? [], [
                    'vnpay_refund_txn_id' => $request->vnpay_refund_txn,
                    'completed_at' => Carbon::now(),
                    'admin_completed' => true,
                    'admin_notes' => $request->admin_notes
                ])
            ]);

            // Update booking
            $booking->update(['payment_status' => 'refunded']);

            // Send notification
            $booking->student->notify(new PaymentRefunded($booking, 'VNPay refund completed'));

            Log::info('VNPay manual refund completed via admin panel', [
                'booking_id' => $booking->id,
                'refund_txn_id' => $request->vnpay_refund_txn,
                'amount' => $refundTransaction->amount
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Hoàn tiền đã được hoàn thành thành công!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function details($refundId)
    {
        $refund = Transaction::with(['booking.student', 'booking.tutor.user'])
            ->findOrFail($refundId);

        $originalTransaction = Transaction::where('booking_id', $refund->booking_id)
            ->where('payment_method', 'vnpay')
            ->where('type', 'payment')
            ->where('status', 'completed')
            ->first();

        $html = view('admin.refund-details', compact('refund', 'originalTransaction'))->render();

        return response()->json(['html' => $html]);
    }
}
