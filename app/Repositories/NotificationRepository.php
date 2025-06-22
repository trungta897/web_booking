<?php

namespace App\Repositories;

use App\Contracts\Repositories\NotificationRepositoryInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Notifications\DatabaseNotification;

class NotificationRepository extends BaseRepository implements NotificationRepositoryInterface
{
    public function __construct(DatabaseNotification $notification)
    {
        parent::__construct($notification);
    }

    /**
     * Get notifications for user
     */
    public function getNotificationsForUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->where('notifiable_id', $userId)
            ->where('notifiable_type', User::class)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get unread notifications for user
     */
    public function getUnreadNotificationsForUser(int $userId): Collection
    {
        return $this->query()
            ->where('notifiable_id', $userId)
            ->where('notifiable_type', User::class)
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Count unread notifications for user
     */
    public function countUnreadNotificationsForUser(int $userId): int
    {
        return $this->query()
            ->where('notifiable_id', $userId)
            ->where('notifiable_type', User::class)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(string $notificationId): bool
    {
        return $this->query()
            ->where('id', $notificationId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]) > 0;
    }

    /**
     * Mark all notifications as read for user
     */
    public function markAllAsReadForUser(int $userId): void
    {
        $this->query()
            ->where('notifiable_id', $userId)
            ->where('notifiable_type', User::class)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Delete notification
     */
    public function deleteNotification(string $notificationId): bool
    {
        return $this->query()
            ->where('id', $notificationId)
            ->delete() > 0;
    }

    /**
     * Delete all notifications for user
     */
    public function deleteAllForUser(int $userId): bool
    {
        return $this->query()
            ->where('notifiable_id', $userId)
            ->where('notifiable_type', User::class)
            ->delete() > 0;
    }

    /**
     * Get notifications by type for user
     */
    public function getNotificationsByType(int $userId, string $type): Collection
    {
        return $this->query()
            ->where('notifiable_id', $userId)
            ->where('notifiable_type', User::class)
            ->where('type', $type)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get recent notifications for user
     */
    public function getRecentNotificationsForUser(int $userId, int $limit = 10): Collection
    {
        return $this->query()
            ->where('notifiable_id', $userId)
            ->where('notifiable_type', User::class)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get notification statistics for user
     */
    public function getNotificationStatistics(int $userId): array
    {
        $total = $this->query()
            ->where('notifiable_id', $userId)
            ->where('notifiable_type', User::class)
            ->count();

        $unread = $this->countUnreadNotificationsForUser($userId);
        $read = $total - $unread;

        return [
            'total' => $total,
            'unread' => $unread,
            'read' => $read,
            'unread_percentage' => $total > 0 ? round(($unread / $total) * 100, 2) : 0
        ];
    }

    /**
     * Clean old notifications
     */
    public function cleanOldNotifications(int $days = 30): int
    {
        return $this->query()
            ->where('created_at', '<', now()->subDays($days))
            ->delete();
    }

    /**
     * Clean old notifications for specific user
     */
    public function cleanOldNotificationsForUser(int $userId, int $daysOld = 30): int
    {
        return $this->query()
            ->where('notifiable_id', $userId)
            ->where('notifiable_type', User::class)
            ->where('created_at', '<', now()->subDays($daysOld))
            ->delete();
    }
}
