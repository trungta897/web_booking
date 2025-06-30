<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Transaction;
use Illuminate\Console\Command;

class ResetBookingPayment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'booking:reset-payment {booking_id : The booking ID to reset payment for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset payment status for a booking to allow fresh payment attempt';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $bookingId = $this->argument('booking_id');

        $booking = Booking::find($bookingId);

        if (!$booking) {
            $this->error("Booking #{$bookingId} not found.");
            return 1;
        }

        $this->info("=== BOOKING #{$bookingId} PAYMENT RESET ===");
        $this->info("Current Status: {$booking->status}");
        $this->info("Payment Status: {$booking->payment_status}");
        $this->info("Payment Method: " . ($booking->payment_method ?? 'null'));
        $this->info("VNPay TxnRef: " . ($booking->vnpay_txn_ref ?? 'null'));

        // Check if booking can be reset
        if ($booking->status !== 'accepted') {
            $this->error("Cannot reset payment: Booking status is not 'accepted'");
            return 1;
        }

        if ($booking->payment_status === 'paid' || $booking->isFullyPaid()) {
            $this->error("Cannot reset payment: Booking is already paid");
            return 1;
        }

        if (!$this->confirm("Are you sure you want to reset payment for booking #{$bookingId}?")) {
            $this->info("Operation cancelled.");
            return 0;
        }

        // Clean up pending transactions
        $pendingTransactions = $booking->transactions()
            ->where('type', 'payment')
            ->where('status', 'pending')
            ->get();

        if ($pendingTransactions->count() > 0) {
            $this->info("Deleting {$pendingTransactions->count()} pending transaction(s)...");
            foreach ($pendingTransactions as $tx) {
                $this->line("  - Deleting transaction #{$tx->id}");
                $tx->delete();
            }
        }

        // Reset payment fields
        $booking->update([
            'vnpay_txn_ref' => null,
            'payment_metadata' => [],
        ]);

        $this->info("✅ Payment reset completed!");
        $this->info("User can now make a fresh payment attempt.");

        return 0;
    }
}
