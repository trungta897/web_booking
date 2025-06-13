<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('tutor_id')->constrained('tutors')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->enum('status', ['pending', 'accepted', 'rejected', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->decimal('price', 8, 2);
            $table->string('meeting_link')->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            $table->string('payment_intent_id')->nullable();
            $table->timestamps();

            // Add indexes for better query performance
            $table->index(['student_id', 'status']);
            $table->index(['tutor_id', 'status']);
            $table->index(['start_time', 'end_time']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('bookings');
    }
};
