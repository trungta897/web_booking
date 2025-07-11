<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Tutor;
use App\Models\TutorPayout;
use App\Services\PayoutService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TutorPayoutController extends Controller
{
    protected PayoutService $payoutService;

    public function __construct(PayoutService $payoutService)
    {
        $this->payoutService = $payoutService;
    }

    /**
     * Show tutor earnings dashboard.
     */
    public function index(): View
    {
        $user = Auth::user();

        // Ensure user is a tutor
        $tutor = $user->tutor;
        if (!$tutor) {
            abort(403, 'Access denied. Tutor account required.');
        }

        // Calculate earnings
        $earnings = $this->payoutService->calculateTutorEarnings($tutor);

        // Get recent payouts
        $recentPayouts = $tutor->payouts()
            ->latest()
            ->take(5)
            ->get();

        // Get commission analytics
        $analytics = $this->getTutorAnalytics($tutor);

        return view('tutors.earnings.index', compact(
            'tutor',
            'earnings',
            'recentPayouts',
            'analytics'
        ));
    }

    /**
     * Show earnings details with pagination.
     */
    public function earnings(Request $request): View
    {
        $user = Auth::user();
        $tutor = $user->tutor;

        if (!$tutor) {
            abort(403, 'Access denied. Tutor account required.');
        }

        // Get eligible bookings for payout
        $eligibleBookings = Booking::where('tutor_id', $tutor->id)
            ->eligibleForPayout()
            ->with(['student', 'subject'])
            ->orderBy('commission_calculated_at', 'desc')
            ->paginate(15);

        // Calculate earnings summary
        $earnings = $this->payoutService->calculateTutorEarnings($tutor);

        return view('tutors.earnings.details', compact(
            'tutor',
            'eligibleBookings',
            'earnings'
        ));
    }

    /**
     * Show payout request form.
     */
    public function create(): View|RedirectResponse
    {
        $user = Auth::user();
        $tutor = $user->tutor;

        if (!$tutor) {
            abort(403, 'Access denied. Tutor account required.');
        }

        $earnings = $this->payoutService->calculateTutorEarnings($tutor);

        // Check if minimum payout amount is met
        $minimumPayout = 100000; // 100,000 VND

        if ($earnings['available_earnings'] < $minimumPayout) {
            return redirect()->route('tutors.earnings.index')
                ->with('error', 'Số dư khả dụng chưa đủ để rút tiền. Tối thiểu: ' . number_format($minimumPayout, 0, ',', '.') . ' VND');
        }

        // Bank list for Vietnam
        $banks = $this->getVietnamBanks();

        return view('tutors.earnings.create-payout', compact(
            'tutor',
            'earnings',
            'banks',
            'minimumPayout'
        ));
    }

    /**
     * Process payout request.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        $tutor = $user->tutor;

        if (!$tutor) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $request->validate([
            'bank_name' => 'required|string|max:100',
            'account_number' => 'required|string|max:50',
            'account_holder_name' => 'required|string|max:255',
            'amount' => 'nullable|numeric|min:100000', // Minimum 100k VND
        ]);

        try {
            $earnings = $this->payoutService->calculateTutorEarnings($tutor);

            // Validate amount
            $requestedAmount = $request->amount ? (float) $request->amount : null;

            if ($requestedAmount && $requestedAmount > $earnings['available_earnings']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Số tiền yêu cầu vượt quá số dư khả dụng.'
                ], 422);
            }

            if ($earnings['available_earnings'] < 100000) {
                return response()->json([
                    'success' => false,
                    'message' => 'Số dư khả dụng không đủ để rút tiền (tối thiểu 100,000 VND).'
                ], 422);
            }

            // Create payout
            $payout = $this->payoutService->createPayout($tutor, [
                'bank_name' => $request->bank_name,
                'account_number' => $request->account_number,
                'account_holder_name' => $request->account_holder_name,
            ], $requestedAmount);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'payout_id' => $payout->id,
                    'message' => 'Yêu cầu rút tiền đã được gửi thành công. Chúng tôi sẽ xử lý trong 1-3 ngày làm việc.',
                ]);
            }

            return redirect()->route('tutors.earnings.index')
                ->with('success', 'Yêu cầu rút tiền đã được gửi thành công. Chúng tôi sẽ xử lý trong 1-3 ngày làm việc.');

        } catch (Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Show payout history.
     */
    public function history(): View
    {
        $user = Auth::user();
        $tutor = $user->tutor;

        if (!$tutor) {
            abort(403, 'Access denied. Tutor account required.');
        }

        $payouts = $this->payoutService->getTutorPayouts($tutor, 10);

        return view('tutors.earnings.history', compact('tutor', 'payouts'));
    }

    /**
     * Show specific payout details.
     */
    public function show(TutorPayout $payout): View
    {
        $user = Auth::user();

        // Check access
        if ($payout->tutor->user_id !== $user->id) {
            abort(403, 'Access denied.');
        }

        $payout->load(['payoutItems.booking.subject', 'payoutItems.booking.student']);

        return view('tutors.earnings.payout-details', compact('payout'));
    }

    /**
     * Get tutor analytics.
     */
    private function getTutorAnalytics(Tutor $tutor): array
    {
        $bookingsWithCommission = Booking::where('tutor_id', $tutor->id)
            ->whereNotNull('commission_calculated_at')
            ->get();

        $totalRevenue = $bookingsWithCommission->sum('price');
        $totalEarnings = $bookingsWithCommission->sum('tutor_earnings');
        $totalPlatformFees = $bookingsWithCommission->sum('platform_fee_amount');

        $averageCommissionRate = $totalRevenue > 0
            ? round(($totalPlatformFees / $totalRevenue) * 100, 1)
            : 0;

        // Monthly data for chart (last 6 months)
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();

            $monthBookings = $bookingsWithCommission
                ->whereBetween('commission_calculated_at', [$monthStart, $monthEnd]);

            $monthlyData[] = [
                'month' => $date->format('M Y'),
                'revenue' => $monthBookings->sum('price'),
                'earnings' => $monthBookings->sum('tutor_earnings'),
                'bookings_count' => $monthBookings->count(),
            ];
        }

        return [
            'total_revenue' => $totalRevenue,
            'total_earnings' => $totalEarnings,
            'total_platform_fees' => $totalPlatformFees,
            'average_commission_rate' => $averageCommissionRate,
            'total_bookings' => $bookingsWithCommission->count(),
            'monthly_data' => $monthlyData,
        ];
    }

    /**
     * Get Vietnam banks list.
     */
    private function getVietnamBanks(): array
    {
        return [
            'Vietcombank' => 'Ngân hàng TMCP Ngoại Thương Việt Nam',
            'BIDV' => 'Ngân hàng TMCP Đầu tư và Phát triển Việt Nam',
            'VietinBank' => 'Ngân hàng TMCP Công Thương Việt Nam',
            'Agribank' => 'Ngân hàng Nông nghiệp và Phát triển Nông thôn Việt Nam',
            'ACB' => 'Ngân hàng TMCP Á Châu',
            'Techcombank' => 'Ngân hàng TMCP Kỹ Thương Việt Nam',
            'MB' => 'Ngân hàng TMCP Quân Đội',
            'VPBank' => 'Ngân hàng TMCP Việt Nam Thịnh Vượng',
            'TPBank' => 'Ngân hàng TMCP Tiên Phong',
            'Sacombank' => 'Ngân hàng TMCP Sài Gòn Thương Tín',
            'HDBank' => 'Ngân hàng TMCP Phát triển Thành phố Hồ Chí Minh',
            'VIB' => 'Ngân hàng TMCP Quốc tế Việt Nam',
            'SHB' => 'Ngân hàng TMCP Sài Gòn - Hà Nội',
            'Eximbank' => 'Ngân hàng TMCP Xuất Nhập khẩu Việt Nam',
            'OCB' => 'Ngân hàng TMCP Phương Đông',
        ];
    }
}
