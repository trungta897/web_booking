<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\NotificationRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Exception;

class NotificationService extends BaseService
{
    protected NotificationRepository $notificationRepository;

    public function __construct()
    {
        $this->notificationRepository = new NotificationRepository(new DatabaseNotification());
    }

    /**
     * Get notifications for user
     */
    public function getUserNotifications(?int $userId = null, int $perPage = 15): LengthAwarePaginator
    {
        $userId = $userId ?? Auth::id();

        return $this->notificationRepository->getNotificationsForUser($userId, $perPage);
    }

    /**
     * Get unread notifications for user
     */
    public function getUnreadNotifications(?int $userId = null): Collection
    {
        $userId = $userId ?? Auth::id();

        return $this->notificationRepository->getUnreadNotificationsForUser($userId);
    }

    /**
     * Count unread notifications for user
     */
    public function getUnreadCount(?int $userId = null): int
    {
        $userId = $userId ?? Auth::id();

        return $this->notificationRepository->countUnreadNotificationsForUser($userId);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(string $notificationId, ?int $userId = null): bool
    {
        $userId = $userId ?? Auth::id();

        // Verify notification belongs to user
        $notification = DatabaseNotification::where('id', $notificationId)
            ->where('notifiable_id', $userId)
            ->where('notifiable_type', User::class)
            ->first();

        if (!$notification) {
            throw new Exception(__('Notification not found or access denied'));
        }

        $result = $this->notificationRepository->markAsRead($notificationId);

        if ($result) {
            $this->logActivity('Notification marked as read', [
                'notification_id' => $notificationId,
                'user_id' => $userId
            ]);
        }

        return $result;
    }

    /**
     * Mark all notifications as read for user
     */
    public function markAllAsRead(?int $userId = null): bool
    {
        $userId = $userId ?? Auth::id();

        $this->notificationRepository->markAllAsReadForUser($userId);

        $this->logActivity('All notifications marked as read', [
            'user_id' => $userId
        ]);

        return true;
    }

    /**
     * Delete notification
     */
    public function deleteNotification(string $notificationId, ?int $userId = null): bool
    {
        $userId = $userId ?? Auth::id();

        // Verify notification belongs to user
        $notification = DatabaseNotification::where('id', $notificationId)
            ->where('notifiable_id', $userId)
            ->where('notifiable_type', User::class)
            ->first();

        if (!$notification) {
            throw new Exception(__('Notification not found or access denied'));
        }

        $result = $this->notificationRepository->deleteNotification($notificationId);

        if ($result) {
            $this->logActivity('Notification deleted', [
                'notification_id' => $notificationId,
                'user_id' => $userId
            ]);
        }

        return $result;
    }

    /**
     * Delete all notifications for user
     */
    public function deleteAllNotifications(?int $userId = null): bool
    {
        $userId = $userId ?? Auth::id();

        $result = $this->notificationRepository->deleteAllForUser($userId);

        if ($result) {
            $this->logActivity('All notifications deleted', [
                'user_id' => $userId
            ]);
        }

        return $result;
    }

    /**
     * Get notifications by type
     */
    public function getNotificationsByType(string $type, ?int $userId = null): Collection
    {
        $userId = $userId ?? Auth::id();

        return $this->notificationRepository->getNotificationsByType($userId, $type);
    }

    /**
     * Get recent notifications for dashboard
     */
    public function getRecentNotifications(?int $userId = null, int $limit = 10): Collection
    {
        $userId = $userId ?? Auth::id();

        return $this->notificationRepository->getRecentNotificationsForUser($userId, $limit);
    }

    /**
     * Get notification statistics
     */
    public function getNotificationStatistics(?int $userId = null): array
    {
        $userId = $userId ?? Auth::id();

        $stats = $this->notificationRepository->getNotificationStatistics($userId);

        return [
            'total' => $stats['total'],
            'unread' => $stats['unread'],
            'read' => $stats['read'],
            'unread_percentage' => $stats['unread_percentage'],
            'formatted_stats' => [
                'total' => number_format($stats['total']),
                'unread' => number_format($stats['unread']),
                'read' => number_format($stats['read']),
                'unread_percentage' => $stats['unread_percentage'] . '%'
            ]
        ];
    }

    /**
     * Get notification data for UI
     */
    public function getNotificationForUI(string $notificationId, ?int $userId = null): array
    {
        $userId = $userId ?? Auth::id();

        $notification = DatabaseNotification::where('id', $notificationId)
            ->where('notifiable_id', $userId)
            ->where('notifiable_type', User::class)
            ->first();

        if (!$notification) {
            throw new Exception(__('Notification not found or access denied'));
        }

        $data = $notification->data;

        return [
            'id' => $notification->id,
            'type' => $notification->type,
            'title' => $data['title'] ?? __('Notification'),
            'message' => $data['message'] ?? '',
            'action_url' => $data['action_url'] ?? null,
            'is_read' => !is_null($notification->read_at),
            'created_at' => $notification->created_at,
            'time_ago' => $notification->created_at->diffForHumans(),
            'formatted_date' => $notification->created_at->format('d-m-Y H:i'),
        ];
    }

    /**
     * Bulk operations on notifications
     */
    public function bulkOperation(array $notificationIds, string $operation, ?int $userId = null): array
    {
        return $this->executeTransaction(function () use ($notificationIds, $operation, $userId) {
            $userId = $userId ?? Auth::id();
            $results = ['success' => 0, 'failed' => 0];

            foreach ($notificationIds as $notificationId) {
                try {
                    switch ($operation) {
                        case 'mark_read':
                            $success = $this->markAsRead($notificationId, $userId);
                            break;
                        case 'delete':
                            $success = $this->deleteNotification($notificationId, $userId);
                            break;
                        default:
                            throw new Exception(__('Invalid operation'));
                    }

                    if ($success) {
                        $results['success']++;
                    } else {
                        $results['failed']++;
                    }
                } catch (Exception $e) {
                    $results['failed']++;
                }
            }

            $this->logActivity('Bulk notification operation', [
                'operation' => $operation,
                'user_id' => $userId,
                'results' => $results
            ]);

            return $results;
        });
    }

    /**
     * Clean old notifications
     */
    public function cleanOldNotifications(?int $userId = null, int $daysOld = 30): int
    {
        $userId = $userId ?? Auth::id();

        $deletedCount = $this->notificationRepository->cleanOldNotificationsForUser($userId, $daysOld);

        if ($deletedCount > 0) {
            $this->logActivity('Old notifications cleaned', [
                'user_id' => $userId,
                'deleted_count' => $deletedCount,
                'days_old' => $daysOld
            ]);
        }

        return $deletedCount;
    }

        /**
     * Send custom notification to user
     * TODO: Create CustomNotification class
     */
    public function sendCustomNotification(int $userId, string $title, string $message, ?string $actionUrl = null): bool
    {
        try {
            // TODO: Implement custom notification when class is created
            // $user = User::findOrFail($userId);
            // $user->notify(new \App\Notifications\CustomNotification($title, $message, $actionUrl));

            $this->logActivity('Custom notification sent', [
                'user_id' => $userId,
                'title' => $title
            ]);

            return true;
        } catch (Exception $e) {
            $this->logError('Failed to send custom notification', $e, [
                'user_id' => $userId,
                'title' => $title
            ]);

            return false;
        }
    }

    /**
     * Handle errors specific to notification service
     */
    public function handleError(\Exception $e, string $context = ''): void
    {
        $this->logError($context ?: 'Notification service error occurred', $e);
        throw $e;
    }
}
