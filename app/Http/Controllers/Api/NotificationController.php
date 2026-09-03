<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Sync global announcements (non-chat) to this user if not yet present in user_notifications
        $announcements = Announcement::where(function ($q) {
            $q->whereNull('type')->orWhere('type', '!=', 'chat');
        })->get();
        foreach ($announcements as $announcement) {
            UserNotification::firstOrCreate([
                'user_id'         => $user->id,
                'announcement_id' => $announcement->id,
            ]);
        }

        $notifications = UserNotification::with('announcement')
            ->where('user_id', $user->id)
            ->latest('id')
            ->get()
            ->map(function ($un) {
                return [
                    'id'              => $un->id,
                    'announcement_id' => $un->announcement_id,
                    'title'           => $un->announcement->title ?? 'Pengumuman',
                    'content'         => $un->announcement->content ?? '',
                    'type'            => $un->announcement->type ?? 'info',
                    'action_url'      => $un->announcement->action_url ?? null,
                    'is_read'         => $un->read_at !== null,
                    'read_at'         => $un->read_at ? $un->read_at->toIso8601String() : null,
                    'created_at'      => $un->created_at ? $un->created_at->toIso8601String() : null,
                ];
            });

        $unreadCount = UserNotification::where('user_id', '=', $user->id)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'unread_count'  => $unreadCount,
            'notifications' => $notifications,
        ]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(Request $request, int|string $id): JsonResponse
    {
        $user = $request->user();
        $notification = UserNotification::where('user_id', '=', $user->id)
            ->where('id', '=', $id)
            ->first();

        if ($notification && !$notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        return response()->json([
            'message' => 'Notifikasi ditandai sudah dibaca.',
        ]);
    }

    /**
     * Mark all notifications as read for the user.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();
        UserNotification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'message' => 'Semua notifikasi ditandai sudah dibaca.',
        ]);
    }

    /**
     * Update user's FCM device token for push notifications.
     */
    public function updateFcmToken(Request $request): JsonResponse
    {
        $request->validate([
            'fcm_token' => ['required', 'string'],
        ]);

        $request->user()->update([
            'fcm_token' => $request->input('fcm_token'),
        ]);

        return response()->json([
            'message' => 'FCM token berhasil disimpan.',
        ]);
    }
}
