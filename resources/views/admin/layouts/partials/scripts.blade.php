<script>
    // ─── Preview avatar upload ────────────────────────────────────
    function previewAvatar(input) {
        if (input.files && input.files[0]) {
            const reader = new window.FileReader();
            reader.onload = e => {
                const prev = document.getElementById('avatarPreview');
                if (prev) prev.src = e.target.result;
                const wrap = document.getElementById('avatarPreviewWrap');
                if (wrap) wrap.innerHTML =
                    `<img id="avatarPreview" src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;">`;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // ─── Sidebar ──────────────────────────────────────────────────

    function toggleSidebar() {
        if (window.innerWidth > 768) {
            document.body.classList.toggle('sidebar-collapsed');
            const isCollapsed = document.body.classList.contains('sidebar-collapsed');
            localStorage.setItem('sidebarCollapsed', isCollapsed ? 'true' : 'false');
        } else {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('sidebarOverlay').classList.toggle('open');
        }
        updateSidebarToggleIcon();
    }

    function closeSidebar() {
        if (window.innerWidth > 768) {
            document.body.classList.add('sidebar-collapsed');
            localStorage.setItem('sidebarCollapsed', 'true');
        } else {
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('sidebarOverlay').classList.remove('open');
        }
        updateSidebarToggleIcon();
    }

    // ─── Loading Overlay ─────────────────────────────────────────
    function showLoading(text) {
        const el = document.getElementById('globalLoadingOverlay');
        if (!el) return;
        const t = document.getElementById('loadingText');
        if (t) t.textContent = text || 'Memproses, harap tunggu…';
        el.style.display = 'flex';
    }

    function hideLoading() {
        const el = document.getElementById('globalLoadingOverlay');
        if (el) el.style.display = 'none';
    }

    // ─── Helper: close any modal by id ───────────────────────────
    function _openModal(id) {
        const m = document.getElementById(id);
        if (m) m.classList.add('open');
    }

    function _closeModal(id) {
        const m = document.getElementById(id);
        if (m) m.classList.remove('open');
    }

    // ─── Delete Confirm ──────────────────────────────────────────
    function confirmDelete(url, name) {
        const body = document.getElementById('confirmModalBody');
        if (body) body.textContent = `Apakah Anda yakin ingin menghapus "${name}"? Tindakan ini tidak dapat dibatalkan.`;
        const form = document.getElementById('confirmForm');
        if (form) form.action = url;
        _openModal('confirmModal');
    }

    function closeConfirm() {
        _closeModal('confirmModal');
    }

    // ─── Create/Tambah Confirm ───────────────────────────────────
    // formId    : id of the <form> element to submit
    // title     : (optional) custom modal title
    // bodyText  : (optional) custom modal body text
    function confirmCreate(formId, title, bodyText) {
        const form = document.getElementById(formId);
        if (form && typeof form.checkValidity === 'function' && !form.checkValidity()) {
            form.reportValidity();
            return false;
        }
        window._pendingFormId = formId;
        const t = document.getElementById('createConfirmTitle');
        const b = document.getElementById('createConfirmBody');
        if (t) t.textContent = title || 'Konfirmasi Simpan Data';
        if (b) b.textContent = bodyText || 'Apakah Anda yakin ingin menyimpan data baru ini?';
        _openModal('createConfirmModal');
    }

    function closeCreateConfirm() {
        _closeModal('createConfirmModal');
    }

    // ─── Update/Edit Confirm ─────────────────────────────────────
    function confirmUpdate(formId, title, bodyText) {
        const form = document.getElementById(formId);
        if (form && typeof form.checkValidity === 'function' && !form.checkValidity()) {
            form.reportValidity();
            return false;
        }
        window._pendingFormId = formId;
        const t = document.getElementById('updateConfirmTitle');
        const b = document.getElementById('updateConfirmBody');
        if (t) t.textContent = title || 'Konfirmasi Perubahan';
        if (b) b.textContent = bodyText || 'Apakah Anda yakin ingin menyimpan perubahan ini?';
        _openModal('updateConfirmModal');
    }

    function closeUpdateConfirm() {
        _closeModal('updateConfirmModal');
    }

    // ─── Submit the pending form (called from modal buttons) ─────
    function doFormSubmit(modalId) {
        _closeModal(modalId);
        const formId = window._pendingFormId;
        if (!formId) return;
        const form = document.getElementById(formId);
        if (form) {
            if (typeof form.checkValidity === 'function' && !form.checkValidity()) {
                form.reportValidity();
                return false;
            }
            if (typeof window.showAdminLoading === 'function') {
                window.showAdminLoading('Menyimpan data…');
            }
            form.submit();
        }
    }

    // ─── Logout Confirm ──────────────────────────────────────────
    function confirmLogout() {
        _openModal('logoutModal');
    }

    function closeLogoutModal() {
        _closeModal('logoutModal');
    }

    // Close modal when clicking backdrop
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-overlay')) {
            e.target.classList.remove('open');
        }
    });
    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open'));
        }
    });


    // Theme toggle helper functions
    function updateThemeIcons() {
        const theme = document.documentElement.getAttribute('data-theme') || 'dark';
        const sunIcon = document.getElementById('themeIconSun');
        const moonIcon = document.getElementById('themeIconMoon');
        if (sunIcon && moonIcon) {
            if (theme === 'light') {
                sunIcon.style.display = 'none';
                moonIcon.style.display = 'block';
            } else {
                sunIcon.style.display = 'block';
                moonIcon.style.display = 'none';
            }
        }
    }

    function toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateThemeIcons();
    }

    // Sidebar toggle icon helper
    function updateSidebarToggleIcon() {
        const menuIcon = document.getElementById('menuIcon');
        const closeIcon = document.getElementById('closeIcon');
        if (menuIcon && closeIcon) {
            let isCollapsed = false;
            if (window.innerWidth > 768) {
                isCollapsed = document.body.classList.contains('sidebar-collapsed');
            } else {
                const sidebar = document.getElementById('sidebar');
                isCollapsed = sidebar ? !sidebar.classList.contains('open') : true;
            }

            if (isCollapsed) {
                menuIcon.style.display = 'block';
                closeIcon.style.display = 'none';
            } else {
                menuIcon.style.display = 'none';
                closeIcon.style.display = 'block';
            }
        }
    }

    // Initialize icons and update sidebar toggle icon
    updateThemeIcons();
    updateSidebarToggleIcon();
    window.addEventListener('resize', updateSidebarToggleIcon);
</script>

@auth
<script>
    (function() {
        let audioCtx = null;

        function initAudioContext() {
            if (!audioCtx) {
                audioCtx = new(window.AudioContext || window.webkitAudioContext)();
            }
            if (audioCtx && audioCtx.state === 'suspended') {
                audioCtx.resume();
            }
        }
        document.addEventListener('click', initAudioContext, {
            once: true
        });
        document.addEventListener('keydown', initAudioContext, {
            once: true
        });

        function playNotificationSound() {
            try {
                initAudioContext();
                if (!audioCtx) return;

                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.type = 'sine';

                // Ring/alert melody pattern: D5 -> A5 -> F5
                const now = audioCtx.currentTime;
                osc.frequency.setValueAtTime(587.33, now); // D5
                osc.frequency.setValueAtTime(880, now + 0.12); // A5
                osc.frequency.setValueAtTime(698.46, now + 0.24); // F5

                gain.gain.setValueAtTime(0.12, now);
                gain.gain.exponentialRampToValueAtTime(0.01, now + 0.45);

                osc.start(now);
                osc.stop(now + 0.45);
            } catch (e) {
                console.warn("AudioContext failed:", e);
            }
        }

        function showToastNotification() {
            // Remove existing toast if any
            const existing = document.getElementById('chatToastNotification');
            if (existing) existing.remove();

            const toast = document.createElement('div');
            toast.id = 'chatToastNotification';
            toast.style.cssText = `
                position: fixed;
                top: 20px;
                right: -350px;
                background: var(--bg-card, #ffffff);
                border-left: 4px solid var(--accent, #4f6ef7);
                border-radius: var(--radius, 12px);
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
                padding: 16px 20px;
                z-index: 9999;
                transition: right 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                display: flex;
                align-items: center;
                gap: 12px;
                font-family: 'Inter', sans-serif;
                cursor: pointer;
                border: 1px solid var(--border);
            `;
            toast.innerHTML = `
                <div style="font-size: 24px;">💬</div>
                <div>
                    <div style="font-weight: 700; font-size: 14px; color: var(--text-primary, #0f172a); margin-bottom: 2px;">Chat Baru Masuk!</div>
                    <div style="font-size: 12px; color: var(--text-secondary, #475569);">Customer membutuhkan bantuan. Klik untuk balas.</div>
                </div>
            `;
            toast.onclick = () => {
                window.location.href = "{{ route('admin.chats.index') }}";
            };
            document.body.appendChild(toast);
            setTimeout(() => toast.style.right = '20px', 100);

            // Play Sound
            playNotificationSound();

            // Auto remove
            setTimeout(() => {
                toast.style.right = '-350px';
                setTimeout(() => toast.remove(), 500);
            }, 6000);
        }

        let lastUnreadChatCount = -1;
        let lastUnreadReviewCount = -1;
        let lastPaidOrderCount = -1;

        // Request Desktop Browser Notification Permission
        if ('Notification' in window && Notification.permission !== 'granted' && Notification.permission !== 'denied') {
            Notification.requestPermission();
        }

        function showDesktopOrderNotification(data) {
            if ('Notification' in window && Notification.permission === 'granted') {
                try {
                    const notif = new Notification('📦 Pesanan Baru Masuk & Dibayar!', {
                        body: `Invoice #${data.latest_paid_invoice || 'Lunas'} oleh ${data.latest_paid_customer || 'Pelanggan'} (Rp ${data.latest_paid_amount || '0'})`,
                        icon: '{{ asset("assets/img/logo-cyberstore.jpg") }}',
                        tag: 'new-order-' + (data.latest_paid_invoice || Date.now()),
                    });
                    notif.onclick = function() {
                        window.focus();
                        if (data.latest_paid_url) window.location.href = data.latest_paid_url;
                    };
                } catch (e) {}
            }
        }

        function showOrderToastNotification(data) {
            const toast = document.createElement('div');
            toast.style.cssText = `
                position: fixed;
                bottom: 80px;
                right: -380px;
                background: var(--bg-card, #1e293b);
                color: var(--text-primary, #ffffff);
                padding: 14px 18px;
                border-radius: 14px;
                box-shadow: 0 10px 25px rgba(16, 185, 129, 0.4);
                z-index: 9999;
                transition: right 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                display: flex;
                align-items: center;
                gap: 12px;
                font-family: 'Inter', sans-serif;
                cursor: pointer;
                border: 1px solid rgba(16, 185, 129, 0.5);
            `;
            toast.innerHTML = `
                <div style="font-size: 26px; animation: pulse 1.5s infinite;">📦</div>
                <div>
                    <div style="font-weight: 700; font-size: 13.5px; color: #10b981; margin-bottom: 2px;">Pesanan Baru Dibayar!</div>
                    <div style="font-size: 12px; color: var(--text-secondary, #94a3b8);">Invoice #${data.latest_paid_invoice || 'Lunas'} oleh <strong>${data.latest_paid_customer || 'Pelanggan'}</strong> (Rp ${data.latest_paid_amount || '0'})</div>
                </div>
            `;
            toast.onclick = () => {
                window.location.href = data.latest_paid_url || "{{ route('admin.orders.index', ['status' => 'paid']) }}";
            };
            document.body.appendChild(toast);
            setTimeout(() => toast.style.right = '20px', 100);

            // Play sound
            playNotificationSound();

            // Trigger OS Desktop Notification
            showDesktopOrderNotification(data);

            // Auto remove
            setTimeout(() => {
                toast.style.right = '-380px';
                setTimeout(() => toast.remove(), 500);
            }, 7000);
        }

        function showReviewToastNotification() {
            const existing = document.getElementById('reviewToastNotification');
            if (existing) existing.remove();

            const toast = document.createElement('div');
            toast.id = 'reviewToastNotification';
            toast.style.cssText = `
                position: fixed;
                top: 20px;
                right: -350px;
                background: var(--bg-card, #ffffff);
                border-left: 4px solid #f59e0b;
                border-radius: var(--radius, 12px);
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
                padding: 16px 20px;
                z-index: 9999;
                transition: right 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                display: flex;
                align-items: center;
                gap: 12px;
                font-family: 'Inter', sans-serif;
                cursor: pointer;
                border: 1px solid var(--border);
            `;
            toast.innerHTML = `
                <div style="font-size: 24px;">⭐</div>
                <div>
                    <div style="font-weight: 700; font-size: 14px; color: var(--text-primary, #0f172a); margin-bottom: 2px;">Ulasan Produk Baru!</div>
                    <div style="font-size: 12px; color: var(--text-secondary, #475569);">Pembeli memberikan ulasan baru. Klik untuk melihat & membalas.</div>
                </div>
            `;
            toast.onclick = () => {
                window.location.href = "{{ route('admin.review-chats.index') }}";
            };
            document.body.appendChild(toast);
            setTimeout(() => toast.style.right = '20px', 100);

            playNotificationSound();

            setTimeout(() => {
                toast.style.right = '-350px';
                setTimeout(() => toast.remove(), 500);
            }, 6000);
        }

        function syncAllAdminNotifications() {
            if (document.hidden) return;
            fetch("{{ route('admin.all-unread-counts') }}")
                .then(res => res.json())
                .then(data => {
                    // 1. Orders Notification
                    const paidCount = parseInt(data.paid_count || '0');
                    const cancelCount = parseInt(data.pending_cancel_count || '0');

                    if (lastPaidOrderCount !== -1 && paidCount > lastPaidOrderCount) {
                        showOrderToastNotification(data);
                    }
                    lastPaidOrderCount = paidCount;

                    const sidebarPaidBadge = document.getElementById('sidebar-order-paid-badge');
                    if (sidebarPaidBadge) {
                        sidebarPaidBadge.innerText = paidCount + ' Baru';
                        sidebarPaidBadge.style.display = paidCount > 0 ? 'inline-block' : 'none';
                    }

                    const sidebarCancelBadge = document.getElementById('sidebar-cancel-badge');
                    if (sidebarCancelBadge) {
                        sidebarCancelBadge.innerText = cancelCount + ' Batal';
                        sidebarCancelBadge.style.display = cancelCount > 0 ? 'inline-block' : 'none';
                    }

                    const topbarOrderBadge = document.getElementById('topbar-order-badge');
                    if (topbarOrderBadge) {
                        topbarOrderBadge.innerText = paidCount;
                        topbarOrderBadge.style.display = paidCount > 0 ? 'flex' : 'none';
                    }

                    // 2. Chat Notification
                    const chatCount = parseInt(data.chat_unread_count || '0');
                    if (lastUnreadChatCount !== -1 && chatCount > lastUnreadChatCount) {
                        showToastNotification();
                        const bubble = document.getElementById('floating-chat-bubble');
                        if (bubble) {
                            bubble.classList.add('wiggle-animation');
                            setTimeout(() => bubble.classList.remove('wiggle-animation'), 600);
                        }
                    }
                    lastUnreadChatCount = chatCount;

                    const sidebarChatBadge = document.getElementById('sidebar-chat-badge');
                    if (sidebarChatBadge) {
                        sidebarChatBadge.innerText = chatCount;
                        sidebarChatBadge.style.display = chatCount > 0 ? 'inline-block' : 'none';
                    }

                    const topbarChatBadge = document.getElementById('topbar-chat-badge');
                    if (topbarChatBadge) {
                        topbarChatBadge.innerText = chatCount;
                        topbarChatBadge.style.display = chatCount > 0 ? 'flex' : 'none';
                    }

                    const bubbleBadge = document.getElementById('bubble-unread-badge');
                    if (bubbleBadge) {
                        bubbleBadge.innerText = chatCount;
                        bubbleBadge.style.display = chatCount > 0 ? 'flex' : 'none';
                    }

                    // 3. Review Notification
                    const reviewCount = parseInt(data.review_unread_count || '0');
                    if (lastUnreadReviewCount !== -1 && reviewCount > lastUnreadReviewCount) {
                        showReviewToastNotification();
                    }
                    lastUnreadReviewCount = reviewCount;

                    const sidebarReviewBadge = document.getElementById('sidebar-review-badge');
                    if (sidebarReviewBadge) {
                        sidebarReviewBadge.innerText = reviewCount;
                        sidebarReviewBadge.style.display = reviewCount > 0 ? 'inline-block' : 'none';
                    }

                    // 4. Render interactive topbar dropdown popovers
                    updateTopbarDropdowns(data);
                })
                .catch(() => {});
        }

        function updateTopbarDropdowns(data) {
            // 1. Order Popover
            const orderList = document.getElementById('topbar-order-list');
            const popoverOrderHeaderBadge = document.getElementById('popover-order-badge-header');
            const paidCount = parseInt(data.paid_count || '0');
            const cancelCount = parseInt(data.pending_cancel_count || '0');

            if (popoverOrderHeaderBadge) {
                if (cancelCount > 0 && paidCount > 0) {
                    popoverOrderHeaderBadge.innerText = `${paidCount} Lunas, ${cancelCount} Batal`;
                } else if (cancelCount > 0) {
                    popoverOrderHeaderBadge.innerText = `${cancelCount} Pengajuan Batal`;
                    popoverOrderHeaderBadge.style.color = '#ef4444';
                    popoverOrderHeaderBadge.style.background = 'rgba(239, 68, 68, 0.15)';
                } else {
                    popoverOrderHeaderBadge.innerText = `${paidCount} Perlu Dikemas`;
                    popoverOrderHeaderBadge.style.color = '#10b981';
                    popoverOrderHeaderBadge.style.background = 'rgba(16, 185, 129, 0.15)';
                }
            }

            if (orderList) {
                if (data.recent_orders && data.recent_orders.length > 0) {
                    let html = '';
                    data.recent_orders.forEach(order => {
                        const isCancel = order.cancel_request_status === 'pending';
                        const statusPill = isCancel
                            ? `<span style="background: rgba(239, 68, 68, 0.15); color: #ef4444; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px;">Minta Batal</span>`
                            : `<span style="background: rgba(16, 185, 129, 0.15); color: #10b981; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px;">Lunas</span>`;

                        const initialBg = isCancel ? '#fee2e2' : '#dcfce7';
                        const initialColor = isCancel ? '#991b1b' : '#166534';

                        html += `
                            <a href="${order.url}" class="topbar-popover-item">
                                <div style="width: 36px; height: 36px; border-radius: 50%; background: ${initialBg}; color: ${initialColor}; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px; flex-shrink: 0;">
                                    ${order.customer_initial}
                                </div>
                                <div style="flex: 1; min-width: 0;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px;">
                                        <span style="font-weight: 700; font-size: 12.5px; color: var(--text-primary);">${order.invoice_number}</span>
                                        ${statusPill}
                                    </div>
                                    <div style="font-size: 12px; color: var(--text-secondary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        ${order.customer_name} · <strong>${order.amount_formatted}</strong>
                                    </div>
                                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px; display: flex; justify-content: space-between; align-items: center;">
                                        <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 180px;">${order.item_summary}</span>
                                        <span>${order.time_ago}</span>
                                    </div>
                                </div>
                            </a>
                        `;
                    });
                    orderList.innerHTML = html;
                } else {
                    orderList.innerHTML = `
                        <div class="topbar-popover-empty">
                            <iconify-icon icon="flat-color-icons:ok" style="font-size: 32px;"></iconify-icon>
                            <div>Semua pesanan sudah diproses 🎉</div>
                        </div>
                    `;
                }
            }

            // 2. Chat & Review Popover
            const chatCount = parseInt(data.chat_unread_count || '0');
            const reviewCount = parseInt(data.review_unread_count || '0');
            const totalNotif = chatCount + reviewCount;

            const notifTotalBadge = document.getElementById('popover-notif-total-badge');
            if (notifTotalBadge) {
                notifTotalBadge.innerText = `${totalNotif} Baru`;
                notifTotalBadge.style.display = totalNotif > 0 ? 'inline-block' : 'none';
            }

            const tabBadgeChat = document.getElementById('tabBadgeChat');
            if (tabBadgeChat) {
                tabBadgeChat.innerText = chatCount;
                tabBadgeChat.style.display = chatCount > 0 ? 'inline-block' : 'none';
            }

            const tabBadgeReview = document.getElementById('tabBadgeReview');
            if (tabBadgeReview) {
                tabBadgeReview.innerText = reviewCount;
                tabBadgeReview.style.display = reviewCount > 0 ? 'inline-block' : 'none';
            }

            // Render Chat List
            const chatList = document.getElementById('topbar-chat-list');
            if (chatList) {
                if (data.recent_chats && data.recent_chats.length > 0) {
                    let html = '';
                    data.recent_chats.forEach(chat => {
                        const unreadDot = chat.unread_count > 0 
                            ? `<span style="background: #DF0B2B; color: #fff; font-size: 9px; font-weight: 700; padding: 1px 6px; border-radius: 10px;">${chat.unread_count} baru</span>`
                            : '';

                        html += `
                            <a href="${chat.url}" class="topbar-popover-item">
                                <div style="width: 36px; height: 36px; border-radius: 50%; background: #e0e7ff; color: #3730a3; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px; flex-shrink: 0;">
                                    ${chat.customer_initial}
                                </div>
                                <div style="flex: 1; min-width: 0;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px;">
                                        <span style="font-weight: 700; font-size: 12.5px; color: var(--text-primary);">${chat.customer_name}</span>
                                        ${unreadDot}
                                    </div>
                                    <div style="font-size: 11.5px; font-weight: 600; color: var(--primary, #3b82f6); margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        📦 ${chat.product_name}
                                    </div>
                                    <div style="font-size: 11.5px; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        ${chat.last_message}
                                    </div>
                                    <div style="font-size: 10.5px; color: var(--text-muted); margin-top: 3px; text-align: right;">
                                        ${chat.time_ago}
                                    </div>
                                </div>
                            </a>
                        `;
                    });
                    chatList.innerHTML = html;
                } else {
                    chatList.innerHTML = `
                        <div class="topbar-popover-empty">
                            <iconify-icon icon="flat-color-icons:speech-bubble" style="font-size: 32px;"></iconify-icon>
                            <div>Belum ada pesan chat masuk</div>
                        </div>
                    `;
                }
            }

            // Render Review List
            const reviewList = document.getElementById('topbar-review-list');
            if (reviewList) {
                if (data.recent_reviews && data.recent_reviews.length > 0) {
                    let html = '';
                    data.recent_reviews.forEach(review => {
                        let stars = '⭐'.repeat(Math.min(5, Math.max(1, review.rating)));
                        const unreadBadge = !review.is_read
                            ? `<span style="background: rgba(245, 158, 11, 0.15); color: #d97706; font-size: 9px; font-weight: 700; padding: 1px 5px; border-radius: 4px;">Baru</span>`
                            : '';

                        html += `
                            <a href="${review.url}" class="topbar-popover-item">
                                <div style="width: 36px; height: 36px; border-radius: 50%; background: #fef3c7; color: #92400e; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px; flex-shrink: 0;">
                                    ${review.customer_initial}
                                </div>
                                <div style="flex: 1; min-width: 0;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px;">
                                        <span style="font-weight: 700; font-size: 12.5px; color: var(--text-primary);">${review.customer_name}</span>
                                        ${unreadBadge}
                                    </div>
                                    <div style="font-size: 11px; margin-bottom: 2px;">
                                        <span style="font-size: 10px;">${stars}</span> · <span style="color: var(--text-muted);">${review.product_name}</span>
                                    </div>
                                    <div style="font-size: 11.5px; color: var(--text-secondary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        "${review.comment}"
                                    </div>
                                    <div style="font-size: 10.5px; color: var(--text-muted); margin-top: 3px; text-align: right;">
                                        ${review.time_ago}
                                    </div>
                                </div>
                            </a>
                        `;
                    });
                    reviewList.innerHTML = html;
                } else {
                    reviewList.innerHTML = `
                        <div class="topbar-popover-empty">
                            <iconify-icon icon="flat-color-icons:rating" style="font-size: 32px;"></iconify-icon>
                            <div>Belum ada ulasan baru</div>
                        </div>
                    `;
                }
            }
        }

        // Initial sync on page load
        syncAllAdminNotifications();

        // Consolidated poll every 10 seconds, resume immediately on focus
        setInterval(syncAllAdminNotifications, 10000);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) syncAllAdminNotifications();
        });

        // ── FLOATING CHAT WIDGET ──────────────────────────────────────────
        let activeChatId = null;
        let widgetPollInterval = null;

        // Styles injection
        const style = document.createElement('style');
        style.textContent = `
            #floating-chat-bubble {
                position: fixed; bottom: 20px; right: 20px; z-index: 9998;
                width: 56px; height: 56px; border-radius: 50%;
                background: #3C3565; display: flex; align-items: center; justify-content: center;
                box-shadow: 0 4px 12px rgba(0,0,0,0.25); cursor: pointer; color: white;
                transition: transform 0.2s, background 0.2s;
            }
            #floating-chat-bubble:hover { transform: scale(1.05); background: #2C2458; }
            
            @keyframes wiggle-chat {
                0% { transform: scale(1) rotate(0deg); }
                15% { transform: scale(1.1) rotate(8deg); }
                30% { transform: scale(1.1) rotate(-8deg); }
                45% { transform: scale(1.1) rotate(6deg); }
                60% { transform: scale(1.1) rotate(-6deg); }
                75% { transform: scale(1.1) rotate(3deg); }
                90% { transform: scale(1.1) rotate(-3deg); }
                100% { transform: scale(1) rotate(0deg); }
            }
            .wiggle-animation {
                animation: wiggle-chat 0.6s ease-in-out;
            }
            
            #floating-chat-window {
                position: fixed; bottom: 90px; right: 20px; z-index: 9998;
                width: 360px; height: 480px; display: none; flex-direction: column;
                background: var(--bg-card, #ffffff); border: 1px solid var(--border);
                border-radius: 16px; box-shadow: 0 8px 30px rgba(0,0,0,0.2);
                overflow: hidden; font-family: 'Inter', sans-serif;
            }
            #floating-chat-window.open { display: flex; animation: slideUp-chat 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.2); }
            @keyframes slideUp-chat { from { opacity: 0; transform: translateY(20px) scale(0.95); } to { opacity: 1; transform: translateY(0) scale(1); } }
            
            .widget-header {
                background: #3C3565; color: white; padding: 14px 16px;
                display: flex; align-items: center; justify-content: space-between;
                flex-shrink: 0;
            }
            .widget-header-title { font-weight: 700; font-size: 14px; display: flex; align-items: center; gap: 8px; }
            .widget-header-actions { display: flex; gap: 8px; align-items: center; }
            .widget-btn { background: none; border: none; color: white; cursor: pointer; padding: 4px; display: flex; align-items: center; transition: opacity 0.2s; }
            .widget-btn:hover { opacity: 0.8; }
            
            .widget-body { flex: 1; overflow-y: auto; display: flex; flex-direction: column; background: var(--bg-dark, #f8fafc); }
            
            /* Chat list */
            .widget-chat-item {
                display: flex; gap: 10px; padding: 12px 16px; border-bottom: 1px solid var(--border);
                cursor: pointer; transition: background 0.2s; align-items: center;
            }
            .widget-chat-item:hover { background: var(--bg-card-hover, rgba(0,0,0,0.02)); }
            .widget-chat-avatar {
                width: 36px; height: 36px; border-radius: 50%; background: #64748b; color: white;
                display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; flex-shrink: 0;
            }
            .widget-chat-details { flex: 1; min-width: 0; }
            .widget-chat-name { font-weight: 700; font-size: 13px; color: var(--text-primary); margin-bottom: 2px; }
            .widget-chat-message { font-size: 12px; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
            .widget-chat-badge {
                background: #3C3565; color: white; font-size: 9px; font-weight: 700;
                padding: 1px 6px; border-radius: 999px; min-width: 15px; text-align: center;
            }
            
            /* Conversations */
            .widget-messages-container { display: flex; flex-direction: column; gap: 10px; padding: 14px; flex: 1; overflow-y: auto; }
            .widget-msg-wrap { display: flex; gap: 8px; align-items: flex-end; }
            .widget-msg-wrap.admin { flex-direction: row-reverse; }
            .widget-msg-avatar {
                width: 24px; height: 24px; border-radius: 50%; background: #3C3565; color: white;
                display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 700; flex-shrink: 0;
            }
            .widget-msg-wrap.customer .widget-msg-avatar { background: #64748b; }
            .widget-msg-bubble {
                max-width: 75%; padding: 8px 12px; border-radius: 12px; font-size: 12.5px; line-height: 1.4; word-break: break-word;
            }
            .widget-msg-bubble.customer { background: var(--bg-card, #fff); border: 1px solid var(--border); border-bottom-left-radius: 2px; color: var(--text-primary); }
            .widget-msg-bubble.admin { background: #3C3565; color: white; border-bottom-right-radius: 2px; }
            .widget-msg-time { font-size: 9px; color: var(--text-muted); margin-top: 2px; text-align: right; }
            .widget-msg-wrap.customer .widget-msg-time { text-align: left; }
            
            /* Footer reply form */
            .widget-footer { padding: 10px; background: var(--bg-card, #fff); border-top: 1px solid var(--border); display: flex; gap: 8px; align-items: center; flex-shrink: 0; }
            .widget-input {
                flex: 1; border: 1px solid var(--border); background: var(--bg-input, #f1f5f9); color: var(--text-primary);
                border-radius: 20px; padding: 8px 14px; font-size: 12.5px; outline: none; transition: border-color 0.2s;
            }
            .widget-input:focus { border-color: #3C3565; }
            .widget-send-btn {
                background: #3C3565; border: none; border-radius: 50%; width: 32px; height: 32px;
                color: white; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.2s;
            }
            .widget-send-btn:hover { background: #2C2458; }
        `;

        // Render Bubble Button
        const chatBubble = document.createElement('div');
        chatBubble.id = 'floating-chat-bubble';
        chatBubble.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-message-square"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            <span id="bubble-unread-badge" style="position:absolute; top:-2px; right:-2px; background:#3C3565; color:#fff; font-size:9px; font-weight:700; width:18px; height:18px; border-radius:50%; display:none; align-items:center; justify-content:center; border:2px solid var(--bg-dark);">0</span>
        `;

        // Render Chat Window
        const chatBox = document.createElement('div');
        chatBox.id = 'floating-chat-window';

        function initFloatingWidgetDOM() {
            if (document.head && !document.getElementById('floating-chat-styles')) {
                style.id = 'floating-chat-styles';
                document.head.appendChild(style);
            }
            if (document.body && !document.getElementById('floating-chat-bubble')) {
                document.body.appendChild(chatBubble);
                document.body.appendChild(chatBox);
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initFloatingWidgetDOM);
        } else {
            initFloatingWidgetDOM();
        }

        // Open/Close toggle
        chatBubble.onclick = () => {
            chatBox.classList.toggle('open');
            if (chatBox.classList.contains('open')) {
                openChatList();
            } else {
                closeWidgetConversation();
            }
        };

        function openChatList() {
            activeChatId = null;
            closeWidgetConversation();
            chatBox.innerHTML = `
                <div class="widget-header">
                    <span class="widget-header-title">💬 Chat Support</span>
                    <button class="widget-btn" onclick="document.getElementById('floating-chat-window').classList.remove('open')">✕</button>
                </div>
                <div class="widget-body" id="widgetBody">
                    <div style="display:flex; justify-content:center; padding:40px;"><div class="loading-spinner">Mencari chat...</div></div>
                </div>
            `;
            loadWidgetChats();
        }

        function loadWidgetChats() {
            fetch("{{ route('admin.chats.index') }}", {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    const container = document.getElementById('widgetBody');
                    if (!container) return;

                    if (!data.chats || data.chats.length === 0) {
                        container.innerHTML = `<div style="text-align:center; color:var(--text-muted); padding:40px; font-size:13px;">Belum ada chat masuk.</div>`;
                        return;
                    }

                    let html = '';
                    data.chats.forEach(chat => {
                        const name = chat.customer?.name || 'Customer';
                        const initials = name.substring(0, 1).toUpperCase();
                        const unreadHtml = chat.unread_count > 0 ? `<span class="widget-chat-badge">${chat.unread_count}</span>` : '';
                        let lastMsg = chat.last_message?.message || 'Memulai percakapan...';
                        if (lastMsg.startsWith('[IMAGE]:') || lastMsg.includes('data:image')) {
                            lastMsg = '<iconify-icon icon="flat-color-icons:picture" style="font-size:13px; vertical-align:middle;"></iconify-icon> [Gambar]';
                        }
                        const productHtml = chat.product_name ? `<div style="font-size:11px; color:var(--text-muted); margin-top:2px; display:flex; align-items:center; gap:4px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><iconify-icon icon="flat-color-icons:box" style="font-size:13px;"></iconify-icon> ${escapeHtml(chat.product_name)}</div>` : '';

                        html += `
                            <div class="widget-chat-item" onclick="openWidgetChat(${chat.id}, '${name}', '${chat.product_name || ''}')">
                                <div class="widget-chat-avatar">${initials}</div>
                                <div class="widget-chat-details" style="min-width:0;">
                                    <div class="widget-chat-name">${name}</div>
                                    ${productHtml}
                                    <div class="widget-chat-message" style="margin-top:2px;">${lastMsg}</div>
                                </div>
                                ${unreadHtml}
                            </div>
                        `;
                    });
                    container.innerHTML = html;
                })
                .catch(() => {
                    const container = document.getElementById('widgetBody');
                    if (container) container.innerHTML = `<div style="padding:20px; font-size:12px; color:red;">Gagal memuat chat.</div>`;
                });
        }

        window.openWidgetChat = function(chatId, customerName, productName) {
            activeChatId = chatId;
            const productBar = productName && productName !== 'null' && productName !== '' ? `
                <div style="background:var(--bg-card-hover, rgba(0,0,0,0.03)); padding:8px 16px; border-bottom:1px solid var(--border); font-size:11px; color:var(--text-muted); display:flex; align-items:center; gap:4px; flex-shrink:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                    📦 Produk: <strong style="color:var(--text-primary);">${productName}</strong>
                </div>
            ` : '';

            chatBox.innerHTML = `
                <div class="widget-header">
                    <div class="widget-header-title">
                        <button class="widget-btn" onclick="openChatList()">←</button>
                        <span>${customerName}</span>
                    </div>
                    <button class="widget-btn" onclick="document.getElementById('floating-chat-window').classList.remove('open'); closeWidgetConversation();">✕</button>
                </div>
                ${productBar}
                <div class="widget-body">
                    <div class="widget-messages-container" id="widgetMessages"></div>
                    <div class="widget-footer">
                        <input type="text" class="widget-input" id="widgetInput" placeholder="Ketik balasan..." onkeydown="if(event.key === 'Enter') sendWidgetReply()">
                        <button class="widget-send-btn" onclick="sendWidgetReply()">↗</button>
                    </div>
                </div>
            `;
            loadWidgetConversation();
            widgetPollInterval = setInterval(loadWidgetConversation, 4000);
        };

        function closeWidgetConversation() {
            if (widgetPollInterval) {
                clearInterval(widgetPollInterval);
                widgetPollInterval = null;
            }
        }

        function loadWidgetConversation() {
            if (!activeChatId) return;
            fetch(`/admin/chats/${activeChatId}`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    const msgContainer = document.getElementById('widgetMessages');
                    if (!msgContainer) return;

                    const wasAtBottom = msgContainer.scrollTop + msgContainer.clientHeight >= msgContainer.scrollHeight - 10;

                    let html = '';
                    data.messages.forEach(msg => {
                        const isSelf = msg.sender_type === 'admin';
                        const avatarChar = isSelf ? 'A' : data.chat.customer?.name?.substring(0, 1).toUpperCase() || 'C';
                        const time = msg.formatted_time || (function(dStr) {
                            if (!dStr) return '';
                            const d = new Date(dStr);
                            const h = String(d.getHours()).padStart(2, '0');
                            const m = String(d.getMinutes()).padStart(2, '0');
                            return h + ':' + m;
                        })(msg.created_at);

                        let msgContent = msg.message;
                        let bubbleStyle = '';
                        if (msgContent.indexOf('[IMAGE]:') === 0) {
                            const base64 = msgContent.substring(8);
                            msgContent = `<img src="${base64}" style="max-width:180px; border-radius:8px; display:block;" />`;
                        } else if (msgContent.indexOf('[STICKER]:') === 0) {
                            const stickerUrl = msgContent.substring(10);
                            msgContent = `<img src="${stickerUrl}" style="width:70px; height:70px; display:block;" />`;
                            bubbleStyle = 'background:transparent;box-shadow:none;padding:0;';
                        } else if (msgContent.indexOf('[VOICE]:') === 0) {
                            const duration = msgContent.substring(8);
                            const padDuration = duration.padStart(2, '0');
                            msgContent = `
                            <div style="display:flex; align-items:center; gap:8px; padding:4px 0; min-width:150px;">
                                <button type="button" style="background:rgba(255,255,255,0.15); border:none; border-radius:50%; width:28px; height:28px; color:white; display:flex; align-items:center; justify-content:center; cursor:pointer;" onclick="playMockAudio(this, ${duration})">
                                    <i class="bi bi-play-fill" style="font-size:14px;"></i>
                                </button>
                                <div style="flex:1;">
                                    <div style="height:3px; background:rgba(255,255,255,0.2); border-radius:1.5px; overflow:hidden;">
                                        <div class="progress-bar-fill" style="width:0%; height:100%; background:#fff; transition:width 0.1s linear;"></div>
                                    </div>
                                    <div style="display:flex; justify-content:space-between; font-size:9px; color:rgba(255,255,255,0.7); margin-top:3px;">
                                        <span class="audio-time">0:00</span>
                                        <span>0:${padDuration}</span>
                                    </div>
                                </div>
                            </div>`;
                        } else {
                            msgContent = msgContent.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
                        }

                        let checkmarksHtml = '';
                        if (isSelf) {
                            const isRead = msg.is_read === true || msg.is_read === 1 || msg.is_read === '1';
                            const color = isRead ? '#3b82f6' : '#94a3b8';
                            checkmarksHtml = `<i class="bi bi-check-all" style="color: ${color}; font-size: 13px; margin-left: 2px; vertical-align: middle;" title="${isRead ? 'Dibaca' : 'Terkirim'}"></i>`;
                        }
                        const justify = isSelf ? 'flex-end' : 'flex-start';

                        html += `
                            <div class="widget-msg-wrap ${msg.sender_type}">
                                <div class="widget-msg-avatar">${avatarChar}</div>
                                <div class="widget-msg-bubble ${msg.sender_type}" style="${bubbleStyle}">
                                    <div>${msgContent}</div>
                                    <div class="widget-msg-time" style="display: flex; align-items: center; justify-content: ${justify}; gap: 2px;">
                                        <span>${time}</span>
                                        ${checkmarksHtml}
                                    </div>
                                </div>
                            </div>
                        `;
                    });

                    msgContainer.innerHTML = html;
                    if (wasAtBottom || msgContainer.scrollTop === 0) {
                        msgContainer.scrollTop = msgContainer.scrollHeight;
                    }
                });
        }

        window.sendWidgetReply = function() {
            const input = document.getElementById('widgetInput');
            if (!input || !activeChatId) return;
            const text = input.value.trim();
            if (!text) return;
            input.value = '';

            fetch(`/admin/chats/${activeChatId}/reply`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({
                        message: text
                    })
                })
                .then(res => res.json())
                .then(data => {
                    loadWidgetConversation();
                });
        };

        window.playMockAudio = function(btn, duration) {
            const icon = btn.querySelector('i');
            const container = btn.parentElement;
            const bar = container.querySelector('.progress-bar-fill');
            const timeLabel = container.querySelector('.audio-time');

            if (btn.dataset.playing === 'true') {
                btn.dataset.playing = 'false';
                icon.className = 'bi bi-play-fill';
                clearInterval(btn.intervalId);
            } else {
                btn.dataset.playing = 'true';
                icon.className = 'bi bi-pause-fill';
                let current = 0;

                if (parseFloat(bar.style.width || '0') >= 100) {
                    bar.style.width = '0%';
                    timeLabel.innerText = '0:00';
                }

                const step = 0.1;
                btn.intervalId = setInterval(() => {
                    current += step;
                    if (current >= duration) {
                        current = duration;
                        btn.dataset.playing = 'false';
                        icon.className = 'bi bi-play-fill';
                        clearInterval(btn.intervalId);
                    }
                    const pct = (current / duration) * 100;
                    bar.style.width = pct + '%';

                    const secs = Math.floor(current);
                    timeLabel.innerText = '0:' + String(secs).padStart(2, '0');
                }, 100);
            }
        };

        // Export local helpers to global window object
        window.openChatList = openChatList;
        window.closeWidgetConversation = closeWidgetConversation;
    })();
</script>
@endauth

<script>
    // ─── LIVE SEARCH & REDIS RECOMMENDATIONS HANDLER ─────────────
    document.addEventListener('DOMContentLoaded', function () {
        const searchInputs = document.querySelectorAll('input.search-input[data-suggestion-url]');
        
        searchInputs.forEach(input => {
            const wrapper = input.closest('.search-input-wrapper') || input.parentElement;
            if (!wrapper) return;

            // Pastikan wrapper position relative
            wrapper.style.position = 'relative';

            // Buat elemen dropdown
            const dropdown = document.createElement('div');
            dropdown.className = 'search-suggestions-dropdown';
            dropdown.innerHTML = `
                <div class="search-suggestions-header">
                    <span>💡 Rekomendasi Pencarian</span>
                    <span style="font-size: 9.5px; opacity: 0.7; font-weight: normal; color: #10B981; display: inline-flex; align-items: center; gap: 3px;">
                        <span style="display:inline-block; width:6px; height:6px; background:#10B981; border-radius:50%;"></span> Redis Cache
                    </span>
                </div>
                <div class="search-suggestions-list">
                    <div style="padding: 16px; text-align: center; color: var(--text-muted); font-size: 12px;">Memuat rekomendasi...</div>
                </div>
                <div class="search-suggestions-footer">
                    <span>Gunakan tombol ↑ ↓ dan Enter untuk memilih</span>
                    <span>Tekan Esc untuk tutup</span>
                </div>
            `;
            wrapper.appendChild(dropdown);

            const listContainer = dropdown.querySelector('.search-suggestions-list');
            let debounceTimer = null;
            let currentFocusIndex = -1;
            let currentItems = [];

            function renderSuggestions(items, query) {
                currentItems = items || [];
                currentFocusIndex = -1;

                if (!items || items.length === 0) {
                    listContainer.innerHTML = `
                        <div style="padding: 16px; text-align: center; color: var(--text-muted); font-size: 12px;">
                            ${query ? `Tidak ada rekomendasi untuk "${query}"` : 'Belum ada rekomendasi'}
                        </div>
                    `;
                    return;
                }

                let html = '';
                items.forEach((item, index) => {
                    let badgeHtml = '';
                    if (item.badge) {
                        let badgeBg = 'rgba(100, 116, 139, 0.12)';
                        let badgeColor = 'var(--text-muted)';
                        
                        if (item.badge_color) {
                            badgeColor = item.badge_color;
                            badgeBg = item.badge_color + '1A';
                        } else if (item.badge_status === 'paid' || item.badge === 'Aktif') {
                            badgeBg = 'rgba(16, 185, 129, 0.15)';
                            badgeColor = '#10B981';
                        } else if (item.badge_status === 'pending_payment' || item.badge_status === 'waiting_payment') {
                            badgeBg = 'rgba(245, 158, 11, 0.15)';
                            badgeColor = '#F59E0B';
                        } else if (item.badge_status === 'shipped') {
                            badgeBg = 'rgba(139, 92, 246, 0.15)';
                            badgeColor = '#8B5CF6';
                        } else if (item.badge_status === 'cancelled' || item.badge_status === 'failed') {
                            badgeBg = 'rgba(239, 68, 68, 0.15)';
                            badgeColor = '#EF4444';
                        }

                        badgeHtml = `<span class="search-suggestion-badge" style="background:${badgeBg}; color:${badgeColor};">${item.badge}</span>`;
                    }

                    const iconVal = item.icon || 'flat-color-icons:search';

                    html += `
                        <div class="search-suggestion-item" data-index="${index}" data-value="${item.value || item.title}">
                            <div class="search-suggestion-left">
                                <div class="search-suggestion-icon">
                                    <iconify-icon icon="${iconVal}"></iconify-icon>
                                </div>
                                <div class="search-suggestion-content">
                                    <span class="search-suggestion-title">${escapeHtml(item.title)}</span>
                                    ${item.subtitle ? `<span class="search-suggestion-subtitle">${item.subtitle}</span>` : ''}
                                </div>
                            </div>
                            ${badgeHtml}
                        </div>
                    `;
                });

                listContainer.innerHTML = html;

                // Event listener klik setiap item
                const domItems = listContainer.querySelectorAll('.search-suggestion-item');
                domItems.forEach(el => {
                    el.addEventListener('click', function (e) {
                        e.preventDefault();
                        const val = this.getAttribute('data-value');
                        selectSuggestion(val);
                    });
                });
            }

            function escapeHtml(str) {
                if (!str) return '';
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;');
            }

            function fetchSuggestions(query) {
                const url = new URL(input.getAttribute('data-suggestion-url'), window.location.origin);
                url.searchParams.set('q', query);

                fetch(url.toString(), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    renderSuggestions(data, query);
                })
                .catch(err => {
                    console.warn('Gagal memuat rekomendasi Redis:', err);
                });
            }

            function selectSuggestion(value) {
                input.value = value;
                dropdown.classList.remove('open');
                const form = input.closest('form');
                if (form) {
                    form.submit();
                }
            }

            function updateActiveSuggestion() {
                const domItems = listContainer.querySelectorAll('.search-suggestion-item');
                domItems.forEach((el, idx) => {
                    if (idx === currentFocusIndex) {
                        el.classList.add('active');
                        el.scrollIntoView({ block: 'nearest' });
                    } else {
                        el.classList.remove('active');
                    }
                });
            }

            // Saat input di-klik / fokus -> langsung buka & load rekomendasi
            input.addEventListener('focus', function () {
                dropdown.classList.add('open');
                fetchSuggestions(input.value.trim());
            });

            // Saat mengetik -> debounced fetch
            input.addEventListener('input', function () {
                dropdown.classList.add('open');
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    fetchSuggestions(input.value.trim());
                }, 250);
            });

            // Navigasi keyboard
            input.addEventListener('keydown', function (e) {
                if (!dropdown.classList.contains('open')) return;

                const domItems = listContainer.querySelectorAll('.search-suggestion-item');
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (domItems.length > 0) {
                        currentFocusIndex = (currentFocusIndex + 1) % domItems.length;
                        updateActiveSuggestion();
                    }
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (domItems.length > 0) {
                        currentFocusIndex = (currentFocusIndex - 1 + domItems.length) % domItems.length;
                        updateActiveSuggestion();
                    }
                } else if (e.key === 'Enter') {
                    if (currentFocusIndex >= 0 && domItems[currentFocusIndex]) {
                        e.preventDefault();
                        const val = domItems[currentFocusIndex].getAttribute('data-value');
                        selectSuggestion(val);
                    }
                } else if (e.key === 'Escape') {
                    dropdown.classList.remove('open');
                }
            });

            // Tutup dropdown jika klik di luar
            document.addEventListener('click', function (e) {
                if (!wrapper.contains(e.target)) {
                    dropdown.classList.remove('open');
                }
            });
        });
    });
</script>