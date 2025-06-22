<?php

namespace App\Services;

use App\Models\Message;
use App\Models\User;
use App\Repositories\MessageRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Exception;

class MessageService extends BaseService
{
    protected MessageRepository $messageRepository;

    public function __construct()
    {
        $this->messageRepository = new MessageRepository(new Message());
    }

    /**
     * Get conversations for current user
     */
    public function getUserConversations(?int $userId = null): Collection
    {
        $userId = $userId ?? Auth::id();

        return $this->messageRepository->getConversationsForUser($userId);
    }

    /**
     * Get messages between users
     */
    public function getMessagesBetweenUsers(int $otherUserId, ?int $currentUserId = null, int $perPage = 20): LengthAwarePaginator
    {
        $currentUserId = $currentUserId ?? Auth::id();

        // Mark messages as read when viewing conversation
        $this->markMessagesAsRead($currentUserId, $otherUserId);

        return $this->messageRepository->getMessagesBetweenUsers($currentUserId, $otherUserId, $perPage);
    }

    /**
     * Send a message
     */
    public function sendMessage(int $receiverId, string $content, ?int $senderId = null): Message
    {
        return $this->executeTransaction(function () use ($receiverId, $content, $senderId) {
            $senderId = $senderId ?? Auth::id();

            // Validate users exist
            $sender = User::findOrFail($senderId);
            $receiver = User::findOrFail($receiverId);

            // Prevent sending message to self
            if ($senderId === $receiverId) {
                throw new Exception(__('You cannot send a message to yourself'));
            }

            // Create message
            $message = $this->messageRepository->create([
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'content' => trim($content),
                'is_read' => false,
            ]);

            $this->logActivity('Message sent', [
                'message_id' => $message->id,
                'sender_id' => $senderId,
                'receiver_id' => $receiverId
            ]);

            return $message;
        });
    }

    /**
     * Mark messages as read
     */
    public function markMessagesAsRead(int $receiverId, int $senderId): bool
    {
        $this->messageRepository->markMessagesAsRead($receiverId, $senderId);

        $this->logActivity('Messages marked as read', [
            'receiver_id' => $receiverId,
            'sender_id' => $senderId
        ]);

        return true;
    }

    /**
     * Get unread messages for user
     */
    public function getUnreadMessages(?int $userId = null): Collection
    {
        $userId = $userId ?? Auth::id();

        return $this->messageRepository->getUnreadMessagesForUser($userId);
    }

    /**
     * Count unread messages for user
     */
    public function getUnreadCount(?int $userId = null): int
    {
        $userId = $userId ?? Auth::id();

        return $this->messageRepository->countUnreadMessagesForUser($userId);
    }

    /**
     * Delete a message
     */
    public function deleteMessage(int $messageId, ?int $userId = null): bool
    {
        return $this->executeTransaction(function () use ($messageId, $userId) {
            $userId = $userId ?? Auth::id();
            $message = Message::findOrFail($messageId);

            // Check permission - only sender can delete
            if ($message->sender_id !== $userId) {
                throw new Exception(__('You can only delete your own messages'));
            }

            $result = $this->messageRepository->delete($message);

            if ($result) {
                $this->logActivity('Message deleted', [
                    'message_id' => $messageId,
                    'sender_id' => $message->sender_id
                ]);
            }

            return $result;
        });
    }

    /**
     * Search messages for user
     */
    public function searchMessages(string $query, ?int $userId = null): Collection
    {
        $userId = $userId ?? Auth::id();

        if (empty(trim($query))) {
            return collect();
        }

        return $this->messageRepository->searchMessages($userId, $query);
    }

    /**
     * Get conversation with user
     */
    public function getConversationWith(int $otherUserId, ?int $currentUserId = null): array
    {
        $currentUserId = $currentUserId ?? Auth::id();

        $otherUser = User::findOrFail($otherUserId);
        $messages = $this->getMessagesBetweenUsers($otherUserId, $currentUserId);
        $unreadCount = $this->messageRepository->countUnreadMessagesForUser($currentUserId);

        return [
            'other_user' => $otherUser,
            'messages' => $messages,
            'unread_count' => $unreadCount,
            'latest_message' => $this->messageRepository->getLatestMessageBetweenUsers($currentUserId, $otherUserId)
        ];
    }

    /**
     * Get message statistics for user
     */
    public function getMessageStatistics(?int $userId = null): array
    {
        $userId = $userId ?? Auth::id();

        $stats = $this->messageRepository->getMessageStatistics($userId);

        return [
            'total_sent' => $stats['total_sent'],
            'total_received' => $stats['total_received'],
            'total_messages' => $stats['total_messages'],
            'unread_count' => $stats['unread_count'],
            'unique_conversations' => $stats['unique_conversations'],
            'formatted_stats' => [
                'total_sent' => number_format($stats['total_sent']),
                'total_received' => number_format($stats['total_received']),
                'total_messages' => number_format($stats['total_messages']),
                'unread_count' => number_format($stats['unread_count']),
            ]
        ];
    }

    /**
     * Check if user can message another user
     */
    public function canUserMessage(int $senderId, int $receiverId): bool
    {
        // Basic checks
        if ($senderId === $receiverId) {
            return false;
        }

        $sender = User::find($senderId);
        $receiver = User::find($receiverId);

        if (!$sender || !$receiver) {
            return false;
        }

        // Check if both users are active
        if ($sender->account_status !== 'active' || $receiver->account_status !== 'active') {
            return false;
        }

        // Additional business rules can be added here
        // For example: check if they have had a booking together, etc.

        return true;
    }

    /**
     * Get conversation preview for dashboard
     */
    public function getConversationPreviews(?int $userId = null, int $limit = 5): Collection
    {
        $userId = $userId ?? Auth::id();

        $conversations = $this->getUserConversations($userId);

        return $conversations->take($limit)->map(function ($message) use ($userId) {
            $otherUser = $message->sender_id === $userId ? $message->receiver : $message->sender;

            return [
                'user' => $otherUser,
                'latest_message' => $message,
                'unread_count' => $this->messageRepository->countUnreadMessagesForUser($userId),
                'time_ago' => $message->created_at->diffForHumans(),
            ];
        });
    }

    /**
     * Bulk mark messages as read
     */
    public function bulkMarkAsRead(array $messageIds, ?int $userId = null): int
    {
        $userId = $userId ?? Auth::id();

        return $this->executeTransaction(function () use ($messageIds, $userId) {
            $count = Message::whereIn('id', $messageIds)
                ->where('receiver_id', $userId)
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now()
                ]);

            if ($count > 0) {
                $this->logActivity('Bulk messages marked as read', [
                    'user_id' => $userId,
                    'count' => $count
                ]);
            }

            return $count;
        });
    }

    /**
     * Handle errors specific to message service
     */
    public function handleError(\Exception $e, string $context = ''): void
    {
        $this->logError($context ?: 'Message service error occurred', $e);
        throw $e;
    }
}
