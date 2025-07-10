<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->string('level')->nullable();
            $table->timestamps();

            // Add indexes for better query performance
            $table->index('name');
            $table->index('category');
            $table->index('level');
        });

        Schema::create('subject_tutor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('tutor_id')->constrained('tutors')->onDelete('cascade');
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            // Optional: Add a unique constraint for subject_id and tutor_id if a tutor cannot have the same subject multiple times
            // $table->unique(['subject_id', 'tutor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_tutor');
        Schema::dropIfExists('subjects');
    }
};
