<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('reviews', function (Blueprint $table) {
            if (!Schema::hasColumn('reviews', 'tutor_id')) {
                $table->foreignId('tutor_id')->constrained('users');
            }
            if (!Schema::hasColumn('reviews', 'student_id')) {
                $table->foreignId('student_id')->constrained('users');
            }
            if (!Schema::hasColumn('reviews', 'booking_id')) {
                $table->foreignId('booking_id')->constrained('bookings');
            }
            if (!Schema::hasColumn('reviews', 'rating')) {
                $table->integer('rating');
            }
            if (!Schema::hasColumn('reviews', 'comment')) {
                $table->text('comment');
            }
        });

        // Add unique constraint if it doesn't exist
        if (!Schema::hasIndex('reviews', 'reviews_tutor_id_student_id_booking_id_unique')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->unique(['tutor_id', 'student_id', 'booking_id'], 'reviews_tutor_id_student_id_booking_id_unique');
            });
        }
    }

    public function down()
    {
        Schema::table('reviews', function (Blueprint $table) {
            // Drop unique constraint if it exists
            if (Schema::hasIndex('reviews', 'reviews_tutor_id_student_id_booking_id_unique')) {
                $table->dropUnique('reviews_tutor_id_student_id_booking_id_unique');
            }

            // Drop columns if they exist
            $table->dropForeign(['tutor_id']);
            $table->dropForeign(['student_id']);
            $table->dropForeign(['booking_id']);
            $table->dropColumn(['tutor_id', 'student_id', 'booking_id', 'rating', 'comment']);
        });
    }
};
