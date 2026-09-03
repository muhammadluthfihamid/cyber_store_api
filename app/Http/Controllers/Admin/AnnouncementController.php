<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of announcements.
     */
    public function index()
    {
        $announcements = Announcement::withCount('userNotifications')
            ->latest()
            ->paginate(15);

        return view('admin.announcements.index', compact('announcements'));
    }

    /**
     * Store a newly created announcement and broadcast to users.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'      => ['required', 'string', 'max:255'],
            'content'    => ['required', 'string', 'max:2000'],
            'type'       => ['required', 'in:info,promo,system'],
            'action_url' => ['nullable', 'url'],
        ]);

        $announcement = Announcement::create($validated);

        // Broadcast notification entry to all active customers
        $customers = User::query()->where('is_active', '=', true)->pluck('id');
        $notificationData = [];

        foreach ($customers as $userId) {
            $notificationData[] = [
                'user_id'         => $userId,
                'announcement_id' => $announcement->id,
                'created_at'      => now(),
                'updated_at'      => now(),
            ];
        }

        if (!empty($notificationData)) {
            UserNotification::insert($notificationData);
        }

        // Optional: Send FCM Push Notification payload to users with FCM token
        $this->sendPushNotifications($announcement);

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Pengumuman berhasil dibuat dan dikirim ke seluruh pengguna!');
    }

    /**
     * Display the specified announcement details.
     */
    public function show(Announcement $announcement)
    {
        $announcement->loadCount([
            'userNotifications',
            'userNotifications as read_count' => function ($query) {
                $query->whereNotNull('read_at');
            },
            'userNotifications as unread_count' => function ($query) {
                $query->whereNull('read_at');
            }
        ]);

        $recipients = $announcement->userNotifications()
            ->with('user')
            ->latest()
            ->paginate(15);

        return view('admin.announcements.show', compact('announcement', 'recipients'));
    }

    /**
     * Delete an announcement.
     */
    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Pengumuman berhasil dihapus.');
    }

    /**
     * Send FCM Push Notification to registered devices (Helper).
     */
    private function sendPushNotifications(Announcement $announcement)
    {
        $fcmTokens = User::query()
            ->whereNotNull('fcm_token', 'and')
            ->where('push_notifications_enabled', '=', true)
            ->pluck('fcm_token')
            ->filter()
            ->values()
            ->toArray();

        if (empty($fcmTokens)) {
            return;
        }

        $serverKey = env('FCM_SERVER_KEY');
        if (!$serverKey) {
            Log::info('FCM Push Notification skipped: FCM_SERVER_KEY not configured in .env');
            return;
        }

        try {
            foreach ($fcmTokens as $token) {
                Http::withHeaders([
                    'Authorization' => 'key=' . $serverKey,
                    'Content-Type'  => 'application/json',
                ])->post('https://fcm.googleapis.com/fcm/send', [
                    'to' => $token,
                    'notification' => [
                        'title' => $announcement->title,
                        'body'  => $announcement->content,
                        'sound' => 'default',
                    ],
                    'data' => [
                        'announcement_id' => (string) $announcement->id,
                        'type'            => $announcement->type,
                        'click_action'    => 'FLUTTER_NOTIFICATION_CLICK',
                    ],
                ]);
            }
        } catch (\Exception $e) {
            Log::error('FCM Push Notification dispatch failed: ' . $e->getMessage());
        }
    }
}
