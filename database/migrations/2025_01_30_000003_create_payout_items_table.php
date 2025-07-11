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
        Schema::create('payout_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payout_id')->constrained('tutor_payouts')->onDelete('cascade');
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->decimal('tutor_earnings', 10, 2);
            $table->decimal('platform_fee_amount', 10, 2);
            $table->decimal('booking_total', 10, 2);
            $table->timestamps();

            // Ensure một booking chỉ thuộc về một payout
            $table->unique('booking_id', 'unique_booking_payout');

            // Indexes for performance
            $table->index('payout_id', 'idx_payout_id');
            $table->index('booking_id', 'idx_booking_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payout_items');
    }
};
