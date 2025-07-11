<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Services\PayoutService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ApplyCommissionToExistingBookings extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'commission:apply-existing {--dry-run : Run without making changes} {--limit=100 : Number of bookings to process}';

    /**
     * The console command description.
     */
    protected $description = 'Apply commission calculation to existing bookings that don\'t have commission calculated yet';

    protected PayoutService $payoutService;

    public function __construct(PayoutService $payoutService)
    {
        parent::__construct();
        $this->payoutService = $payoutService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $limit = (int) $this->option('limit');

        $this->info('🔍 Searching for bookings without commission calculation...');

        // Find paid bookings without commission calculated
        $bookings = Booking::with('tutor')
            ->where('payment_status', Booking::PAYMENT_STATUS_PAID)
            ->whereNull('commission_calculated_at')
            ->limit($limit)
            ->get();

        if ($bookings->isEmpty()) {
            $this->info('✅ No bookings found that need commission calculation.');
            return 0;
        }

        $this->info("📊 Found {$bookings->count()} bookings to process.");

        if ($dryRun) {
            $this->warn('🧪 DRY RUN MODE - No changes will be made');
        }

        $processedCount = 0;
        $errorCount = 0;

        foreach ($bookings as $booking) {
            try {
                $commission = $this->payoutService->calculateCommission($booking);

                $this->line(sprintf(
                    '📝 Booking #%d: %s VND → Platform: %s VND (%.1f%%), Tutor: %s VND',
                    $booking->id,
                    number_format($booking->price, 0, ',', '.'),
                    number_format($commission['platform_fee_amount'], 0, ',', '.'),
                    $commission['platform_fee_percentage'],
                    number_format($commission['tutor_earnings'], 0, ',', '.')
                ));

                if (!$dryRun) {
                    $this->payoutService->applyCommissionToBooking($booking);
                    $processedCount++;
                }
            } catch (\Exception $e) {
                $this->error("❌ Error processing booking #{$booking->id}: {$e->getMessage()}");
                $errorCount++;
            }
        }

        $this->newLine();

        if ($dryRun) {
            $this->info("🧪 DRY RUN COMPLETE: Would process {$bookings->count()} bookings");
        } else {
            $this->info("✅ COMPLETE: Processed {$processedCount} bookings successfully");
            if ($errorCount > 0) {
                $this->warn("⚠️  {$errorCount} bookings had errors");
            }
        }

        // Show summary statistics
        if (!$dryRun && $processedCount > 0) {
            $this->showSummary();
        }

        return $errorCount > 0 ? 1 : 0;
    }

    private function showSummary(): void
    {
        $this->newLine();
        $this->info('📈 COMMISSION SUMMARY:');

        $stats = DB::select('
            SELECT
                COUNT(*) as total_bookings,
                SUM(price) as total_revenue,
                SUM(platform_fee_amount) as total_platform_fees,
                SUM(tutor_earnings) as total_tutor_earnings,
                AVG(platform_fee_percentage) as avg_commission_rate
            FROM bookings
            WHERE commission_calculated_at IS NOT NULL
        ')[0];

        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Bookings with Commission', number_format($stats->total_bookings)],
                ['Total Revenue', number_format($stats->total_revenue, 0, ',', '.') . ' VND'],
                ['Platform Fees', number_format($stats->total_platform_fees, 0, ',', '.') . ' VND'],
                ['Tutor Earnings', number_format($stats->total_tutor_earnings, 0, ',', '.') . ' VND'],
                ['Average Commission Rate', number_format($stats->avg_commission_rate, 1) . '%'],
            ]
        );
    }
}
