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
        // 🎯 BƯỚC 1: THÊM FOREIGN KEYS CHO BOOKINGS TABLE

        Schema::table('bookings', function (Blueprint $table) {
            // Kiểm tra và thêm foreign keys nếu chưa có
            if (!$this->foreignKeyExists('bookings', 'bookings_student_id_foreign')) {
                $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
                echo "✅ Added FK: bookings.student_id -> users.id\n";
            }

            if (!$this->foreignKeyExists('bookings', 'bookings_tutor_id_foreign')) {
                $table->foreign('tutor_id')->references('id')->on('tutors')->onDelete('cascade');
                echo "✅ Added FK: bookings.tutor_id -> tutors.id\n";
            }

            if (!$this->foreignKeyExists('bookings', 'bookings_subject_id_foreign')) {
                $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
                echo "✅ Added FK: bookings.subject_id -> subjects.id\n";
            }
        });

        // 🎯 BƯỚC 2: THÊM FOREIGN KEYS CHO REVIEWS TABLE

        Schema::table('reviews', function (Blueprint $table) {
            if (!$this->foreignKeyExists('reviews', 'reviews_reviewer_id_foreign')) {
                $table->foreign('reviewer_id')->references('id')->on('users')->onDelete('cascade');
                echo "✅ Added FK: reviews.reviewer_id -> users.id\n";
            }

            if (!$this->foreignKeyExists('reviews', 'reviews_reviewed_user_id_foreign')) {
                $table->foreign('reviewed_user_id')->references('id')->on('users')->onDelete('cascade');
                echo "✅ Added FK: reviews.reviewed_user_id -> users.id\n";
            }
        });

        // 🎯 BƯỚC 3: THÊM FOREIGN KEYS CHO MESSAGES TABLE

        Schema::table('messages', function (Blueprint $table) {
            if (!$this->foreignKeyExists('messages', 'messages_sender_id_foreign')) {
                $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
                echo "✅ Added FK: messages.sender_id -> users.id\n";
            }

            if (!$this->foreignKeyExists('messages', 'messages_receiver_id_foreign')) {
                $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');
                echo "✅ Added FK: messages.receiver_id -> users.id\n";
            }
        });

        echo "\n🎉 All missing foreign keys have been added!\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign('bookings_student_id_foreign');
            $table->dropForeign('bookings_tutor_id_foreign');
            $table->dropForeign('bookings_subject_id_foreign');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign('reviews_reviewer_id_foreign');
            $table->dropForeign('reviews_reviewed_user_id_foreign');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign('messages_sender_id_foreign');
            $table->dropForeign('messages_receiver_id_foreign');
        });
    }

    /**
     * Check if foreign key exists.
     */
    private function foreignKeyExists(string $table, string $keyName): bool
    {
        try {
            $keyExists = collect(DB::select('
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = ?
                AND CONSTRAINT_NAME = ?
            ', [$table, $keyName]))->isNotEmpty();

            return $keyExists;
        } catch (\Exception $e) {
            return false;
        }
    }
};
