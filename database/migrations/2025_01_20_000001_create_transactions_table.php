<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('transaction_id')->unique(); // ID từ payment gateway
            $table->enum('payment_method', ['stripe', 'vnpay', 'paypal', 'momo', 'zalopay', 'bank_transfer']);
            $table->enum('type', ['payment', 'refund', 'partial_refund']);
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('VND');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded']);
            $table->json('gateway_response')->nullable(); // Lưu response từ gateway
            $table->json('metadata')->nullable(); // Thông tin bổ sung
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['booking_id', 'status']);
            $table->index(['user_id', 'type']);
            $table->index(['payment_method', 'status']);
            $table->index('transaction_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
