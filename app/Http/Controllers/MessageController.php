<?php

namespace App\Http\Controllers;

use App\Http\Requests\MessageRequest;
use App\Models\Message;
use App\Models\User;
use App\Services\MessageService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MessageController extends Controller
{
    protected MessageService $messageService;

    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
    }

    /**
     * Display conversations list
     */
    public function index(): View
    {
        $userConversations = $this->messageService->getConversationPreviews(Auth::id());

        return view('messages.index', compact('userConversations'));
    }

    /**
     * Store new message
     */
    public function store(MessageRequest $request): RedirectResponse
    {
        try {
            $this->messageService->sendMessage(Auth::user(), $request->validated());

            return back()->with('success', __('Message sent successfully'));

        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display conversation with specific user
     */
    public function show(User $user): View|RedirectResponse
    {
        try {
            $conversationData = $this->messageService->getConversationWith($user->id);

            return view('messages.show', [
                'user' => $user,
                'messages' => $conversationData['messages'],
                'unread_count' => $conversationData['unread_count'],
            ]);

        } catch (Exception $e) {
            return redirect()->route('messages.index')
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Mark message as read
     */
    public function markAsRead(Message $message): RedirectResponse
    {
        try {
            // Check if user is the receiver
            if ($message->receiver_id !== Auth::id()) {
                throw new Exception(__('Unauthorized action'));
            }

            if (! $message->read_at) {
                $message->update(['read_at' => now()]);
            }

            return back()->with('success', __('Message marked as read'));

        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Delete message
     */
    public function destroy(Message $message): RedirectResponse
    {
        try {
            $result = $this->messageService->deleteMessage($message, Auth::user());

            if ($result) {
                return back()->with('success', __('Message deleted successfully'));
            }

            return back()->withErrors(['error' => __('Failed to delete message')]);

        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
