<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tutor_subjects_new', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tutor_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Ensure a tutor can't have the same subject twice
            $table->unique(['tutor_id', 'subject_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tutor_subjects_new');
    }
};
