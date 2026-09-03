<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;

class OrderController extends Controller
{
    public static function flushRedisCache(): void
    {
        try {
            $redis = Cache::store('redis');
            $redis->increment('admin:orders:version');
        } catch (\Throwable $e) {
            Cache::forget('admin:orders:version');
        }
    }

    protected array $statusLabels = [
        'pending_payment' => 'Menunggu Bayar',
        'paid'            => 'Dibayar',
        'packed'          => 'Dikemas',
        'shipped'         => 'Dikirim',
        'arrived'         => 'Tiba',
        'completed'       => 'Selesai',
        'cancelled'       => 'Dibatalkan',
    ];

    public function index(Request $request)
    {
        $search = trim((string)$request->query('search', ''));
        $status = $request->query('status');
        $cancel_request = $request->query('cancel_request');
        $page = (int)$request->query('page', 1);

        try {
            $version = Cache::store('redis')->get('admin:orders:version', 1);
            $cacheKey = "admin:orders:v{$version}:" . md5(json_encode([
                'search' => $search,
                'status' => $status,
                'cancel_request' => $cancel_request,
                'page' => $page,
            ]));

            $cachedData = Cache::store('redis')->remember($cacheKey, now()->addMinutes(15), function () use ($search, $status, $cancel_request) {
                $query = Order::query()->latest('id');

                if ($search !== '') {
                    $query->where(function ($q) use ($search) {
                        $q->where('invoice_number', 'like', "%{$search}%")
                            ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%"))
                            ->orWhereHas('items', fn($i) => $i->where('product_name', 'like', "%{$search}%"));
                    });
                }

                if ($status !== null && $status !== '') {
                    $query->where('status', $status);
                }

                if ($cancel_request !== null && $cancel_request !== '') {
                    $query->where('cancel_request_status', $cancel_request);
                }

                $paginator = $query->paginate(15);
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
                : Order::with(['user', 'expedition'])
                    ->whereIn('id', $ids)
                    ->get()
                    ->sortBy(fn ($o) => array_search($o->id, $ids))
                    ->values();

            $orders = new LengthAwarePaginator(
                $items,
                $cachedData['total'] ?? 0,
                $cachedData['per_page'] ?? 15,
                $cachedData['current_page'] ?? 1,
                [
                    'path' => Paginator::resolveCurrentPath(),
                    'query' => $request->query(),
                ]
            );
        } catch (\Throwable $e) {
            $query = Order::with(['user', 'expedition'])->latest();
            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('invoice_number', 'like', "%{$search}%")
                        ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"))
                        ->orWhereHas('items', fn($i) => $i->where('product_name', 'like', "%{$search}%"));
                });
            }
            if ($status !== null && $status !== '') {
                $query->where('status', $status);
            }
            if ($cancel_request !== null && $cancel_request !== '') {
                $query->where('cancel_request_status', $cancel_request);
            }
            $orders = $query->paginate(15)->withQueryString();
        }

        $statusLabels = $this->statusLabels;

        return view('admin.orders.index', compact('orders', 'statusLabels'));
    }

    public function suggestions(Request $request)
    {
        $q = trim((string)$request->query('q', ''));

        try {
            $version = Cache::store('redis')->get('admin:orders:version', 1);
            $cacheKey = "admin:orders:suggestions:v{$version}:" . md5($q);

            $results = Cache::store('redis')->remember($cacheKey, now()->addMinutes(10), function () use ($q) {
                $query = Order::with(['user', 'items'])->latest();
                if ($q !== '') {
                    $query->where(function ($w) use ($q) {
                        $w->where('invoice_number', 'like', "%{$q}%")
                          ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"))
                          ->orWhereHas('items', fn($i) => $i->where('product_name', 'like', "%{$q}%"));
                    });
                }
                return $query->take(8)->get()->map(function ($order) {
                    $statusLabels = [
                        'pending_payment' => 'Menunggu Bayar',
                        'paid'            => 'Dibayar',
                        'packed'          => 'Dikemas',
                        'shipped'         => 'Dikirim',
                        'arrived'         => 'Tiba',
                        'completed'       => 'Selesai',
                        'cancelled'       => 'Dibatalkan',
                    ];
                    $customerName = $order->user?->name ?? 'Customer';
                    $firstItem = $order->items->first()?->product_name ?? '';
                    $itemsCount = $order->items->count();
                    $itemDesc = $firstItem ? ($itemsCount > 1 ? "{$firstItem} (+".($itemsCount-1)." item)" : $firstItem) : '';
                    
                    return [
                        'id' => $order->id,
                        'title' => $order->invoice_number,
                        'subtitle' => $customerName . ($itemDesc ? ' • ' . $itemDesc : '') . ' • Rp ' . number_format($order->grand_total, 0, ',', '.'),
                        'value' => $order->invoice_number,
                        'badge' => $statusLabels[$order->status] ?? $order->status,
                        'badge_status' => $order->status,
                        'icon' => 'flat-color-icons:shopping-cart',
                    ];
                });
            });
        } catch (\Throwable $e) {
            $query = Order::with(['user', 'items'])->latest();
            if ($q !== '') {
                $query->where(function ($w) use ($q) {
                    $w->where('invoice_number', 'like', "%{$q}%")
                      ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"))
                      ->orWhereHas('items', fn($i) => $i->where('product_name', 'like', "%{$q}%"));
                });
            }
            $results = $query->take(8)->get()->map(function ($order) {
                $statusLabels = [
                    'pending_payment' => 'Menunggu Bayar',
                    'paid'            => 'Dibayar',
                    'packed'          => 'Dikemas',
                    'shipped'         => 'Dikirim',
                    'arrived'         => 'Tiba',
                    'completed'       => 'Selesai',
                    'cancelled'       => 'Dibatalkan',
                ];
                $customerName = $order->user?->name ?? 'Customer';
                $firstItem = $order->items->first()?->product_name ?? '';
                $itemsCount = $order->items->count();
                $itemDesc = $firstItem ? ($itemsCount > 1 ? "{$firstItem} (+".($itemsCount-1)." item)" : $firstItem) : '';
                
                return [
                    'id' => $order->id,
                    'title' => $order->invoice_number,
                    'subtitle' => $customerName . ($itemDesc ? ' • ' . $itemDesc : '') . ' • Rp ' . number_format($order->grand_total, 0, ',', '.'),
                    'value' => $order->invoice_number,
                    'badge' => $statusLabels[$order->status] ?? $order->status,
                    'badge_status' => $order->status,
                    'icon' => 'flat-color-icons:shopping-cart',
                ];
            });
        }

        return response()->json($results);
    }


    public function show(Order $order)
    {
        $order->load(['user', 'address', 'expedition', 'items.product', 'payment', 'trackings']);
        $statusLabels = $this->statusLabels;

        return view('admin.orders.show', compact('order', 'statusLabels'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => ['required', \Illuminate\Validation\Rule::in(array_keys($this->statusLabels))],
            'proof_photo' => ['nullable', 'image', 'max:2048'],
        ]);

        $updateData = ['status' => $request->status];

        // Otomatis input resi jika status dikirim ('shipped') dan belum ada resi
        if ($request->status === 'shipped' && empty($order->resi_number)) {
            $expName = preg_replace('/[^A-Za-z0-9]/', '', $order->expedition?->name ?? 'EXP');
            $updateData['resi_number'] = strtoupper($expName) . mt_rand(100000000, 999999999);
        }

        $order->update($updateData);

        $proofPhotoPath = null;
        if ($request->hasFile('proof_photo')) {
            $proofPhotoPath = $request->file('proof_photo')->store('order_proofs', 'public');
        }

        $storeName = \App\Models\Setting::get('store_name', 'UBSI Store');
        $storeCity = \App\Models\Setting::get('store_city_name', 'Jakarta Pusat');
        $location = "$storeCity ($storeName)";

        $customDesc = 'Status diperbarui oleh Admin: ' . ($this->statusLabels[$request->status] ?? $request->status);
        if ($request->status === 'packed') {
            $customDesc = 'Pesanan sedang dikemas oleh toko dan disiapkan untuk pengiriman.';
        } elseif ($request->status === 'shipped') {
            $customDesc = 'Paket telah diserahkan ke pihak ekspedisi (' . ($order->expedition?->name ?? 'Kurir') . ') No. Resi: ' . ($order->resi_number ?? '-');
        } elseif ($request->status === 'arrived') {
            $customDesc = 'Paket telah sampai di alamat tujuan pembeli.';
        } elseif ($request->status === 'completed') {
            $customDesc = 'Pesanan telah selesai. Terima kasih telah berbelanja!';
        }

        // Tambah tracking otomatis
        $order->trackings()->create([
            'status'      => $request->status,
            'description' => $customDesc,
            'location'    => $location,
            'proof_photo' => $proofPhotoPath,
        ]);

        static::flushRedisCache();

        return back()->with('success', 'Status order berhasil diperbarui.');
    }

    public function simulateCourierPod(Order $order)
    {
        $receiver = $order->address?->receiver_name ?? ($order->user?->name ?? 'Pelanggan');
        $city = $order->address?->city ?? 'Alamat Tujuan';
        $courierName = $order->expedition?->name ?? 'Kurir Ekspedisi';

        // 1. Update order status to arrived
        $order->update(['status' => Order::STATUS_ARRIVED]);

        // 2. Add tracking entry with auto mock POD photo
        $order->trackings()->create([
            'status'      => Order::STATUS_ARRIVED,
            'description' => "Paket telah sampai di lokasi tujuan dan diserahkan oleh {$courierName} kepada [{$receiver}] (Ybs). Bukti foto serah terima (POD) otomatis terverifikasi sistem.",
            'location'    => $city,
            'proof_photo' => 'order_proofs/mock_pod_sample.jpg',
        ]);

        static::flushRedisCache();

        return back()->with('success', 'Simulasi Kurir Berhasil! Foto bukti serah terima (Auto-POD) otomatis terunggah ke sistem dan langsung tampil di aplikasi.');
    }

    public function updateResi(Request $request, Order $order)
    {
        $request->validate([
            'resi_number' => ['required', 'string', 'max:100'],
        ]);

        $resi = strtoupper(trim($request->resi_number));
        $updateData = ['resi_number' => $resi];

        $storeName = \App\Models\Setting::get('store_name', 'UBSI Store');
        $storeCity = \App\Models\Setting::get('store_city_name', 'Jakarta Pusat');

        // Jika status saat ini masih 'paid' atau 'packed', otomatis alihkan ke 'shipped'
        if (in_array($order->status, ['paid', 'packed'], true)) {
            $updateData['status'] = Order::STATUS_SHIPPED;

            $order->update($updateData);

            $order->trackings()->create([
                'status'      => Order::STATUS_SHIPPED,
                'description' => 'Paket telah diserahkan ke pihak ekspedisi (' . ($order->expedition?->name ?? 'Kurir') . ') No. Resi: ' . $resi,
                'location'    => "$storeCity ($storeName)",
            ]);

            static::flushRedisCache();

            return back()->with('success', "Nomor resi ({$resi}) berhasil disimpan dan status pesanan diperbarui menjadi Dikirim.");
        }

        $order->update($updateData);

        static::flushRedisCache();

        return back()->with('success', "Nomor resi ({$resi}) berhasil disimpan.");
    }

    public function trackWaybill(Order $order)
    {
        $result = $order->syncTracking();

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        static::flushRedisCache();

        return back()->with('success', $result['message']);
    }

    public function approveCancel(Order $order)
    {
        if ($order->cancel_request_status !== 'pending') {
            return back()->with('error', 'Tidak ada pengajuan pembatalan yang aktif untuk pesanan ini.');
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($order) {
                if ($order->payment) {
                    $order->payment->update([
                        'status' => \App\Models\Payment::STATUS_FAILED,
                    ]);
                }

                $order->update([
                    'status' => Order::STATUS_CANCELLED,
                    'cancel_request_status' => 'approved',
                ]);

                $order->trackings()->create([
                    'status' => Order::STATUS_CANCELLED,
                    'description' => 'Pengajuan pembatalan disetujui oleh Admin. Pesanan dibatalkan.',
                    'location' => 'Admin Cyber',
                ]);

                foreach ($order->items as $item) {
                    $product = $item->product;
                    if ($product) {
                        $product->increment('stock', $item->quantity, []);

                        $product->stockMovements()->create([
                            'user_id' => $order->user_id,
                            'type' => 'in',
                            'quantity' => $item->quantity,
                            'reference' => $order->invoice_number,
                            'note' => 'Restock: Pengajuan pembatalan disetujui Admin',
                        ]);
                    }
                }
            });

            static::flushRedisCache();

            return back()->with('success', 'Pengajuan pembatalan pesanan berhasil disetujui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyetujui pembatalan: ' . $e->getMessage());
        }
    }

    public function rejectCancel(Request $request, Order $order)
    {
        if ($order->cancel_request_status !== 'pending') {
            return back()->with('error', 'Tidak ada pengajuan pembatalan yang aktif untuk pesanan ini.');
        }

        $rejectReason = $request->input('reject_reason', 'Pengajuan ditolak oleh admin.');

        $order->update([
            'cancel_request_status' => 'rejected',
            'note' => $order->note ? $order->note . ' | Penolakan Batal: ' . $rejectReason : 'Penolakan Batal: ' . $rejectReason,
        ]);

        $order->trackings()->create([
            'status' => $order->status,
            'description' => 'Pengajuan pembatalan ditolak oleh Admin. Alasan: ' . $rejectReason,
            'location' => 'Admin Cyber',
        ]);

        static::flushRedisCache();

        return back()->with('success', 'Pengajuan pembatalan pesanan berhasil ditolak.');
    }

    public function unreadCount()
    {
        $paidCount = Order::where(['status' => 'paid'])->count();
        $pendingCancelCount = Order::where(['cancel_request_status' => 'pending'])->count();
        $latestOrder = Order::where(['status' => 'paid'])
            ->with(['user', 'items'])
            ->latest('updated_at')
            ->first();

        return response()->json([
            'paid_count' => $paidCount,
            'pending_cancel_count' => $pendingCancelCount,
            'latest_paid_invoice' => $latestOrder?->invoice_number,
            'latest_paid_customer' => $latestOrder?->user?->name ?? 'Pelanggan',
            'latest_paid_amount' => $latestOrder ? number_format((float)$latestOrder->total_amount, 0, ',', '.') : '0',
            'latest_paid_url' => $latestOrder ? route('admin.orders.show', $latestOrder->getRouteKey()) : null,
        ]);
    }

    public function allUnreadCounts()
    {
        try {
            $data = Cache::store('redis')->remember('admin:all_unread_counts_data', 5, function () {
                $paidCount = Order::where(['status' => 'paid'])->count();
                $pendingCancelCount = Order::where(['cancel_request_status' => 'pending'])->count();

                $chatUnread = \App\Models\Chat::whereHas(
                    'messages',
                    fn($q) =>
                    $q->where('sender_type', 'customer')->where('is_read', false)
                )->count();

                $reviewUnread = \App\Models\ProductReview::where('is_read', false)
                    ->whereNotNull('comment')
                    ->where('comment', '!=', '')
                    ->count();

                $latestOrder = Order::where(['status' => 'paid'])
                    ->with(['user'])
                    ->latest('updated_at')
                    ->first();

                // 1. Recent Orders for Popover Dropdown (Paid & Pending Cancel)
                $recentOrders = Order::query()
                    ->where(function ($q) {
                        $q->where('status', 'paid')
                          ->orWhere('cancel_request_status', 'pending');
                    })
                    ->with(['user', 'items.product'])
                    ->latest('updated_at')
                    ->take(5)
                    ->get()
                    ->map(function ($order) {
                        $firstItemName = $order->items->first()?->product?->name ?? 'Produk';
                        $extraCount = $order->items->count() - 1;
                        $itemSummary = $extraCount > 0 ? "{$firstItemName} (+{$extraCount} item)" : $firstItemName;

                        return [
                            'id' => $order->id,
                            'invoice_number' => $order->invoice_number,
                            'customer_name' => $order->user?->name ?? 'Pelanggan',
                            'customer_initial' => strtoupper(substr($order->user?->name ?? 'P', 0, 1)),
                            'amount_formatted' => 'Rp ' . number_format((float)$order->grand_total, 0, ',', '.'),
                            'status' => $order->status,
                            'status_label' => Order::statuses()[$order->status] ?? $order->status,
                            'cancel_request_status' => $order->cancel_request_status,
                            'item_summary' => $itemSummary,
                            'time_ago' => $order->updated_at->diffForHumans(),
                            'url' => route('admin.orders.show', $order),
                        ];
                    })
                    ->toArray();

                // 2. Recent Chats for Popover Dropdown
                $recentChats = \App\Models\Chat::query()
                    ->with(['customer', 'product', 'lastMessage'])
                    ->withCount(['messages as unread_count' => function ($q) {
                        $q->where('sender_type', 'customer')->where('is_read', false);
                    }])
                    ->orderByRaw('CASE WHEN (SELECT COUNT(*) FROM chat_messages WHERE chat_messages.chat_id = chats.id AND sender_type = "customer" AND is_read = 0) > 0 THEN 0 ELSE 1 END')
                    ->latest('last_message_at')
                    ->latest('updated_at')
                    ->take(5)
                    ->get()
                    ->map(function ($chat) {
                        $lastMsg = $chat->lastMessage?->message ?? $chat->subject ?? 'Percakapan baru';
                        return [
                            'id' => $chat->id,
                            'customer_name' => $chat->customer?->name ?? 'Pelanggan',
                            'customer_initial' => strtoupper(substr($chat->customer?->name ?? 'P', 0, 1)),
                            'product_name' => $chat->product?->name ?? $chat->product_name ?? 'Pertanyaan Toko',
                            'last_message' => \Illuminate\Support\Str::limit($lastMsg, 55),
                            'unread_count' => (int) $chat->unread_count,
                            'time_ago' => ($chat->last_message_at ?? $chat->updated_at)->diffForHumans(),
                            'url' => route('admin.chats.show', $chat),
                        ];
                    })
                    ->toArray();

                // 3. Recent Reviews for Popover Dropdown
                $recentReviews = \App\Models\ProductReview::query()
                    ->with(['user', 'product'])
                    ->orderBy('is_read', 'asc')
                    ->latest('created_at')
                    ->take(4)
                    ->get()
                    ->map(function ($review) {
                        return [
                            'id' => $review->id,
                            'customer_name' => $review->user?->name ?? 'Pelanggan',
                            'customer_initial' => strtoupper(substr($review->user?->name ?? 'P', 0, 1)),
                            'product_name' => $review->product?->name ?? 'Produk',
                            'rating' => (int) $review->rating,
                            'comment' => \Illuminate\Support\Str::limit($review->comment ?? 'Memberikan ulasan bintang.', 55),
                            'is_read' => (bool) $review->is_read,
                            'time_ago' => $review->created_at->diffForHumans(),
                            'url' => route('admin.review-chats.show', $review),
                        ];
                    })
                    ->toArray();

                return [
                    'paid_count' => $paidCount,
                    'pending_cancel_count' => $pendingCancelCount,
                    'chat_unread_count' => $chatUnread,
                    'review_unread_count' => $reviewUnread,
                    'latest_paid_invoice' => $latestOrder?->invoice_number,
                    'latest_paid_customer' => $latestOrder?->user?->name ?? 'Pelanggan',
                    'latest_paid_amount' => $latestOrder ? number_format((float)$latestOrder->total_amount, 0, ',', '.') : '0',
                    'latest_paid_url' => $latestOrder ? route('admin.orders.show', $latestOrder->getRouteKey()) : null,
                    'recent_orders' => $recentOrders,
                    'recent_chats' => $recentChats,
                    'recent_reviews' => $recentReviews,
                ];
            });

            return response()->json($data);
        } catch (\Throwable $e) {
            return response()->json([
                'paid_count' => 0,
                'pending_cancel_count' => 0,
                'chat_unread_count' => 0,
                'review_unread_count' => 0,
                'recent_orders' => [],
                'recent_chats' => [],
                'recent_reviews' => [],
            ]);
        }
    }
}
