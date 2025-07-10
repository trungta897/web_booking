<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\NotificationRepository;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationService extends BaseService
{
    protected NotificationRepository $notificationRepository;

    public function __construct()
    {
        $this->notificationRepository = new NotificationRepository(new DatabaseNotification());
    }

    /**
     * Get notifications for user with User object.
     */
    public function getUserNotifications(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $this->notificationRepository->getNotificationsForUser($user->id, $perPage);
    }

    /**
     * Get unread notifications for user.
     */
    public function getUnreadNotifications(?int $userId = null): Collection
    {
        $userId = $userId ?? Auth::id();

        return $this->notificationRepository->getUnreadNotificationsForUser($userId);
    }

    /**
     * Count unread notifications for user with User object.
     */
    public function getUnreadCount(User $user): int
    {
        return $this->notificationRepository->countUnreadNotificationsForUser($user->id);
    }

    /**
     * Mark notification as read with DatabaseNotification object.
     */
    public function markAsRead(DatabaseNotification $notification): bool
    {
        if (!$notification->read_at) {
            $notification->markAsRead();

            $this->logActivity('Notification marked as read', [
                'notification_id' => $notification->id,
                'user_id' => $notification->notifiable_id,
            ]);
        }

        return true;
    }

    /**
     * Mark all notifications as read with User object.
     */
    public function markAllAsRead(User $user): bool
    {
        $this->notificationRepository->markAllAsReadForUser($user->id);

        $this->logActivity('All notifications marked as read', [
            'user_id' => $user->id,
        ]);

        return true;
    }

    /**
     * Delete notification with DatabaseNotification object.
     */
    public function deleteNotification(DatabaseNotification $notification): bool
    {
        $result = $notification->delete();

        if ($result) {
            $this->logActivity('Notification deleted', [
                'notification_id' => $notification->id,
                'user_id' => $notification->notifiable_id,
            ]);
        }

        return $result;
    }

    /**
     * Delete all notifications with User object.
     */
    public function deleteAllNotifications(User $user): bool
    {
        $result = $this->notificationRepository->deleteAllForUser($user->id);

        if ($result) {
            $this->logActivity('All notifications deleted', [
                'user_id' => $user->id,
            ]);
        }

        return $result;
    }

    /**
     * Get notifications by type.
     */
    public function getNotificationsByType(string $type, ?int $userId = null): Collection
    {
        $userId = $userId ?? Auth::id();

        return $this->notificationRepository->getNotificationsByType($userId, $type);
    }

    /**
     * Get recent notifications for dashboard.
     */
    public function getRecentNotifications(?int $userId = null, int $limit = 10): Collection
    {
        $userId = $userId ?? Auth::id();

        return $this->notificationRepository->getRecentNotificationsForUser($userId, $limit);
    }

    /**
     * Get notification statistics.
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
                'unread_percentage' => $stats['unread_percentage'] . '%',
            ],
        ];
    }

    /**
     * Get notification data for UI.
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
     * Bulk operations on notifications.
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
                            $notification = DatabaseNotification::where('id', $notificationId)
                                ->where('notifiable_id', $userId)->first();
                            if ($notification) {
                                $success = $this->markAsRead($notification);
                            } else {
                                $success = false;
                            }

                            break;
                        case 'delete':
                            $notification = DatabaseNotification::where('id', $notificationId)
                                ->where('notifiable_id', $userId)->first();
                            if ($notification) {
                                $success = $this->deleteNotification($notification);
                            } else {
                                $success = false;
                            }

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
                'results' => $results,
            ]);

            return $results;
        });
    }

    /**
     * Clean old notifications.
     */
    public function cleanOldNotifications(?int $userId = null, int $daysOld = 30): int
    {
        $userId = $userId ?? Auth::id();

        $deletedCount = $this->notificationRepository->cleanOldNotificationsForUser($userId, $daysOld);

        if ($deletedCount > 0) {
            $this->logActivity('Old notifications cleaned', [
                'user_id' => $userId,
                'deleted_count' => $deletedCount,
                'days_old' => $daysOld,
            ]);
        }

        return $deletedCount;
    }

    /**
     * Handle errors consistently.
     */
    public function handleError(Exception $e, string $context = ''): void
    {
        Log::error("NotificationService Error: {$context}", [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'user_id' => Auth::id(),
        ]);
    }
}
