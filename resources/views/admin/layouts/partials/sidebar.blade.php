@use('App\Models\Chat')
@use('App\Models\Order')
@use('App\Models\Setting')
<!-- ─── SIDEBAR ─────────────────────────────────────────────────────── -->
<aside class="sidebar" id="sidebar">
    <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
        @php
        $sidebarLogoSetting = Setting::get('store_logo');
        $sidebarLogoUrl = $sidebarLogoSetting ? \Storage::disk('public')->url($sidebarLogoSetting) : asset('/assets/img/logo-cyberstore.jpg');
        @endphp
        <img src="{{ $sidebarLogoUrl }}" alt="Logo Toko" style="width: 36px; height: 36px; border-radius: 0.5rem; object-fit: cover; flex-shrink: 0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        <div class="sidebar-brand-text">
            <div class="store-name">{{ Setting::get('store_name', 'BSI Cyber Store') }}</div>
            <div class="store-sub">{{ Setting::get('store_slogan', 'Your Trusted Cyber Store') }}</div>
        </div>
    </a>

    <nav class="sidebar-nav">
        {{-- Dashboard --}}
        <div class="nav-section-label">Utama</div>
        <a href="{{ route('admin.dashboard') }}"
            class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" data-tooltip="Dashboard">
            <span class="icon">
                <iconify-icon icon="lucide:layout-dashboard" style="font-size: 16px;"></iconify-icon>
            </span> Dashboard
        </a>

        {{-- Catalog --}}
        <div class="nav-section-label">Katalog</div>
        <a href="{{ route('admin.categories.index') }}"
            class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" data-tooltip="Kategori">
            <span class="icon">
                <iconify-icon icon="lucide:tag" style="font-size: 16px;"></iconify-icon>
            </span> Kategori
        </a>
        <a href="{{ route('admin.products.index') }}"
            class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" data-tooltip="Produk">
            <span class="icon">
                <iconify-icon icon="lucide:package" style="font-size: 16px;"></iconify-icon>
            </span> Produk
        </a>
        <a href="{{ route('admin.stock-movements.index') }}"
            class="nav-link {{ request()->routeIs('admin.stock-movements.*') ? 'active' : '' }}" data-tooltip="Mutasi Stok">
            <span class="icon">
                <iconify-icon icon="lucide:trending-up" style="font-size: 16px;"></iconify-icon>
            </span> Mutasi Stok
        </a>

        {{-- Transaksi --}}
        <div class="nav-section-label">Transaksi</div>
        <a href="{{ route('admin.orders.index') }}"
            class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" data-tooltip="Pesanan">
            <span class="icon">
                <iconify-icon icon="lucide:shopping-cart" style="font-size: 16px;"></iconify-icon>
            </span> Pesanan
            <span id="sidebar-order-paid-badge" style="margin-left: auto; background-color: #10b981; color: white; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 10px; display: none; line-height: 1;" title="Pesanan Lunas Perlu Diproses">
                0 Baru
            </span>
            <span id="sidebar-cancel-badge" style="margin-left: 4px; background-color: #f59e0b; color: white; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 10px; display: none; line-height: 1;" title="Pengajuan Pembatalan">
                0 Batal
            </span>
        </a>
        <a href="{{ route('admin.payments.index') }}"
            class="nav-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}" data-tooltip="Pembayaran">
            <span class="icon">
                <iconify-icon icon="lucide:credit-card" style="font-size: 16px;"></iconify-icon>
            </span> Pembayaran
        </a>
        <a href="{{ route('admin.chats.index') }}"
            class="nav-link {{ request()->routeIs('admin.chats.*') ? 'active' : '' }}" data-tooltip="Chat Customer">
            <span class="icon">
                <iconify-icon icon="lucide:message-square" style="font-size: 16px;"></iconify-icon>
            </span> Chat Customer
            <span id="sidebar-chat-badge" style="margin-left: auto; background-color: #DF0B2B; color: white; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 10px; display: none; line-height: 1;">
                0
            </span>
        </a>
        <a href="{{ route('admin.review-chats.index') }}"
            class="nav-link {{ request()->routeIs('admin.review-chats.*') ? 'active' : '' }}" data-tooltip="Chat Ulasan">
            <span class="icon">
                <iconify-icon icon="lucide:message-circle" style="font-size: 16px;"></iconify-icon>
            </span> Chat Ulasan
            <span id="sidebar-review-badge" style="margin-left: auto; background-color: #DF0B2B; color: white; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 10px; display: none; line-height: 1;" title="Ulasan Baru">
                0
            </span>
        </a>
        <a href="{{ route('admin.announcements.index') }}"
            class="nav-link {{ request()->routeIs('admin.announcements.*') ? 'active' : '' }}" data-tooltip="Pengumuman">
            <span class="icon">
                <iconify-icon icon="lucide:bell" style="font-size: 16px;"></iconify-icon>
            </span> Pengumuman
        </a>

        {{-- Master Data --}}
        <div class="nav-section-label">Master Data</div>
        <a href="{{ route('admin.expeditions.index') }}"
            class="nav-link {{ request()->routeIs('admin.expeditions.*') ? 'active' : '' }}" data-tooltip="Ekspedisi">
            <span class="icon">
                <iconify-icon icon="lucide:truck" style="font-size: 16px;"></iconify-icon>
            </span> Ekspedisi
        </a>

        {{-- Pengaturan --}}
        <div class="nav-section-label">Pengaturan</div>
        <a href="{{ route('admin.settings.index') }}"
            class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" data-tooltip="Pengaturan Toko">
            <span class="icon">
                <iconify-icon icon="lucide:settings" style="font-size: 16px;"></iconify-icon>
            </span> Pengaturan Toko
        </a>

        {{-- Banner Management --}}
        <div class="nav-section-label">Banner</div>
        <a href="{{ route('admin.banners.index') }}" class="nav-link {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}" data-tooltip="Banner">
            <span class="icon">
                <iconify-icon icon="lucide:image" style="font-size: 16px;"></iconify-icon>
            </span> Banner
        </a>

        {{-- User --}}
        <div class="nav-section-label">Pengguna</div>
        <a href="{{ route('admin.users.index') }}"
            class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" data-tooltip="Pengguna">
            <span class="icon">
                <iconify-icon icon="lucide:users" style="font-size: 16px;"></iconify-icon>
            </span> Pengguna
        </a>


    </nav>

    {{-- User Card --}}
    <div class="sidebar-footer">
        <div class="user-card">
            <div class="user-avatar">
                @if (auth()->user()->photo)
                <img src="{{ \Storage::disk('public')->url(auth()->user()->photo) }}"
                    alt="{{ auth()->user()->name }}">
                @else
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                @endif
            </div>
            <div class="user-info">
                <div class="user-name">{{ auth()->user()->name }}</div>
                <div class="user-role">{{ ucfirst(auth()->user()->role) }}</div>
            </div>
            <button type="button" class="logout-btn" title="Logout" onclick="confirmLogout()">
                <iconify-icon icon="lucide:power" style="font-size: 18px;"></iconify-icon>
            </button>
        </div>
    </div>
</aside>