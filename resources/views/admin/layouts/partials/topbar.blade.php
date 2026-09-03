<header class="topbar">
    <div class="topbar-left">
        <button class="menu-toggle" onclick="toggleSidebar()" title="Toggle Sidebar">
            <!-- Menu Icon -->
            <svg id="menuIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-menu">
                <path d="M4 5h16" />
                <path d="M4 12h16" />
                <path d="M4 19h16" />
            </svg>
        </button>
        <div>
            <div class="breadcrumb" style="font-size: 11.5px; opacity: 0.85;">
                <a href="{{ route('admin.dashboard') }}" style="display: inline-flex; align-items: center; gap: 4px;">
                    <iconify-icon icon="flat-color-icons:home" style="font-size: 15px;"></iconify-icon> Pages
                </a>
                @yield('breadcrumb')
            </div>
            <div class="page-title" style="margin-top: 1px;">@yield('page-title', 'Overview')</div>
        </div>
    </div>
    <div class="topbar-right" style="gap: 14px;">
        <!-- {{-- Search Input Pill --}}
        <div style="position: relative; display: flex; align-items: center;" class="d-none d-md-flex">
            <input type="text" placeholder="Type here..." style="padding: 7px 14px 7px 32px; font-size: 12.5px; border: 1px solid var(--border); border-radius: 20px; background: var(--bg-input); color: var(--text-primary); outline: none; width: 180px; transition: all 0.2s ease;">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 12px; color: var(--text-muted);">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
        </div> -->

        <!-- 1. Order Notification Popover Container -->
        <div class="topbar-dropdown-container" id="orderDropdownContainer">
            <button type="button" class="theme-toggle-btn" id="orderDropdownBtn" onclick="toggleTopDropdown(event, 'orderDropdownMenu')" title="Notifikasi Pesanan" style="position:relative; display:flex; align-items:center; justify-content:center; text-decoration:none; cursor:pointer;">
                <iconify-icon icon="solar:bag-check-bold-duotone" style="font-size: 18px; color: #10b981;"></iconify-icon>
                <span id="topbar-order-badge" style="position:absolute; top:-4px; right:-4px; background:#10b981; color:#fff; font-size:9px; font-weight:700; width:15px; height:15px; border-radius:50%; display:none; align-items:center; justify-content:center; line-height:1;">
                    0
                </span>
            </button>

            <div id="orderDropdownMenu" class="topbar-popover-menu">
                <div class="topbar-popover-header">
                    <div class="topbar-popover-title">
                        <iconify-icon icon="solar:bag-check-bold-duotone" style="font-size: 18px; color: #10b981;"></iconify-icon>
                        Pesanan Masuk
                    </div>
                    <span id="popover-order-badge-header" style="font-size: 11px; font-weight: 700; background: rgba(16, 185, 129, 0.15); color: #10b981; padding: 2px 8px; border-radius: 6px;">
                        0 Perlu Proses
                    </span>
                </div>

                <div class="topbar-popover-body" id="topbar-order-list">
                    <div class="topbar-popover-empty">
                        <iconify-icon icon="flat-color-icons:ok" style="font-size: 32px;"></iconify-icon>
                        <div>Tidak ada pesanan yang perlu diproses</div>
                    </div>
                </div>

                <div class="topbar-popover-footer">
                    <a href="{{ route('admin.orders.index', ['status' => 'paid']) }}">
                        <span>Lihat Semua Pesanan Lunas</span>
                        <iconify-icon icon="lucide:arrow-right" style="font-size: 13px;"></iconify-icon>
                    </a>
                </div>
            </div>
        </div>

        <!-- 2. Chat & Notifications Popover Container -->
        <div class="topbar-dropdown-container" id="notifDropdownContainer">
            <button type="button" class="theme-toggle-btn" id="notifDropdownBtn" onclick="toggleTopDropdown(event, 'notifDropdownMenu')" title="Pusat Notifikasi & Chat" style="position:relative; display:flex; align-items:center; justify-content:center; text-decoration:none; cursor:pointer;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bell">
                    <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
                    <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" />
                </svg>
                <span id="topbar-chat-badge" style="position:absolute; top:-4px; right:-4px; background:#DF0B2B; color:#fff; font-size:9px; font-weight:700; width:15px; height:15px; border-radius:50%; display:none; align-items:center; justify-content:center; line-height:1;">
                    0
                </span>
            </button>

            <div id="notifDropdownMenu" class="topbar-popover-menu">
                <div class="topbar-popover-header">
                    <div class="topbar-popover-title">
                        <iconify-icon icon="flat-color-icons:comments" style="font-size: 18px;"></iconify-icon>
                        Pusat Notifikasi
                    </div>
                    <span id="popover-notif-total-badge" style="font-size: 11px; font-weight: 700; background: rgba(223, 11, 43, 0.12); color: #DF0B2B; padding: 2px 8px; border-radius: 6px;">
                        0 Baru
                    </span>
                </div>

                <!-- Tabs for Chat & Reviews -->
                <div class="topbar-popover-tabs">
                    <button type="button" class="topbar-tab-btn active" id="tabBtnChat" onclick="switchNotifTab('chat')">
                        <iconify-icon icon="solar:chat-round-line-bold" style="font-size: 14px;"></iconify-icon>
                        <span>Chat Customer</span>
                        <span id="tabBadgeChat" style="font-size: 10px; background: rgba(255,255,255,0.25); padding: 1px 5px; border-radius: 10px; display: none;">0</span>
                    </button>
                    <button type="button" class="topbar-tab-btn" id="tabBtnReview" onclick="switchNotifTab('review')">
                        <iconify-icon icon="solar:star-bold" style="font-size: 14px;"></iconify-icon>
                        <span>Ulasan Baru</span>
                        <span id="tabBadgeReview" style="font-size: 10px; background: rgba(255,255,255,0.25); padding: 1px 5px; border-radius: 10px; display: none;">0</span>
                    </button>
                </div>

                <!-- Tab Pane 1: Chat Customer -->
                <div class="topbar-popover-body" id="topbar-chat-pane">
                    <div id="topbar-chat-list">
                        <div class="topbar-popover-empty">
                            <iconify-icon icon="flat-color-icons:speech-bubble" style="font-size: 32px;"></iconify-icon>
                            <div>Belum ada pesan chat customer</div>
                        </div>
                    </div>
                </div>

                <!-- Tab Pane 2: Reviews (Hidden initially) -->
                <div class="topbar-popover-body" id="topbar-review-pane" style="display: none;">
                    <div id="topbar-review-list">
                        <div class="topbar-popover-empty">
                            <iconify-icon icon="flat-color-icons:rating" style="font-size: 32px;"></iconify-icon>
                            <div>Belum ada ulasan baru</div>
                        </div>
                    </div>
                </div>

                <div class="topbar-popover-footer" id="notifFooter">
                    <a href="{{ route('admin.chats.index') }}" id="footerChatLink">
                        <span>Buka Semua Percakapan Chat</span>
                        <iconify-icon icon="lucide:arrow-right" style="font-size: 13px;"></iconify-icon>
                    </a>
                    <a href="{{ route('admin.review-chats.index') }}" id="footerReviewLink" style="display: none;">
                        <span>Buka Semua Ulasan Produk</span>
                        <iconify-icon icon="lucide:arrow-right" style="font-size: 13px;"></iconify-icon>
                    </a>
                </div>
            </div>
        </div>

        <button id="themeToggle" class="theme-toggle-btn" title="Ganti Tema" onclick="toggleTheme()">
            <!-- Sun icon -->
            <svg id="themeIconSun" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-sun" style="display: none;">
                <circle cx="12" cy="12" r="4" />
                <path d="M12 2v2" />
                <path d="M12 20v2" />
                <path d="m4.93 4.93 1.41 1.41" />
                <path d="m17.66 17.66 1.41 1.41" />
                <path d="M2 12h2" />
                <path d="M20 12h2" />
                <path d="m6.34 17.66-1.41 1.41" />
                <path d="m19.07 4.93-1.41 1.41" />
            </svg>
            <!-- Moon icon -->
            <svg id="themeIconMoon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-moon" style="display: none;">
                <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z" />
            </svg>
        </button>

        <!-- User Dropdown Menu -->
        <div class="user-dropdown-container">
            <button type="button" class="user-dropdown-btn" onclick="toggleUserDropdown(event)" aria-expanded="false">
                <iconify-icon icon="lucide:user" style="font-size: 16px;"></iconify-icon>

                <iconify-icon icon="lucide:chevron-down" style="font-size: 14px; opacity: 0.7; transition: transform 0.2s;" id="userDropdownChevron"></iconify-icon>
            </button>

            <div id="userDropdownMenu" class="user-dropdown-menu">
                @if(auth()->check())
                <div class="user-dropdown-header">
                    <div class="user-dropdown-name">{{ auth()->user()->name }}</div>
                    <div class="user-dropdown-role">{{ ucfirst(auth()->user()->role ?? 'Admin') }}</div>
                </div>

                <div class="user-dropdown-divider"></div>
                <button type="button" onclick="confirmLogout()" class="user-dropdown-item text-danger">
                    <iconify-icon icon="solar:logout-3-bold-duotone" style="font-size: 16px;"></iconify-icon>
                    <span>Keluar / Logout</span>
                </button>
                @else
                <a href="{{ route('admin.login') }}" class="user-dropdown-item">
                    <iconify-icon icon="solar:login-3-bold-duotone" style="font-size: 16px;"></iconify-icon>
                    <span>Sign In</span>
                </a>
                @endif
            </div>
        </div>
    </div>
</header>

<script>
    function toggleTopDropdown(e, menuId) {
        e.stopPropagation();
        const targetMenu = document.getElementById(menuId);
        if (!targetMenu) return;

        // Close all other popovers/dropdowns first
        const allPopovers = document.querySelectorAll('.topbar-popover-menu, .user-dropdown-menu');
        const isAlreadyOpen = targetMenu.classList.contains('show');

        allPopovers.forEach(menu => menu.classList.remove('show'));
        const chevron = document.getElementById('userDropdownChevron');
        if (chevron) chevron.style.transform = 'rotate(0deg)';

        if (!isAlreadyOpen) {
            targetMenu.classList.add('show');
        }
    }

    function switchNotifTab(tabName) {
        const tabBtnChat = document.getElementById('tabBtnChat');
        const tabBtnReview = document.getElementById('tabBtnReview');
        const chatPane = document.getElementById('topbar-chat-pane');
        const reviewPane = document.getElementById('topbar-review-pane');
        const footerChatLink = document.getElementById('footerChatLink');
        const footerReviewLink = document.getElementById('footerReviewLink');

        if (tabName === 'chat') {
            tabBtnChat?.classList.add('active');
            tabBtnReview?.classList.remove('active');
            if (chatPane) chatPane.style.display = 'block';
            if (reviewPane) reviewPane.style.display = 'none';
            if (footerChatLink) footerChatLink.style.display = 'inline-flex';
            if (footerReviewLink) footerReviewLink.style.display = 'none';
        } else {
            tabBtnReview?.classList.add('active');
            tabBtnChat?.classList.remove('active');
            if (chatPane) chatPane.style.display = 'none';
            if (reviewPane) reviewPane.style.display = 'block';
            if (footerChatLink) footerChatLink.style.display = 'none';
            if (footerReviewLink) footerReviewLink.style.display = 'inline-flex';
        }
    }

    function toggleUserDropdown(e) {
        e.stopPropagation();
        const menu = document.getElementById('userDropdownMenu');
        const chevron = document.getElementById('userDropdownChevron');
        if (!menu) return;

        // Close other topbar popovers
        const otherPopovers = document.querySelectorAll('.topbar-popover-menu');
        otherPopovers.forEach(p => p.classList.remove('show'));

        const isShown = menu.classList.contains('show');
        menu.classList.toggle('show');
        if (chevron) {
            chevron.style.transform = isShown ? 'rotate(0deg)' : 'rotate(180deg)';
        }
    }

    document.addEventListener('click', function(e) {
        const openPopovers = document.querySelectorAll('.topbar-popover-menu.show, .user-dropdown-menu.show');
        openPopovers.forEach(menu => {
            if (!menu.contains(e.target) && !e.target.closest('.theme-toggle-btn') && !e.target.closest('.user-dropdown-btn')) {
                menu.classList.remove('show');
            }
        });

        const chevron = document.getElementById('userDropdownChevron');
        const userMenu = document.getElementById('userDropdownMenu');
        if (userMenu && !userMenu.classList.contains('show') && chevron) {
            chevron.style.transform = 'rotate(0deg)';
        }
    });
</script>