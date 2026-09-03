<!-- ─── ADMIN PRELOADER & SYSTEM OVERLAY ─────────────────────────────────── -->
<div id="admin-page-loader" class="admin-loader-overlay">
    <div class="admin-loader-card">
        <div class="admin-loader-brand-wrapper">
            <div class="admin-loader-pulse-ring"></div>
            <div class="admin-loader-spinner-ring"></div>
            <div class="admin-loader-logo">
                @php
                $sidebarLogoSetting = \App\Models\Setting::get('store_logo');
                $sidebarLogoUrl = $sidebarLogoSetting ? \Storage::disk('public')->url($sidebarLogoSetting) : asset('/assets/img/logo-cyberstore.jpg');
                @endphp
                @if($sidebarLogoUrl)
                <img src="{{ $sidebarLogoUrl }}" alt="Logo" class="admin-loader-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';" />
                <iconify-icon icon="flat-color-icons:shop" style="font-size: 34px; display: none;"></iconify-icon>
                @else
                <iconify-icon icon="flat-color-icons:shop" style="font-size: 34px;"></iconify-icon>
                @endif
            </div>
        </div>

        <div class="admin-loader-content">
            <div class="admin-loader-title">{{ \App\Models\Setting::get('store_name', 'BSI Cyber Store') }}</div>
            <div class="admin-loader-badge">
                <span class="admin-loader-dot"></span> Admin Control Center
            </div>
            <div class="admin-loader-text" id="admin-loader-msg">Memuat Data & Sistem...</div>
        </div>

        <div class="admin-loader-progress-track">
            <div class="admin-loader-progress-bar" id="admin-loader-bar"></div>
        </div>
    </div>
</div>

<style>
    /* ── Executive Admin Loader Screen Styles ── */
    .admin-loader-overlay {
        position: fixed;
        inset: 0;
        z-index: 999999;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(11, 2, 62, 0.85);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        opacity: 1;
        visibility: visible;
        transition: opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1), visibility 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    [data-theme="dark"] .admin-loader-overlay {
        background: rgba(15, 23, 42, 0.88);
    }

    .admin-loader-overlay.fade-out {
        opacity: 0 !important;
        visibility: hidden !important;
        pointer-events: none !important;
    }

    .admin-loader-card {
        width: 320px;
        padding: 32px 24px 28px 24px;
        background: rgba(255, 255, 255, 0.95);
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.4);
        box-shadow: 0 25px 50px -12px rgba(11, 2, 62, 0.25), 0 0 0 1px rgba(11, 2, 62, 0.05);
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        position: relative;
        overflow: hidden;
        animation: adminLoaderPop 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    [data-theme="dark"] .admin-loader-card {
        background: rgba(30, 41, 59, 0.95);
        border-color: rgba(255, 255, 255, 0.1);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }

    @keyframes adminLoaderPop {
        0% {
            transform: scale(0.92);
            opacity: 0;
        }

        100% {
            transform: scale(1);
            opacity: 1;
        }
    }

    .admin-loader-brand-wrapper {
        position: relative;
        width: 80px;
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 18px;
    }

    .admin-loader-pulse-ring {
        position: absolute;
        inset: -6px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(37, 99, 235, 0.25) 0%, rgba(223, 11, 43, 0.15) 70%, transparent 100%);
        animation: adminPulse 2s infinite ease-in-out;
    }

    @keyframes adminPulse {

        0%,
        100% {
            transform: scale(0.95);
            opacity: 0.5;
        }

        50% {
            transform: scale(1.12);
            opacity: 0.9;
        }
    }

    .admin-loader-spinner-ring {
        position: absolute;
        inset: -2px;
        border-radius: 50%;
        border: 3px solid transparent;
        border-top-color: #DF0B2B;
        border-right-color: #2563eb;
        animation: adminSpin 1s linear infinite;
    }

    @keyframes adminSpin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    .admin-loader-logo {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: #ffffff;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2;
        overflow: hidden;
    }

    .admin-loader-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .admin-loader-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        width: 100%;
        margin-bottom: 20px;
    }

    .admin-loader-title {
        font-weight: 800;
        font-size: 16px;
        color: #0b023e;
        letter-spacing: -0.2px;
        line-height: 1.3;
    }

    [data-theme="dark"] .admin-loader-title {
        color: #f8fafc;
    }

    .admin-loader-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        font-weight: 700;
        color: #2563eb;
        background: rgba(37, 99, 235, 0.1);
        padding: 3px 10px;
        border-radius: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 2px;
    }

    .admin-loader-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #10b981;
        box-shadow: 0 0 6px #10b981;
        animation: adminBlink 1.2s infinite;
    }

    @keyframes adminBlink {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.3;
        }
    }

    .admin-loader-text {
        font-size: 12.5px;
        color: #64748b;
        font-weight: 500;
        margin-top: 8px;
    }

    [data-theme="dark"] .admin-loader-text {
        color: #94a3b8;
    }

    .admin-loader-progress-track {
        width: 100%;
        height: 5px;
        background: rgba(0, 0, 0, 0.06);
        border-radius: 99px;
        overflow: hidden;
        position: relative;
    }

    [data-theme="dark"] .admin-loader-progress-track {
        background: rgba(255, 255, 255, 0.08);
    }

    .admin-loader-progress-bar {
        height: 100%;
        width: 40%;
        background: linear-gradient(90deg, #DF0B2B 0%, #2563eb 100%);
        border-radius: 99px;
        position: absolute;
        left: 0;
        animation: adminProgressIndeterminate 1.5s infinite ease-in-out;
    }

    @keyframes adminProgressIndeterminate {
        0% {
            left: -35%;
            width: 35%;
        }

        50% {
            left: 35%;
            width: 50%;
        }

        100% {
            left: 100%;
            width: 25%;
        }
    }
</style>

<!-- Floating Error Toast Notification Container -->
<div id="admin-error-toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999999; display: flex; flex-direction: column; gap: 10px; max-width: 380px; width: calc(100% - 40px); pointer-events: none;"></div>

<script>
    (function() {
        const loader = document.getElementById('admin-page-loader');
        const loaderMsg = document.getElementById('admin-loader-msg');

        // Global functions to control loading overlay
        window.showAdminLoading = function(message) {
            if (loaderMsg && message) {
                loaderMsg.textContent = message;
            }
            if (loader) {
                loader.classList.remove('fade-out');
            }
        };

        window.hideAdminLoading = function() {
            if (loader) {
                loader.classList.add('fade-out');
            }
        };

        // Global Error Toast Notification Helper
        window.showAdminError = function(title, message) {
            window.hideAdminLoading();
            const container = document.getElementById('admin-error-toast-container');
            if (!container) return;

            // Clear existing toast so alerts never stack or double up
            container.innerHTML = '';

            const toast = document.createElement('div');
            toast.style.cssText = 'pointer-events: auto; background: #ffffff; color: #1e293b; border: 1px solid #fecaca; border-left: 5px solid #ef4444; border-radius: 12px; padding: 14px 16px; box-shadow: 0 10px 25px rgba(239, 68, 68, 0.15); display: flex; align-items: flex-start; gap: 12px; font-family: Inter, sans-serif; transition: all 0.3s ease;';

            toast.innerHTML = `
                <div style="color: #ef4444; font-size: 22px; flex-shrink: 0; display: flex; align-items: center;">
                    <iconify-icon icon="flat-color-icons:error"></iconify-icon>
                </div>
                <div style="flex: 1; min-width: 0;">
                    <div style="font-weight: 700; font-size: 13.5px; color: #0f172a; margin-bottom: 2px;">${title || 'Terjadi Kesalahan'}</div>
                    <div style="font-size: 12px; color: #64748b; line-height: 1.4; word-break: break-word;">${message || 'Terjadi kendala saat memproses data. Silakan periksa kembali.'}</div>
                </div>
                <button type="button" style="background: none; border: none; color: #94a3b8; font-size: 16px; cursor: pointer; padding: 0; display: flex;" onclick="this.parentElement.remove()">&times;</button>
            `;

            container.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-10px)';
                setTimeout(() => toast.remove(), 300);
            }, 6000);
        };

        // Automatically hide loader when DOM and resources are loaded
        window.addEventListener('load', function() {
            setTimeout(window.hideAdminLoading, 150);
        });

        // Safety fallback: Hide loader after max 5 seconds if window.onload delayed
        setTimeout(function() {
            if (loader && !loader.classList.contains('fade-out')) {
                window.hideAdminLoading();
            }
        }, 5000);

        // Show loader on form submit (unless form has data-no-loader)
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (form && !form.hasAttribute('data-no-loader')) {
                window.showAdminLoading('Memproses Data...');
            }
        });

        // Automatic error handling for form validation / invalid input events
        document.addEventListener('invalid', function(e) {
            window.hideAdminLoading();
            const input = e.target;
            if (input && input.validationMessage) {
                window.showAdminError('Validasi Form Gagal', `Harap periksa kolom "${input.name || 'input'}": ${input.validationMessage}`);
            }
        }, true);

        // Catch unhandled errors or rejection events on page to prevent infinite loading
        window.addEventListener('error', function(e) {
            window.hideAdminLoading();
        });

        window.addEventListener('unhandledrejection', function(e) {
            window.hideAdminLoading();
        });
    })();
</script>