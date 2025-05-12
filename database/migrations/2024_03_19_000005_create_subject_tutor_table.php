<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('subject_tutor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('tutor_id')->constrained()->onDelete('cascade');
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            // Add unique constraint to prevent duplicate subject-tutor combinations
            $table->unique(['subject_id', 'tutor_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('subject_tutor');
    }
};
