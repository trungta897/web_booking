<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        echo "🧹 Starting duplicate indexes cleanup...\n";

        // 🎯 CLEANUP STRATEGY: Keep the most useful index, drop duplicates

        // 1. AVAILABILITY TABLE - Keep composite indexes, drop simple ones
        $this->cleanupAvailabilityIndexes();

        // 2. BOOKINGS TABLE - Keep most efficient composite indexes
        $this->cleanupBookingsIndexes();

        // 3. MESSAGES TABLE - Optimize for common queries
        $this->cleanupMessagesIndexes();

        // 4. NOTIFICATIONS TABLE - Keep Laravel standard indexes
        $this->cleanupNotificationsIndexes();

        // 5. REVIEWS TABLE - Keep unique constraints, optimize others
        $this->cleanupReviewsIndexes();

        // 6. TRANSACTIONS TABLE - Keep most used combinations
        $this->cleanupTransactionsIndexes();

        // 7. OTHER TABLES - Minor cleanups
        $this->cleanupOtherIndexes();

        echo "\n🎉 Duplicate indexes cleanup completed!\n";
    }

    private function cleanupAvailabilityIndexes()
    {
        echo "📋 Cleaning availability indexes...\n";

        Schema::table('availability', function (Blueprint $table) {
            // Drop less useful indexes, keep the comprehensive one
            $this->dropIndexIfExists('availability', 'availability_day_of_week_is_available_index');
            echo "  ✅ Dropped day_of_week + is_available (redundant)\n";

            // Keep: availability_tutor_id_day_of_week_is_available_index (most useful for queries)
            // Keep: availability_tutor_id_day_of_week_start_time_end_time_unique (unique constraint)
        });
    }

    private function cleanupBookingsIndexes()
    {
        echo "📋 Cleaning bookings indexes...\n";

        Schema::table('bookings', function (Blueprint $table) {
            // Keep the composite status+payment_status index, it covers both individual cases
            // This index can be used for queries on status alone OR status+payment_status

            // Drop redundant commission index (less frequently used)
            $this->dropIndexIfExists('bookings', 'idx_commission_calculated');
            echo "  ✅ Dropped commission_calculated (rarely queried)\n";

            // Keep: bookings_status_payment_status_index (most comprehensive)
            // Keep: bookings_start_time_end_time_index (for time-based queries)
            // Keep: idx_tutor_payout (needed for payouts)
        });
    }

    private function cleanupMessagesIndexes()
    {
        echo "📋 Cleaning messages indexes...\n";

        Schema::table('messages', function (Blueprint $table) {
            // Drop the triple composite index, the dual ones are more flexible
            $this->dropIndexIfExists('messages', 'messages_sender_id_receiver_id_is_read_index');
            echo "  ✅ Dropped sender+receiver+is_read (too specific)\n";

            // Keep: messages_sender_id_receiver_id_index (for conversation queries)
            // Keep: messages_receiver_id_is_read_index (for unread messages)
        });
    }

    private function cleanupNotificationsIndexes()
    {
        echo "📋 Cleaning notifications indexes...\n";

        Schema::table('notifications', function (Blueprint $table) {
            // Laravel standard uses notifiable_type + notifiable_id + read_at
            // Drop the simpler notifiable_type + notifiable_id as it's covered by the above
            $this->dropIndexIfExists('notifications', 'notifications_notifiable_type_notifiable_id_index');
            echo "  ✅ Dropped notifiable_type+notifiable_id (covered by larger index)\n";

            // Keep: notifications_notifiable_id_notifiable_type_read_at_index (Laravel standard)
        });
    }

    private function cleanupReviewsIndexes()
    {
        echo "📋 Cleaning reviews indexes...\n";

        Schema::table('reviews', function (Blueprint $table) {
            // Keep unique constraints but optimize performance indexes
            // Drop one of the overlapping unique indexes
            $this->dropIndexIfExists('reviews', 'reviews_booking_id_reviewer_id_unique');
            echo "  ✅ Dropped booking+reviewer unique (keep the 3-column one)\n";

            // Keep: reviews_tutor_id_student_id_booking_id_unique (most comprehensive)
            // Keep: reviews_tutor_id_rating_index (for rating queries)
        });
    }

    private function cleanupTransactionsIndexes()
    {
        echo "📋 Cleaning transactions indexes...\n";

        Schema::table('transactions', function (Blueprint $table) {
            // Keep the most useful composite indexes
            // Drop payment_method+status as booking+status is more commonly used
            $this->dropIndexIfExists('transactions', 'transactions_payment_method_status_index');
            echo "  ✅ Dropped payment_method+status (less common query)\n";

            // Keep: transactions_booking_id_status_index (most common queries)
            // Keep: transactions_user_id_type_index (for user transaction history)
        });
    }

    private function cleanupOtherIndexes()
    {
        echo "📋 Cleaning other table indexes...\n";

        // FAVORITE_TUTORS - Keep unique constraint, drop performance duplicate
        Schema::table('favorite_tutors', function (Blueprint $table) {
            $this->dropIndexIfExists('favorite_tutors', 'favorite_tutors_user_id_tutor_id_index');
            echo "  ✅ Dropped favorite_tutors performance index (unique constraint covers it)\n";
            // Keep: favorite_tutors_user_id_tutor_id_unique
        });

        // SUBJECT_TUTOR - Similar case
        Schema::table('subject_tutor', function (Blueprint $table) {
            $this->dropIndexIfExists('subject_tutor', 'subject_tutor_subject_id_tutor_id_index');
            echo "  ✅ Dropped subject_tutor performance index\n";
        });

        // TUTOR_PAYOUTS - Keep the more specific one
        Schema::table('tutor_payouts', function (Blueprint $table) {
            $this->dropIndexIfExists('tutor_payouts', 'idx_status');
            echo "  ✅ Dropped simple status index (tutor+status is better)\n";
            // Keep: idx_tutor_status
        });

        // TUTORS - Keep composite index
        Schema::table('tutors', function (Blueprint $table) {
            // The composite index tutors_is_available_hourly_rate_index covers individual queries too
            echo "  ✅ Keeping composite tutors index (covers multiple query patterns)\n";
        });

        // USERS - Keep composite index
        Schema::table('users', function (Blueprint $table) {
            echo "  ✅ Keeping composite users index (role+account_status)\n";
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        echo "🔄 Restoring some indexes...\n";

        // Only restore the most important ones for safety
        Schema::table('availability', function (Blueprint $table) {
            $table->index(['day_of_week', 'is_available']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->index(['sender_id', 'receiver_id', 'is_read']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['notifiable_type', 'notifiable_id']);
        });

        echo "🔄 Essential indexes restored\n";
    }

    /**
     * Safely drop index if it exists.
     */
    private function dropIndexIfExists(string $table, string $indexName): void
    {
        try {
            $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
            if (!empty($indexes)) {
                Schema::table($table, function (Blueprint $table) use ($indexName) {
                    $table->dropIndex($indexName);
                });
            }
        } catch (\Exception $e) {
            echo "    ⚠️ Could not drop {$indexName}: " . $e->getMessage() . "\n";
        }
    }
};
