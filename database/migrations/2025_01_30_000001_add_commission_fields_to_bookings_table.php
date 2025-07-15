<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Commission fields - chỉ thêm nếu chưa có
            if (!Schema::hasColumn('bookings', 'platform_fee_percentage')) {
                $table->decimal('platform_fee_percentage', 5, 2)->default(15.00)->after('price');
            }
            if (!Schema::hasColumn('bookings', 'platform_fee_amount')) {
                $table->decimal('platform_fee_amount', 10, 2)->nullable()->after('platform_fee_percentage');
            }
            if (!Schema::hasColumn('bookings', 'tutor_earnings')) {
                $table->decimal('tutor_earnings', 10, 2)->nullable()->after('platform_fee_amount');
            }
            if (!Schema::hasColumn('bookings', 'commission_calculated_at')) {
                $table->timestamp('commission_calculated_at')->nullable()->after('tutor_earnings');
            }
            if (!Schema::hasColumn('bookings', 'payout_id')) {
                $table->bigInteger('payout_id')->unsigned()->nullable()->after('commission_calculated_at');
            }

            // Index for performance - chỉ thêm nếu chưa có
            if (!$this->hasIndex('bookings', 'idx_tutor_payout')) {
                $table->index(['tutor_id', 'payout_id'], 'idx_tutor_payout');
            }
            if (!$this->hasIndex('bookings', 'idx_commission_calculated')) {
                $table->index('commission_calculated_at', 'idx_commission_calculated');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex('idx_tutor_payout');
            $table->dropIndex('idx_commission_calculated');

            // Drop columns
            $table->dropColumn([
                'platform_fee_percentage',
                'platform_fee_amount',
                'tutor_earnings',
                'commission_calculated_at',
                'payout_id',
            ]);
        });
    }

    /**
     * Check if an index exists on a table.
     */
    private function hasIndex(string $table, string $index): bool
    {
        try {
            $exists = Schema::getConnection()->select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$index]);

            return !empty($exists);
        } catch (\Exception $e) {
            return false;
        }
    }
};
