<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Models\Booking;
use App\Notifications\PaymentRefunded;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class CleanupStaleRefunds extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'refunds:cleanup
                           {--dry-run : Show what would be cleaned up without actually doing it}
                           {--send-reminders : Send reminder notifications for stale refunds}
                           {--force : Force cleanup without confirmation}';

    /**
     * The console command description.
     */
    protected $description = 'Cleanup stale refund transactions and send reminders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $sendReminders = $this->option('send-reminders');
        $force = $this->option('force');

        $this->info('🧹 Starting refund cleanup process...');
        $this->newLine();

        // Get stale refunds (older than 7 days in pending status)
        $staleRefunds = $this->getStaleRefunds();

        // Get processing refunds that need reminders (older than 24 hours)
        $processingRefunds = $this->getProcessingRefunds();

        $this->displaySummary($staleRefunds, $processingRefunds);

        if ($staleRefunds->isEmpty() && $processingRefunds->isEmpty()) {
            $this->info('✅ No stale refunds found. Everything looks good!');
            return 0;
        }

        // Send reminders for processing refunds
        if ($sendReminders && $processingRefunds->isNotEmpty()) {
            $this->sendReminders($processingRefunds, $dryRun);
        }

        // Cleanup stale refunds
        if ($staleRefunds->isNotEmpty()) {
            if (!$force && !$this->confirm('Do you want to proceed with cleanup?')) {
                $this->info('Cleanup cancelled.');
                return 0;
            }

            $this->cleanupStaleRefunds($staleRefunds, $dryRun);
        }

        $this->newLine();
        $this->info('✅ Refund cleanup completed successfully!');

        return 0;
    }

    /**
     * Get refunds that have been pending for too long
     */
    private function getStaleRefunds()
    {
        return Transaction::whereIn('type', [Transaction::TYPE_REFUND, Transaction::TYPE_PARTIAL_REFUND])
            ->where('status', Transaction::STATUS_PENDING)
            ->where('created_at', '<', Carbon::now()->subDays(7))
            ->with(['booking.student', 'booking.tutor.user'])
            ->get();
    }

    /**
     * Get refunds that are processing and might need reminders
     */
    private function getProcessingRefunds()
    {
        return Transaction::whereIn('type', [Transaction::TYPE_REFUND, Transaction::TYPE_PARTIAL_REFUND])
            ->where('status', Transaction::STATUS_PROCESSING)
            ->where('created_at', '<', Carbon::now()->subHours(24))
            ->with(['booking.student', 'booking.tutor.user'])
            ->get();
    }

    /**
     * Display summary of what will be processed
     */
    private function displaySummary($staleRefunds, $processingRefunds)
    {
        $this->table(
            ['Type', 'Count', 'Total Amount', 'Description'],
            [
                [
                    'Stale Pending',
                    $staleRefunds->count(),
                    'VND ' . number_format(abs($staleRefunds->sum('amount')), 0, ',', '.'),
                    'Pending > 7 days (will be cancelled)'
                ],
                [
                    'Processing Long',
                    $processingRefunds->count(),
                    'VND ' . number_format(abs($processingRefunds->sum('amount')), 0, ',', '.'),
                    'Processing > 24h (reminder eligible)'
                ]
            ]
        );
        $this->newLine();
    }

    /**
     * Send reminder notifications for processing refunds
     */
    private function sendReminders($processingRefunds, $dryRun)
    {
        $this->info('📧 Sending reminders for processing refunds...');

        foreach ($processingRefunds as $refund) {
            if ($dryRun) {
                $this->line("  [DRY RUN] Would send reminder for refund #{$refund->id} (Booking #{$refund->booking_id})");
                continue;
            }

            try {
                // Send email to admin (or notify via Slack, etc.)
                Log::warning('Refund processing reminder', [
                    'refund_id' => $refund->id,
                    'booking_id' => $refund->booking_id,
                    'amount' => $refund->amount,
                    'processing_since' => $refund->updated_at,
                    'student' => $refund->booking->student->name,
                    'action_required' => 'Complete refund processing in VNPay portal'
                ]);

                // Update metadata to track reminder sent
                $metadata = $refund->metadata ?? [];
                $metadata['reminder_sent_at'] = Carbon::now();
                $metadata['reminder_count'] = ($metadata['reminder_count'] ?? 0) + 1;

                $refund->update(['metadata' => $metadata]);

                $this->line("  ✅ Reminder sent for refund #{$refund->id}");

            } catch (\Exception $e) {
                $this->error("  ❌ Failed to send reminder for refund #{$refund->id}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Cleanup stale refunds by cancelling them
     */
    private function cleanupStaleRefunds($staleRefunds, $dryRun)
    {
        $this->info('🗑️  Cleaning up stale refunds...');

        foreach ($staleRefunds as $refund) {
            if ($dryRun) {
                $this->line("  [DRY RUN] Would cancel refund #{$refund->id} (Booking #{$refund->booking_id})");
                continue;
            }

            try {
                // Cancel the refund transaction
                $refund->update([
                    'status' => Transaction::STATUS_CANCELLED,
                    'metadata' => array_merge($refund->metadata ?? [], [
                        'cancelled_reason' => 'Stale refund - auto cleanup',
                        'cancelled_at' => Carbon::now(),
                        'auto_cancelled' => true
                    ])
                ]);

                // Reset booking payment status if this was the only refund
                $booking = $refund->booking;
                $hasOtherRefunds = Transaction::where('booking_id', $booking->id)
                    ->whereIn('type', [Transaction::TYPE_REFUND, Transaction::TYPE_PARTIAL_REFUND])
                    ->where('status', Transaction::STATUS_COMPLETED)
                    ->exists();

                if (!$hasOtherRefunds) {
                    $booking->update(['payment_status' => 'paid']);
                }

                Log::info('Stale refund cleaned up', [
                    'refund_id' => $refund->id,
                    'booking_id' => $refund->booking_id,
                    'amount' => $refund->amount,
                    'pending_since' => $refund->created_at
                ]);

                $this->line("  ✅ Cancelled stale refund #{$refund->id}");

            } catch (\Exception $e) {
                $this->error("  ❌ Failed to cleanup refund #{$refund->id}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Show detailed list of refunds
     */
    private function showRefundDetails($refunds, $title)
    {
        if ($refunds->isEmpty()) {
            return;
        }

        $this->info($title);

        $tableData = [];
        foreach ($refunds as $refund) {
            $tableData[] = [
                $refund->id,
                $refund->booking_id,
                $refund->booking->student->name,
                'VND ' . number_format(abs($refund->amount), 0, ',', '.'),
                $refund->status,
                $refund->created_at->format('d/m/Y H:i'),
                $refund->created_at->diffForHumans()
            ];
        }

        $this->table(
            ['ID', 'Booking', 'Student', 'Amount', 'Status', 'Created', 'Age'],
            $tableData
        );
    }
}
