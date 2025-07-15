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
        // Backup dữ liệu cũ nếu có
        $oldNotifications = DB::table('notifications')->get();

        // Drop bảng cũ
        Schema::dropIfExists('notifications');

        // Tạo lại bảng theo chuẩn Laravel notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable'); // Tạo notifiable_type và notifiable_id
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // Thêm index riêng biệt với kiểm tra
        if (!Schema::hasIndex('notifications', 'notifications_notifiable_type_notifiable_id_index')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->index(['notifiable_type', 'notifiable_id']);
            });
        }

        // MIGRATION DỮ LIỆU CŨ (nếu có)
        foreach ($oldNotifications as $oldNotification) {
            try {
                DB::table('notifications')->insert([
                    'id' => \Illuminate\Support\Str::uuid(),
                    'type' => 'App\\Notifications\\GeneralNotification',
                    'notifiable_type' => 'App\\Models\\User',
                    'notifiable_id' => $oldNotification->user_id,
                    'data' => json_encode([
                        'message' => $oldNotification->message,
                        'link' => $oldNotification->link,
                        'migrated_from_old_structure' => true,
                    ]),
                    'read_at' => $oldNotification->is_read ? $oldNotification->updated_at : null,
                    'created_at' => $oldNotification->created_at,
                    'updated_at' => $oldNotification->updated_at,
                ]);
            } catch (\Exception $e) {
                // Log lỗi nhưng không dừng migration
                \Illuminate\Support\Facades\Log::warning('Failed to migrate old notification', [
                    'old_notification_id' => $oldNotification->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        \Illuminate\Support\Facades\Log::info('Notifications table structure fixed', [
            'old_notifications_count' => count($oldNotifications),
            'migrated_count' => DB::table('notifications')->count(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Backup dữ liệu Laravel notifications hiện tại
        $currentNotifications = DB::table('notifications')->get();

        // Drop bảng Laravel notifications
        Schema::dropIfExists('notifications');

        // Tạo lại bảng theo cấu trúc cũ
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('message');
            $table->string('link')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        // Restore dữ liệu cũ
        foreach ($currentNotifications as $notification) {
            try {
                $data = json_decode($notification->data, true);

                DB::table('notifications')->insert([
                    'user_id' => $notification->notifiable_id,
                    'message' => $data['message'] ?? 'Notification',
                    'link' => $data['link'] ?? null,
                    'is_read' => $notification->read_at !== null,
                    'created_at' => $notification->created_at,
                    'updated_at' => $notification->updated_at,
                ]);
            } catch (\Exception $e) {
                // Log lỗi nhưng không dừng rollback
                \Illuminate\Support\Facades\Log::warning('Failed to rollback notification', [
                    'notification_id' => $notification->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
};
