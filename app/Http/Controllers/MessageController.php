<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    public function index()
    {
        // Get all users the current user has exchanged messages with
        $userConversations = $this->getUserConversations();

        return view('messages.index', compact('userConversations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string|max:1000',
        ]);

        $message = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $validated['receiver_id'],
            'message' => $validated['message'],
        ]);

        return back()->with('success', 'Message sent successfully.');
    }

    public function show(User $user)
    {
        // Mark all messages from this user as read
        Message::where('sender_id', $user->id)
              ->where('receiver_id', Auth::id())
              ->whereNull('read_at')
              ->update(['read_at' => now()]);

        $messages = Message::where(function($query) use ($user) {
            $query->where('sender_id', Auth::id())
                  ->where('receiver_id', $user->id);
        })->orWhere(function($query) use ($user) {
            $query->where('sender_id', $user->id)
                  ->where('receiver_id', Auth::id());
        })
        ->with(['sender', 'receiver'])
        ->latest()
        ->paginate(20);

        return view('messages.show', compact('messages', 'user'));
    }

    private function getUserConversations()
    {
        $userId = Auth::id();

        // Get latest message with each user
        $latestMessages = DB::table('messages as m1')
            ->select('m1.*')
            ->join(DB::raw('(
                SELECT
                    CASE
                        WHEN sender_id = ' . $userId . ' THEN receiver_id
                        ELSE sender_id
                    END as user_id,
                    MAX(created_at) as max_created_at
                FROM messages
                WHERE sender_id = ' . $userId . ' OR receiver_id = ' . $userId . '
                GROUP BY user_id
            ) as m2'), function($join) use ($userId) {
                $join->on(function($query) use ($userId) {
                    $query->on(DB::raw('CASE WHEN m1.sender_id = ' . $userId . ' THEN m1.receiver_id ELSE m1.sender_id END'), '=', 'm2.user_id')
                          ->on('m1.created_at', '=', 'm2.max_created_at');
                });
            })
            ->orderBy('m1.created_at', 'desc')
            ->get();

        // Get user details and unread counts
        $conversations = [];
        foreach ($latestMessages as $message) {
            $otherUserId = $message->sender_id == $userId ? $message->receiver_id : $message->sender_id;

            $user = User::with(['tutor' => function($query) {
                $query->with('subjects');
            }])->find($otherUserId);

            $unreadCount = Message::where('sender_id', $otherUserId)
                ->where('receiver_id', $userId)
                ->whereNull('read_at')
                ->count();

            $conversations[] = [
                'user' => $user,
                'last_message' => $message,
                'unread_count' => $unreadCount
            ];
        }

        return $conversations;
    }
}
