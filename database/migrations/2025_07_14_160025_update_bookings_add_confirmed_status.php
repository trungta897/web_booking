<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'confirmed' status to the existing enum
        DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM('pending', 'accepted', 'confirmed', 'rejected', 'cancelled', 'completed') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'confirmed' status from enum, convert any 'confirmed' to 'accepted'
        DB::statement("UPDATE bookings SET status = 'accepted' WHERE status = 'confirmed'");
        DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM('pending', 'accepted', 'rejected', 'cancelled', 'completed') DEFAULT 'pending'");
    }
};
