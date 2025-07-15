<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // 🎯 THÊM CÁC BOOLEAN FIELDS MỚI (chỉ nếu chưa có)
            if (!Schema::hasColumn('bookings', 'is_confirmed')) {
                $table->boolean('is_confirmed')->default(false)->after('end_time');
            }
            if (!Schema::hasColumn('bookings', 'is_cancelled')) {
                $table->boolean('is_cancelled')->default(false)->after('is_confirmed');
            }
            if (!Schema::hasColumn('bookings', 'is_completed')) {
                $table->boolean('is_completed')->default(false)->after('is_cancelled');
            }

            // Thêm các field cần thiết khác nếu chưa có
            if (!Schema::hasColumn('bookings', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('meeting_link');
            }
            if (!Schema::hasColumn('bookings', 'vnpay_txn_ref')) {
                $table->string('vnpay_txn_ref')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('bookings', 'payment_intent_id')) {
                $table->string('payment_intent_id')->nullable()->after('vnpay_txn_ref');
            }
            if (!Schema::hasColumn('bookings', 'payment_at')) {
                $table->datetime('payment_at')->nullable()->after('payment_intent_id');
            }
            if (!Schema::hasColumn('bookings', 'payment_metadata')) {
                $table->json('payment_metadata')->nullable()->after('payment_at');
            }
            if (!Schema::hasColumn('bookings', 'cancellation_reason')) {
                $table->string('cancellation_reason')->nullable()->after('is_completed');
            }
            if (!Schema::hasColumn('bookings', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('cancellation_reason');
            }
        });

        // 🔄 CHUYỂN ĐỔI DỮ LIỆU TỪ STATUS/PAYMENT_STATUS SANG BOOLEAN
        $this->convertExistingData();

        // 🗑️ XÓA CÁC COLUMN CŨ SAU KHI CHUYỂN ĐỔI
        Schema::table('bookings', function (Blueprint $table) {
            // Chỉ xóa nếu column tồn tại
            if (Schema::hasColumn('bookings', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('bookings', 'payment_status')) {
                $table->dropColumn('payment_status');
            }
        });
    }

    /**
     * Chuyển đổi dữ liệu hiện tại sang boolean logic.
     */
    private function convertExistingData(): void
    {
        // Lấy tất cả bookings hiện có
        $bookings = DB::table('bookings')->get();

        foreach ($bookings as $booking) {
            $isConfirmed = false;
            $isCancelled = false;
            $isCompleted = false;

            // Chuyển đổi từ status cũ (nếu có)
            if (isset($booking->status)) {
                switch ($booking->status) {
                    case 'accepted':
                        // Kiểm tra xem đã thanh toán chưa
                        $isPaid = !is_null($booking->payment_at) ||
                                 (isset($booking->payment_status) && $booking->payment_status === 'paid');
                        $isConfirmed = $isPaid;

                        break;
                    case 'confirmed':
                        $isConfirmed = true;

                        break;
                    case 'cancelled':
                        $isCancelled = true;

                        break;
                    case 'completed':
                        $isCompleted = true;

                        break;
                    case 'rejected':
                        $isCancelled = true;

                        break;
                        // 'pending' -> giữ nguyên false cho tất cả
                }
            }

            // Cập nhật boolean fields
            DB::table('bookings')
                ->where('id', $booking->id)
                ->update([
                    'is_confirmed' => $isConfirmed,
                    'is_cancelled' => $isCancelled,
                    'is_completed' => $isCompleted,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Thêm lại status và payment_status columns
        Schema::table('bookings', function (Blueprint $table) {
            $table->enum('status', ['pending', 'accepted', 'rejected', 'cancelled', 'completed'])->default('pending')->after('end_time');
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded', 'partial_refunded'])->nullable()->after('status');
        });

        // Chuyển đổi dữ liệu ngược lại
        $bookings = DB::table('bookings')->get();
        foreach ($bookings as $booking) {
            $status = 'pending';
            $paymentStatus = 'pending';

            if ($booking->is_completed) {
                $status = 'completed';
                $paymentStatus = 'paid';
            } elseif ($booking->is_cancelled) {
                $status = 'cancelled';
                $paymentStatus = null;
            } elseif ($booking->is_confirmed) {
                $status = 'accepted';
                $paymentStatus = 'paid';
            }

            DB::table('bookings')
                ->where('id', $booking->id)
                ->update([
                    'status' => $status,
                    'payment_status' => $paymentStatus,
                ]);
        }

        // Xóa boolean columns
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['is_confirmed', 'is_cancelled', 'is_completed']);
        });
    }
};
