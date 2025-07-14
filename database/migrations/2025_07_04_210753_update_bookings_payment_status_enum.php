<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Chỉ modify nếu column payment_status tồn tại
        if (Schema::hasColumn('bookings', 'payment_status')) {
            DB::statement("ALTER TABLE bookings MODIFY COLUMN payment_status ENUM('pending', 'paid', 'failed', 'refunded', 'partial_refunded') DEFAULT 'pending'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Chỉ modify nếu column payment_status tồn tại
        if (Schema::hasColumn('bookings', 'payment_status')) {
            DB::statement("ALTER TABLE bookings MODIFY COLUMN payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending'");
        }
    }
};
