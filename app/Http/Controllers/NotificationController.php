<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display notifications list.
     */
    public function index(): View
    {
        $notifications = $this->notificationService->getUserNotifications(Auth::user());

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Display specific notification.
     */
    public function show(DatabaseNotification $notification): View|RedirectResponse
    {
        try {
            $this->authorizeNotification($notification);

            $this->notificationService->markAsRead($notification);

            return view('notifications.show', compact('notification'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(DatabaseNotification $notification): RedirectResponse
    {
        try {
            $this->authorizeNotification($notification);

            $this->notificationService->markAsRead($notification);

            return redirect()->back()->with('success', __('Notification marked as read'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(): RedirectResponse
    {
        try {
            $this->notificationService->markAllAsRead(Auth::user());

            return redirect()->back()->with('success', __('All notifications marked as read'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete notification.
     */
    public function destroy(DatabaseNotification $notification): RedirectResponse
    {
        try {
            $this->authorizeNotification($notification);

            $this->notificationService->deleteNotification($notification);

            return redirect()->back()->with('success', __('Notification deleted'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete all notifications.
     */
    public function destroyAll(): RedirectResponse
    {
        try {
            $this->notificationService->deleteAllNotifications(Auth::user());

            return redirect()->back()->with('success', __('All notifications deleted'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get unread notification count (for AJAX).
     */
    public function getUnreadCount(): \Illuminate\Http\JsonResponse
    {
        try {
            $count = $this->notificationService->getUnreadCount(Auth::user());

            return response()->json(['count' => $count]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Authorize notification access.
     */
    protected function authorizeNotification(DatabaseNotification $notification): void
    {
        if ($notification->notifiable_id !== Auth::id()) {
            throw new Exception(__('Unauthorized action'));
        }
    }
}
