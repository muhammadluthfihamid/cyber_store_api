<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\ProductReview;
use App\Models\Product;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Mengambil daftar notifikasi pop up admin secara dinamis dan terpusat.
     */
    public function feed(Request $request)
    {
        $notifications = [];

        // 1. Pesanan Baru Lunas (Paid) yang perlu diproses
        $paidOrders = Order::with('user')
            ->where('status', Order::STATUS_PAID)
            ->latest('updated_at')
            ->take(5)
            ->get();

        foreach ($paidOrders as $order) {
            $notifications[] = [
                'id' => 'order_paid_' . $order->id,
                'category' => 'order',
                'type' => 'paid',
                'title' => 'Pesanan Baru Perlu Diproses',
                'message' => 'Invoice ' . $order->invoice_number . ' dari ' . ($order->user->name ?? 'Pelanggan') . ' sebesar Rp ' . number_format((float)$order->grand_total, 0, ',', '.'),
                'time' => $order->updated_at ? $order->updated_at->diffForHumans() : 'Baru saja',
                'raw_time' => $order->updated_at ? $order->updated_at->timestamp : 0,
                'url' => route('admin.orders.show', $order->getRouteKey()),
                'icon' => 'solar:bag-check-bold-duotone',
                'icon_color' => '#10b981',
                'icon_bg' => '#ecfdf5',
                'is_unread' => true,
            ];
        }

        // 2. Permintaan Pembatalan Pesanan (Pending Cancellation)
        $cancelOrders = Order::with('user')
            ->where('cancel_request_status', 'pending')
            ->latest('updated_at')
            ->take(5)
            ->get();

        foreach ($cancelOrders as $order) {
            $notifications[] = [
                'id' => 'order_cancel_' . $order->id,
                'category' => 'order',
                'type' => 'cancel',
                'title' => 'Permintaan Pembatalan Pesanan',
                'message' => 'Invoice ' . $order->invoice_number . ' mengajukan pembatalan: "' . \Illuminate\Support\Str::limit($order->cancel_request_reason ?? 'Tanpa alasan', 40) . '"',
                'time' => $order->updated_at ? $order->updated_at->diffForHumans() : 'Baru saja',
                'raw_time' => $order->updated_at ? $order->updated_at->timestamp : 0,
                'url' => route('admin.orders.show', $order->getRouteKey()),
                'icon' => 'solar:danger-triangle-bold-duotone',
                'icon_color' => '#ef4444',
                'icon_bg' => '#fef2f2',
                'is_unread' => true,
            ];
        }

        // 3. Chat Customer Belum Dibaca
        $unreadChats = Chat::with(['user', 'messages' => function ($q) {
            $q->latest()->take(1);
        }])
            ->whereHas('messages', function ($q) {
                $q->where('sender_type', 'customer')->where('is_read', false);
            })
            ->latest('updated_at')
            ->take(5)
            ->get();

        foreach ($unreadChats as $chat) {
            $lastMsg = $chat->messages->first();
            $notifications[] = [
                'id' => 'chat_' . $chat->id,
                'category' => 'chat',
                'type' => 'chat',
                'title' => 'Pesan Masuk dari ' . ($chat->user->name ?? 'Customer'),
                'message' => $lastMsg ? \Illuminate\Support\Str::limit($lastMsg->message, 50) : 'Mengirimkan pesan baru',
                'time' => $lastMsg && $lastMsg->created_at ? $lastMsg->created_at->diffForHumans() : 'Baru saja',
                'raw_time' => $lastMsg && $lastMsg->created_at ? $lastMsg->created_at->timestamp : ($chat->updated_at ? $chat->updated_at->timestamp : 0),
                'url' => route('admin.chats.show', $chat->id),
                'icon' => 'solar:chat-round-dots-bold-duotone',
                'icon_color' => '#3b82f6',
                'icon_bg' => '#eff6ff',
                'is_unread' => true,
            ];
        }

        // 4. Ulasan Produk Baru Belum Dibalas / Belum Dibaca
        $unreadReviews = ProductReview::with(['user', 'product'])
            ->where('is_read', false)
            ->whereNotNull('comment')
            ->where('comment', '!=', '')
            ->latest('created_at')
            ->take(5)
            ->get();

        foreach ($unreadReviews as $review) {
            $notifications[] = [
                'id' => 'review_' . $review->id,
                'category' => 'review',
                'type' => 'review',
                'title' => 'Ulasan Baru: ' . ($review->product->name ?? 'Produk'),
                'message' => '⭐ ' . $review->rating . '/5 oleh ' . ($review->user->name ?? 'Pembeli') . ': "' . \Illuminate\Support\Str::limit($review->comment, 40) . '"',
                'time' => $review->created_at ? $review->created_at->diffForHumans() : 'Baru saja',
                'raw_time' => $review->created_at ? $review->created_at->timestamp : 0,
                'url' => route('admin.review-chats.show', $review->id),
                'icon' => 'solar:star-fall-minimalistic-2-bold-duotone',
                'icon_color' => '#f59e0b',
                'icon_bg' => '#fffbeb',
                'is_unread' => true,
            ];
        }

        // 5. Stok Produk Kritis / Menipis (<= 5)
        $lowStockProducts = Product::where('stock', '<=', 5)
            ->where('is_active', true)
            ->orderBy('stock', 'asc')
            ->take(3)
            ->get();

        foreach ($lowStockProducts as $prod) {
            $notifications[] = [
                'id' => 'stock_' . $prod->id,
                'category' => 'stock',
                'type' => 'stock',
                'title' => 'Peringatan Stok Menipis!',
                'message' => $prod->name . ' sisa ' . $prod->stock . ' unit segera lakukan restok.',
                'time' => 'Perhatian',
                'raw_time' => 0,
                'url' => route('admin.products.edit', $prod->id),
                'icon' => 'solar:box-minimalistic-bold-duotone',
                'icon_color' => '#ea580c',
                'icon_bg' => '#fff7ed',
                'is_unread' => true,
            ];
        }

        // Sort notifications by timestamp desc
        usort($notifications, function ($a, $b) {
            return $b['raw_time'] <=> $a['raw_time'];
        });

        $totalCount = count($notifications);

        return response()->json([
            'status' => 'success',
            'total' => $totalCount,
            'counts' => [
                'paid_orders' => $paidOrders->count(),
                'cancel_orders' => $cancelOrders->count(),
                'unread_chats' => $unreadChats->count(),
                'unread_reviews' => $unreadReviews->count(),
                'low_stock' => $lowStockProducts->count(),
            ],
            'data' => $notifications,
        ]);
    }
}
