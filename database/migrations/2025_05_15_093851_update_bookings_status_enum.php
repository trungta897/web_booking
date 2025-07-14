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
        Schema::table('bookings', function (Blueprint $table) {
            // Add new columns - chỉ nếu chưa có
            if (!Schema::hasColumn('bookings', 'cancellation_reason')) {
                $table->string('cancellation_reason')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('bookings', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('cancellation_reason');
            }
            if (!Schema::hasColumn('bookings', 'accepted_at')) {
                $table->timestamp('accepted_at')->nullable()->after('cancelled_at');
            }
            if (!Schema::hasColumn('bookings', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('accepted_at');
            }
            if (!Schema::hasColumn('bookings', 'rejection_reason')) {
                $table->string('rejection_reason')->nullable()->after('rejected_at');
            }
            if (!Schema::hasColumn('bookings', 'is_recurring')) {
                $table->boolean('is_recurring')->default(false)->after('rejection_reason');
            }
            if (!Schema::hasColumn('bookings', 'recurring_pattern')) {
                $table->string('recurring_pattern')->nullable()->after('is_recurring');
            }
            if (!Schema::hasColumn('bookings', 'recurring_interval')) {
                $table->integer('recurring_interval')->nullable()->after('recurring_pattern');
            }
            if (!Schema::hasColumn('bookings', 'recurring_end_date')) {
                $table->date('recurring_end_date')->nullable()->after('recurring_interval');
            }

            // Add new indexes - chỉ nếu column tồn tại
            if (Schema::hasColumn('bookings', 'status') && Schema::hasColumn('bookings', 'start_time')) {
                if (!$this->hasIndex('bookings', 'bookings_status_start_time_index')) {
                    $table->index(['status', 'start_time']);
                }
            }
            if (Schema::hasColumn('bookings', 'payment_status') && Schema::hasColumn('bookings', 'status')) {
                if (!$this->hasIndex('bookings', 'bookings_payment_status_status_index')) {
                    $table->index(['payment_status', 'status']);
                }
            }
            if (Schema::hasColumn('bookings', 'cancelled_at')) {
                if (!$this->hasIndex('bookings', 'bookings_cancelled_at_index')) {
                    $table->index('cancelled_at');
                }
            }
            if (Schema::hasColumn('bookings', 'accepted_at')) {
                if (!$this->hasIndex('bookings', 'bookings_accepted_at_index')) {
                    $table->index('accepted_at');
                }
            }
            if (Schema::hasColumn('bookings', 'rejected_at')) {
                if (!$this->hasIndex('bookings', 'bookings_rejected_at_index')) {
                    $table->index('rejected_at');
                }
            }
        });

        // Modify the status enum to include 'completed' - chỉ nếu column status tồn tại
        if (Schema::hasColumn('bookings', 'status')) {
            DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM('pending', 'accepted', 'rejected', 'cancelled', 'completed') DEFAULT 'pending'");
        }
    }

    /**
     * Check if an index exists on a table.
     */
    private function hasIndex(string $table, string $index): bool
    {
        try {
            $exists = Schema::getConnection()->select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$index]);
            return !empty($exists);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Drop new columns
            $table->dropColumn([
                'cancellation_reason',
                'cancelled_at',
                'accepted_at',
                'rejected_at',
                'rejection_reason',
                'is_recurring',
                'recurring_pattern',
                'recurring_interval',
                'recurring_end_date',
            ]);

            // Drop new indexes
            $table->dropIndex(['status', 'start_time']);
            $table->dropIndex(['payment_status', 'status']);
            $table->dropIndex('cancelled_at');
            $table->dropIndex('accepted_at');
            $table->dropIndex('rejected_at');
        });

        // Before reverting the ENUM, update any 'completed' statuses
        // to a value that will be valid in the reverted ENUM.
        // For example, let's change them to 'accepted'.
        DB::table('bookings')->where('status', 'completed')->update(['status' => 'accepted']);

        // Revert back to the original enum values (without 'completed')
        DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM('pending', 'accepted', 'rejected', 'cancelled') DEFAULT 'pending'");
    }
};
