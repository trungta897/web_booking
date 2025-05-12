<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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

        Schema::create('subject_tutor_profile', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('tutor_profile_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_tutor_profile');
        Schema::dropIfExists('subjects');
    }
};
