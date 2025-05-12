<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tutor_subjects', function (Blueprint $table) {
            // Drop any existing foreign keys
            $table->dropForeign(['tutor_id']);
            $table->dropForeign(['subject_id']);

            // Drop any existing columns
            $table->dropColumn(['tutor_id', 'subject_id']);

            // Add the correct columns
            $table->foreignId('tutor_id')->after('id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->after('tutor_id')->constrained()->onDelete('cascade');

            // Add unique constraint
            $table->unique(['tutor_id', 'subject_id']);
        });
    }

    public function down()
    {
        Schema::table('tutor_subjects', function (Blueprint $table) {
            $table->dropForeign(['tutor_id']);
            $table->dropForeign(['subject_id']);
            $table->dropColumn(['tutor_id', 'subject_id']);
        });
    }
};
