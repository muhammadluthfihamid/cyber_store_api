<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /** Batas stok rendah yang ditampilkan di dashboard. */
    private const LOW_STOCK_THRESHOLD = 10;

    /** Durasi cache statistik dashboard (menit). */
    private const STATS_CACHE_TTL = 5;

    public function index()
    {
        // Cache stats selama 5 menit — data statistik tidak perlu 100% real-time.
        // Menggunakan default cache driver (tidak hardcode 'redis') agar tetap berjalan
        // di environment yang hanya punya driver file/array (mis. CI, local tanpa Redis).
        $stats = Cache::remember(
            'admin:dashboard:stats',
            now()->addMinutes(self::STATS_CACHE_TTL),
            fn() => $this->computeDashboardStats()
        );


        $recent_users = User::query()->latest()->take(5)->get();
        $recent_orders = Order::query()->with('user')->latest()->take(5)->get();
        $low_stock_products = Product::query()->where('stock', '<=', self::LOW_STOCK_THRESHOLD)
            ->where('is_active', true)
            ->orderBy('stock')
            ->take(5)
            ->get();

        return view(
            'admin.dashboard.index',
            compact('stats', 'recent_users', 'recent_orders', 'low_stock_products')
        );
    }

    /**
     * Hitung semua statistik dashboard.
     *
     * @return array<string, mixed>
     */
    private function computeDashboardStats(): array
    {
        return [
            'total_users'       => User::query()->count(),
            'total_customers'   => User::query()->where('role', User::ROLE_CUSTOMER)->count(),
            'total_admins'      => User::query()->whereIn('role', [User::ROLE_ADMIN, User::ROLE_SUPERADMIN])->count(),
            'total_products'    => Product::query()->count(),
            'active_products'   => Product::query()->where('is_active', true)->count(),
            'total_categories'  => Category::query()->count(),
            'total_orders'      => Order::query()->count(),
            'total_revenue'     => Order::query()->where('status', Order::STATUS_COMPLETED)->sum('grand_total'),
            'pending_orders'    => Order::query()->where('status', Order::STATUS_PENDING_PAYMENT)->count(),
            'processing_orders' => Order::query()->whereIn('status', [
                Order::STATUS_PAID,
                Order::STATUS_PACKED,
                Order::STATUS_SHIPPED,
            ])->count(),
        ];
    }
}
