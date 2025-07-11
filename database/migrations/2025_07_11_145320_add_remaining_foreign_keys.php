<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Thêm foreign keys cho bảng tutor_payouts
        if ($this->tableExists('tutor_payouts') && !$this->foreignKeyExists('tutor_payouts', 'tutor_payouts_tutor_id_foreign')) {
            Schema::table('tutor_payouts', function (Blueprint $table) {
                $table->foreign('tutor_id')->references('id')->on('tutors')->onDelete('cascade');
            });
            echo "✅ Added tutor_payouts.tutor_id -> tutors.id\n";
        }

        // 2. Thêm foreign keys cho bảng payout_items
        if ($this->tableExists('payout_items')) {
            Schema::table('payout_items', function (Blueprint $table) {
                if (!$this->foreignKeyExists('payout_items', 'payout_items_payout_id_foreign')) {
                    $table->foreign('payout_id')->references('id')->on('tutor_payouts')->onDelete('cascade');
                    echo "✅ Added payout_items.payout_id -> tutor_payouts.id\n";
                }
                if (!$this->foreignKeyExists('payout_items', 'payout_items_booking_id_foreign')) {
                    $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
                    echo "✅ Added payout_items.booking_id -> bookings.id\n";
                }
            });
        }

        // 3. Thêm foreign keys cho bảng transactions
        if ($this->tableExists('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                if (!$this->foreignKeyExists('transactions', 'transactions_booking_id_foreign')) {
                    $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
                    echo "✅ Added transactions.booking_id -> bookings.id\n";
                }
                if (!$this->foreignKeyExists('transactions', 'transactions_user_id_foreign')) {
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                    echo "✅ Added transactions.user_id -> users.id\n";
                }
            });
        }

        // 4. Thêm foreign key cho bảng education (nếu có tutor_id column)
        if ($this->tableExists('education') && $this->columnExists('education', 'tutor_id')) {
            if (!$this->foreignKeyExists('education', 'education_tutor_id_foreign')) {
                Schema::table('education', function (Blueprint $table) {
                    $table->foreign('tutor_id')->references('id')->on('tutors')->onDelete('cascade');
                });
                echo "✅ Added education.tutor_id -> tutors.id\n";
            }
        }

        echo "\n🎉 Completed adding remaining foreign keys!\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign keys in reverse order
        $this->dropForeignKeyIfExists('education', 'education_tutor_id_foreign');
        $this->dropForeignKeyIfExists('transactions', 'transactions_user_id_foreign');
        $this->dropForeignKeyIfExists('transactions', 'transactions_booking_id_foreign');
        $this->dropForeignKeyIfExists('payout_items', 'payout_items_booking_id_foreign');
        $this->dropForeignKeyIfExists('payout_items', 'payout_items_payout_id_foreign');
        $this->dropForeignKeyIfExists('tutor_payouts', 'tutor_payouts_tutor_id_foreign');
    }

    /**
     * Check if table exists.
     */
    private function tableExists(string $table): bool
    {
        return Schema::hasTable($table);
    }

    /**
     * Check if column exists.
     */
    private function columnExists(string $table, string $column): bool
    {
        return Schema::hasColumn($table, $column);
    }

    /**
     * Check if foreign key exists.
     */
    private function foreignKeyExists(string $table, string $keyName): bool
    {
        $keyExists = collect(DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = ?
            AND CONSTRAINT_NAME = ?
        ", [$table, $keyName]))->isNotEmpty();

        return $keyExists;
    }

    /**
     * Drop foreign key if exists.
     */
    private function dropForeignKeyIfExists(string $table, string $keyName): void
    {
        if ($this->tableExists($table) && $this->foreignKeyExists($table, $keyName)) {
            Schema::table($table, function (Blueprint $table) use ($keyName) {
                $table->dropForeign($keyName);
            });
        }
    }
};
