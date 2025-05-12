<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('availability', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tutor_id')->constrained()->onDelete('cascade');
            $table->string('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            // Add unique constraint to prevent duplicate time slots
            $table->unique(['tutor_id', 'day_of_week', 'start_time', 'end_time']);

            // Add indexes for better query performance
            $table->index(['tutor_id', 'day_of_week', 'is_available']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('availability');
    }
};
