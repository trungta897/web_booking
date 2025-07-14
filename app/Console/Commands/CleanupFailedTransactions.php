<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use Illuminate\Console\Command;

class CleanupFailedTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'booking:cleanup-failed-transactions {--booking= : Specific booking ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup failed transactions with incorrect pricing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $bookingId = $this->option('booking');
        
        $query = Transaction::where('status', 'failed')
            ->where('amount', '>', 1000000); // Transactions with suspiciously high amounts
            
        if ($bookingId) {
            $query->where('booking_id', $bookingId);
            $this->info("🔍 Cleaning up failed transactions for booking #{$bookingId}");
        } else {
            $this->info("🔍 Cleaning up all failed transactions with incorrect pricing");
        }
        
        $failedTransactions = $query->get();
        
        if ($failedTransactions->isEmpty()) {
            $this->info('✅ No failed transactions found to cleanup');
            return 0;
        }
        
        $this->warn("Found {$failedTransactions->count()} failed transactions to cleanup:");
        
        foreach ($failedTransactions as $tx) {
            $this->line("  - Transaction #{$tx->id} (Booking #{$tx->booking_id}) - Amount: " . number_format($tx->amount, 0, ',', '.') . " VND");
        }
        
        if (!$this->confirm('Delete these failed transactions?')) {
            $this->info('❌ Cancelled');
            return 0;
        }
        
        $deleted = 0;
        foreach ($failedTransactions as $tx) {
            $tx->delete();
            $deleted++;
        }
        
        $this->info("✅ Deleted {$deleted} failed transactions successfully!");
        
        return 0;
    }
}
