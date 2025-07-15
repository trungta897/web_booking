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
        echo "Ensuring booking boolean columns exist...\n";

        // Check if columns exist and add them if missing
        $columns = Schema::getColumnListing('bookings');
        echo "Current columns in bookings table: " . implode(', ', $columns) . "\n";

        Schema::table('bookings', function (Blueprint $table) {
            // Add is_confirmed column if it doesn't exist
            if (!Schema::hasColumn('bookings', 'is_confirmed')) {
                $table->boolean('is_confirmed')->default(false)->after('notes');
                echo "Added is_confirmed column\n";
            } else {
                echo "is_confirmed column already exists\n";
            }

            // Add is_cancelled column if it doesn't exist
            if (!Schema::hasColumn('bookings', 'is_cancelled')) {
                $table->boolean('is_cancelled')->default(false)->after('is_confirmed');
                echo "Added is_cancelled column\n";
            } else {
                echo "is_cancelled column already exists\n";
            }

            // Add is_completed column if it doesn't exist
            if (!Schema::hasColumn('bookings', 'is_completed')) {
                $table->boolean('is_completed')->default(false)->after('is_cancelled');
                echo "Added is_completed column\n";
            } else {
                echo "is_completed column already exists\n";
            }

            // Add accepted_at column if it doesn't exist
            if (!Schema::hasColumn('bookings', 'accepted_at')) {
                $table->timestamp('accepted_at')->nullable()->after('is_completed');
                echo "Added accepted_at column\n";
            } else {
                echo "accepted_at column already exists\n";
            }

            // Add payment_at column if it doesn't exist
            if (!Schema::hasColumn('bookings', 'payment_at')) {
                $table->timestamp('payment_at')->nullable()->after('accepted_at');
                echo "Added payment_at column\n";
            } else {
                echo "payment_at column already exists\n";
            }
        });

        // If we have existing data with status column, migrate it to boolean columns
        if (Schema::hasColumn('bookings', 'status')) {
            echo "Migrating existing status data to boolean columns...\n";

            // Update is_confirmed based on status
            DB::update("UPDATE bookings SET is_confirmed = 1 WHERE status = 'accepted'");
            DB::update("UPDATE bookings SET is_cancelled = 1 WHERE status = 'cancelled'");
            DB::update("UPDATE bookings SET is_completed = 1 WHERE status = 'completed'");

            // Set accepted_at for confirmed bookings
            DB::update("UPDATE bookings SET accepted_at = updated_at WHERE is_confirmed = 1 AND accepted_at IS NULL");

            echo "Completed status data migration\n";
        }

        echo "Booking boolean columns setup completed!\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'is_confirmed',
                'is_cancelled',
                'is_completed',
                'accepted_at',
                'payment_at'
            ]);
        });
    }
};
