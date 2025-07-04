<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Check if an index exists on a table
     */
    private function hasIndex(string $table, string $index): bool
    {
        try {
            $exists = Schema::getConnection()->select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$index]);
            return !empty($exists);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Indexes for users table
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!$this->hasIndex('users', 'users_role_account_status_index')) {
                    $table->index(['role', 'account_status']);
                }
                if (!$this->hasIndex('users', 'users_account_status_index')) {
                    $table->index('account_status');
                }
                if (!$this->hasIndex('users', 'users_email_verified_at_index')) {
                    $table->index('email_verified_at');
                }
            });
        }

        // Indexes for tutors table
        if (Schema::hasTable('tutors')) {
            Schema::table('tutors', function (Blueprint $table) {
                if (!$this->hasIndex('tutors', 'tutors_is_available_hourly_rate_index')) {
                    $table->index(['is_available', 'hourly_rate']);
                }
                if (!$this->hasIndex('tutors', 'tutors_experience_years_index')) {
                    $table->index('experience_years');
                }
                if (!$this->hasIndex('tutors', 'tutors_user_id_index')) {
                    $table->index('user_id');
                }
            });
        }

        // Indexes for bookings table
        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                if (!$this->hasIndex('bookings', 'bookings_status_payment_status_index')) {
                    $table->index(['status', 'payment_status']);
                }
                if (!$this->hasIndex('bookings', 'bookings_tutor_id_status_index')) {
                    $table->index(['tutor_id', 'status']);
                }
                if (!$this->hasIndex('bookings', 'bookings_student_id_status_index')) {
                    $table->index(['student_id', 'status']);
                }
                if (!$this->hasIndex('bookings', 'bookings_start_time_end_time_index')) {
                    $table->index(['start_time', 'end_time']);
                }
                if (!$this->hasIndex('bookings', 'bookings_start_time_index')) {
                    $table->index('start_time');
                }
                if (!$this->hasIndex('bookings', 'bookings_created_at_index')) {
                    $table->index('created_at');
                }
            });
        }

        // Indexes for reviews table
        Schema::table('reviews', function (Blueprint $table) {
            $table->index(['tutor_id', 'rating']);
            $table->index('student_id');
            $table->index('created_at');
        });

        // Indexes for availability table
        Schema::table('availability', function (Blueprint $table) {
            $table->index(['tutor_id', 'day_of_week', 'is_available']);
            $table->index(['day_of_week', 'is_available']);
        });

        // Indexes for subjects table
        Schema::table('subjects', function (Blueprint $table) {
            $table->index('is_active');
            $table->index('name');
        });

        // Indexes for subject_tutor pivot table
        Schema::table('subject_tutor', function (Blueprint $table) {
            $table->index(['subject_id', 'tutor_id']);
            $table->index('hourly_rate');
        });

        // Indexes for favorite_tutors table
        Schema::table('favorite_tutors', function (Blueprint $table) {
            $table->index(['user_id', 'tutor_id']);
        });

        // Indexes for messages table
        Schema::table('messages', function (Blueprint $table) {
            $table->index(['sender_id', 'receiver_id', 'is_read']);
            $table->index(['receiver_id', 'is_read']);
            $table->index('created_at');
        });

        // Indexes for notifications table
        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['notifiable_id', 'notifiable_type', 'read_at']);
            $table->index('created_at');
        });

        // Indexes for transactions table
        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['booking_id', 'status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes in reverse order
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['booking_id', 'status']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['notifiable_id', 'notifiable_type', 'read_at']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['sender_id', 'receiver_id', 'is_read']);
            $table->dropIndex(['receiver_id', 'is_read']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('favorite_tutors', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'tutor_id']);
        });

        Schema::table('subject_tutor', function (Blueprint $table) {
            $table->dropIndex(['subject_id', 'tutor_id']);
            $table->dropIndex(['hourly_rate']);
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropIndex(['name']);
        });

        Schema::table('availability', function (Blueprint $table) {
            $table->dropIndex(['tutor_id', 'day_of_week', 'is_available']);
            $table->dropIndex(['day_of_week', 'is_available']);
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex(['tutor_id', 'rating']);
            $table->dropIndex(['student_id']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['status', 'payment_status']);
            $table->dropIndex(['tutor_id', 'status']);
            $table->dropIndex(['student_id', 'status']);
            $table->dropIndex(['start_time', 'end_time']);
            $table->dropIndex(['start_time']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('tutors', function (Blueprint $table) {
            $table->dropIndex(['is_available', 'hourly_rate']);
            $table->dropIndex(['experience_years']);
            $table->dropIndex(['user_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role', 'account_status']);
            $table->dropIndex(['account_status']);
            $table->dropIndex(['email_verified_at']);
        });
    }
};
