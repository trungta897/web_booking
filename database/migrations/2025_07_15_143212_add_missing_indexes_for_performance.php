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
        // 🎯 THÊM STATUS ENUM VÀ INDEXES CHO BOOKINGS TABLE

        // Thêm cột status enum nếu chưa có
        if (!Schema::hasColumn('bookings', 'status')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])
                      ->default('pending')
                      ->after('subject_id');
                echo "✅ Added status enum column to bookings\n";
            });
        }

        // Thêm payment_status enum nếu chưa có
        if (!Schema::hasColumn('bookings', 'payment_status')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])
                      ->default('pending')
                      ->after('status');
                echo "✅ Added payment_status enum column to bookings\n";
            });
        }

        Schema::table('bookings', function (Blueprint $table) {
            // Thêm indexes quan trọng cho performance
            if (!$this->indexExists('bookings', 'bookings_status_index')) {
                $table->index('status');
                echo "✅ Added index on bookings.status\n";
            }

            if (!$this->indexExists('bookings', 'bookings_payment_status_index')) {
                $table->index('payment_status');
                echo "✅ Added index on bookings.payment_status\n";
            }

            if (!$this->indexExists('bookings', 'bookings_status_payment_status_index')) {
                $table->index(['status', 'payment_status']);
                echo "✅ Added composite index on bookings.status + payment_status\n";
            }

            if (!$this->indexExists('bookings', 'bookings_start_time_end_time_index')) {
                $table->index(['start_time', 'end_time']);
                echo "✅ Added composite index on bookings time range\n";
            }

            if (!$this->indexExists('bookings', 'bookings_is_confirmed_index')) {
                $table->index('is_confirmed');
                echo "✅ Added index on bookings.is_confirmed\n";
            }
        });

        // 🎯 THÊM INDEXES CHO TRANSACTIONS TABLE
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                if (!$this->indexExists('transactions', 'transactions_payment_method_index')) {
                    $table->index('payment_method');
                    echo "✅ Added index on transactions.payment_method\n";
                }

                if (!$this->indexExists('transactions', 'transactions_type_index')) {
                    $table->index('type');
                    echo "✅ Added index on transactions.type\n";
                }
            });
        }

        // 🎯 THÊM INDEXES CHO USERS TABLE
        Schema::table('users', function (Blueprint $table) {
            if (!$this->indexExists('users', 'users_account_status_index')) {
                $table->index('account_status');
                echo "✅ Added index on users.account_status\n";
            }

            if (!$this->indexExists('users', 'users_role_account_status_index')) {
                $table->index(['role', 'account_status']);
                echo "✅ Added composite index on users.role + account_status\n";
            }
        });

        echo "\n🚀 All performance indexes have been added!\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex(['bookings_status_index']);
            $table->dropIndex(['bookings_payment_status_index']);
            $table->dropIndex(['bookings_status_payment_status_index']);
            $table->dropIndex(['bookings_start_time_end_time_index']);
            $table->dropIndex(['bookings_is_confirmed_index']);

            // Drop columns
            if (Schema::hasColumn('bookings', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('bookings', 'payment_status')) {
                $table->dropColumn('payment_status');
            }
        });

        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropIndex(['transactions_payment_method_index']);
                $table->dropIndex(['transactions_type_index']);
            });
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['users_account_status_index']);
            $table->dropIndex(['users_role_account_status_index']);
        });
    }

    /**
     * Check if index exists.
     */
    private function indexExists(string $table, string $indexName): bool
    {
        try {
            $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);

            return !empty($indexes);
        } catch (\Exception $e) {
            return false;
        }
    }
};
