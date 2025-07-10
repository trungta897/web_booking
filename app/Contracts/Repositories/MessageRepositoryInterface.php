<?php

namespace App\Contracts\Repositories;

use App\Models\Message;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface MessageRepositoryInterface extends RepositoryInterface
{
    /**
     * Get conversations for user.
     */
    public function getConversationsForUser(int $userId): Collection;

    /**
     * Get messages between users.
     */
    public function getMessagesBetweenUsers(int $user1Id, int $user2Id): LengthAwarePaginator;

    /**
     * Get unread messages for user.
     */
    public function getUnreadMessagesForUser(int $userId): Collection;

    /**
     * Mark messages as read.
     */
    public function markMessagesAsRead(int $userId, int $fromUserId): void;

    /**
     * Search messages.
     */
    public function searchMessages(int $userId, string $query): Collection;

    /**
     * Get message statistics.
     */
    public function getMessageStatistics(int $userId): array;
}
