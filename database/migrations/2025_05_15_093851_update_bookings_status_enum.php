<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the status enum to include 'completed'
        DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM('pending', 'accepted', 'rejected', 'cancelled', 'completed') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Before reverting the ENUM, update any 'completed' statuses
        // to a value that will be valid in the reverted ENUM.
        // For example, let's change them to 'accepted'.
        DB::table('bookings')->where('status', 'completed')->update(['status' => 'accepted']);

        // Revert back to the original enum values (without 'completed')
        DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM('pending', 'accepted', 'rejected', 'cancelled') DEFAULT 'pending'");
    }
};
