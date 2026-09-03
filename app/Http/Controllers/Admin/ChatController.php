<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ChatController extends Controller
{
    public static function flushRedisCache(): void
    {
        try {
            $redis = Cache::store('redis');
            $redis->increment('admin:chats:version');
        } catch (\Throwable $e) {
            Cache::forget('admin:chats:version');
        }
    }

    /**
     * GET /admin/chats
     * Daftar semua percakapan customer.
     */
    public function index(Request $request)
    {
        $status = $request->query('status');
        $search = trim((string)$request->query('search', ''));
        $page = (int)$request->query('page', 1);

        try {
            $version = Cache::store('redis')->get('admin:chats:version', 1);
            $cacheKey = "admin:chats:v{$version}:" . md5(json_encode([
                'status' => $status,
                'search' => $search,
                'page' => $page,
            ]));

            $cachedData = Cache::store('redis')->remember($cacheKey, now()->addMinutes(5), function () use ($status, $search) {
                $query = Chat::query()
                    ->orderByDesc('last_message_at')
                    ->orderByDesc('created_at');

                if ($status !== null && $status !== '') {
                    $query->where('status', $status);
                }

                if ($search !== '') {
                    $query->where(function ($q) use ($search) {
                        $q->where('subject', 'like', "%{$search}%")
                            ->orWhere('product_name', 'like', "%{$search}%")
                            ->orWhereHas('customer', fn ($u) => $u->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%"));
                    });
                }

                $paginator = $query->paginate(20);
                return [
                    'ids' => $paginator->pluck('id')->toArray(),
                    'total' => $paginator->total(),
                    'per_page' => $paginator->perPage(),
                    'current_page' => $paginator->currentPage(),
                ];
            });

            $ids = $cachedData['ids'] ?? [];
            $items = empty($ids)
                ? collect()
                : Chat::with(['customer', 'lastMessage'])
                    ->withCount(['messages as unread_count' => function ($q) {
                        $q->where('sender_type', 'customer')->where('is_read', false);
                    }])
                    ->whereIn('id', $ids)
                    ->get()
                    ->sortBy(fn ($c) => array_search($c->id, $ids))
                    ->values();

            $chats = new LengthAwarePaginator(
                $items,
                $cachedData['total'] ?? 0,
                $cachedData['per_page'] ?? 20,
                $cachedData['current_page'] ?? 1,
                [
                    'path' => Paginator::resolveCurrentPath(),
                    'query' => $request->query(),
                ]
            );
        } catch (\Throwable $e) {
            $query = Chat::with(['customer', 'lastMessage'])
                ->withCount(['messages as unread_count' => function ($q) {
                    $q->where('sender_type', 'customer')->where('is_read', false);
                }])
                ->orderByDesc('last_message_at')
                ->orderByDesc('created_at');

            if ($status !== null && $status !== '') {
                $query->where('status', $status);
            }

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('subject', 'like', "%{$search}%")
                        ->orWhere('product_name', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($u) => $u->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%"));
                });
            }

            $chats = $query->paginate(20)->withQueryString();
        }

        // Total unread untuk badge di navbar
        $totalUnread = Chat::whereHas('messages', fn ($q) => $q->where('sender_type', 'customer')->where('is_read', false)
        )->count();

        if ($request->wantsJson()) {
            return response()->json([
                'chats' => collect($chats->items())->map(function ($c) {
                    return [
                        'id' => $c->id,
                        'url' => route('admin.chats.show', $c),
                        'customer_name' => $c->customer?->name ?? 'Customer',
                        'initials' => strtoupper(substr($c->customer?->name ?? 'C', 0, 1)),
                        'unread_count' => (int) ($c->unread_count ?? 0),
                        'product_name' => $c->product_name,
                        'last_message' => $c->lastMessage?->message ?? 'Memulai percakapan...',
                        'last_message_at' => $c->last_message_at?->toIso8601String(),
                        'status' => $c->status,
                    ];
                }),
                'total_unread' => $totalUnread,
            ]);
        }

        return view('admin.chats.index', compact('chats', 'totalUnread'));
    }

    public function suggestions(Request $request)
    {
        $q = trim((string)$request->query('q', ''));

        try {
            $version = Cache::store('redis')->get('admin:chats:version', 1);
            $cacheKey = "admin:chats:suggestions:v{$version}:" . md5($q);

            $results = Cache::store('redis')->remember($cacheKey, now()->addMinutes(5), function () use ($q) {
                $query = Chat::with(['customer', 'lastMessage'])->orderByDesc('last_message_at');
                if ($q !== '') {
                    $query->where(function ($w) use ($q) {
                        $w->where('subject', 'like', "%{$q}%")
                          ->orWhere('product_name', 'like', "%{$q}%")
                          ->orWhereHas('customer', fn ($u) => $u->where('name', 'like', "%{$q}%")
                              ->orWhere('email', 'like', "%{$q}%")
                              ->orWhere('phone', 'like', "%{$q}%"));
                    });
                }
                return $query->take(8)->get()->map(function ($c) {
                    $name = $c->customer?->name ?? 'Customer';
                    $lastMsg = $c->lastMessage?->message ?? 'Percakapan baru';
                    if (\Illuminate\Support\Str::startsWith($lastMsg, '[IMAGE]:')) {
                        $lastMsg = '<iconify-icon icon="flat-color-icons:picture" style="font-size: 13px; vertical-align: middle;"></iconify-icon> [Gambar]';
                    }
                    $prod = $c->product_name ? ' • <iconify-icon icon="flat-color-icons:box" style="font-size: 13px; vertical-align: middle;"></iconify-icon> ' . $c->product_name : '';

                    return [
                        'id' => $c->id,
                        'title' => $name,
                        'subtitle' => $lastMsg . $prod,
                        'value' => $name,
                        'badge' => $c->status === 'closed' ? 'Selesai' : 'Aktif',
                        'badge_color' => $c->status === 'closed' ? '#64748B' : '#10B981',
                        'icon' => 'flat-color-icons:comments',
                    ];
                });
            });
        } catch (\Throwable $e) {
            $query = Chat::with(['customer', 'lastMessage'])->orderByDesc('last_message_at');
            if ($q !== '') {
                $query->where(function ($w) use ($q) {
                    $w->where('subject', 'like', "%{$q}%")
                      ->orWhere('product_name', 'like', "%{$q}%")
                      ->orWhereHas('customer', fn ($u) => $u->where('name', 'like', "%{$q}%")
                          ->orWhere('email', 'like', "%{$q}%")
                          ->orWhere('phone', 'like', "%{$q}%"));
                });
            }
            $results = $query->take(8)->get()->map(function ($c) {
                $name = $c->customer?->name ?? 'Customer';
                $lastMsg = $c->lastMessage?->message ?? 'Percakapan baru';
                if (\Illuminate\Support\Str::startsWith($lastMsg, '[IMAGE]:')) {
                    $lastMsg = '<iconify-icon icon="flat-color-icons:picture" style="font-size: 13px; vertical-align: middle;"></iconify-icon> [Gambar]';
                }
                $prod = $c->product_name ? ' • <iconify-icon icon="flat-color-icons:box" style="font-size: 13px; vertical-align: middle;"></iconify-icon> ' . $c->product_name : '';

                return [
                    'id' => $c->id,
                    'title' => $name,
                    'subtitle' => $lastMsg . $prod,
                    'value' => $name,
                    'badge' => $c->status === 'closed' ? 'Selesai' : 'Aktif',
                    'badge_color' => $c->status === 'closed' ? '#64748B' : '#10B981',
                    'icon' => 'flat-color-icons:comments',
                ];
            });
        }

        return response()->json($results);
    }

    /**
     * GET /admin/chats/{chat}
     * Detail percakapan + form balas.
     */
    public function show(Request $request, Chat $chat)
    {
        $afterId = $request->query('after_id');

        if ($request->wantsJson()) {
            // Tandai semua pesan customer sebagai sudah dibaca oleh admin
            $chat->messages()
                ->where('sender_type', 'customer')
                ->where('is_read', false)
                ->update(['is_read' => true]);

            static::flushRedisCache();

            $messagesQuery = $chat->messages()->with('sender');
            if ($afterId) {
                $messagesQuery->where('id', '>', (int)$afterId);
            }

            $messages = $messagesQuery->orderBy('created_at')->get();

            return response()->json([
                'chat' => [
                    'id' => $chat->id,
                    'status' => $chat->status,
                    'subject' => $chat->subject,
                    'product_name' => $chat->product_name,
                    'customer_name' => $chat->customer?->name ?? 'Customer',
                ],
                'messages' => $messages->map(function ($m) {
                    return [
                        'id' => $m->id,
                        'sender_type' => $m->sender_type,
                        'sender_name' => $m->sender?->name ?? 'Admin',
                        'message' => $m->message,
                        'is_read' => (bool) $m->is_read,
                        'created_at' => $m->created_at->toIso8601String(),
                        'formatted_time' => $m->created_at->setTimezone('Asia/Jakarta')->format('H:i'),
                    ];
                }),
            ]);
        }

        $chat->load(['customer', 'product', 'messages.sender']);

        // Tandai semua pesan customer sebagai sudah dibaca oleh admin
        $chat->messages()
            ->where('sender_type', 'customer')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        static::flushRedisCache();

        $query = Chat::with(['customer', 'lastMessage'])
            ->withCount(['messages as unread_count' => function ($q) {
                $q->where('sender_type', 'customer')->where('is_read', false);
            }]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('product_name', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($u) => $u->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%"));
            });
        }

        $chats = $query->orderByDesc('last_message_at')
            ->orderByDesc('created_at')
            ->take(50)
            ->get();

        $totalUnread = Chat::whereHas('messages', fn ($q) => $q->where('sender_type', 'customer')->where('is_read', false)
        )->count();

        return view('admin.chats.index', compact('chat', 'chats', 'totalUnread'));
    }

    /**
     * POST /admin/chats/{chat}/reply
     * Admin membalas pesan.
     */
    public function reply(Request $request, Chat $chat)
    {
        $request->validate(['message' => 'required|string|max:10000000']);

        if ($chat->status === 'closed') {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Chat sudah ditutup.'], 422);
            }

            return back()->with('error', 'Chat sudah ditutup.');
        }

        $msgText = $request->input('message');

        $msg = ChatMessage::create([
            'chat_id' => $chat->id,
            'sender_type' => 'admin',
            'sender_id' => Auth::id(),
            'message' => $msgText,
            'is_read' => false,
        ]);

        $chat->update(['last_message_at' => now()]);

        static::flushRedisCache();

        // Create notification entry for the customer (wrapped in try-catch for resilience)
        if ($chat->customer_id) {
            try {
                if (\Illuminate\Support\Str::startsWith($msgText, '[IMAGE]:')) {
                    $snippet = '📷 Admin mengirimkan foto';
                } elseif (\Illuminate\Support\Str::startsWith($msgText, '[STICKER]:')) {
                    $snippet = '😄 Admin mengirimkan stiker';
                } else {
                    $snippet = \Illuminate\Support\Str::limit(strip_tags($msgText), 120);
                }

                $announcement = \App\Models\Announcement::create([
                    'title'      => 'Pesan Baru dari Admin',
                    'content'    => $snippet,
                    'type'       => 'chat',
                    'action_url' => '/chat',
                ]);

                \App\Models\UserNotification::create([
                    'user_id'         => $chat->customer_id,
                    'announcement_id' => $announcement->id,
                    'read_at'         => null,
                ]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to create chat notification: ' . $e->getMessage());
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $msg->id,
                    'sender_type' => $msg->sender_type,
                    'sender_name' => Auth::user()?->name ?? 'Admin',
                    'message' => $msg->message,
                    'is_read' => (bool) $msg->is_read,
                    'created_at' => $msg->created_at->toIso8601String(),
                    'formatted_time' => $msg->created_at->setTimezone('Asia/Jakarta')->format('H:i'),
                ],
            ]);
        }

        return back()->with('success', 'Balasan terkirim.');
    }

    /**
     * POST /admin/chats/{chat}/close
     * Tutup percakapan.
     */
    public function close(Chat $chat)
    {
        $chat->update(['status' => 'closed']);

        static::flushRedisCache();

        return back()->with('success', 'Chat ditutup.');
    }

    /**
     * POST /admin/chats/{chat}/reopen
     * Buka kembali percakapan.
     */
    public function reopen(Chat $chat)
    {
        $chat->update(['status' => 'open']);

        static::flushRedisCache();

        return back()->with('success', 'Chat dibuka kembali.');
    }

    /**
     * GET /admin/chats/unread-count (JSON — untuk badge polling JS)
     */
    public function unreadCount()
    {
        $count = Chat::whereHas('messages', fn ($q) => $q->where('sender_type', 'customer')->where('is_read', false)
        )->count();

        return response()->json(['unread' => $count]);
    }
}
