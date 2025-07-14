<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('tutor_id')->constrained('tutors')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->dateTime('start_time');
            $table->dateTime('end_time');

            // 🎯 CHUYỂN SANG BOOLEAN LOGIC
            // is_confirmed: 0 = chưa chấp nhận HOẶC chưa thanh toán
            //              1 = đã chấp nhận VÀ đã thanh toán (sẵn sàng học)
            $table->boolean('is_confirmed')->default(false);

            // Giữ lại các trường cần thiết khác
            $table->boolean('is_cancelled')->default(false);
            $table->boolean('is_completed')->default(false);
            $table->string('cancellation_reason')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('price', 8, 2);
            $table->string('meeting_link')->nullable();

            // Payment fields - simplified
            $table->string('payment_method')->nullable(); // vnpay, stripe, etc
            $table->string('vnpay_txn_ref')->nullable();
            $table->string('payment_intent_id')->nullable();
            $table->datetime('payment_at')->nullable();
            $table->json('payment_metadata')->nullable();

            // Commission fields
            $table->decimal('platform_fee_percentage', 5, 2)->nullable();
            $table->decimal('platform_fee_amount', 10, 2)->nullable();
            $table->decimal('tutor_earnings', 10, 2)->nullable();
            $table->datetime('commission_calculated_at')->nullable();
            $table->foreignId('payout_id')->nullable()->constrained('tutor_payouts')->onDelete('set null');

            $table->timestamps();

            // Add indexes for better query performance
            $table->index(['student_id', 'is_confirmed']);
            $table->index(['tutor_id', 'is_confirmed']);
            $table->index(['start_time', 'end_time']);
            $table->index(['is_confirmed', 'is_cancelled', 'is_completed']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('bookings');
    }
};
