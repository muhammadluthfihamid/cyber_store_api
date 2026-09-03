@extends('admin.layouts.app')

@section('title', 'Kelola Banner')
@section('page-title', 'Kelola Banner')
@section('breadcrumb')
    <span class="breadcrumb-sep">›</span>
    <span>Banner</span>
@endsection

@section('content')
    <div class="card">

        {{-- ── Header ─────────────────────────────────────────────── --}}
        <div class="card-header">
            <span class="card-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <rect width="20" height="14" x="2" y="5" rx="2"/>
                    <line x1="2" x2="22" y1="10" y2="10"/>
                </svg>
                Daftar Banner
            </span>
            <a href="{{ route('admin.banners.create') }}" class="btn btn-primary">
                ＋ Tambah Banner
            </a>
        </div>

        {{-- ── Info strip ──────────────────────────────────────────── --}}
        <div style="padding:12px 20px; border-bottom:1px solid var(--border);
                    background:var(--accent-light); display:flex; align-items:center; gap:8px;
                    font-size:13px; color:var(--accent);">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>
            </svg>
            Banner ditampilkan di aplikasi sesuai urutan (order). Semakin kecil angka, semakin awal tampil.
        </div>


        {{-- ── Table ──────────────────────────────────────────────── --}}

        @if ($banners->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon">🖼️</div>
                <h3>Belum ada banner</h3>
                <p>Tambahkan banner pertama untuk ditampilkan di halaman utama aplikasi.</p>
                <a href="{{ route('admin.banners.create') }}" class="btn btn-primary" style="margin-top:12px;">
                    ＋ Tambah Banner
                </a>
            </div>
        @else
            <!-- Desktop Table View (>768px) -->
            <div class="table-wrapper desktop-table-container">
                <table>
                    <thead>
                        <tr>
                            <th style="width:44px;">No.</th>
                            <th style="width:200px;">Preview</th>
                            <th>Judul &amp; Deskripsi</th>
                            <th style="width:80px; text-align:center;">Urutan</th>
                            <th style="width:120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($banners as $banner)
                            <tr>
                                <td style="color:var(--text-muted); font-size:12px; text-align:center;">
                                    {{ $loop->iteration + ($banners->currentPage() - 1) * $banners->perPage() }}
                                </td>
                                <td>
                                    <div style="width:180px; height:68px; border-radius:8px;
                                                overflow:hidden; background:var(--bg-input);
                                                border:1px solid var(--border);">
                                        @if ($banner->image_path)
                                            <img src="{{ Storage::disk('public')->url($banner->image_path) }}"
                                                alt="{{ $banner->title ?? 'Banner' }}"
                                                style="width:100%; height:100%; object-fit:cover;">
                                        @else
                                            <div style="width:100%; height:100%; display:flex;
                                                        align-items:center; justify-content:center;
                                                        font-size:24px;">🖼️</div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight:600; color:var(--text-primary); margin-bottom:4px;">
                                        {{ $banner->title ?? '—' }}
                                    </div>
                                    @if ($banner->description)
                                        <div style="font-size:12px; color:var(--text-muted); line-height:1.4;">
                                            {{ \Illuminate\Support\Str::limit($banner->description, 80) }}
                                        </div>
                                    @else
                                        <div style="font-size:12px; color:var(--text-muted);">Tidak ada deskripsi</div>
                                    @endif
                                </td>
                                <td style="text-align:center;">
                                    <span style="display:inline-flex; align-items:center; justify-content:center;
                                                 width:32px; height:32px; border-radius:50%;
                                                 background:var(--accent-light); color:var(--accent);
                                                 font-weight:700; font-size:14px;">
                                        {{ $banner->order ?? 0 }}
                                    </span>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="{{ route('admin.banners.edit', $banner) }}"
                                            class="btn btn-secondary btn-sm btn-icon" title="Edit">
                                            <iconify-icon icon="flat-color-icons:edit-image" style="font-size: 16px;"></iconify-icon>
                                        </a>

                                        <button type="button"
                                            class="btn btn-danger btn-sm btn-icon"
                                            title="Hapus"
                                            data-url="{{ route('admin.banners.destroy', $banner) }}"
                                            data-name="{{ $banner->title ?? 'banner ini' }}"
                                            onclick="confirmDelete(this.dataset.url, this.dataset.name)">
                                            <iconify-icon icon="fluent-emoji-flat:wastebasket" style="font-size: 16px;"></iconify-icon>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Banner Card View (<=768px) -->
            <div class="mobile-banner-grid">
                @foreach ($banners as $banner)
                <div class="mobile-banner-card">
                    <div style="width: 100%; height: 120px; border-radius: 10px; overflow: hidden; background: var(--bg-input); border: 1px solid var(--border); margin-bottom: 12px; display: flex; align-items: center; justify-content: center;">
                        @if ($banner->image_path)
                            <img src="{{ Storage::disk('public')->url($banner->image_path) }}" alt="{{ $banner->title ?? 'Banner' }}" style="width:100%; height:100%; object-fit:cover;">
                        @else
                            <span style="font-size: 32px;">🖼️</span>
                        @endif
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <div style="font-weight: 800; font-size: 14.5px; color: var(--text-primary);">
                            {{ $banner->title ?? 'Tanpa Judul' }}
                        </div>
                        <span style="font-size: 12px; padding: 2px 8px; background: var(--accent-light); color: var(--accent); border-radius: 12px; font-weight: 700;">
                            Urutan #{{ $banner->order ?? 0 }}
                        </span>
                    </div>

                    @if ($banner->description)
                    <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 12px; line-height: 1.4;">
                        {{ \Illuminate\Support\Str::limit($banner->description, 90) }}
                    </div>
                    @endif

                    <div style="display: flex; justify-content: flex-end; gap: 6px;">
                        <a href="{{ route('admin.banners.edit', $banner) }}" class="btn btn-secondary btn-sm btn-icon" title="Edit">
                            <iconify-icon icon="flat-color-icons:edit-image" style="font-size: 16px;"></iconify-icon>
                        </a>
                        <button type="button" class="btn btn-danger btn-sm btn-icon" data-url="{{ route('admin.banners.destroy', $banner) }}" data-name="{{ $banner->title ?? 'banner ini' }}" onclick="confirmDelete(this.dataset.url, this.dataset.name)">
                            <iconify-icon icon="fluent-emoji-flat:wastebasket" style="font-size: 16px;"></iconify-icon>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        @endif

        {{-- ── Pagination ──────────────────────────────────────────── --}}
        @if ($banners->hasPages())
            <div class="pagination-wrap">
                <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
                    <span style="font-size:13px; color:var(--text-muted);">
                        Menampilkan {{ $banners->firstItem() }}–{{ $banners->lastItem() }} dari
                        {{ $banners->total() }} banner
                    </span>
                    {{ $banners->links('admin.partials.pagination') }}
                </div>
            </div>
        @endif
    </div>
@endsection
