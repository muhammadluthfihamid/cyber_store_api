@php
    $pendingCancellationsCount = \App\Models\Order::where('cancel_request_status', 'pending')->count();
    $isCurrentlyViewingPending = request()->routeIs('admin.orders.index') && request('cancel_request') === 'pending';
    $hasAlerts = session('success') || session('error') || session('warning') || session('info') || $errors->any();
@endphp

@if ($pendingCancellationsCount > 0 && !$isCurrentlyViewingPending)
<div class="cancel-request-alert" style="
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    background: rgba(245, 158, 11, 0.08);
    border: 1px solid rgba(245, 158, 11, 0.2);
    border-left: 4px solid #f59e0b;
    border-radius: 12px;
    margin-bottom: 24px;
    gap: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    backdrop-filter: blur(8px);
    transition: all 0.3s ease;
">
    <div style="display: flex; align-items: center; gap: 14px; flex: 1;">
        <div style="
            display: flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            background: rgba(245, 158, 11, 0.15);
            color: #f59e0b;
            border-radius: 50%;
            font-size: 18px;
            flex-shrink: 0;
            animation: pulse-amber 2s infinite;
        ">
            <i class="bi bi-exclamation-triangle-fill"></i>
        </div>
        <div>
            <h4 style="margin: 0 0 2px 0; font-size: 14px; font-weight: 700; color: var(--text-primary); text-align: left;">
                Pengajuan Pembatalan Pesanan
            </h4>
            <p style="margin: 0; font-size: 13px; color: var(--text-muted); line-height: 1.4; text-align: left;">
                Ada <strong style="color: #f59e0b;">{{ $pendingCancellationsCount }} pesanan</strong> baru yang diajukan pembatalan oleh customer dan memerlukan persetujuan admin.
            </p>
        </div>
    </div>
    <a href="{{ route('admin.orders.index', ['cancel_request' => 'pending']) }}" class="btn-review" style="
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #f59e0b;
        color: #ffffff;
        text-decoration: none;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
        transition: background 0.2s, transform 0.2s;
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2);
    " onmouseover="this.style.background='#d97706'; this.style.transform='translateY(-1px)';" onmouseout="this.style.background='#f59e0b'; this.style.transform='translateY(0)';">
        Tinjau Sekarang <i class="bi bi-arrow-right" style="font-size: 12px;"></i>
    </a>
</div>

<style>
@keyframes pulse-amber {
    0% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4); }
    70% { box-shadow: 0 0 0 8px rgba(245, 158, 11, 0); }
    100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
}
@media (max-width: 576px) {
    .cancel-request-alert {
        flex-direction: column;
        align-items: stretch !important;
    }
    .cancel-request-alert .btn-review {
        justify-content: center;
    }
}
</style>
@endif

@if ($hasAlerts)
<div class="toast-container" id="toastContainer">

    @if (session('success'))
    <div class="toast toast-success" role="alert">
        <div class="toast-icon"><iconify-icon icon="flat-color-icons:ok" style="font-size: 22px;"></iconify-icon></div>
        <div class="toast-body">
            <div class="toast-title">Berhasil</div>
            <div class="toast-message">{{ session('success') }}</div>
        </div>
        <button class="toast-close" onclick="dismissToast(this.closest('.toast'))"><iconify-icon icon="lucide:x" style="font-size: 16px;"></iconify-icon></button>
        <div class="toast-progress success-progress"></div>
    </div>
    @endif

    @if (session('error'))
    <div class="toast toast-danger" role="alert">
        <div class="toast-icon"><iconify-icon icon="flat-color-icons:cancel" style="font-size: 22px;"></iconify-icon></div>
        <div class="toast-body">
            <div class="toast-title">Error</div>
            <div class="toast-message">{{ session('error') }}</div>
        </div>
        <button class="toast-close" onclick="dismissToast(this.closest('.toast'))"><iconify-icon icon="lucide:x" style="font-size: 16px;"></iconify-icon></button>
        <div class="toast-progress danger-progress"></div>
    </div>
    @endif

    @if (session('warning'))
    <div class="toast toast-warning" role="alert">
        <div class="toast-icon"><iconify-icon icon="flat-color-icons:warning" style="font-size: 22px;"></iconify-icon></div>
        <div class="toast-body">
            <div class="toast-title">Peringatan</div>
            <div class="toast-message">{{ session('warning') }}</div>
        </div>
        <button class="toast-close" onclick="dismissToast(this.closest('.toast'))"><iconify-icon icon="lucide:x" style="font-size: 16px;"></iconify-icon></button>
        <div class="toast-progress warning-progress"></div>
    </div>
    @endif

    @if (session('info'))
    <div class="toast toast-info" role="alert">
        <div class="toast-icon"><iconify-icon icon="flat-color-icons:info" style="font-size: 22px;"></iconify-icon></div>
        <div class="toast-body">
            <div class="toast-title">Informasi</div>
            <div class="toast-message">{{ session('info') }}</div>
        </div>
        <button class="toast-close" onclick="dismissToast(this.closest('.toast'))"><iconify-icon icon="lucide:x" style="font-size: 16px;"></iconify-icon></button>
        <div class="toast-progress info-progress"></div>
    </div>
    @endif

    @if ($errors->any())
    <div class="toast toast-danger" role="alert">
        <div class="toast-icon"><iconify-icon icon="flat-color-icons:cancel" style="font-size: 22px;"></iconify-icon></div>
        <div class="toast-body">
            <div class="toast-title">Validasi Gagal</div>
            <div class="toast-message">
                <ul style="margin:4px 0 0; padding-left:16px; line-height:1.7;">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        <button class="toast-close" onclick="dismissToast(this.closest('.toast'))"><iconify-icon icon="lucide:x" style="font-size: 16px;"></iconify-icon></button>
        <div class="toast-progress danger-progress"></div>
    </div>
    @endif

</div>

<style>
/* ─── Toast Container ─────────────────────────────────────── */
.toast-container {
    position: fixed;
    top: 24px;
    right: 24px;
    z-index: 9999999;
    display: flex;
    flex-direction: column;
    gap: 12px;
    max-width: 400px;
    width: 100%;
    pointer-events: none;
}

/* ─── Toast Base ─────────────────────────────────────────── */
.toast {
    pointer-events: auto;
    position: relative;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 16px 16px 20px;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(0,0,0,.12), 0 2px 8px rgba(0,0,0,.08);
    animation: toastSlideIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
    transition: opacity .3s, transform .3s;
    backdrop-filter: blur(12px);
    background: rgba(255,255,255,0.97);
    border: 1px solid rgba(0,0,0,.06);
}

[data-theme="dark"] .toast {
    background: rgba(15, 23, 42, 0.97);
    border-color: rgba(255,255,255,.08);
    box-shadow: 0 8px 32px rgba(0,0,0,.4);
}

.toast.dismissing {
    animation: toastSlideOut .35s ease forwards;
}

/* ─── Toast Types ─────────────────────────────────────────── */
.toast-success { border-left: 4px solid #10B981; }
.toast-danger  { border-left: 4px solid #EF4444; }
.toast-warning { border-left: 4px solid #F59E0B; }
.toast-info    { border-left: 4px solid #3B82F6; }

/* ─── Toast Icon ─────────────────────────────────────────── */
.toast-icon {
    font-size: 22px;
    flex-shrink: 0;
    margin-top: 1px;
}
.toast-success .toast-icon { color: #10B981; }
.toast-danger  .toast-icon { color: #EF4444; }
.toast-warning .toast-icon { color: #F59E0B; }
.toast-info    .toast-icon { color: #3B82F6; }

/* ─── Toast Body ─────────────────────────────────────────── */
.toast-body { flex: 1; min-width: 0; }

.toast-title {
    font-weight: 700;
    font-size: 13px;
    letter-spacing: .3px;
    color: #0F172A;
    margin-bottom: 3px;
}
[data-theme="dark"] .toast-title { color: #F1F5F9; }

.toast-message {
    font-size: 13px;
    color: #475569;
    line-height: 1.45;
    word-break: break-word;
}
[data-theme="dark"] .toast-message { color: #94A3B8; }

/* ─── Toast Close ─────────────────────────────────────────── */
.toast-close {
    flex-shrink: 0;
    background: none;
    border: none;
    cursor: pointer;
    color: #94A3B8;
    font-size: 13px;
    padding: 2px;
    line-height: 1;
    transition: color .2s;
    margin-top: 1px;
}
.toast-close:hover { color: #475569; }
[data-theme="dark"] .toast-close:hover { color: #CBD5E1; }

/* ─── Progress Bar ─────────────────────────────────────────── */
.toast-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 3px;
    border-radius: 0 0 14px 14px;
    width: 100%;
    transform-origin: left;
    animation: toastProgress 5s linear forwards;
}
.success-progress { background: #10B981; }
.danger-progress  { background: #EF4444; }
.warning-progress { background: #F59E0B; }
.info-progress    { background: #3B82F6; }

/* ─── Keyframes ─────────────────────────────────────────── */
@keyframes toastSlideIn {
    from { opacity: 0; transform: translateX(110%); }
    to   { opacity: 1; transform: translateX(0); }
}
@keyframes toastSlideOut {
    to   { opacity: 0; transform: translateX(120%); }
}
@keyframes toastProgress {
    from { transform: scaleX(1); }
    to   { transform: scaleX(0); }
}
</style>

<script>
function dismissToast(el) {
    if (!el) return;
    el.classList.add('dismissing');
    setTimeout(() => el.remove(), 350);
}

document.querySelectorAll('.toast').forEach(toast => {
    setTimeout(() => dismissToast(toast), 5000);
});
</script>
@endif
