<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\PayoutItem;
use App\Models\Tutor;
use App\Models\TutorPayout;
use Carbon\Carbon;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class PayoutService extends BaseService
{
    /**
     * Calculate commission for a booking (SAFE - doesn't modify existing data).
     */
    public function calculateCommission(Booking $booking): array
    {
        if (!$booking->tutor) {
            throw new Exception('Booking must have a tutor to calculate commission');
        }

        $commissionRate = $this->getTutorCommissionRate($booking->tutor);
        $platformFee = $booking->price * ($commissionRate / 100);
        $tutorEarnings = $booking->price - $platformFee;

        return [
            'platform_fee_percentage' => $commissionRate,
            'platform_fee_amount' => round($platformFee, 2),
            'tutor_earnings' => round($tutorEarnings, 2),
            'booking_total' => $booking->price,
        ];
    }

    /**
     * Apply commission to a booking (SAFE - only updates if not already calculated).
     */
    public function applyCommissionToBooking(Booking $booking): bool
    {
        // Skip if commission already calculated
        if ($booking->hasCommissionCalculated()) {
            Log::info('Commission already calculated for booking', ['booking_id' => $booking->id]);

            return false;
        }

        try {
            $commission = $this->calculateCommission($booking);

            $booking->update([
                'platform_fee_percentage' => $commission['platform_fee_percentage'],
                'platform_fee_amount' => $commission['platform_fee_amount'],
                'tutor_earnings' => $commission['tutor_earnings'],
                'commission_calculated_at' => now(),
            ]);

            Log::info('Commission applied to booking', [
                'booking_id' => $booking->id,
                'platform_fee' => $commission['platform_fee_amount'],
                'tutor_earnings' => $commission['tutor_earnings'],
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Failed to apply commission to booking', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get tutor commission rate based on tier.
     */
    public function getTutorCommissionRate(Tutor $tutor): float
    {
        // Load reviews data if not loaded
        if (!$tutor->relationLoaded('reviews')) {
            $tutor->loadCount('reviews');
            $tutor->loadAvg('reviews', 'rating');
        }

        // New tutor discount (first 30 days)
        if ($tutor->created_at->diffInDays(now()) <= 30) {
            return 10.0;
        }

        // Premium tutor discount (50+ reviews, 4.5+ rating)
        $reviewsCount = $tutor->reviews_count ?? 0;
        $avgRating = $tutor->reviews_avg_rating ?? 0;

        if ($reviewsCount >= 50 && $avgRating >= 4.5) {
            return 12.0;
        }

        return 15.0; // Default rate
    }

    /**
     * Calculate available earnings for tutor.
     */
    public function calculateTutorEarnings(Tutor $tutor): array
    {
        // Bookings eligible for payout (completed, paid, not yet paid out)
        $eligibleBookings = Booking::where('tutor_id', $tutor->id)
            ->eligibleForPayout()
            ->get();

        $totalEarnings = $eligibleBookings->sum('tutor_earnings');
        $totalPlatformFees = $eligibleBookings->sum('platform_fee_amount');

        // Pending payouts
        $pendingPayouts = $tutor->payouts()
            ->whereIn('status', [TutorPayout::STATUS_PENDING, TutorPayout::STATUS_PROCESSING])
            ->sum('total_amount');

        // Completed payouts
        $totalPaidOut = $tutor->payouts()
            ->where('status', TutorPayout::STATUS_COMPLETED)
            ->sum('total_amount');

        return [
            'available_earnings' => $totalEarnings,
            'pending_payout' => $pendingPayouts,
            'total_paid_out' => $totalPaidOut,
            'total_platform_fees' => $totalPlatformFees,
            'eligible_bookings_count' => $eligibleBookings->count(),
            'eligible_bookings' => $eligibleBookings,
        ];
    }

    /**
     * Create payout request for tutor.
     */
    public function createPayout(Tutor $tutor, array $bankInfo, ?float $amount = null): TutorPayout
    {
        return $this->executeTransaction(function () use ($tutor, $bankInfo, $amount) {
            $earnings = $this->calculateTutorEarnings($tutor);

            if ($earnings['available_earnings'] <= 0) {
                throw new Exception('No earnings available for payout');
            }

            // Use specified amount or all available earnings
            $payoutAmount = $amount ?? $earnings['available_earnings'];

            if ($payoutAmount > $earnings['available_earnings']) {
                throw new Exception('Payout amount exceeds available earnings');
            }

            if ($payoutAmount < 100000) { // Minimum 100,000 VND
                throw new Exception('Minimum payout amount is 100,000 VND');
            }

            // Create payout record
            $payout = TutorPayout::create([
                'tutor_id' => $tutor->id,
                'total_amount' => $payoutAmount,
                'status' => TutorPayout::STATUS_PENDING,
                'bank_account' => $bankInfo['account_number'],
                'bank_name' => $bankInfo['bank_name'],
                'account_holder_name' => $bankInfo['account_holder_name'] ?? $tutor->user->name,
                'requested_at' => now(),
            ]);

            // Select bookings to include in payout (FIFO - oldest first)
            $selectedBookings = $earnings['eligible_bookings']
                ->sortBy('commission_calculated_at')
                ->values();

            $currentAmount = 0;
            $includedBookings = collect();

            foreach ($selectedBookings as $booking) {
                if ($currentAmount + $booking->tutor_earnings <= $payoutAmount) {
                    $includedBookings->push($booking);
                    $currentAmount += $booking->tutor_earnings;
                }

                if ($currentAmount >= $payoutAmount) {
                    break;
                }
            }

            // Create payout items and link bookings
            foreach ($includedBookings as $booking) {
                PayoutItem::create([
                    'payout_id' => $payout->id,
                    'booking_id' => $booking->id,
                    'tutor_earnings' => $booking->tutor_earnings,
                    'platform_fee_amount' => $booking->platform_fee_amount,
                    'booking_total' => $booking->price,
                ]);

                // Mark booking as included in payout
                $booking->update(['payout_id' => $payout->id]);
            }

            Log::info('Payout created successfully', [
                'payout_id' => $payout->id,
                'tutor_id' => $tutor->id,
                'amount' => $payoutAmount,
                'bookings_count' => $includedBookings->count(),
            ]);

            return $payout;
        });
    }

    /**
     * Get tutor payout history.
     */
    public function getTutorPayouts(Tutor $tutor, int $perPage = 10): LengthAwarePaginator
    {
        return $tutor->payouts()
            ->with('payoutItems.booking.subject')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Update payout status (Admin only).
     */
    public function updatePayoutStatus(TutorPayout $payout, string $status, ?string $notes = null): bool
    {
        if (!$payout->canBeProcessed() && $status !== TutorPayout::STATUS_CANCELLED) {
            throw new Exception('Payout cannot be updated in current status');
        }

        try {
            $updates = [
                'status' => $status,
                'admin_notes' => $notes,
            ];

            switch ($status) {
                case TutorPayout::STATUS_PROCESSING:
                    $updates['processed_at'] = now();

                    break;
                case TutorPayout::STATUS_COMPLETED:
                    $updates['completed_at'] = now();
                    if (!$payout->processed_at) {
                        $updates['processed_at'] = now();
                    }

                    break;
                case TutorPayout::STATUS_FAILED:
                    $updates['failure_reason'] = $notes;
                    // Release bookings back to available earnings
                    $this->releasePayout($payout);

                    break;
                case TutorPayout::STATUS_CANCELLED:
                    $this->releasePayout($payout);

                    break;
            }

            $payout->update($updates);

            Log::info('Payout status updated', [
                'payout_id' => $payout->id,
                'status' => $status,
                'notes' => $notes,
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Failed to update payout status', [
                'payout_id' => $payout->id,
                'status' => $status,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Release payout (return bookings to available earnings).
     */
    private function releasePayout(TutorPayout $payout): void
    {
        // Remove payout_id from bookings
        Booking::where('payout_id', $payout->id)
            ->update(['payout_id' => null]);

        // Delete payout items
        $payout->payoutItems()->delete();
    }

    /**
     * Get platform revenue analytics.
     */
    public function getPlatformRevenue(Carbon $startDate, Carbon $endDate): array
    {
        $bookings = Booking::whereBetween('commission_calculated_at', [$startDate, $endDate])
            ->whereNotNull('commission_calculated_at')
            ->get();

        $totalRevenue = $bookings->sum('price');
        $platformFees = $bookings->sum('platform_fee_amount');
        $tutorEarnings = $bookings->sum('tutor_earnings');

        return [
            'total_revenue' => $totalRevenue,
            'platform_fees' => $platformFees,
            'tutor_earnings' => $tutorEarnings,
            'commission_rate' => $totalRevenue > 0 ? ($platformFees / $totalRevenue) * 100 : 0,
            'bookings_count' => $bookings->count(),
        ];
    }
}
