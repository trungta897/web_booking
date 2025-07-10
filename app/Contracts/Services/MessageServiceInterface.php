<?php

namespace App\Contracts\Services;

use App\Models\Message;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface MessageServiceInterface extends ServiceInterface
{
    /**
     * Send new message.
     */
    public function sendMessage(array $data): Message;

    /**
     * Get messages between users.
     */
    public function getMessagesBetweenUsers(int $user1Id, int $user2Id): LengthAwarePaginator;

    /**
     * Delete message.
     */
    public function deleteMessage(int $messageId, int $userId): bool;

    /**
     * Check if user can message another user.
     */
    public function canUserMessage(int $fromUserId, int $toUserId): bool;

    /**
     * Get conversation previews for user.
     */
    public function getConversationPreviews(int $userId): Collection;

    /**
     * Mark messages as read in bulk.
     */
    public function bulkMarkAsRead(int $userId, array $messageIds): bool;

    /**
     * Get unread message count.
     */
    public function getUnreadCount(int $userId): int;
}
