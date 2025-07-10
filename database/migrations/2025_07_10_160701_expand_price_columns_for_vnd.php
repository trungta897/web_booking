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
        // Expand bookings price columns for VND
        Schema::table('bookings', function (Blueprint $table) {
            // Change price to support VND amounts (up to 99,999,999 VND)
            $table->decimal('price', 10, 2)->change();
            $table->decimal('original_amount', 10, 2)->nullable()->change();
        });

        // Expand tutors hourly_rate for VND
        Schema::table('tutors', function (Blueprint $table) {
            // Change hourly_rate to support VND amounts (up to 99,999,999 VND)
            $table->decimal('hourly_rate', 10, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Revert back to original size (8,2)
            $table->decimal('price', 8, 2)->change();
            $table->decimal('original_amount', 8, 2)->nullable()->change();
        });

        Schema::table('tutors', function (Blueprint $table) {
            // Revert back to original size (8,2)
            $table->decimal('hourly_rate', 8, 2)->change();
        });
    }
};
