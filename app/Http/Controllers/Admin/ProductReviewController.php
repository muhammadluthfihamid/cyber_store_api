<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use App\Models\ProductReviewReply;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ProductReviewController extends Controller
{
    public static function flushRedisCache(): void
    {
        try {
            $redis = Cache::store('redis');
            $redis->increment('admin:review_chats:version');
        } catch (\Throwable $e) {
            Cache::forget('admin:review_chats:version');
        }
    }

    /**
     * Display a listing of product review chats.
     */
    public function index(Request $request)
    {
        $search = trim((string)$request->query('search', ''));
        $page = (int)$request->query('page', 1);

        try {
            $version = Cache::store('redis')->get('admin:review_chats:version', 1);
            $cacheKey = "admin:review_chats:v{$version}:" . md5(json_encode([
                'search' => $search,
                'page' => $page,
            ]));

            $cachedData = Cache::store('redis')->remember($cacheKey, now()->addMinutes(10), function () use ($search) {
                $query = ProductReview::query()
                    ->whereNotNull('comment')
                    ->where('comment', '!=', '')
                    ->latest('id');

                if ($search !== '') {
                    $query->where(function ($q) use ($search) {
                        $q->where('comment', 'like', "%{$search}%")
                            ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                            ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$search}%"));
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
                : ProductReview::with(['user', 'product', 'replies'])
                    ->whereIn('id', $ids)
                    ->get()
                    ->sortBy(fn ($r) => array_search($r->id, $ids))
                    ->values();

            $reviews = new LengthAwarePaginator(
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
            $query = ProductReview::with(['user', 'product', 'replies'])
                ->whereNotNull('comment')
                ->where('comment', '!=', '')
                ->latest();

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('comment', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                        ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$search}%"));
                });
            }

            $reviews = $query->paginate(20)->withQueryString();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'reviews' => $reviews->items(),
            ]);
        }

        return view('admin.review-chats.index', compact('reviews'));
    }

    public function suggestions(Request $request)
    {
        $q = trim((string)$request->query('q', ''));

        try {
            $version = Cache::store('redis')->get('admin:review_chats:version', 1);
            $cacheKey = "admin:review_chats:suggestions:v{$version}:" . md5($q);

            $results = Cache::store('redis')->remember($cacheKey, now()->addMinutes(10), function () use ($q) {
                $query = ProductReview::with(['user', 'product'])
                    ->whereNotNull('comment')
                    ->where('comment', '!=', '')
                    ->latest();
                if ($q !== '') {
                    $query->where(function ($w) use ($q) {
                        $w->where('comment', 'like', "%{$q}%")
                          ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"))
                          ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$q}%"));
                    });
                }
                return $query->take(8)->get()->map(function ($r) {
                    $userName = $r->user?->name ?? 'User';
                    $productName = $r->product?->name ?? 'Produk';
                    $stars = str_repeat('⭐', max(1, min(5, (int)$r->rating)));

                    return [
                        'id' => $r->id,
                        'title' => $userName . ' — ' . $productName,
                        'subtitle' => $stars . ' "' . \Illuminate\Support\Str::limit($r->comment, 40) . '"',
                        'value' => $userName,
                        'badge' => $r->is_read ? 'Dibaca' : 'Baru',
                        'badge_color' => $r->is_read ? '#64748B' : '#DF0B2B',
                        'icon' => 'flat-color-icons:comments',
                    ];
                });
            });
        } catch (\Throwable $e) {
            $query = ProductReview::with(['user', 'product'])
                ->whereNotNull('comment')
                ->where('comment', '!=', '')
                ->latest();
            if ($q !== '') {
                $query->where(function ($w) use ($q) {
                    $w->where('comment', 'like', "%{$q}%")
                      ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"))
                      ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$q}%"));
                });
            }
            $results = $query->take(8)->get()->map(function ($r) {
                $userName = $r->user?->name ?? 'User';
                $productName = $r->product?->name ?? 'Produk';
                $stars = str_repeat('⭐', max(1, min(5, (int)$r->rating)));

                return [
                    'id' => $r->id,
                    'title' => $userName . ' — ' . $productName,
                    'subtitle' => $stars . ' "' . \Illuminate\Support\Str::limit($r->comment, 40) . '"',
                    'value' => $userName,
                    'badge' => $r->is_read ? 'Dibaca' : 'Baru',
                    'badge_color' => $r->is_read ? '#64748B' : '#DF0B2B',
                    'icon' => 'flat-color-icons:comments',
                ];
            });
        }

        return response()->json($results);
    }

    /**
     * Get count of unread review chats.
     */
    public function unreadCount()
    {
        $count = ProductReview::where('is_read', false)
            ->whereNotNull('comment')
            ->where('comment', '!=', '')
            ->count();

        return response()->json(['unread' => $count]);
    }

    /**
     * Display the specified review chat.
     */
    public function show(Request $request, ProductReview $review)
    {
        // Mark as read when admin views the review chat
        if (!$review->is_read) {
            $review->update(['is_read' => true]);
            static::flushRedisCache();
        }

        $review->load(['user', 'product', 'replies.user']);

        $query = ProductReview::with(['user', 'product', 'replies'])
            ->whereNotNull('comment')
            ->where('comment', '!=', '')
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('comment', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                    ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$search}%"));
            });
        }

        $reviews = $query->take(50)->get();

        if (request()->wantsJson()) {
            return response()->json([
                'review' => $review,
                'replies' => $review->replies->map(function ($r) {
                    return [
                        'id' => $r->id,
                        'reply' => $r->reply,
                        'admin_name' => $r->user?->name ?? 'Admin',
                        'admin_photo' => $r->user?->photo,
                        'created_at' => $r->created_at->toIso8601String(),
                    ];
                }),
            ]);
        }

        return view('admin.review-chats.index', compact('review', 'reviews'));
    }

    /**
     * Admin submits a reply to the review.
     */
    public function reply(Request $request, ProductReview $review)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $reply = ProductReviewReply::create([
            'product_review_id' => $review->id,
            'user_id' => Auth::id(),
            'reply' => $request->input('message'),
        ]);

        // Fallback update on product_reviews reply column for backward compatibility & mark as read
        $review->update([
            'reply' => $request->input('message'),
            'is_read' => true,
        ]);

        // Clear products and reviews caches
        Cache::forget("reviews:product:{$review->product_id}");
        Cache::forget("product:detail:{$review->product_id}");
        static::flushRedisCache();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'reply' => [
                    'id' => $reply->id,
                    'reply' => $reply->reply,
                    'admin_name' => Auth::user()?->name,
                    'admin_photo' => Auth::user()?->photo,
                    'created_at' => $reply->created_at->toIso8601String(),
                ],
            ]);
        }

        return back()->with('success', 'Respon ulasan berhasil dikirim.');
    }
}
