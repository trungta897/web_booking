<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface NotificationRepositoryInterface extends RepositoryInterface
{
    /**
     * Get notifications for user with pagination.
     */
    public function getNotificationsForUser(int $userId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get unread notifications for user.
     */
    public function getUnreadNotificationsForUser(int $userId): Collection;

    /**
     * Mark notification as read.
     */
    public function markAsRead(string $notificationId): bool;

    /**
     * Mark all notifications as read for user.
     */
    public function markAllAsReadForUser(int $userId): void;

    /**
     * Delete notification.
     */
    public function deleteNotification(string $notificationId): bool;

    /**
     * Clean old notifications.
     */
    public function cleanOldNotifications(int $days = 30): int;
}
