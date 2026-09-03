@extends('admin.layouts.app')
@section('title','Kelola Ekspedisi')
@section('page-title','Ekspedisi')
@section('breadcrumb')<span class="breadcrumb-sep">›</span><span>Ekspedisi</span>@endsection
@section('content')
<div class="card">
    <div class="card-header">
        <span class="card-title" style="display: flex; align-items: center; gap: 8px;"><iconify-icon icon="flat-color-icons:shipped" style="font-size: 22px;"></iconify-icon> Daftar Ekspedisi</span>
        <a href="{{ route('admin.expeditions.create') }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;"><iconify-icon icon="flat-color-icons:plus" style="font-size: 18px;"></iconify-icon> Tambah Ekspedisi</a>
    </div>
    <div style="padding:12px 20px; background: rgba(59, 130, 246, 0.08); border-bottom:1px solid rgba(59, 130, 246, 0.2); color: #1d4ed8; font-size: 12.5px; display: flex; align-items: center; gap: 8px;">
        <iconify-icon icon="flat-color-icons:info" style="font-size: 18px; flex-shrink: 0;"></iconify-icon>
        <span>Tarif ongkir pada aplikasi <strong>dihitung otomatis secara real-time dari API RajaOngkir</strong> berdasarkan kota asal toko dan kota tujuan pembeli. "Biaya Dasar" di bawah hanya digunakan sebagai tarif cadangan (fallback).</span>
    </div>
    <div style="padding:14px 20px;border-bottom:1px solid var(--border);">
        <form method="GET" action="{{ route('admin.expeditions.index') }}" class="filter-bar">
            <div class="search-input-wrapper">
                <iconify-icon icon="flat-color-icons:search" class="search-icon" style="font-size: 16px;"></iconify-icon>
                <input type="text" name="search" class="form-control search-input" placeholder="Cari ekspedisi..." value="{{ request('search') }}">
            </div>
            <select name="status" class="form-control">
                <option value="">Semua Status</option>
                <option value="active"   {{ request('status')==='active'  ?'selected':'' }}>Aktif</option>
                <option value="inactive" {{ request('status')==='inactive'?'selected':'' }}>Nonaktif</option>
            </select>
            <button type="submit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;"><iconify-icon icon="flat-color-icons:filter" style="font-size: 16px;"></iconify-icon> Filter</button>
            @if(request()->hasAny(['search','status']))
                <a href="{{ route('admin.expeditions.index') }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px;"><iconify-icon icon="flat-color-icons:undo" style="font-size: 16px;"></iconify-icon> Reset</a>
            @endif
        </form>
    </div>
    @if($expeditions->isEmpty())
        <div class="empty-state"><div class="empty-state-icon"><iconify-icon icon="flat-color-icons:shipped" style="font-size: 48px;"></iconify-icon></div><h3>Belum ada ekspedisi</h3></div>
    @else
        <!-- Desktop Table View (>768px) -->
        <div class="table-wrapper desktop-table-container">
            <table>
                <thead>
                    <tr><th>#</th><th>Nama</th><th>Kode</th><th>Layanan</th><th>Biaya Dasar</th><th>Est. Hari</th><th>Order</th><th>Status</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                @foreach($expeditions as $exp)
                <tr>
                    <td style="color:var(--text-muted);font-size:12px;">{{ $exp->id }}</td>
                    <td style="font-weight:600;color:var(--text-primary);">{{ $exp->name }}</td>
                    <td><code style="font-size:12px;color:var(--accent);background:var(--accent-light);padding:2px 8px;border-radius:4px;">{{ $exp->code }}</code></td>
                    <td><span class="badge badge-info">{{ $exp->service }}</span></td>
                    <td style="font-weight:500;color:var(--text-primary);">Rp {{ number_format($exp->base_cost,0,',','.') }}</td>
                    <td>{{ $exp->estimated_days }} hari</td>
                    <td>
                        <span style="font-weight:600;color:var(--text-primary);">{{ $exp->orders_count }}</span>
                        <span style="font-size:11px;color:var(--text-muted);"> order</span>
                    </td>
                    <td>
                        <span class="badge {{ $exp->is_active?'badge-active':'badge-inactive' }}" style="display: inline-flex; align-items: center; gap: 4px;">
                            <iconify-icon icon="{{ $exp->is_active ? 'flat-color-icons:checkmark' : 'flat-color-icons:cancel' }}" style="font-size: 13px;"></iconify-icon>
                            {{ $exp->is_active?'Aktif':'Nonaktif' }}
                        </span>
                    </td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('admin.expeditions.edit',$exp) }}" class="btn btn-secondary btn-sm btn-icon" title="Edit">
                                <iconify-icon icon="flat-color-icons:edit-image" style="font-size: 16px;"></iconify-icon>
                            </a>
                            <form method="POST" action="{{ route('admin.expeditions.toggle',$exp) }}" style="display:inline;">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-icon {{ $exp->is_active?'btn-warning':'btn-success' }}" title="{{ $exp->is_active?'Nonaktifkan':'Aktifkan' }}">
                                    <iconify-icon icon="{{ $exp->is_active ? 'flat-color-icons:cancel' : 'flat-color-icons:ok' }}" style="font-size: 16px;"></iconify-icon>
                                </button>
                            </form>
                            <button type="button" class="btn btn-danger btn-sm btn-icon" title="Hapus"
                                data-url="{{ route('admin.expeditions.destroy',$exp) }}"
                                data-name="{{ $exp->name }}"
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

        <!-- Mobile Expedition Card View (<=768px) -->
        <div class="mobile-expedition-grid">
            @foreach($expeditions as $exp)
            <div class="mobile-expedition-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <div style="font-weight: 800; font-size: 15px; color: var(--text-primary); display: flex; align-items: center; gap: 6px;">
                        <iconify-icon icon="flat-color-icons:shipped" style="font-size: 18px;"></iconify-icon> {{ $exp->name }}
                    </div>
                    <span class="badge {{ $exp->is_active ? 'badge-active' : 'badge-inactive' }}" style="display: inline-flex; align-items: center; gap: 4px;">
                        <iconify-icon icon="{{ $exp->is_active ? 'flat-color-icons:checkmark' : 'flat-color-icons:cancel' }}" style="font-size: 13px;"></iconify-icon>
                        {{ $exp->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>

                <div style="font-size: 12px; margin-bottom: 10px; display: flex; gap: 8px; align-items: center;">
                    <code style="color: var(--accent); background: var(--accent-light); padding: 2px 8px; border-radius: 4px;">{{ $exp->code }}</code>
                    <span class="badge badge-info">{{ $exp->service }}</span>
                </div>

                <div style="background: var(--bg-input, #f8fafc); padding: 10px 12px; border-radius: 8px; font-size: 12px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center;">
                    <div>Ongkir Dasar: <strong style="color: var(--accent);">Rp {{ number_format($exp->base_cost,0,',','.') }}</strong></div>
                    <div>Est: <strong>{{ $exp->estimated_days }} Hari</strong></div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="font-size: 12px; color: var(--text-muted); display: inline-flex; align-items: center; gap: 4px;">
                        <iconify-icon icon="flat-color-icons:shopping-cart" style="font-size: 14px;"></iconify-icon> Total: <strong>{{ $exp->orders_count }}</strong> Order
                    </div>
                    <div class="actions" style="gap: 6px;">
                        <a href="{{ route('admin.expeditions.edit',$exp) }}" class="btn btn-secondary btn-sm btn-icon" title="Edit">
                            <iconify-icon icon="flat-color-icons:edit-image" style="font-size: 16px;"></iconify-icon>
                        </a>
                        <form method="POST" action="{{ route('admin.expeditions.toggle',$exp) }}" style="display:inline;">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-icon {{ $exp->is_active ? 'btn-warning' : 'btn-success' }}">
                                <iconify-icon icon="{{ $exp->is_active ? 'flat-color-icons:cancel' : 'flat-color-icons:ok' }}" style="font-size: 16px;"></iconify-icon>
                            </button>
                        </form>
                        <button type="button" class="btn btn-danger btn-sm btn-icon" data-url="{{ route('admin.expeditions.destroy',$exp) }}" data-name="{{ $exp->name }}" onclick="confirmDelete(this.dataset.url, this.dataset.name)">
                            <iconify-icon icon="fluent-emoji-flat:wastebasket" style="font-size: 16px;"></iconify-icon>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif
    @if($expeditions->hasPages())
    <div class="pagination-wrap">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
            <span style="font-size:13px;color:var(--text-muted);">{{ $expeditions->firstItem() }}–{{ $expeditions->lastItem() }} dari {{ $expeditions->total() }}</span>
            {{ $expeditions->links('admin.partials.pagination') }}
        </div>
    </div>
    @endif
</div>
@endsection
