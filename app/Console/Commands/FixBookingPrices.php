<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FixBookingPrices extends Command
{
    protected $signature = 'booking:fix-prices {--dry-run : Show what would be fixed without making changes}';
    
    protected $description = 'Fix booking prices that were calculated incorrectly due to currency conversion bugs';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        $this->info('🔍 Scanning for bookings with incorrect prices...');
        
        // Find bookings with suspiciously low prices (likely affected by currency bug)
        $suspiciousBookings = Booking::with(['tutor'])
            ->where('price', '<', 1000) // Any booking less than 1000 VND is suspicious
            ->whereNotNull('start_time')
            ->whereNotNull('end_time')
            ->get();
            
        if ($suspiciousBookings->isEmpty()) {
            $this->info('✅ No suspicious bookings found!');
            return 0;
        }
        
        $this->warn("🚨 Found {$suspiciousBookings->count()} bookings with suspicious prices:");
        
        $fixes = [];
        foreach ($suspiciousBookings as $booking) {
            // Calculate duration from start_time and end_time
            $start = Carbon::parse($booking->start_time);
            $end = Carbon::parse($booking->end_time);
            $durationMinutes = $start->diffInMinutes($end);
            $hours = $durationMinutes / 60.0;
            
            $correctPrice = $hours * $booking->tutor->hourly_rate;
            
            if (abs($booking->price - $correctPrice) > 1) { // Significant difference
                $fixes[] = [
                    'booking_id' => $booking->id,
                    'current_price' => $booking->price,
                    'correct_price' => $correctPrice,
                    'hourly_rate' => $booking->tutor->hourly_rate,
                    'duration_hours' => $hours,
                    'duration_minutes' => $durationMinutes,
                ];
            }
        }
        
        if (empty($fixes)) {
            $this->info('✅ All prices are already correct!');
            return 0;
        }
        
        // Show the fixes
        $this->table(
            ['Booking ID', 'Current Price', 'Correct Price', 'Hourly Rate', 'Duration (min)', 'Difference'],
            array_map(function($fix) {
                return [
                    $fix['booking_id'],
                    number_format($fix['current_price'], 0, ',', '.') . ' VND',
                    number_format($fix['correct_price'], 0, ',', '.') . ' VND',
                    number_format($fix['hourly_rate'], 0, ',', '.') . ' VND/h',
                    $fix['duration_minutes'] . ' min',
                    number_format($fix['correct_price'] - $fix['current_price'], 0, ',', '.') . ' VND',
                ];
            }, $fixes)
        );
        
        if ($dryRun) {
            $this->warn('🧪 DRY RUN: No changes were made. Remove --dry-run to apply fixes.');
            return 0;
        }
        
        if (!$this->confirm('Apply these price fixes?')) {
            $this->info('❌ Cancelled.');
            return 0;
        }
        
        // Apply fixes
        DB::beginTransaction();
        try {
            $fixed = 0;
            foreach ($fixes as $fix) {
                Booking::where('id', $fix['booking_id'])
                    ->update(['price' => $fix['correct_price']]);
                $fixed++;
                
                $this->info("✅ Fixed booking #{$fix['booking_id']}: {$fix['current_price']} → " . number_format($fix['correct_price'], 0, ',', '.') . " VND");
            }
            
            DB::commit();
            $this->info("🎉 Fixed {$fixed} booking prices successfully!");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Error fixing prices: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}