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
            // Chỉ thêm nếu column chưa tồn tại
            if (!Schema::hasColumn('bookings', 'cancellation_reason')) {
                $table->string('cancellation_reason')->nullable()->after('rejection_reason');
            }
            if (!Schema::hasColumn('bookings', 'cancellation_description')) {
                $table->text('cancellation_description')->nullable()->after('cancellation_reason');
            }
            if (!Schema::hasColumn('bookings', 'rejection_description')) {
                $table->text('rejection_description')->nullable()->after('rejection_reason');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['cancellation_reason', 'cancellation_description', 'rejection_description']);
        });
    }
};
