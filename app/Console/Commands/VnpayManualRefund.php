<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Transaction;
use App\Notifications\PaymentRefunded;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class VnpayManualRefund extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'vnpay:refund
                           {action : Action to perform: list, process, complete}
                           {--booking= : Booking ID for process/complete actions}
                           {--txn= : VNPay transaction number}
                           {--amount= : Refund amount}
                           {--reason= : Refund reason}';

    /**
     * The console command description.
     */
    protected $description = 'Manage VNPay manual refunds';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'list':
                $this->listPendingRefunds();

                break;
            case 'process':
                $this->processRefund();

                break;
            case 'complete':
                $this->completeRefund();

                break;
            default:
                $this->error('Invalid action. Use: list, process, or complete');

                return 1;
        }

        return 0;
    }

    /**
     * List all pending refund requests.
     */
    private function listPendingRefunds()
    {
        $this->info('=== PENDING VNPAY REFUNDS ===');

        $pendingRefunds = Transaction::where('payment_method', 'vnpay')
            ->where('type', 'refund')
            ->where('status', 'pending')
            ->with(['booking.student', 'booking.tutor.user'])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($pendingRefunds->isEmpty()) {
            $this->info('Không có yêu cầu hoàn tiền nào đang chờ xử lý.');

            return;
        }

        $headers = ['ID', 'Booking', 'Student', 'Amount', 'Currency', 'Created', 'Original TxnRef'];
        $rows = [];

        foreach ($pendingRefunds as $refund) {
            $rows[] = [
                $refund->id,
                $refund->booking_id,
                $refund->booking->student->name ?? 'N/A',
                number_format($refund->amount, 0, ',', '.'),
                $refund->currency,
                $refund->created_at->format('d/m/Y H:i'),
                $refund->metadata['original_txn_ref'] ?? 'N/A',
            ];
        }

        $this->table($headers, $rows);

        $this->info("\nSử dụng 'php artisan vnpay:refund process --booking=X' để xử lý hoàn tiền");
    }

    /**
     * Start refund process - generate info for VNPay portal.
     */
    private function processRefund()
    {
        $bookingId = $this->option('booking');

        if (!$bookingId) {
            $this->error('Vui lòng cung cấp booking ID: --booking=X');

            return;
        }

        $booking = Booking::find($bookingId);
        if (!$booking) {
            $this->error("Booking #{$bookingId} không tồn tại.");

            return;
        }

        $refundTransaction = Transaction::where('booking_id', $bookingId)
            ->where('payment_method', 'vnpay')
            ->where('type', 'refund')
            ->where('status', 'pending')
            ->first();

        if (!$refundTransaction) {
            $this->error("Không tìm thấy yêu cầu hoàn tiền cho booking #{$bookingId}");

            return;
        }

        $originalTransaction = Transaction::where('booking_id', $bookingId)
            ->where('payment_method', 'vnpay')
            ->where('type', 'payment')
            ->where('status', 'completed')
            ->first();

        $this->info('=== THÔNG TIN HOÀN TIỀN VNPAY ===');
        $this->line('');
        $this->info("📋 Booking ID: #{$booking->id}");
        $this->info("👤 Học viên: {$booking->student->name}");
        $this->info("📧 Email: {$booking->student->email}");
        $this->info('💰 Số tiền hoàn: ' . number_format($refundTransaction->amount, 0, ',', '.') . " {$refundTransaction->currency}");
        $this->info("📅 Ngày yêu cầu: {$refundTransaction->created_at->format('d/m/Y H:i')}");
        $this->line('');

        if ($originalTransaction) {
            $this->info('--- THÔNG TIN GIAO DỊCH GỐC ---');
            $this->info("🏦 VNPay TxnRef: {$booking->vnpay_txn_ref}");
            $this->info("🔢 VNPay Transaction No: {$originalTransaction->gateway_transaction_id}");
            $this->info('💳 Bank Code: ' . ($originalTransaction->metadata['vnpay_bank_code'] ?? 'N/A'));
            $this->info('🃏 Card Type: ' . ($originalTransaction->metadata['vnpay_card_type'] ?? 'N/A'));
            $this->info("📅 Thanh toán lúc: {$originalTransaction->processed_at->format('d/m/Y H:i:s')}");
        }

        $this->line('');
        $this->info('=== HƯỚNG DẪN HOÀN TIỀN ===');
        $this->line('1. Đăng nhập VNPay Merchant Portal');
        $this->line('2. Vào mục "Quản lý giao dịch" > "Hoàn tiền"');
        $this->line('3. Tìm giao dịch theo mã: ' . ($originalTransaction->gateway_transaction_id ?? $booking->vnpay_txn_ref));
        $this->line('4. Nhập số tiền hoàn: ' . number_format($refundTransaction->amount, 0, ',', '.') . ' VND');
        $this->line('5. Lý do hoàn tiền: ' . ($refundTransaction->metadata['refund_reason'] ?? 'Customer request'));
        $this->line('6. Sau khi hoàn tiền thành công, chạy:');
        $this->warn("   php artisan vnpay:refund complete --booking={$bookingId} --txn=VNPAY_REFUND_TXN_ID");

        // Update transaction to processing
        $refundTransaction->update([
            'status' => 'processing',
            'metadata' => array_merge($refundTransaction->metadata ?? [], [
                'processing_started_at' => Carbon::now(),
                'admin_notes' => 'Refund information generated for manual processing',
            ]),
        ]);

        $this->info("\n✅ Đã cập nhật trạng thái thành 'processing'");
    }

    /**
     * Complete refund after manual processing in VNPay portal.
     */
    private function completeRefund()
    {
        $bookingId = $this->option('booking');
        $vnpayTxnId = $this->option('txn');

        if (!$bookingId) {
            $this->error('Vui lòng cung cấp booking ID: --booking=X');

            return;
        }

        if (!$vnpayTxnId) {
            $this->error('Vui lòng cung cấp VNPay transaction ID: --txn=XXX');

            return;
        }

        $booking = Booking::find($bookingId);
        if (!$booking) {
            $this->error("Booking #{$bookingId} không tồn tại.");

            return;
        }

        $refundTransaction = Transaction::where('booking_id', $bookingId)
            ->where('payment_method', 'vnpay')
            ->where('type', 'refund')
            ->whereIn('status', ['pending', 'processing'])
            ->first();

        if (!$refundTransaction) {
            $this->error("Không tìm thấy yêu cầu hoàn tiền cho booking #{$bookingId}");

            return;
        }

        $this->info("Hoàn tất hoàn tiền cho booking #{$bookingId}");
        $this->info('Số tiền: ' . number_format($refundTransaction->amount, 0, ',', '.') . " {$refundTransaction->currency}");
        $this->info("VNPay Transaction ID: {$vnpayTxnId}");

        if (!$this->confirm('Xác nhận hoàn tiền đã được xử lý thành công trên VNPay?')) {
            $this->info('Đã hủy.');

            return;
        }

        // Update refund transaction
        $refundTransaction->update([
            'status' => 'completed',
            'gateway_transaction_id' => $vnpayTxnId,
            'processed_at' => Carbon::now(),
            'metadata' => array_merge($refundTransaction->metadata ?? [], [
                'vnpay_refund_txn_id' => $vnpayTxnId,
                'completed_at' => Carbon::now(),
                'admin_completed' => true,
            ]),
        ]);

        // Update booking status
        $booking->update([
            'payment_status' => 'refunded',
        ]);

        // Send notification to student
        try {
            $booking->student->notify(new PaymentRefunded($booking, 'VNPay refund processed successfully'));
            $this->info('✅ Đã gửi thông báo cho học viên');
        } catch (\Exception $e) {
            $this->warn('⚠️ Không thể gửi thông báo: ' . $e->getMessage());
        }

        // Log activity
        Log::info('VNPay manual refund completed', [
            'booking_id' => $booking->id,
            'refund_transaction_id' => $refundTransaction->id,
            'vnpay_txn_id' => $vnpayTxnId,
            'amount' => $refundTransaction->amount,
            'admin_user' => 'CLI',
        ]);

        $this->info('✅ Hoàn tiền đã được hoàn tất thành công!');
        $this->line('');
        $this->info('📧 Học viên sẽ nhận được thông báo qua email');
        $this->info('💰 Tiền sẽ được hoàn về tài khoản trong 1-3 ngày làm việc');
    }
}
