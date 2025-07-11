<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'users',
            'subjects',
            'tutors',
            'subject_tutor',
            'bookings',
            'reviews',
            'messages',
            'availability',
            'favorite_tutors',
            'notifications',
            'sessions',
            'transactions',
            'tutor_payouts',
            'payout_items',
            'education',
            'cache',
            'cache_locks',
            'jobs',
            'job_batches'
        ];

        foreach ($tables as $table) {
            // Check if table exists before converting
            if (DB::select("SHOW TABLES LIKE '{$table}'")) {
                echo "Converting {$table} to InnoDB...\n";
                DB::statement("ALTER TABLE {$table} ENGINE = InnoDB");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't revert back to MyISAM as it would break foreign keys
        // This is a one-way migration for database integrity
    }
};
