@extends('admin.layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('breadcrumb')
<span class="breadcrumb-sep">›</span>
<span>Ikhtisar</span>
@endsection

@section('content')
<!-- Notice Banner -->
<div class="notice-banner" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; padding: 14px 20px; margin-bottom: 24px;">
    <div style="display: flex; align-items: center; gap: 12px; flex: 1; min-width: 280px;">
        <div class="notice-icon">
            <iconify-icon icon="flat-color-icons:shop" style="font-size: 22px;"></iconify-icon>
        </div>
        <div style="font-size: 13px; color: #334155; line-height: 1.5;">
            <strong>Selamat Datang Kembali, Admin!</strong> Berikut adalah ringkasan aktivitas penjualan, stok barang, serta pengguna toko {{ \App\Models\Setting::get('store_name', 'BSI Cyber Store') }} hari ini.
        </div>
    </div>
    <div style="display: inline-flex; flex-direction: column; gap: 4px; background: var(--bg-card, #ffffff); padding: 8px 14px; border-radius: 10px; border: 1px solid var(--border, #E2E8F0); box-shadow: 0 2px 6px rgba(0,0,0,0.03); white-space: nowrap;">
        <div style="display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; color: var(--text-primary, #0F172A);">
            <iconify-icon icon="flat-color-icons:calendar" style="font-size: 16px;"></iconify-icon>
            <span id="live-date-badge">{{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d F Y') }}</span>
        </div>
        <div style="display: flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 800; color: #0D47A1; font-family: monospace;">
            <iconify-icon icon="flat-color-icons:clock" style="font-size: 16px;"></iconify-icon>
            <span id="live-clock-badge">--:--:-- WIB</span>
        </div>
    </div>
</div>

<!-- Stats Summary Grid (Soft UI Dashboard Pro Style) -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 24px;">
    {{-- Sales / Pendapatan Card --}}
    <div style="background: var(--bg-card); border-radius: 1rem; padding: 20px; box-shadow: var(--shadow); border: 1px solid var(--border); position: relative; overflow: hidden;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
            <div style="font-size: 12.5px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Penjualan</div>
            <div style="font-size: 11px; font-weight: 600; color: var(--text-muted);">Bulan Ini</div>
        </div>
        <div style="font-size: 22px; font-weight: 800; color: var(--text-primary); margin-bottom: 4px; letter-spacing: -0.5px;">
            Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}
        </div>
        <div style="font-size: 12px; font-weight: 700; color: #82d616; display: flex; align-items: center; gap: 4px;">
            <span>+55%</span>
            <span style="color: var(--text-muted); font-weight: 500;">dibanding bulan lalu</span>
        </div>
    </div>

    {{-- Customers Card --}}
    <div style="background: var(--bg-card); border-radius: 1rem; padding: 20px; box-shadow: var(--shadow); border: 1px solid var(--border); position: relative; overflow: hidden;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
            <div style="font-size: 12.5px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Pelanggan</div>
            <div style="font-size: 11px; font-weight: 600; color: var(--text-muted);">Bulan Ini</div>
        </div>
        <div style="font-size: 22px; font-weight: 800; color: var(--text-primary); margin-bottom: 4px; letter-spacing: -0.5px;">
            {{ number_format($stats['total_users']) }}
        </div>
        <div style="font-size: 12px; font-weight: 700; color: #82d616; display: flex; align-items: center; gap: 4px;">
            <span>+12%</span>
            <span style="color: var(--text-muted); font-weight: 500;">dibanding bulan lalu</span>
        </div>
    </div>

    {{-- Orders Card --}}
    <div style="background: var(--bg-card); border-radius: 1rem; padding: 20px; box-shadow: var(--shadow); border: 1px solid var(--border); position: relative; overflow: hidden;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
            <div style="font-size: 12.5px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Total Pesanan</div>
            <div style="font-size: 11px; font-weight: 600; color: var(--text-muted);">Bulan Ini</div>
        </div>
        <div style="font-size: 22px; font-weight: 800; color: var(--text-primary); margin-bottom: 4px; letter-spacing: -0.5px;">
            {{ number_format($stats['total_orders']) }}
        </div>
        <div style="font-size: 12px; font-weight: 700; color: #82d616; display: flex; align-items: center; gap: 4px;">
            <span>+8%</span>
            <span style="color: var(--text-muted); font-weight: 500;">dibanding bulan lalu</span>
        </div>
    </div>

    {{-- Active Products Card --}}
    <div style="background: var(--bg-card); border-radius: 1rem; padding: 20px; box-shadow: var(--shadow); border: 1px solid var(--border); position: relative; overflow: hidden;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
            <div style="font-size: 12.5px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Total Produk</div>
            <div style="font-size: 11px; font-weight: 600; color: var(--text-muted);">Bulan Ini</div>
        </div>
        <div style="font-size: 22px; font-weight: 800; color: var(--text-primary); margin-bottom: 4px; letter-spacing: -0.5px;">
            {{ number_format($stats['total_products']) }}
        </div>
        <div style="font-size: 12px; font-weight: 700; color: #82d616; display: flex; align-items: center; gap: 4px;">
            <span>+15%</span>
            <span style="color: var(--text-muted); font-weight: 500;">dibanding bulan lalu</span>
        </div>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:24px;" class="dashboard-grid">
    {{-- Recent Users Card --}}
    <div class="table-card" style="margin-bottom: 0;">
        <div class="table-card-header">
            <div style="font-weight: 800; font-size: 15px; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                <iconify-icon icon="flat-color-icons:conference-call" style="font-size: 20px;"></iconify-icon> Pengguna Terbaru
            </div>
            <a href="{{ route('admin.users.index') }}" style="font-size: 12px; font-weight: 700; color: #0B023E; text-decoration: none; background: #f1f5f9; padding: 6px 12px; border-radius: 8px; display: inline-flex; align-items: center; gap: 4px;">Lihat Semua <iconify-icon icon="lucide:chevron-right" style="font-size: 14px;"></iconify-icon></a>
        </div>
        <div style="overflow-x: auto;">
            @if($recent_users->isEmpty())
            <div style="text-align: center; padding: 32px; color: #94a3b8;">Belum ada pengguna</div>
            @else
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; font-size: 11.5px; color: #64748b; text-transform: uppercase;">
                        <th style="padding: 12px 16px;">Nama & Email</th>
                        <th style="padding: 12px 16px;">Role</th>
                        <th style="padding: 12px 16px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recent_users as $user)
                    <tr class="table-row-custom" style="border-bottom: 1px solid #f1f5f9; font-size: 13px;">
                        <td style="padding: 12px 16px;">
                            <div style="font-weight: 700; color: #0f172a;">{{ $user->name }}</div>
                            <div style="font-size: 11.5px; color: #64748b;">{{ $user->email }}</div>
                        </td>
                        <td style="padding: 12px 16px;">
                            <span class="badge-type {{ $user->role === 'admin' ? 'badge-promo' : 'badge-info' }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td style="padding: 12px 16px;">
                            <span class="badge-type {{ $user->is_active ? 'badge-success-custom' : 'badge-danger-custom' }}">
                                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>

    {{-- Low Stock Products Card --}}
    <div class="table-card" style="margin-bottom: 0;">
        <div class="table-card-header">
            <div style="font-weight: 800; font-size: 15px; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                <iconify-icon icon="flat-color-icons:warning" style="font-size: 20px;"></iconify-icon> Stok Hampir Habis
            </div>
            <a href="{{ route('admin.products.index') }}" style="font-size: 12px; font-weight: 700; color: #0B023E; text-decoration: none; background: #f1f5f9; padding: 6px 12px; border-radius: 8px; display: inline-flex; align-items: center; gap: 4px;">Lihat Semua <iconify-icon icon="lucide:chevron-right" style="font-size: 14px;"></iconify-icon></a>
        </div>
        <div style="overflow-x: auto;">
            @if($low_stock_products->isEmpty())
            <div style="text-align: center; padding: 32px; color: #16a34a;">
                <div style="margin-bottom: 4px; display: flex; justify-content: center;"><iconify-icon icon="flat-color-icons:ok" style="font-size: 32px;"></iconify-icon></div>
                <div style="font-weight: 700;">Stok Aman</div>
                <div style="font-size: 12px; color: #64748b;">Semua produk memiliki stok cukup</div>
            </div>
            @else
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; font-size: 11.5px; color: #64748b; text-transform: uppercase;">
                        <th style="padding: 12px 16px;">Nama Produk</th>
                        <th style="padding: 12px 16px;">Sisa Stok</th>
                        <th style="padding: 12px 16px; text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($low_stock_products as $product)
                    <tr class="table-row-custom" style="border-bottom: 1px solid #f1f5f9; font-size: 13px;">
                        <td style="padding: 12px 16px;">
                            <div style="font-weight: 700; color: #0f172a;">{{ \Illuminate\Support\Str::limit($product->name, 28) }}</div>
                            <div style="font-size: 11.5px; color: #64748b;">SKU: {{ $product->sku }}</div>
                        </td>
                        <td style="padding: 12px 16px;">
                            <span style="font-weight: 800;" class="{{ $product->stock <= 5 ? 'text-danger' : 'text-warning' }}">
                                {{ $product->stock }} Pcs
                            </span>
                        </td>
                        <td style="padding: 12px 16px; text-align: right;">
                            <a href="{{ route('admin.products.edit', $product) }}" style="background: #fef3c7; color: #b45309; padding: 4px 10px; border-radius: 6px; font-size: 11.5px; font-weight: 700; text-decoration: none;">Restok</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>
</div>

{{-- Recent Orders Table Card --}}
<div class="table-card">
    <div class="table-card-header">
        <div style="font-weight: 800; font-size: 16px; color: #0f172a; display: flex; align-items: center; gap: 8px;">
            <iconify-icon icon="flat-color-icons:shopping-cart" style="font-size: 20px;"></iconify-icon> Pesanan Terbaru
        </div>
        <a href="{{ route('admin.orders.index') }}" style="font-size: 12px; font-weight: 700; color: #0B023E; text-decoration: none; background: #f1f5f9; padding: 6px 12px; border-radius: 8px; display: inline-flex; align-items: center; gap: 4px;">Semua Pesanan <iconify-icon icon="lucide:chevron-right" style="font-size: 14px;"></iconify-icon></a>
    </div>
    <div style="overflow-x: auto;" class="desktop-table-container">
        @if($recent_orders->isEmpty())
        <div style="text-align: center; padding: 40px; color: #94a3b8;">
            <div style="margin-bottom: 8px; display: flex; justify-content: center;"><iconify-icon icon="flat-color-icons:opened-folder" style="font-size: 40px;"></iconify-icon></div>
            <div style="font-weight: 700; color: #475569;">Belum Ada Pesanan</div>
            <div style="font-size: 12.5px;">Pesanan dari pelanggan akan otomatis tampil di sini</div>
        </div>
        @else
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; font-size: 12px; color: #64748b; text-transform: uppercase;">
                    <th style="padding: 14px 20px;">ID Pesanan</th>
                    <th style="padding: 14px 20px;">Pelanggan</th>
                    <th style="padding: 14px 20px;">Status Pembayaran / Pengiriman</th>
                    <th style="padding: 14px 20px;">Total Bayar</th>
                    <th style="padding: 14px 20px;">Tanggal Pesanan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recent_orders as $order)
                <tr class="table-row-custom" style="border-bottom: 1px solid #f1f5f9; font-size: 13.5px;">
                    <td style="padding: 14px 20px; font-family: monospace; font-weight: 800; color: #0B023E;">
                        #{{ $order->id }}
                    </td>
                    <td style="padding: 14px 20px; font-weight: 700; color: #0f172a;">
                        {{ $order->user?->name ?? '-' }}
                    </td>
                    <td style="padding: 14px 20px;">
                        @php
                        $statusLabels = [
                        'pending_payment' => 'Menunggu Bayar',
                        'paid' => 'Dibayar',
                        'packed' => 'Dikemas',
                        'shipped' => 'Dikirim',
                        'arrived' => 'Tiba',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        ];
                        $badgeClass = match($order->status) {
                        'completed', 'paid' => 'badge-success-custom',
                        'pending_payment', 'packed', 'shipped' => 'badge-warning-custom',
                        default => 'badge-danger-custom'
                        };
                        @endphp
                        <span class="badge-type {{ $badgeClass }}">
                            {{ $statusLabels[$order->status] ?? ucfirst($order->status) }}
                        </span>
                    </td>
                    <td style="padding: 14px 20px; font-weight: 800; color: #0f172a;">
                        Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                    </td>
                    <td style="padding: 14px 20px; color: #64748b; font-size: 12.5px;">
                        <span style="display: inline-flex; align-items: center; gap: 4px;"><iconify-icon icon="flat-color-icons:calendar" style="font-size: 14px;"></iconify-icon> {{ $order->created_at->format('d M Y, H:i') }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    <!-- Mobile Card View for Recent Orders (<=768px) -->
    <div class="mobile-dashboard-grid">
        @foreach($recent_orders as $order)
        @php
        $statusLabels = [
        'pending_payment' => 'Menunggu Bayar',
        'paid' => 'Dibayar',
        'packed' => 'Dikemas',
        'shipped' => 'Dikirim',
        'arrived' => 'Tiba',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
        ];
        $badgeClass = match($order->status) {
        'completed', 'paid' => 'badge-success-custom',
        'pending_payment', 'packed', 'shipped' => 'badge-warning-custom',
        default => 'badge-danger-custom'
        };
        @endphp
        <div class="mobile-dash-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                <span style="font-family: monospace; font-weight: 800; color: #0B023E; font-size: 13.5px;">
                    #{{ $order->id }}
                </span>
                <span class="badge-type {{ $badgeClass }}">
                    {{ $statusLabels[$order->status] ?? ucfirst($order->status) }}
                </span>
            </div>
            <div style="font-weight: 700; font-size: 13px; color: #0f172a; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                <iconify-icon icon="flat-color-icons:businessman" style="font-size: 16px;"></iconify-icon> {{ $order->user?->name ?? '-' }}
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: #64748b;">
                <span style="font-weight: 800; color: #0f172a; font-size: 13px;">
                    Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                </span>
                <span style="display: inline-flex; align-items: center; gap: 4px;"><iconify-icon icon="flat-color-icons:calendar" style="font-size: 14px;"></iconify-icon> {{ $order->created_at->format('d M Y') }}</span>
            </div>
        </div>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
    function updateDashboardClock() {
        const now = new Date();
        const optionsDate = {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        };
        const dateStr = now.toLocaleDateString('id-ID', optionsDate);
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        const timeStr = `${hours}:${minutes}:${seconds} WIB`;

        const dateEl = document.getElementById('live-date-badge');
        const clockEl = document.getElementById('live-clock-badge');
        if (dateEl) dateEl.textContent = dateStr;
        if (clockEl) clockEl.textContent = timeStr;
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateDashboardClock();
        setInterval(updateDashboardClock, 1000);
    });
</script>
@endpush
@endsection