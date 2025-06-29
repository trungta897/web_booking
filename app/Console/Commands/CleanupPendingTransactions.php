<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupPendingTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-pending-transactions {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up orphaned pending transactions for bookings that are already paid.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('--- DRY RUN MODE ---');
            $this->info('No changes will be made to the database.');
        }

        $this->info('Starting cleanup of orphaned pending transactions...');

        // Find bookings that are 'paid' but still have 'pending' transactions.
        // This indicates a likely orphaned transaction from the old logic.
        $paidBookingsWithPendingTx = Booking::where('payment_status', 'paid')
            ->whereHas('transactions', function ($query) {
                $query->where('status', Transaction::STATUS_PENDING);
            })
            ->with('transactions')
            ->get();

        if ($paidBookingsWithPendingTx->isEmpty()) {
            $this->info('No paid bookings with pending transactions found. Database is clean.');
            return 0;
        }

        $this->info(sprintf('Found %d paid bookings with pending transactions.', count($paidBookingsWithPendingTx)));
        $deletedCount = 0;

        foreach ($paidBookingsWithPendingTx as $booking) {
            // We only want to delete a pending transaction if a completed one ALSO exists.
            $hasCompletedTransaction = $booking->transactions->contains('status', Transaction::STATUS_COMPLETED);
            $pendingTransactions = $booking->transactions->where('status', Transaction::STATUS_PENDING);

            if ($hasCompletedTransaction && $pendingTransactions->isNotEmpty()) {
                $this->warn(sprintf('Booking #%d has %d completed and %d pending transaction(s).', $booking->id, $booking->transactions->where('status', Transaction::STATUS_COMPLETED)->count(), $pendingTransactions->count()));

                foreach ($pendingTransactions as $tx) {
                    $this->line(sprintf('  - Deleting pending transaction #%d (Ref: %s)', $tx->id, $tx->transaction_id));
                    if (!$isDryRun) {
                        $tx->delete();
                    }
                    $deletedCount++;
                }
            }
        }

        if ($deletedCount > 0) {
            if ($isDryRun) {
                $this->info(sprintf('Dry run complete. Would have deleted %d orphaned pending transaction(s).', $deletedCount));
            } else {
                $this->info(sprintf('Cleanup complete. Deleted %d orphaned pending transaction(s).', $deletedCount));
            }
        } else {
            $this->info('Cleanup complete. No transactions needed to be deleted.');
        }

        return 0;
    }
}
