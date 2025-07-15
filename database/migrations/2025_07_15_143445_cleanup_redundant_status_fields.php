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
        // 🎯 PHASE 1: SYNC BOOLEAN FIELDS WITH ENUM STATUS (Safety check)

        // Đảm bảo data consistency trước khi xóa
        DB::statement("
            UPDATE bookings
            SET status = CASE
                WHEN is_completed = 1 THEN 'completed'
                WHEN is_cancelled = 1 THEN 'cancelled'
                WHEN is_confirmed = 1 THEN 'confirmed'
                ELSE status
            END
        ");

        echo "✅ Synced boolean fields with enum status\n";

        // 🎯 PHASE 2: REMOVE REDUNDANT BOOLEAN STATUS FIELDS

        Schema::table('bookings', function (Blueprint $table) {
            // Xóa các boolean status fields redundant với enum
            $table->dropColumn([
                'is_confirmed',    // Redundant với status = 'confirmed'
                'is_cancelled',    // Redundant với status = 'cancelled'
                'is_completed',     // Redundant với status = 'completed'
            ]);
        });

        echo "✅ Removed redundant boolean status fields\n";

        // 🎯 PHASE 3: OPTIMIZE TIMESTAMP FIELDS

        // Giữ lại timestamp fields quan trọng, nhưng đổi tên cho rõ ràng
        Schema::table('bookings', function (Blueprint $table) {
            // Đổi tên các timestamp fields cho consistency
            $table->renameColumn('accepted_at', 'confirmed_at');
        });

        echo "✅ Renamed accepted_at to confirmed_at for consistency\n";

        // 🎯 PHASE 4: THÊM COMPUTED PROPERTIES VIA ACCESSORS
        // (Sẽ implement trong Model sau)

        $statusCount = DB::table('bookings')->count();
        echo "📊 Processed {$statusCount} bookings\n";

        // Kiểm tra status distribution sau cleanup
        $statusStats = DB::select('
            SELECT status, COUNT(*) as count
            FROM bookings
            GROUP BY status
        ');

        echo "📈 Status distribution after cleanup:\n";
        foreach ($statusStats as $stat) {
            echo "  - {$stat->status}: {$stat->count}\n";
        }

        echo "\n🎉 Status fields cleanup completed!\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Khôi phục lại boolean fields
        Schema::table('bookings', function (Blueprint $table) {
            $table->boolean('is_confirmed')->default(false)->after('end_time');
            $table->boolean('is_cancelled')->default(false)->after('is_confirmed');
            $table->boolean('is_completed')->default(false)->after('is_cancelled');
        });

        // Đổi tên ngược lại
        Schema::table('bookings', function (Blueprint $table) {
            $table->renameColumn('confirmed_at', 'accepted_at');
        });

        // Sync data ngược lại
        DB::statement("
            UPDATE bookings SET
                is_confirmed = CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END,
                is_cancelled = CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END,
                is_completed = CASE WHEN status = 'completed' THEN 1 ELSE 0 END
        ");

        echo "🔄 Restored boolean status fields\n";
    }
};
