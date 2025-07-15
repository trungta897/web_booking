<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 🎯 SYNC DỮ LIỆU GIỮA BOOLEAN LOGIC VÀ ENUM STATUS

        echo "🔄 Starting data synchronization...\n";

        // Cập nhật status enum dựa trên boolean fields
        $updated = DB::update("
            UPDATE bookings
            SET status = CASE
                WHEN is_completed = 1 THEN 'completed'
                WHEN is_cancelled = 1 THEN 'cancelled'
                WHEN is_confirmed = 1 THEN 'confirmed'
                ELSE 'pending'
            END
            WHERE status IS NULL OR status = 'pending'
        ");

        echo "✅ Updated {$updated} booking statuses\n";

        // Cập nhật payment_status dựa trên payment_at và confirmed status
        $paymentUpdated = DB::update("
            UPDATE bookings
            SET payment_status = CASE
                WHEN payment_at IS NOT NULL AND is_confirmed = 1 THEN 'paid'
                WHEN is_cancelled = 1 AND payment_at IS NOT NULL THEN 'refunded'
                ELSE 'pending'
            END
            WHERE payment_status IS NULL OR payment_status = 'pending'
        ");

        echo "✅ Updated {$paymentUpdated} payment statuses\n";

        // Thống kê sau khi sync
        $statusStats = DB::select('
            SELECT status, COUNT(*) as count
            FROM bookings
            GROUP BY status
        ');

        echo "📊 Status distribution after sync:\n";
        foreach ($statusStats as $stat) {
            echo "  - {$stat->status}: {$stat->count}\n";
        }

        $paymentStats = DB::select('
            SELECT payment_status, COUNT(*) as count
            FROM bookings
            GROUP BY payment_status
        ');

        echo "💳 Payment status distribution:\n";
        foreach ($paymentStats as $stat) {
            echo "  - {$stat->payment_status}: {$stat->count}\n";
        }

        echo "\n🎉 Data synchronization completed!\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset về trạng thái pending
        DB::update("UPDATE bookings SET status = 'pending', payment_status = 'pending'");
        echo "🔄 Reset all statuses to pending\n";
    }
};
