<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tutors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('bio')->nullable();
            $table->decimal('hourly_rate', 8, 2);
            $table->string('education')->nullable();
            $table->string('experience')->nullable();
            $table->json('certifications')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamps();

            // Add indexes for better query performance
            $table->index('is_verified');
            $table->index('hourly_rate');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tutors');
    }
};
