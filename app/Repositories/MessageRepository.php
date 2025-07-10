<?php

namespace App\Repositories;

use App\Contracts\Repositories\MessageRepositoryInterface;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class MessageRepository extends BaseRepository implements MessageRepositoryInterface
{
    public function __construct(Message $message)
    {
        parent::__construct($message);
    }

    /**
     * Get conversations for user.
     */
    public function getConversationsForUser(int $userId): Collection
    {
        return $this->query()
            ->select('messages.*')
            ->where(function ($query) use ($userId) {
                $query->where('sender_id', $userId)
                    ->orWhere('receiver_id', $userId);
            })
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function ($message) use ($userId) {
                return $message->sender_id === $userId
                    ? $message->receiver_id
                    : $message->sender_id;
            })
            ->map(function ($messages) {
                return $messages->first();
            })
            ->values();
    }

    /**
     * Get messages between two users.
     */
    public function getMessagesBetweenUsers(int $user1Id, int $user2Id, int $perPage = 20): LengthAwarePaginator
    {
        return $this->query()
            ->where(function ($query) use ($user1Id, $user2Id) {
                $query->where('sender_id', $user1Id)
                    ->where('receiver_id', $user2Id);
            })
            ->orWhere(function ($query) use ($user1Id, $user2Id) {
                $query->where('sender_id', $user2Id)
                    ->where('receiver_id', $user1Id);
            })
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'asc')
            ->paginate($perPage);
    }

    /**
     * Get unread messages for user.
     */
    public function getUnreadMessagesForUser(int $userId): Collection
    {
        return $this->query()
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->with(['sender'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Count unread messages for user.
     */
    public function countUnreadMessagesForUser(int $userId): int
    {
        return $this->query()
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Mark messages as read.
     */
    public function markMessagesAsRead(int $userId, int $fromUserId): void
    {
        $this->query()
            ->where('receiver_id', $userId)
            ->where('sender_id', $fromUserId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Get latest message between users.
     */
    public function getLatestMessageBetweenUsers(int $user1Id, int $user2Id): ?Message
    {
        return $this->query()
            ->where(function ($query) use ($user1Id, $user2Id) {
                $query->where('sender_id', $user1Id)
                    ->where('receiver_id', $user2Id);
            })
            ->orWhere(function ($query) use ($user1Id, $user2Id) {
                $query->where('sender_id', $user2Id)
                    ->where('receiver_id', $user1Id);
            })
            ->latest()
            ->first();
    }

    /**
     * Search messages by content.
     */
    public function searchMessages(int $userId, string $query): Collection
    {
        return $this->query()
            ->where(function ($q) use ($userId) {
                $q->where('sender_id', $userId)
                    ->orWhere('receiver_id', $userId);
            })
            ->where('content', 'like', '%' . $query . '%')
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get message statistics for user.
     */
    public function getMessageStatistics(int $userId): array
    {
        $totalSent = $this->query()->where('sender_id', $userId)->count();
        $totalReceived = $this->query()->where('receiver_id', $userId)->count();
        $unreadCount = $this->countUnreadMessagesForUser($userId);

        return [
            'total_sent' => $totalSent,
            'total_received' => $totalReceived,
            'total_messages' => $totalSent + $totalReceived,
            'unread_count' => $unreadCount,
            'unique_conversations' => $this->getConversationsForUser($userId)->count(),
        ];
    }
}
