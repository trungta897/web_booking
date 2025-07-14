<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixDuplicateVnpayTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vnpay:fix-duplicates {--dry-run : Show what would be fixed without actually making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix duplicate VNPay transactions and inconsistent payment statuses';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        $this->info('🔍 Scanning for VNPay transaction issues...');

        if ($isDryRun) {
            $this->warn('🧪 DRY RUN MODE - No changes will be made');
        }

        $this->line('');

        // 1. Find bookings with multiple completed transactions
        $this->checkDuplicateCompletedTransactions($isDryRun);

        // 2. Find bookings marked as paid but no completed transaction
        $this->checkInconsistentPaymentStatus($isDryRun);

        // 3. Find completed transactions but booking not marked as paid
        $this->checkUnmarkedPaidBookings($isDryRun);

        // 4. Clean up old pending transactions
        $this->cleanupOldPendingTransactions($isDryRun);

        $this->line('');
        $this->info('✅ Scan completed!');

        if ($isDryRun) {
            $this->warn('To apply fixes, run: php artisan vnpay:fix-duplicates');
        }

        return 0;
    }

    /**
     * Check for bookings with multiple completed transactions
     */
    private function checkDuplicateCompletedTransactions(bool $isDryRun): void
    {
        $this->info('1️⃣ Checking for duplicate completed transactions...');

        $duplicates = DB::table('transactions')
            ->select('booking_id', DB::raw('COUNT(*) as count'))
            ->where('payment_method', 'vnpay')
            ->where('type', 'payment')
            ->where('status', 'completed')
            ->groupBy('booking_id')
            ->having('count', '>', 1)
            ->get();

        if ($duplicates->isEmpty()) {
            $this->line('   ✅ No duplicate completed transactions found');
            return;
        }

        $this->warn("   ⚠️ Found {$duplicates->count()} bookings with duplicate completed transactions");

        foreach ($duplicates as $duplicate) {
            $bookingId = $duplicate->booking_id;
            $count = $duplicate->count;

            $transactions = Transaction::where('booking_id', $bookingId)
                ->where('payment_method', 'vnpay')
                ->where('type', 'payment')
                ->where('status', 'completed')
                ->orderBy('processed_at', 'asc')
                ->get();

            $booking = Booking::find($bookingId);

            $this->line("   📋 Booking #{$bookingId}: {$count} completed transactions");

            // Keep the first transaction, mark others as duplicate
            $keepTransaction = $transactions->first();
            $duplicateTransactions = $transactions->skip(1);

            foreach ($duplicateTransactions as $txn) {
                $this->line("      🔄 Transaction #{$txn->id} ({$txn->transaction_id}) - DUPLICATE");

                if (!$isDryRun) {
                    $txn->update([
                        'status' => 'failed',
                        'metadata' => array_merge($txn->metadata ?? [], [
                            'marked_as_duplicate' => true,
                            'original_transaction_id' => $keepTransaction->id,
                            'fixed_at' => now()->toISOString(),
                        ])
                    ]);

                    Log::info('Marked duplicate VNPay transaction as failed', [
                        'transaction_id' => $txn->id,
                        'booking_id' => $bookingId,
                        'original_transaction_id' => $keepTransaction->id,
                    ]);
                }
            }

            $this->line("      ✅ Kept Transaction #{$keepTransaction->id} ({$keepTransaction->transaction_id})");
        }
    }

    /**
     * Check for bookings marked as paid but no completed transaction
     */
    private function checkInconsistentPaymentStatus(bool $isDryRun): void
    {
        $this->info('2️⃣ Checking for inconsistent payment statuses...');

        $inconsistentBookings = Booking::where('payment_status', 'paid')
            ->whereDoesntHave('transactions', function($query) {
                $query->where('type', 'payment')
                      ->where('status', 'completed');
            })
            ->get();

        if ($inconsistentBookings->isEmpty()) {
            $this->line('   ✅ No inconsistent payment statuses found');
            return;
        }

        $this->warn("   ⚠️ Found {$inconsistentBookings->count()} bookings marked as paid without completed transactions");

        foreach ($inconsistentBookings as $booking) {
            $this->line("   📋 Booking #{$booking->id} - marked as paid but no completed transaction");

            if (!$isDryRun) {
                $booking->update(['payment_status' => 'pending']);

                Log::info('Reset payment status for booking without completed transaction', [
                    'booking_id' => $booking->id,
                    'old_status' => 'paid',
                    'new_status' => 'pending',
                ]);
            }
        }
    }

    /**
     * Check for completed transactions but booking not marked as paid
     */
    private function checkUnmarkedPaidBookings(bool $isDryRun): void
    {
        $this->info('3️⃣ Checking for unmarked paid bookings...');

        $unmarkedBookings = Booking::where('payment_status', '!=', 'paid')
            ->whereHas('transactions', function($query) {
                $query->where('type', 'payment')
                      ->where('status', 'completed');
            })
            ->get();

        if ($unmarkedBookings->isEmpty()) {
            $this->line('   ✅ No unmarked paid bookings found');
            return;
        }

        $this->warn("   ⚠️ Found {$unmarkedBookings->count()} bookings with completed transactions but not marked as paid");

        foreach ($unmarkedBookings as $booking) {
            $completedTransaction = $booking->transactions()
                ->where('type', 'payment')
                ->where('status', 'completed')
                ->first();

            $this->line("   📋 Booking #{$booking->id} - has completed transaction but payment_status is '{$booking->payment_status}'");

            if (!$isDryRun) {
                $booking->update([
                    'payment_status' => 'paid',
                    'payment_method' => $completedTransaction->payment_method,
                    'payment_at' => $completedTransaction->processed_at,
                ]);

                Log::info('Updated payment status for booking with completed transaction', [
                    'booking_id' => $booking->id,
                    'old_status' => $booking->payment_status,
                    'new_status' => 'paid',
                    'transaction_id' => $completedTransaction->id,
                ]);
            }
        }
    }

    /**
     * Clean up old pending transactions
     */
    private function cleanupOldPendingTransactions(bool $isDryRun): void
    {
        $this->info('4️⃣ Cleaning up old pending transactions...');

        $oldPending = Transaction::where('payment_method', 'vnpay')
            ->where('type', 'payment')
            ->where('status', 'pending')
            ->where('created_at', '<', now()->subHours(2))
            ->get();

        if ($oldPending->isEmpty()) {
            $this->line('   ✅ No old pending transactions found');
            return;
        }

        $this->warn("   ⚠️ Found {$oldPending->count()} old pending transactions (>2 hours)");

        foreach ($oldPending as $transaction) {
            $this->line("   🕐 Transaction #{$transaction->id} (Booking #{$transaction->booking_id}) - created {$transaction->created_at->diffForHumans()}");

            if (!$isDryRun) {
                $transaction->update([
                    'status' => 'failed',
                    'metadata' => array_merge($transaction->metadata ?? [], [
                        'failure_reason' => 'timeout_cleanup',
                        'cleaned_up_at' => now()->toISOString(),
                    ])
                ]);

                Log::info('Marked old pending transaction as failed', [
                    'transaction_id' => $transaction->id,
                    'booking_id' => $transaction->booking_id,
                    'age_hours' => $transaction->created_at->diffInHours(now()),
                ]);
            }
        }
    }
}
