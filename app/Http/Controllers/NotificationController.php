<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Auth::user()->notifications()->latest()->paginate(20);
        return view('notifications.index', compact('notifications'));
    }

    public function show(DatabaseNotification $notification)
    {
        // Check if the notification belongs to the authenticated user
        if ($notification->notifiable_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        // Mark as read if not already read
        if (!$notification->read_at) {
            $notification->markAsRead();
        }

        return view('notifications.show', compact('notification'));
    }

    public function markAsRead(DatabaseNotification $notification)
    {
        // Check if the notification belongs to the authenticated user
        if ($notification->notifiable_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $notification->markAsRead();

        return redirect()->back()->with('success', 'Notification marked as read.');
    }

    public function destroy(DatabaseNotification $notification)
    {
        // Check if the notification belongs to the authenticated user
        if ($notification->notifiable_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $notification->delete();

        return redirect()->back()->with('success', 'Notification deleted.');
    }
}
