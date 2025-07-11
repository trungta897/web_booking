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
            // Commission fields - với DEFAULT values để không ảnh hưởng existing data
            $table->decimal('platform_fee_percentage', 5, 2)->default(15.00)->after('currency');
            $table->decimal('platform_fee_amount', 10, 2)->nullable()->after('platform_fee_percentage');
            $table->decimal('tutor_earnings', 10, 2)->nullable()->after('platform_fee_amount');
            $table->timestamp('commission_calculated_at')->nullable()->after('tutor_earnings');
            $table->bigInteger('payout_id')->unsigned()->nullable()->after('commission_calculated_at');

            // Index for performance
            $table->index(['tutor_id', 'payout_id'], 'idx_tutor_payout');
            $table->index('commission_calculated_at', 'idx_commission_calculated');
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
                'payout_id'
            ]);
        });
    }
};
