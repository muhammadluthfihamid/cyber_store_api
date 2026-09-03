{{-- ═══════════════════════════════════════════════════════════════
     Modal: Konfirmasi HAPUS (dipakai oleh confirmDelete())
═══════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="confirmModal">
    <div class="modal-box">
        <div class="modal-icon-wrap danger-icon">
            <iconify-icon icon="lucide:trash-2" style="font-size: 32px;"></iconify-icon>
        </div>
        <div class="modal-title">Konfirmasi Hapus</div>
        <div class="modal-body" id="confirmModalBody">Apakah Anda yakin ingin menghapus item ini? Tindakan ini tidak dapat dibatalkan.</div>
        <div class="modal-actions">
            <button class="btn btn-secondary" onclick="closeConfirm()">
                <iconify-icon icon="lucide:x" style="font-size: 16px;"></iconify-icon> Batal
            </button>
            <form id="confirmForm" method="POST">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="showLoading('Menghapus data…')">
                    <iconify-icon icon="lucide:trash-2" style="font-size: 16px;"></iconify-icon> Ya, Hapus
                </button>
            </form>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     Modal: Konfirmasi SIMPAN/TAMBAH BARU (dipakai oleh confirmCreate())
═══════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="createConfirmModal">
    <div class="modal-box">
        <div class="modal-icon-wrap success-icon">
            <iconify-icon icon="lucide:plus-circle" style="font-size: 32px;"></iconify-icon>
        </div>
        <div class="modal-title" id="createConfirmTitle">Konfirmasi Simpan Data</div>
        <div class="modal-body" id="createConfirmBody">Apakah Anda yakin ingin menyimpan data baru ini?</div>
        <div class="modal-actions">
            <button class="btn btn-secondary" onclick="closeCreateConfirm()">
                <iconify-icon icon="lucide:x" style="font-size: 16px;"></iconify-icon> Batal
            </button>
            <button type="button" class="btn btn-primary" id="createConfirmBtn" onclick="doFormSubmit('createConfirmModal')">
                <iconify-icon icon="lucide:check" style="font-size: 16px;"></iconify-icon> Ya, Simpan
            </button>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     Modal: Konfirmasi UPDATE/PERBARUI (dipakai oleh confirmUpdate())
═══════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="updateConfirmModal">
    <div class="modal-box">
        <div class="modal-icon-wrap info-icon">
            <iconify-icon icon="lucide:edit-3" style="font-size: 32px;"></iconify-icon>
        </div>
        <div class="modal-title" id="updateConfirmTitle">Konfirmasi Perubahan</div>
        <div class="modal-body" id="updateConfirmBody">Apakah Anda yakin ingin menyimpan perubahan ini?</div>
        <div class="modal-actions">
            <button class="btn btn-secondary" onclick="closeUpdateConfirm()">
                <iconify-icon icon="lucide:x" style="font-size: 16px;"></iconify-icon> Batal
            </button>
            <button type="button" class="btn btn-primary" id="updateConfirmBtn" onclick="doFormSubmit('updateConfirmModal')">
                <iconify-icon icon="lucide:check" style="font-size: 16px;"></iconify-icon> Ya, Perbarui
            </button>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     Modal: Konfirmasi LOGOUT
═══════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="logoutModal">
    <div class="modal-box">
        <div class="modal-icon-wrap danger-icon">
            <iconify-icon icon="lucide:log-out" style="font-size: 32px;"></iconify-icon>
        </div>
        <div class="modal-title">Konfirmasi Keluar</div>
        <div class="modal-body">Apakah Anda yakin ingin keluar dari Admin Panel?</div>
        <div class="modal-actions">
            <button class="btn btn-secondary" onclick="closeLogoutModal()">
                <iconify-icon icon="lucide:x" style="font-size: 16px;"></iconify-icon> Batal
            </button>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="btn btn-danger" onclick="showLoading('Keluar dari sistem…')">
                    <iconify-icon icon="lucide:log-out" style="font-size: 16px;"></iconify-icon> Ya, Keluar
                </button>
            </form>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     Loading Overlay Global
═══════════════════════════════════════════════════════════════ --}}
<div id="globalLoadingOverlay" style="
    display:none; position:fixed; inset:0; z-index:100000;
    background:rgba(15,23,42,.55); backdrop-filter:blur(5px);
    align-items:center; justify-content:center; flex-direction:column; gap:16px;">
    <div style="
        background:rgba(255,255,255,.97); border-radius:20px;
        padding:32px 40px; text-align:center;
        box-shadow:0 24px 64px rgba(0,0,0,.25);
        display:flex; flex-direction:column; align-items:center; gap:16px;">
        <div id="loadingSpinner" style="
            width:52px; height:52px; border:4px solid #E2E8F0;
            border-top-color:#3B82F6; border-radius:50%;
            animation: globalSpin .8s linear infinite;"></div>
        <p id="loadingText" style="margin:0; font-size:14px; color:#1E293B; font-weight:600;">Memproses, harap tunggu…</p>
    </div>
</div>

<style>
/* ─── Modal Overlay ──────────────────────────────────────── */
.modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 9998;
    background: rgba(0,0,0,.5);
    backdrop-filter: blur(5px);
    align-items: center;
    justify-content: center;
    padding: 20px;
    animation: none;
}
.modal-overlay.open {
    display: flex;
    animation: modalFadeIn .25s ease;
}

/* ─── Modal Box ──────────────────────────────────────────── */
.modal-box {
    background: var(--bg-card, #fff);
    border-radius: 20px;
    padding: 32px 28px;
    max-width: 440px;
    width: 100%;
    box-shadow: 0 24px 64px rgba(0,0,0,.18);
    animation: modalSlideUp .3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    text-align: center;
    border: 1px solid var(--border, rgba(0,0,0,.06));
}

/* ─── Modal Icon ──────────────────────────────────────────── */
.modal-icon-wrap {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 30px;
    margin-bottom: 4px;
}
.danger-icon  { background: #FEF2F2; color: #EF4444; }
.success-icon { background: #ECFDF5; color: #10B981; }
.info-icon    { background: #EFF6FF; color: #3B82F6; }
.warning-icon { background: #FFFBEB; color: #F59E0B; }

/* ─── Modal Title & Body ──────────────────────────────────── */
.modal-title {
    font-size: 18px;
    font-weight: 700;
    color: var(--text-primary, #0F172A);
    margin: 0;
}
.modal-body {
    font-size: 14px;
    color: var(--text-muted, #64748B);
    line-height: 1.6;
    margin: 0;
}

/* ─── Modal Actions ──────────────────────────────────────── */
.modal-actions {
    display: flex;
    gap: 12px;
    margin-top: 8px;
    width: 100%;
    justify-content: center;
}
.modal-actions .btn {
    flex: 1;
    justify-content: center;
    gap: 6px;
    display: flex;
    align-items: center;
}

/* ─── Keyframes ──────────────────────────────────────────── */
@keyframes modalFadeIn {
    from { opacity: 0; }
    to   { opacity: 1; }
}
@keyframes modalSlideUp {
    from { opacity: 0; transform: translateY(20px) scale(.97); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}
@keyframes globalSpin {
    to { transform: rotate(360deg); }
}
</style>
