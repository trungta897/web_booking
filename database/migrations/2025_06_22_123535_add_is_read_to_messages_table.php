<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->boolean('is_read')->default(false)->after('message');
            $table->index('is_read');
        });

        // Sync existing data: if read_at is not null, set is_read = true
        DB::table('messages')
            ->whereNotNull('read_at')
            ->update(['is_read' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['is_read']);
            $table->dropColumn('is_read');
        });
    }
};
