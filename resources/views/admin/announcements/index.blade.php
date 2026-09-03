@extends('admin.layouts.app')

@section('title', 'Pengumuman & Push Notification')
@section('page-title', 'Pengumuman & Push Notification')
@section('breadcrumb')
<span class="breadcrumb-sep">›</span>
<span>Pengumuman</span>
@endsection

@section('content')
<!-- Top Stats Summary -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background: #eff6ff;"><iconify-icon icon="flat-color-icons:speaker" style="font-size: 26px;"></iconify-icon></div>
        <div class="stat-info">
            <div class="stat-value">{{ $announcements->total() }}</div>
            <div class="stat-label">Total Broadcast Sent</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #fffbeb;"><iconify-icon icon="flat-color-icons:tag" style="font-size: 26px;"></iconify-icon></div>
        <div class="stat-info">
            <div class="stat-value">{{ $announcements->where('type', 'promo')->count() }}</div>
            <div class="stat-label">Promo & Diskon</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #fef2f2;"><iconify-icon icon="flat-color-icons:settings" style="font-size: 26px;"></iconify-icon></div>
        <div class="stat-info">
            <div class="stat-value">{{ $announcements->where('type', 'system')->count() }}</div>
            <div class="stat-label">Notifikasi Sistem</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #f0fdf4;"><iconify-icon icon="flat-color-icons:conference-call" style="font-size: 26px;"></iconify-icon></div>
        <div class="stat-info">
            <div class="stat-value">{{ $announcements->sum('user_notifications_count') }}</div>
            <div class="stat-label">Total Penerima Inbox</div>
        </div>
    </div>
</div>

<!-- Notice Banner -->
<div class="notice-banner">
    <div class="notice-icon">
        <iconify-icon icon="lucide:bell" style="font-size: 20px; color: #ffffff;"></iconify-icon>
    </div>
    <div style="font-size: 13px; color: #334155; line-height: 1.5;">
        <strong>Info Broadcast:</strong> Setiap pengumuman yang dikirim akan otomatis muncul di inbox notifikasi aplikasi Flutter pengguna dan memicu Push Notification ke perangkat yang terhubung.
    </div>
</div>

<!-- Main Table Card -->
<div class="table-card">
    <div class="table-card-header">
        <div style="font-weight: 800; font-size: 16.5px; color: #0f172a; display: flex; align-items: center; gap: 10px;">
            <iconify-icon icon="lucide:list" style="font-size: 22px; color: #0B023E;"></iconify-icon>
            Riwayat Broadcast Notifikasi
        </div>
        <button type="button" class="btn-create-broadcast" onclick="document.getElementById('modalCreateAnnouncement').style.display='flex'">
            <iconify-icon icon="lucide:plus-circle" style="font-size: 18px; color: #ffffff;"></iconify-icon>
            Buat Pengumuman Baru
        </button>
    </div>

    <!-- Desktop Table View (>768px) -->
    <div style="padding: 0; overflow-x: auto;" class="desktop-table-container">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; font-size: 12.5px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">
                    <th style="padding: 14px 20px;">Judul Broadcast</th>
                    <th style="padding: 14px 20px;">Isi Pesan</th>
                    <th style="padding: 14px 20px;">Kategori</th>
                    <th style="padding: 14px 20px;">Jangkauan User</th>
                    <th style="padding: 14px 20px;">Waktu Kirim</th>
                    <th style="padding: 14px 20px; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($announcements as $announcement)
                <tr class="table-row-custom" style="border-bottom: 1px solid #f1f5f9; font-size: 13.5px;">
                    <td style="padding: 16px 20px; font-weight: 700; color: #0f172a;">
                        {{ $announcement->title }}
                    </td>
                    <td style="padding: 16px 20px; max-width: 320px; color: #475569; line-height: 1.4;">
                        {{ \Illuminate\Support\Str::limit($announcement->content, 90) }}
                    </td>
                    <td style="padding: 16px 20px;">
                        @if($announcement->type === 'promo')
                        <span class="badge-type badge-promo" style="display: inline-flex; align-items: center; gap: 4px;"><iconify-icon icon="flat-color-icons:tag" style="font-size: 14px;"></iconify-icon> Promo</span>
                        @elseif($announcement->type === 'system')
                        <span class="badge-type badge-system" style="display: inline-flex; align-items: center; gap: 4px;"><iconify-icon icon="flat-color-icons:settings" style="font-size: 14px;"></iconify-icon> Sistem</span>
                        @else
                        <span class="badge-type badge-info" style="display: inline-flex; align-items: center; gap: 4px;"><iconify-icon icon="flat-color-icons:speaker" style="font-size: 14px;"></iconify-icon> Informasi</span>
                        @endif
                    </td>
                    <td style="padding: 16px 20px;">
                        <span style="font-weight: 700; color: #0f172a; background: #f1f5f9; padding: 4px 10px; border-radius: 8px; font-size: 12px; display: inline-flex; align-items: center; gap: 4px;">
                            <iconify-icon icon="flat-color-icons:conference-call" style="font-size: 14px;"></iconify-icon> {{ number_format($announcement->user_notifications_count) }} User
                        </span>
                    </td>
                    <td style="padding: 16px 20px; color: #64748b; font-size: 12.5px;">
                        <span style="display: inline-flex; align-items: center; gap: 4px;"><iconify-icon icon="flat-color-icons:calendar" style="font-size: 14px;"></iconify-icon> {{ $announcement->created_at->format('d M Y, H:i') }}</span>
                    </td>
                    <td style="padding: 16px 20px; text-align: right;">
                        <div style="display: inline-flex; align-items: center; justify-content: flex-end; gap: 8px;">
                            <a href="{{ route('admin.announcements.show', $announcement) }}" class="btn btn-secondary btn-sm" title="Lihat Detail"
                                style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; font-size: 12px; font-weight: 700; text-decoration: none;">
                                <iconify-icon icon="flat-color-icons:view-details" style="font-size: 15px;"></iconify-icon> Detail
                            </a>
                            <button type="button" class="btn btn-danger btn-sm" title="Hapus"
                                data-url="{{ route('admin.announcements.destroy', $announcement) }}"
                                data-name="{{ $announcement->title }}"
                                onclick="confirmDelete(this.dataset.url, this.dataset.name)"
                                style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; font-size: 12px; font-weight: 700;">
                                <iconify-icon icon="fluent-emoji-flat:wastebasket" style="font-size: 14px;"></iconify-icon> Hapus
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 48px 24px; color: #94a3b8;">
                        <div style="margin-bottom: 12px; display: flex; justify-content: center;"><iconify-icon icon="flat-color-icons:speaker" style="font-size: 48px;"></iconify-icon></div>
                        <div style="font-size: 16px; font-weight: 700; color: #475569; margin-bottom: 4px;">Belum Ada Pengumuman</div>
                        <div style="font-size: 13px;">Klik tombol "Buat Pengumuman Baru" di atas untuk mengirimkan broadcast pertama Anda.</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Announcement Card View (<=768px) -->
    <div class="mobile-announcement-grid">
        @foreach($announcements as $announcement)
        <div class="mobile-announcement-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <div style="font-weight: 800; font-size: 14px; color: #0f172a; display: flex; align-items: center; gap: 6px;">
                    <iconify-icon icon="flat-color-icons:speaker" style="font-size: 16px;"></iconify-icon> {{ $announcement->title }}
                </div>
                @if($announcement->type === 'promo')
                <span class="badge-type badge-promo" style="display: inline-flex; align-items: center; gap: 4px;"><iconify-icon icon="flat-color-icons:tag" style="font-size: 13px;"></iconify-icon> Promo</span>
                @elseif($announcement->type === 'system')
                <span class="badge-type badge-system" style="display: inline-flex; align-items: center; gap: 4px;"><iconify-icon icon="flat-color-icons:settings" style="font-size: 13px;"></iconify-icon> Sistem</span>
                @else
                <span class="badge-type badge-info" style="display: inline-flex; align-items: center; gap: 4px;"><iconify-icon icon="flat-color-icons:speaker" style="font-size: 13px;"></iconify-icon> Info</span>
                @endif
            </div>

            <div style="font-size: 12.5px; color: #475569; margin-bottom: 12px; line-height: 1.4;">
                {{ \Illuminate\Support\Str::limit($announcement->content, 120) }}
            </div>

            <div style="background: var(--bg-input, #f8fafc); padding: 8px 12px; border-radius: 8px; font-size: 12px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center;">
                <div>Jangkauan: <strong style="display: inline-flex; align-items: center; gap: 4px;"><iconify-icon icon="flat-color-icons:conference-call" style="font-size: 13px;"></iconify-icon> {{ number_format($announcement->user_notifications_count) }} User</strong></div>
                <div style="display: inline-flex; align-items: center; gap: 4px;"><iconify-icon icon="flat-color-icons:calendar" style="font-size: 13px;"></iconify-icon> {{ $announcement->created_at->format('d M Y, H:i') }}</div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 8px;">
                <a href="{{ route('admin.announcements.show', $announcement) }}" class="btn btn-secondary btn-sm" title="Lihat Detail"
                    style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; font-size: 12px; font-weight: 700; text-decoration: none;">
                    <iconify-icon icon="flat-color-icons:view-details" style="font-size: 15px;"></iconify-icon> Detail
                </a>
                <button type="button" class="btn btn-danger btn-sm btn-icon" title="Hapus"
                    data-url="{{ route('admin.announcements.destroy', $announcement) }}"
                    data-name="{{ $announcement->title }}"
                    onclick="confirmDelete(this.dataset.url, this.dataset.name)">
                    <iconify-icon icon="fluent-emoji-flat:wastebasket" style="font-size: 16px;"></iconify-icon>
                </button>
            </div>
        </div>
        @endforeach
    </div>

    @if($announcements->hasPages())
    <div style="padding: 20px 24px; border-top: 1px solid #f1f5f9;">
        {{ $announcements->links() }}
    </div>
    @endif
</div>
</div>

{{-- Glassmorphic Modal Create Announcement --}}
<div id="modalCreateAnnouncement" class="modal-backdrop-custom">
    <div class="modal-content-custom">
        <div class="modal-header-custom">
            <div style="font-size: 17px; font-weight: 800; display: flex; align-items: center; gap: 10px;">
                <iconify-icon icon="flat-color-icons:speaker" style="font-size: 22px;"></iconify-icon> Buat Pengumuman Baru
            </div>
            <button type="button" onclick="document.getElementById('modalCreateAnnouncement').style.display='none'" style="background: rgba(255,255,255,0.15); border: none; color: white; width: 32px; height: 32px; border-radius: 50%; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                <iconify-icon icon="lucide:x" style="font-size: 18px; color: #ffffff;"></iconify-icon>
            </button>
        </div>
        <form action="{{ route('admin.announcements.store') }}" method="POST" style="padding: 24px;">
            @csrf
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Judul Pengumuman</label>
                <input type="text" name="title" required class="form-input-custom" placeholder="Contoh: Promo Flash Sale Ormik 30%">
            </div>
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Kategori Tipe</label>
                <select name="type" required class="form-input-custom">
                    <option value="info">Informasi Umum</option>
                    <option value="promo">Promo & Diskon Special</option>
                    <option value="system">Pemberitahuan Sistem</option>
                </select>
            </div>
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Isi Pesan Pengumuman</label>
                <textarea name="content" rows="4" required class="form-input-custom" placeholder="Tuliskan pesan lengkap yang akan tampil di aplikasi pengguna..."></textarea>
            </div>
            <div style="margin-bottom: 24px;">
                <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Action Link URL (Opsional)</label>
                <input type="url" name="action_url" class="form-input-custom" placeholder="https://cyberstore.co.id/promo">
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="document.getElementById('modalCreateAnnouncement').style.display='none'" style="padding: 10px 18px; background: #f1f5f9; color: #475569; border: none; border-radius: 10px; font-weight: 700; font-size: 13px; cursor: pointer;">
                    Batal
                </button>
                <button type="submit" class="btn-create-broadcast" style="display: inline-flex; align-items: center; gap: 6px;">
                    <iconify-icon icon="flat-color-icons:paper-plane" style="font-size: 18px;"></iconify-icon> Kirim Broadcast Sekarang
                </button>
            </div>
        </form>
    </div>
</div>
@endsection