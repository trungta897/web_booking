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
        Schema::create('tutor_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tutor_id')->constrained('tutors')->onDelete('cascade');
            $table->decimal('total_amount', 10, 2);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->string('bank_account', 50)->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->string('account_holder_name', 255)->nullable();
            $table->text('admin_notes')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamp('requested_at')->default(now());
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['tutor_id', 'status'], 'idx_tutor_status');
            $table->index('status', 'idx_status');
            $table->index('requested_at', 'idx_requested_at');
            $table->index('processed_at', 'idx_processed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tutor_payouts');
    }
};
