<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Services\LogService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class FixNegativeBookingPrices extends Command
{
    protected $signature = 'booking:fix-negative-prices
                            {--dry-run : Show what would be fixed without making changes}
                            {--force : Skip confirmation prompt}';

    protected $description = 'Fix bookings with negative prices by recalculating based on duration and tutor rate';

    public function handle(): int
    {
        $this->info('🔧 Fixing Negative Booking Prices');
        $this->newLine();

        // Find bookings with negative prices
        $negativeBookings = Booking::with(['tutor'])
            ->where('price', '<', 0)
            ->get();

        if ($negativeBookings->isEmpty()) {
            $this->info('✅ No bookings with negative prices found!');
            return self::SUCCESS;
        }

        $this->info("Found {$negativeBookings->count()} bookings with negative prices:");
        $this->newLine();

        // Display affected bookings
        $tableData = [];
        foreach ($negativeBookings as $booking) {
            $tableData[] = [
                $booking->id,
                $booking->tutor?->user?->name ?? 'Unknown',
                $booking->start_time?->format('Y-m-d H:i') ?? 'N/A',
                $booking->end_time?->format('Y-m-d H:i') ?? 'N/A',
                number_format($booking->price, 2),
                $booking->tutor?->hourly_rate ?? 'N/A',
            ];
        }

        $this->table([
            'ID',
            'Tutor',
            'Start Time',
            'End Time',
            'Current Price',
            'Hourly Rate'
        ], $tableData);

        if ($this->option('dry-run')) {
            $this->info('📋 DRY RUN - No changes will be made');
            $this->showWhatWouldBeFixed($negativeBookings);
            return self::SUCCESS;
        }

        // Confirm action
        if (!$this->option('force')) {
            $this->newLine();
            if (!$this->confirm('Do you want to fix these bookings?')) {
                $this->info('🚫 Operation cancelled');
                return self::SUCCESS;
            }
        }

        // Fix the bookings
        $fixed = 0;
        $failed = 0;

        foreach ($negativeBookings as $booking) {
            try {
                $newPrice = $this->calculateCorrectPrice($booking);

                if ($newPrice !== null && $newPrice > 0) {
                    $oldPrice = $booking->price;
                    $booking->update(['price' => $newPrice]);

                    $this->line("✅ Fixed booking #{$booking->id}: {$oldPrice} → {$newPrice}");

                    LogService::database('Fixed negative booking price', [
                        'booking_id' => $booking->id,
                        'old_price' => $oldPrice,
                        'new_price' => $newPrice,
                    ]);

                    $fixed++;
                } else {
                    $this->error("❌ Could not fix booking #{$booking->id}: Invalid data");
                    $failed++;
                }
            } catch (\Exception $e) {
                $this->error("❌ Error fixing booking #{$booking->id}: " . $e->getMessage());
                $failed++;
            }
        }

        $this->newLine();
        $this->info("🎉 Fixed {$fixed} bookings");
        if ($failed > 0) {
            $this->warn("⚠️  {$failed} bookings could not be fixed");
        }

        return self::SUCCESS;
    }

    private function showWhatWouldBeFixed($bookings): void
    {
        $this->newLine();
        $this->info('📋 What would be fixed:');
        $this->newLine();

        foreach ($bookings as $booking) {
            $newPrice = $this->calculateCorrectPrice($booking);
            $status = $newPrice !== null && $newPrice > 0 ? '✅' : '❌';

            $this->line("{$status} Booking #{$booking->id}: {$booking->price} → " .
                       ($newPrice !== null ? number_format($newPrice, 2) : 'Cannot calculate'));
        }
    }

    private function calculateCorrectPrice(Booking $booking): ?float
    {
        try {
            // Check if we have all required data
            if (!$booking->start_time || !$booking->end_time || !$booking->tutor?->hourly_rate) {
                return null;
            }

            $startTime = Carbon::parse($booking->start_time);
            $endTime = Carbon::parse($booking->end_time);

            // Ensure end time is after start time
            if ($endTime->lte($startTime)) {
                return null;
            }

            // Calculate duration in hours (using correct order)
            $durationInMinutes = $startTime->diffInMinutes($endTime);
            $hours = $durationInMinutes / 60;

            // Calculate price
            $price = $hours * $booking->tutor->hourly_rate;

            return $price > 0 ? round($price, 2) : null;

        } catch (\Exception $e) {
            return null;
        }
    }
}
