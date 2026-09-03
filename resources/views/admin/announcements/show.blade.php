@extends('admin.layouts.app')

@section('title', 'Detail Pengumuman & Broadcast')
@section('page-title', 'Detail Pengumuman & Broadcast')
@section('breadcrumb')
<span class="breadcrumb-sep">›</span>
<a href="{{ route('admin.announcements.index') }}" style="color: var(--text-muted); text-decoration: none;">Pengumuman</a>
<span class="breadcrumb-sep">›</span>
<span>Detail</span>
@endsection

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Top Action Bar -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div>
            <h2 style="font-size: 20px; font-weight: 800; color: var(--text-primary, #0f172a); margin: 0 0 4px 0; display: flex; align-items: center; gap: 8px;">
                <iconify-icon icon="flat-color-icons:speaker" style="font-size: 26px;"></iconify-icon>
                {{ $announcement->title }}
            </h2>
            <div style="font-size: 13px; color: var(--text-muted, #64748b); display: flex; align-items: center; gap: 6px;">
                <iconify-icon icon="flat-color-icons:calendar" style="font-size: 15px;"></iconify-icon>
                Dikirim pada {{ $announcement->created_at->format('d F Y, H:i:s') }} ({{ $announcement->created_at->diffForHumans() }})
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 10px;">
            <a href="{{ route('admin.announcements.index') }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; font-size: 13px; font-weight: 700;">
                <iconify-icon icon="flat-color-icons:previous" style="font-size: 16px;"></iconify-icon> Kembali
            </a>
            <button type="button" class="btn btn-danger"
                data-url="{{ route('admin.announcements.destroy', $announcement) }}"
                data-name="{{ $announcement->title }}"
                onclick="confirmDelete(this.dataset.url, this.dataset.name)"
                style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; font-size: 13px; font-weight: 700;">
                <iconify-icon icon="fluent-emoji-flat:wastebasket" style="font-size: 16px;"></iconify-icon> Hapus Broadcast
            </button>
        </div>
    </div>

    <!-- Stats Metric Cards -->
    @php
        $totalRecipients = $announcement->user_notifications_count ?? 0;
        $readCount = $announcement->read_count ?? 0;
        $unreadCount = $announcement->unread_count ?? 0;
        $readRate = $totalRecipients > 0 ? round(($readCount / $totalRecipients) * 100, 1) : 0;
    @endphp
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: #eff6ff;"><iconify-icon icon="flat-color-icons:conference-call" style="font-size: 26px;"></iconify-icon></div>
            <div class="stat-info">
                <div class="stat-value">{{ number_format($totalRecipients) }}</div>
                <div class="stat-label">Total Penerima Inbox</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #f0fdf4;"><iconify-icon icon="flat-color-icons:ok" style="font-size: 26px;"></iconify-icon></div>
            <div class="stat-info">
                <div class="stat-value" style="color: #16a34a;">{{ number_format($readCount) }}</div>
                <div class="stat-label">Sudah Dibaca User</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #fffbeb;"><iconify-icon icon="flat-color-icons:clock" style="font-size: 26px;"></iconify-icon></div>
            <div class="stat-info">
                <div class="stat-value" style="color: #d97706;">{{ number_format($unreadCount) }}</div>
                <div class="stat-label">Belum Dibaca</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #faf5ff;"><iconify-icon icon="flat-color-icons:bullish" style="font-size: 26px;"></iconify-icon></div>
            <div class="stat-info">
                <div class="stat-value" style="color: #9333ea;">{{ $readRate }}%</div>
                <div class="stat-label">Tingkat Keterbacaan</div>
            </div>
        </div>
    </div>

    <!-- Main Content Details (2 Columns) -->
    <div style="display: grid; grid-template-columns: 1.6fr 1fr; gap: 24px; align-items: start;">
        
        <!-- Left: Pesan & Preview -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            
            <!-- Card Detail Broadcast -->
            <div class="card" style="padding: 24px; border-radius: var(--radius-md, 14px);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1px solid var(--border, #e2e8f0);">
                    <div style="font-size: 15px; font-weight: 800; color: var(--text-primary, #0f172a); display: flex; align-items: center; gap: 8px;">
                        <iconify-icon icon="flat-color-icons:document" style="font-size: 20px;"></iconify-icon>
                        Isi Konten Pengumuman
                    </div>
                    @if($announcement->type === 'promo')
                        <span class="badge-type badge-promo" style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; font-size: 12px;">
                            <iconify-icon icon="flat-color-icons:tag" style="font-size: 14px;"></iconify-icon> Promo & Diskon
                        </span>
                    @elseif($announcement->type === 'system')
                        <span class="badge-type badge-system" style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; font-size: 12px;">
                            <iconify-icon icon="flat-color-icons:settings" style="font-size: 14px;"></iconify-icon> Notifikasi Sistem
                        </span>
                    @else
                        <span class="badge-type badge-info" style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; font-size: 12px;">
                            <iconify-icon icon="flat-color-icons:speaker" style="font-size: 14px;"></iconify-icon> Informasi Umum
                        </span>
                    @endif
                </div>

                <div style="margin-bottom: 20px;">
                    <div style="font-size: 11.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted, #64748b); margin-bottom: 6px;">Judul</div>
                    <div style="font-size: 16px; font-weight: 800; color: var(--text-primary, #0f172a); line-height: 1.4;">
                        {{ $announcement->title }}
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <div style="font-size: 11.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted, #64748b); margin-bottom: 6px;">Isi Pesan</div>
                    <div style="font-size: 14px; color: var(--text-secondary, #334155); line-height: 1.7; background: var(--bg-input, #f8fafc); padding: 16px 18px; border-radius: 10px; border: 1px solid var(--border, #e2e8f0); white-space: pre-line;">
                        {{ $announcement->content }}
                    </div>
                </div>

                @if($announcement->action_url)
                <div style="margin-top: 10px;">
                    <div style="font-size: 11.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted, #64748b); margin-bottom: 6px;">Action Link URL</div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <a href="{{ $announcement->action_url }}" target="_blank" rel="noopener noreferrer" style="font-size: 13px; font-weight: 600; color: var(--primary, #3b82f6); text-decoration: underline; display: inline-flex; align-items: center; gap: 6px; word-break: break-all;">
                            <iconify-icon icon="lucide:external-link" style="font-size: 15px;"></iconify-icon>
                            {{ $announcement->action_url }}
                        </a>
                    </div>
                </div>
                @endif
            </div>

            <!-- Visual Preview Mobile Flutter Card -->
            <div class="card" style="padding: 24px; border-radius: var(--radius-md, 14px);">
                <div style="font-size: 14px; font-weight: 800; color: var(--text-primary, #0f172a); margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
                    <iconify-icon icon="flat-color-icons:android-os" style="font-size: 20px;"></iconify-icon>
                    Simulasi Tampilan di Aplikasi Mobile Flutter
                </div>

                <div style="background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%); padding: 18px; border-radius: 16px; color: white; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1);">
                    <div style="display: flex; align-items: flex-start; gap: 12px;">
                        <div style="width: 42px; height: 42px; border-radius: 12px; background: rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            @if($announcement->type === 'promo')
                                <iconify-icon icon="flat-color-icons:tag" style="font-size: 24px;"></iconify-icon>
                            @elseif($announcement->type === 'system')
                                <iconify-icon icon="flat-color-icons:settings" style="font-size: 24px;"></iconify-icon>
                            @else
                                <iconify-icon icon="flat-color-icons:speaker" style="font-size: 24px;"></iconify-icon>
                            @endif
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                <div style="font-weight: 700; font-size: 13.5px; color: #ffffff;">{{ $announcement->title }}</div>
                                <span style="font-size: 10.5px; color: rgba(255,255,255,0.5);">Baru saja</span>
                            </div>
                            <div style="font-size: 12px; color: rgba(255,255,255,0.8); line-height: 1.4; margin-bottom: 8px;">
                                {{ \Illuminate\Support\Str::limit($announcement->content, 140) }}
                            </div>
                            @if($announcement->action_url)
                            <div style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; color: #38bdf8; font-weight: 700;">
                                Buka Tautan <iconify-icon icon="lucide:arrow-right" style="font-size: 12px;"></iconify-icon>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right: Metadata & Info Box -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            
            <div class="card" style="padding: 24px; border-radius: var(--radius-md, 14px);">
                <div style="font-size: 14.5px; font-weight: 800; color: var(--text-primary, #0f172a); margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid var(--border, #e2e8f0); display: flex; align-items: center; gap: 8px;">
                    <iconify-icon icon="flat-color-icons:info" style="font-size: 18px;"></iconify-icon>
                    Informasi Broadcast
                </div>

                <div style="display: flex; flex-direction: column; gap: 14px; font-size: 13px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: var(--text-muted, #64748b);">Tipe Pengumuman</span>
                        <span style="font-weight: 700; color: var(--text-primary, #0f172a); text-transform: capitalize;">{{ $announcement->type }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: var(--text-muted, #64748b);">Target Broadcast</span>
                        <span style="font-weight: 700; color: var(--text-primary, #0f172a);">Semua Pengguna Aktif</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: var(--text-muted, #64748b);">Pusat Notifikasi (Inbox)</span>
                        <span style="font-weight: 700; color: #16a34a; display: inline-flex; align-items: center; gap: 4px;">
                            <iconify-icon icon="flat-color-icons:ok" style="font-size: 14px;"></iconify-icon> Terkirim
                        </span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: var(--text-muted, #64748b);">Push Notification FCM</span>
                        <span style="font-weight: 700; color: var(--text-primary, #0f172a); display: inline-flex; align-items: center; gap: 4px;">
                            <iconify-icon icon="flat-color-icons:smartphone-tablet" style="font-size: 14px;"></iconify-icon> Dipicu
                        </span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: var(--text-muted, #64748b);">Dibuat Pada</span>
                        <span style="font-weight: 600; color: var(--text-primary, #0f172a);">{{ $announcement->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>

            <!-- Info Alert Card -->
            <div style="background: rgba(59, 130, 246, 0.08); border: 1px solid rgba(59, 130, 246, 0.2); border-radius: 12px; padding: 16px;">
                <div style="font-size: 12.5px; color: var(--text-secondary, #334155); line-height: 1.5; display: flex; gap: 10px;">
                    <iconify-icon icon="flat-color-icons:idea" style="font-size: 24px; flex-shrink: 0;"></iconify-icon>
                    <div>
                        <strong>Tips Analitik:</strong> Tingkat keterbacaan (Read Rate) bertambah secara otomatis setiap kali customer membuka menu notifikasi di aplikasi mobile.
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- Bottom: Table of Recipients -->
    <div class="table-card" style="margin-top: 10px;">
        <div class="table-card-header">
            <div style="font-weight: 800; font-size: 16px; color: #0f172a; display: flex; align-items: center; gap: 10px;">
                <iconify-icon icon="flat-color-icons:list" style="font-size: 22px;"></iconify-icon>
                Daftar Penerima Notifikasi Inbox ({{ number_format($totalRecipients) }} Pengguna)
            </div>
        </div>

        <div style="padding: 0; overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">
                        <th style="padding: 14px 20px;">Nama Pengguna</th>
                        <th style="padding: 14px 20px;">Email</th>
                        <th style="padding: 14px 20px;">Role</th>
                        <th style="padding: 14px 20px;">Status Keterbacaan</th>
                        <th style="padding: 14px 20px; text-align: right;">Waktu Dibaca</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recipients as $recipient)
                    <tr class="table-row-custom" style="border-bottom: 1px solid #f1f5f9; font-size: 13px;">
                        <td style="padding: 14px 20px; font-weight: 700; color: #0f172a;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: #e0e7ff; color: #3730a3; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12px;">
                                    {{ strtoupper(substr($recipient->user?->name ?? 'U', 0, 1)) }}
                                </div>
                                <div>{{ $recipient->user?->name ?? 'User Terhapus' }}</div>
                            </div>
                        </td>
                        <td style="padding: 14px 20px; color: #64748b;">
                            {{ $recipient->user?->email ?? '—' }}
                        </td>
                        <td style="padding: 14px 20px;">
                            <span class="badge-type {{ ($recipient->user?->role ?? '') === 'admin' ? 'badge-promo' : 'badge-info' }}" style="font-size: 11px;">
                                {{ ucfirst($recipient->user?->role ?? 'customer') }}
                            </span>
                        </td>
                        <td style="padding: 14px 20px;">
                            @if($recipient->read_at)
                                <span style="background: #f0fdf4; color: #15803d; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 11.5px; display: inline-flex; align-items: center; gap: 4px;">
                                    <iconify-icon icon="flat-color-icons:ok" style="font-size: 13px;"></iconify-icon> Sudah Dibaca
                                </span>
                            @else
                                <span style="background: #fffbeb; color: #b45309; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 11.5px; display: inline-flex; align-items: center; gap: 4px;">
                                    <iconify-icon icon="flat-color-icons:clock" style="font-size: 13px;"></iconify-icon> Belum Dibaca
                                </span>
                            @endif
                        </td>
                        <td style="padding: 14px 20px; text-align: right; color: #64748b; font-size: 12.5px;">
                            @if($recipient->read_at)
                                {{ $recipient->read_at->format('d M Y, H:i') }}
                            @else
                                <span style="color: #94a3b8;">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 36px 20px; color: #94a3b8;">
                            Belum ada log penerima untuk pengumuman ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($recipients->hasPages())
        <div style="padding: 18px 20px; border-top: 1px solid #f1f5f9;">
            {{ $recipients->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
