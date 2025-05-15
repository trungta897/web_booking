<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tutors', function (Blueprint $table) {
            $table->boolean('is_available')->default(true)->after('hourly_rate');
            $table->integer('experience_years')->nullable()->after('is_available');
            $table->string('specialization')->nullable()->after('experience_years');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tutors', function (Blueprint $table) {
            $table->dropColumn('is_available');
            $table->dropColumn('experience_years');
            $table->dropColumn('specialization');
        });
    }
};
