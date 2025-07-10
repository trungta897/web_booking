<?php

namespace App\Contracts\Services;

use Illuminate\Pagination\LengthAwarePaginator;

interface NotificationServiceInterface extends ServiceInterface
{
    /**
     * Get user notifications with pagination.
     */
    public function getUserNotifications(int $userId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Mark notification as read.
     */
    public function markAsRead(string $notificationId, int $userId): bool;

    /**
     * Delete notification.
     */
    public function deleteNotification(string $notificationId, int $userId): bool;

    /**
     * Get notification for UI display.
     */
    public function getNotificationForUI(string $notificationId): ?array;

    /**
     * Bulk operation on notifications.
     */
    public function bulkOperation(int $userId, string $operation, array $notificationIds = []): bool;

    /**
     * Clean old notifications.
     */
    public function cleanOldNotifications(int $days = 30): int;

    /**
     * Get unread notification count.
     */
    public function getUnreadCount(int $userId): int;
}
