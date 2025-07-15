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
        // Kiểm tra xem cột status có tồn tại không trước khi modify
        if (Schema::hasColumn('bookings', 'status')) {
            DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM('pending', 'accepted', 'confirmed', 'rejected', 'cancelled', 'completed') DEFAULT 'pending'");
        } else {
            // Nếu không có cột status, tạo mới
            Schema::table('bookings', function ($table) {
                $table->enum('status', ['pending', 'accepted', 'confirmed', 'rejected', 'cancelled', 'completed'])
                      ->default('pending')
                      ->after('subject_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('bookings', 'status')) {
            // Remove 'confirmed' status from enum, convert any 'confirmed' to 'accepted'
            DB::statement("UPDATE bookings SET status = 'accepted' WHERE status = 'confirmed'");
            DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM('pending', 'accepted', 'rejected', 'cancelled', 'completed') DEFAULT 'pending'");
        }
    }
};
