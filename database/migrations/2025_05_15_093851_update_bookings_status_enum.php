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
        Schema::table('bookings', function (Blueprint $table) {
            // Add new columns
            $table->string('cancellation_reason')->nullable()->after('notes');
            $table->timestamp('cancelled_at')->nullable()->after('cancellation_reason');
            $table->timestamp('accepted_at')->nullable()->after('cancelled_at');
            $table->timestamp('rejected_at')->nullable()->after('accepted_at');
            $table->string('rejection_reason')->nullable()->after('rejected_at');
            $table->boolean('is_recurring')->default(false)->after('rejection_reason');
            $table->string('recurring_pattern')->nullable()->after('is_recurring');
            $table->integer('recurring_interval')->nullable()->after('recurring_pattern');
            $table->date('recurring_end_date')->nullable()->after('recurring_interval');

            // Add new indexes
            $table->index(['status', 'start_time']);
            $table->index(['payment_status', 'status']);
            $table->index('cancelled_at');
            $table->index('accepted_at');
            $table->index('rejected_at');
        });

        // Modify the status enum to include 'completed'
        DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM('pending', 'accepted', 'rejected', 'cancelled', 'completed') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Drop new columns
            $table->dropColumn([
                'cancellation_reason',
                'cancelled_at',
                'accepted_at',
                'rejected_at',
                'rejection_reason',
                'is_recurring',
                'recurring_pattern',
                'recurring_interval',
                'recurring_end_date'
            ]);

            // Drop new indexes
            $table->dropIndex(['status', 'start_time']);
            $table->dropIndex(['payment_status', 'status']);
            $table->dropIndex('cancelled_at');
            $table->dropIndex('accepted_at');
            $table->dropIndex('rejected_at');
        });

        // Before reverting the ENUM, update any 'completed' statuses
        // to a value that will be valid in the reverted ENUM.
        // For example, let's change them to 'accepted'.
        DB::table('bookings')->where('status', 'completed')->update(['status' => 'accepted']);

        // Revert back to the original enum values (without 'completed')
        DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM('pending', 'accepted', 'rejected', 'cancelled') DEFAULT 'pending'");
    }
};
