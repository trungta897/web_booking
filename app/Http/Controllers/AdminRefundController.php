<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Transaction;
use App\Notifications\PaymentRefunded;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminRefundController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::where('payment_method', 'vnpay')
            ->whereIn('type', [Transaction::TYPE_REFUND, Transaction::TYPE_PARTIAL_REFUND])
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

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $refunds = $query->orderBy('created_at', 'desc')->paginate(20);

        // Enhanced statistics
        $stats = $this->getRefundStatistics($request);

        return view('admin.refunds', compact('refunds', 'stats'));
    }

    /**
     * Get comprehensive refund statistics.
     */
    private function getRefundStatistics(Request $request): array
    {
        $baseQuery = Transaction::whereIn('type', [Transaction::TYPE_REFUND, Transaction::TYPE_PARTIAL_REFUND]);

        // Apply same filters as main query
        if ($request->filled('date_from')) {
            $baseQuery->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $baseQuery->whereDate('created_at', '<=', $request->date_to);
        }

        return [
            // Status counts
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'processing' => (clone $baseQuery)->where('status', 'processing')->count(),
            'completed' => (clone $baseQuery)->where('status', 'completed')->count(),
            'failed' => (clone $baseQuery)->where('status', 'failed')->count(),

            // Amount statistics (this month)
            'total_amount_month' => (clone $baseQuery)
                ->where('status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount'),

            'total_amount_all_time' => (clone $baseQuery)
                ->where('status', 'completed')
                ->sum('amount'),

            // Payment method breakdown
            'by_payment_method' => (clone $baseQuery)
                ->where('status', 'completed')
                ->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total_amount')
                ->groupBy('payment_method')
                ->get(),

            // Average processing time
            'avg_processing_time' => $this->getAverageProcessingTime(),

            // Refund type breakdown
            'by_type' => (clone $baseQuery)
                ->where('status', 'completed')
                ->selectRaw('type, COUNT(*) as count, SUM(amount) as total_amount')
                ->groupBy('type')
                ->get(),

            // Recent trends (last 7 days)
            'daily_trends' => $this->getDailyRefundTrends(),

            // Top reasons (if we track this in metadata)
            'top_reasons' => $this->getTopRefundReasons(),
        ];
    }

    /**
     * Get average refund processing time.
     */
    private function getAverageProcessingTime(): ?float
    {
        $completedRefunds = Transaction::whereIn('type', [Transaction::TYPE_REFUND, Transaction::TYPE_PARTIAL_REFUND])
            ->where('status', 'completed')
            ->whereNotNull('processed_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, processed_at)) as avg_hours')
            ->value('avg_hours');

        return $completedRefunds ? round($completedRefunds, 1) : null;
    }

    /**
     * Get daily refund trends - shows all historical data by month.
     */
    private function getDailyRefundTrends(): array
    {
        // Get the first refund transaction date, or use 12 months ago as fallback
        $firstRefundDate = Transaction::whereIn('type', [Transaction::TYPE_REFUND, Transaction::TYPE_PARTIAL_REFUND])
            ->orderBy('created_at', 'asc')
            ->value('created_at');

        if (!$firstRefundDate) {
            // If no refunds exist, show last 12 months with zero data
            $startDate = Carbon::now()->subMonths(11)->startOfMonth();
        } else {
            $startDate = Carbon::parse($firstRefundDate)->startOfMonth();
            // Limit to maximum 24 months for performance
            if ($startDate->diffInMonths(Carbon::now()) > 24) {
                $startDate = Carbon::now()->subMonths(23)->startOfMonth();
            }
        }

        $trends = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte(Carbon::now()->endOfMonth())) {
            $nextMonth = $currentDate->copy()->endOfMonth();

            $monthlyCount = Transaction::whereIn('type', [Transaction::TYPE_REFUND, Transaction::TYPE_PARTIAL_REFUND])
                ->whereBetween('created_at', [$currentDate, $nextMonth])
                ->count();

            $monthlyAmount = abs(Transaction::whereIn('type', [Transaction::TYPE_REFUND, Transaction::TYPE_PARTIAL_REFUND])
                ->whereBetween('created_at', [$currentDate, $nextMonth])
                ->where('status', 'completed')
                ->sum('amount'));

            $trends[] = [
                'date' => $currentDate->format('m/Y'),
                'count' => $monthlyCount,
                'amount' => $monthlyAmount,
            ];

            $currentDate->addMonth();
        }

        // Ensure we always have at least 3 months of data for chart display
        while (count($trends) < 3) {
            $currentDate = $startDate->copy()->subMonth();
            array_unshift($trends, [
                'date' => $currentDate->format('m/Y'),
                'count' => 0,
                'amount' => 0,
            ]);
            $startDate = $currentDate;
        }

        return $trends;
    }

    /**
     * Get top refund reasons from metadata.
     */
    private function getTopRefundReasons(): array
    {
        $refunds = Transaction::whereIn('type', [Transaction::TYPE_REFUND, Transaction::TYPE_PARTIAL_REFUND])
            ->where('status', 'completed')
            ->whereNotNull('metadata')
            ->get();

        $reasons = [];
        foreach ($refunds as $refund) {
            $metadata = is_array($refund->metadata) ? $refund->metadata : [];
            $reason = $metadata['reason'] ?? $metadata['refund_reason'] ?? 'Unknown';
            $reasons[$reason] = ($reasons[$reason] ?? 0) + 1;
        }

        arsort($reasons);

        return array_slice($reasons, 0, 5, true);
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
                    'admin_notes' => 'Started manual processing via admin panel',
                ]),
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
                    'Process refund amount: ' . number_format((float) $refundTransaction->amount, 0, ',', '.') . ' VND',
                    'After completing, run: php artisan vnpay:refund complete --booking=' . $bookingId . ' --txn=REFUND_TXN_ID',
                ],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đã bắt đầu xử lý hoàn tiền. Kiểm tra log để xem hướng dẫn chi tiết.',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to start refund processing', [
                'booking_id' => $bookingId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function complete(Request $request, $bookingId)
    {
        $request->validate([
            'vnpay_refund_txn' => 'required|string',
            'admin_notes' => 'nullable|string',
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
                    'admin_notes' => $request->admin_notes,
                ]),
            ]);

            // Update booking
            $booking->update(['payment_status' => 'refunded']);

            // Send notification
            $booking->student->notify(new PaymentRefunded($booking, 'VNPay refund completed'));

            Log::info('VNPay manual refund completed via admin panel', [
                'booking_id' => $booking->id,
                'refund_txn_id' => $request->vnpay_refund_txn,
                'amount' => $refundTransaction->amount,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Hoàn tiền đã được hoàn thành thành công!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
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
