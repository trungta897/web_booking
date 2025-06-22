<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Thêm các trường cho VNPay và payment methods khác
            $table->enum('payment_method', ['stripe', 'vnpay', 'paypal', 'cash'])->default('stripe')->after('payment_intent_id');
            $table->string('vnpay_txn_ref')->nullable()->after('payment_method'); // Mã tham chiếu VNPay
            $table->decimal('exchange_rate', 10, 4)->nullable()->after('vnpay_txn_ref'); // Tỷ giá quy đổi
            $table->string('currency', 3)->default('VND')->after('exchange_rate');
            $table->decimal('original_amount', 10, 2)->nullable()->after('currency'); // Số tiền gốc trước quy đổi
            $table->json('payment_metadata')->nullable()->after('original_amount'); // Metadata cho payment
        });
    }

    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method',
                'vnpay_txn_ref',
                'exchange_rate',
                'currency',
                'original_amount',
                'payment_metadata',
            ]);
        });
    }
};
