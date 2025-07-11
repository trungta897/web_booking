<?php

namespace App\Http\Controllers;

use App\Models\TutorPayout;
use App\Models\Booking;
use App\Services\PayoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminPayoutController extends Controller
{
    protected $payoutService;

    public function __construct(PayoutService $payoutService)
    {
        $this->payoutService = $payoutService;
    }

    /**
     * Display admin payout dashboard
     */
    public function index(Request $request)
    {
        $query = TutorPayout::with(['tutor.user', 'payoutItems'])
            ->withCount('payoutItems');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tutor_id')) {
            $query->where('tutor_id', $request->tutor_id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('requested_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('requested_at', '<=', $request->to_date);
        }

        // Apply sorting
        $sortField = $request->get('sort', 'requested_at');
        $sortDirection = $request->get('direction', 'desc');

        $allowedSorts = ['requested_at', 'amount', 'status', 'tutor_name'];
        if (in_array($sortField, $allowedSorts)) {
            if ($sortField === 'tutor_name') {
                $query->join('tutors', 'tutor_payouts.tutor_id', '=', 'tutors.id')
                      ->join('users', 'tutors.user_id', '=', 'users.id')
                      ->orderBy('users.name', $sortDirection)
                      ->select('tutor_payouts.*');
            } else {
                $query->orderBy($sortField, $sortDirection);
            }
        }

        $payouts = $query->paginate(20)->withQueryString();

        // Get statistics
        $stats = $this->getPayoutStatistics($request);

        // Get recent activity
        $recentActivity = TutorPayout::with(['tutor.user'])
            ->whereIn('status', ['processing', 'completed', 'failed'])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.payouts.index', compact('payouts', 'stats', 'recentActivity'));
    }

    /**
     * Show specific payout details
     */
    public function show(TutorPayout $payout)
    {
        $payout->load(['tutor.user', 'payoutItems.booking.student', 'payoutItems.booking.subject']);

        return view('admin.payouts.show', compact('payout'));
    }

    /**
     * Approve a payout request
     */
    public function approve(Request $request, TutorPayout $payout)
    {
        if ($payout->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => __('admin.payout_not_pending')
            ], 400);
        }

        try {
            DB::transaction(function () use ($payout, $request) {
                $payout->update([
                    'status' => 'processing',
                    'processed_by' => Auth::id(),
                    'processed_at' => now(),
                    'admin_notes' => $request->admin_notes
                ]);

                // Log admin action
                Log::info('Payout approved by admin', [
                    'payout_id' => $payout->id,
                    'admin_id' => Auth::id(),
                    'admin_notes' => $request->admin_notes,
                    'amount' => $payout->amount
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => __('admin.payout_approved_successfully')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('admin.error_approving_payout') . ': ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a payout request
     */
    public function reject(Request $request, TutorPayout $payout)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        if ($payout->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => __('admin.payout_not_pending')
            ], 400);
        }

        try {
            DB::transaction(function () use ($payout, $request) {
                // Mark payout as failed
                $payout->update([
                    'status' => 'failed',
                    'processed_by' => Auth::id(),
                    'processed_at' => now(),
                    'failure_reason' => $request->rejection_reason,
                    'admin_notes' => $request->admin_notes
                ]);

                // Return bookings to available status
                foreach ($payout->payoutItems as $item) {
                    $item->booking->update(['payout_id' => null]);
                }

                // Log admin action
                Log::info('Payout rejected by admin', [
                    'payout_id' => $payout->id,
                    'admin_id' => Auth::id(),
                    'rejection_reason' => $request->rejection_reason,
                    'admin_notes' => $request->admin_notes,
                    'amount' => $payout->amount
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => __('admin.payout_rejected_successfully')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('admin.error_rejecting_payout') . ': ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark payout as completed
     */
    public function complete(Request $request, TutorPayout $payout)
    {
        $request->validate([
            'transaction_id' => 'nullable|string|max:255',
            'completion_notes' => 'nullable|string|max:500'
        ]);

        if ($payout->status !== 'processing') {
            return response()->json([
                'success' => false,
                'message' => __('admin.payout_not_processing')
            ], 400);
        }

        try {
            DB::transaction(function () use ($payout, $request) {
                $payout->update([
                    'status' => 'completed',
                    'processed_by' => Auth::id(),
                    'processed_at' => now(),
                    'transaction_id' => $request->transaction_id,
                    'admin_notes' => $request->completion_notes
                ]);

                // Log admin action
                Log::info('Payout completed by admin', [
                    'payout_id' => $payout->id,
                    'admin_id' => Auth::id(),
                    'transaction_id' => $request->transaction_id,
                    'completion_notes' => $request->completion_notes,
                    'amount' => $payout->amount
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => __('admin.payout_completed_successfully')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('admin.error_completing_payout') . ': ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payout analytics
     */
    public function analytics(Request $request)
    {
        $period = $request->get('period', '30'); // days
        $startDate = now()->subDays($period);

        $analytics = [
            'total_payouts' => TutorPayout::where('created_at', '>=', $startDate)->count(),
            'pending_payouts' => TutorPayout::where('status', 'pending')->count(),
            'processing_payouts' => TutorPayout::where('status', 'processing')->count(),
            'completed_payouts' => TutorPayout::where('status', 'completed')
                ->where('created_at', '>=', $startDate)->count(),
            'total_amount_paid' => TutorPayout::where('status', 'completed')
                ->where('created_at', '>=', $startDate)->sum('amount'),
            'average_payout_amount' => TutorPayout::where('created_at', '>=', $startDate)
                ->avg('amount'),
            'top_tutors' => $this->getTopTutorsByPayouts($startDate),
            'monthly_trends' => $this->getMonthlyPayoutTrends(),
            'status_distribution' => $this->getStatusDistribution()
        ];

        return view('admin.payouts.analytics', compact('analytics'));
    }

    /**
     * Export payouts data
     */
    public function export(Request $request)
    {
        $query = TutorPayout::with(['tutor.user', 'payoutItems']);

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('requested_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('requested_at', '<=', $request->to_date);
        }

        $payouts = $query->get();

        $filename = 'payouts_export_' . now()->format('Y_m_d_H_i_s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($payouts) {
            $file = fopen('php://output', 'w');

            // CSV Headers
            fputcsv($file, [
                'ID', 'Tutor Name', 'Email', 'Amount', 'Status',
                'Bank Name', 'Account Number', 'Account Holder',
                'Requested Date', 'Processed Date', 'Bookings Count',
                'Transaction ID', 'Admin Notes'
            ]);

            foreach ($payouts as $payout) {
                fputcsv($file, [
                    $payout->id,
                    $payout->tutor->user->name,
                    $payout->tutor->user->email,
                    $payout->amount,
                    $payout->status,
                    $payout->bank_name,
                    $payout->account_number,
                    $payout->account_holder_name,
                    $payout->requested_at->format('Y-m-d H:i:s'),
                    $payout->processed_at ? $payout->processed_at->format('Y-m-d H:i:s') : '',
                    $payout->payoutItems->count(),
                    $payout->transaction_id ?? '',
                    $payout->admin_notes ?? ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get payout statistics
     */
    protected function getPayoutStatistics($request)
    {
        $query = TutorPayout::query();

        // Apply same filters
        if ($request->filled('from_date')) {
            $query->whereDate('requested_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('requested_at', '<=', $request->to_date);
        }

        return [
            'total_payouts' => $query->count(),
            'pending_count' => (clone $query)->where('status', 'pending')->count(),
            'processing_count' => (clone $query)->where('status', 'processing')->count(),
            'completed_count' => (clone $query)->where('status', 'completed')->count(),
            'failed_count' => (clone $query)->where('status', 'failed')->count(),
            'total_amount' => $query->sum('amount'),
            'pending_amount' => (clone $query)->where('status', 'pending')->sum('amount'),
            'completed_amount' => (clone $query)->where('status', 'completed')->sum('amount'),
            'average_amount' => $query->avg('amount') ?: 0,
            'average_processing_time' => $this->getAverageProcessingTime()
        ];
    }

    /**
     * Get top tutors by payout amount
     */
    protected function getTopTutorsByPayouts($startDate)
    {
        return TutorPayout::select('tutor_id', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(*) as payout_count'))
            ->with(['tutor.user'])
            ->where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->groupBy('tutor_id')
            ->orderBy('total_amount', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Get monthly payout trends
     */
    protected function getMonthlyPayoutTrends()
    {
        return TutorPayout::select(
                DB::raw('DATE_FORMAT(requested_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('AVG(amount) as avg_amount')
            )
            ->where('requested_at', '>=', now()->subYear())
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    /**
     * Get status distribution
     */
    protected function getStatusDistribution()
    {
        return TutorPayout::select('status', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total_amount'))
            ->groupBy('status')
            ->get();
    }

    /**
     * Get average processing time
     */
    protected function getAverageProcessingTime()
    {
        $processed = TutorPayout::whereNotNull('processed_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, requested_at, processed_at)) as avg_hours')
            ->first();

        return $processed ? round($processed->avg_hours, 1) : 0;
    }
}
