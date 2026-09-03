@extends('admin.layouts.app')
@section('title','Kelola Pesanan')
@section('page-title','Pesanan')
@section('breadcrumb')<span class="breadcrumb-sep">›</span><span>Pesanan</span>@endsection
@section('content')

@php
$statusColors = [
'pending_payment' => 'badge-warning',
'paid' => 'badge-paid',
'packed' => 'badge-info',
'shipped' => 'badge-purple',
'arrived' => 'badge-recommended',
'completed' => 'badge-active',
'cancelled' => 'badge-inactive',
];
@endphp

<div class="card">
    <div class="card-header">
        <span class="card-title" style="display: flex; align-items: center; gap: 8px;"><iconify-icon icon="flat-color-icons:shopping-cart" style="font-size: 22px;"></iconify-icon> Daftar Pesanan</span>
        <div style="display: flex; gap: 8px; align-items: center;">
            <span style="font-size:13px;color:var(--text-muted); margin-right: 8px;">Total: {{ $orders->total() }} order</span>
            {{-- Tombol flush cache pesanan di Redis --}}
            <form method="POST" action="{{ route('admin.cache.flush-orders') }}" style="margin: 0;" onsubmit="return confirm('Bersihkan cache pesanan di Redis?')">
                @csrf
                <button type="submit" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; background-color: #F59E0B; border-color: #F59E0B; color: white;">
                    <iconify-icon icon="flat-color-icons:synchronize" style="font-size: 18px;"></iconify-icon> Bersihkan Cache
                </button>
            </form>
        </div>
    </div>

    <div style="padding:14px 20px;border-bottom:1px solid var(--border);">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="filter-bar">
            <div class="search-input-wrapper">
                <iconify-icon icon="flat-color-icons:search" class="search-icon" style="font-size: 16px;"></iconify-icon>
                <input type="text" name="search" class="form-control search-input"
                    placeholder="Cari invoice, nama customer..." value="{{ request('search') }}" data-suggestion-url="{{ route('admin.orders.suggestions') }}" autocomplete="off">
            </div>
            <select name="status" class="form-control">
                <option value="">Semua Status</option>
                @foreach($statusLabels as $val => $label)
                <option value="{{ $val }}" {{ request('status')===$val?'selected':'' }}>{{ $label }}</option>
                @endforeach
            </select>
            <select name="cancel_request" class="form-control">
                <option value="">Semua Pengajuan Batal</option>
                <option value="pending" {{ request('cancel_request')==='pending'?'selected':'' }}>Menunggu Persetujuan</option>
                <option value="approved" {{ request('cancel_request')==='approved'?'selected':'' }}>Disetujui</option>
                <option value="rejected" {{ request('cancel_request')==='rejected'?'selected':'' }}>Ditolak</option>
            </select>
            <button type="submit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;"><iconify-icon icon="flat-color-icons:filter" style="font-size: 16px;"></iconify-icon> Filter</button>
            @if(request()->hasAny(['search','status','cancel_request']))
            <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px;"><iconify-icon icon="flat-color-icons:undo" style="font-size: 16px;"></iconify-icon> Reset</a>
            @endif
        </form>
    </div>

    @if($orders->isEmpty())
    <div class="empty-state">
        <div class="empty-state-icon"><iconify-icon icon="flat-color-icons:shopping-cart" style="font-size: 48px;"></iconify-icon></div>
        <h3>Belum ada pesanan</h3>
    </div>
    @else
    <!-- Desktop Table View (>768px) -->
    <div class="table-wrapper desktop-table-container">
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Invoice</th>
                    <th>Customer</th>
                    <th>Ekspedisi</th>
                    <th>Subtotal</th>
                    <th>Ongkir</th>
                    <th>Grand Total</th>
                    <th>Status</th>
                    <th>Resi</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                <tr>
                    <td style="color:var(--text-muted);font-size:12px;">{{ $orders->firstItem() + $loop->index }}</td>
                    <td>
                        <a href="{{ route('admin.orders.show',$order) }}"
                            style="font-family:monospace;color:var(--accent);font-weight:600;text-decoration:none;">
                            {{ $order->invoice_number }}
                        </a>
                    </td>
                    <td>
                        <div style="font-weight:500;color:var(--text-primary);">{{ $order->user?->name }}</div>
                        <div style="font-size:11px;color:var(--text-muted);">{{ $order->user?->email }}</div>
                    </td>
                    <td style="font-size:12px;">{{ $order->expedition?->name }}</td>
                    <td>Rp {{ number_format($order->subtotal,0,',','.') }}</td>
                    <td>Rp {{ number_format($order->shipping_cost,0,',','.') }}</td>
                    <td style="font-weight:700;color:var(--text-primary);">Rp {{ number_format($order->grand_total,0,',','.') }}</td>
                    <td>
                        <span class="badge {{ $statusColors[$order->status] ?? 'badge-inactive' }}">{{ $statusLabels[$order->status] ?? $order->status }}</span>
                        @if($order->cancel_request_status === 'pending')
                        <span class="badge" style="background-color: rgba(245,158,11,0.12); color: #f59e0b; font-size: 10px; font-weight: 700; display: inline-flex; align-items: center; gap: 3px; margin-top: 4px; border: 1px solid rgba(245,158,11,0.25); justify-content: center; white-space: nowrap;">
                            <iconify-icon icon="flat-color-icons:warning" style="font-size: 12px;"></iconify-icon> Pengajuan Batal
                        </span>
                        @elseif($order->cancel_request_status === 'approved')
                        <span class="badge" style="background-color: rgba(16,185,129,0.12); color: #10b981; font-size: 10px; font-weight: 700; display: block; margin-top: 4px; border: 1px solid rgba(16,185,129,0.25); text-align: center; white-space: nowrap;">
                            Batal Disetujui
                        </span>
                        @elseif($order->cancel_request_status === 'rejected')
                        <span class="badge" style="background-color: rgba(239,68,68,0.12); color: #ef4444; font-size: 10px; font-weight: 700; display: block; margin-top: 4px; border: 1px solid rgba(239,68,68,0.25); text-align: center; white-space: nowrap;">
                            Batal Ditolak
                        </span>
                        @endif
                    </td>
                    <td style="font-size:12px;color:var(--text-muted);">{{ $order->resi_number ?? '—' }}</td>
                    <td style="font-size:12px;color:var(--text-muted);white-space:nowrap;">{{ $order->created_at->format('d M Y') }}</td>
                    <td>
                        <a href="{{ route('admin.orders.show',$order) }}" class="btn btn-info btn-sm btn-icon" title="Detail">
                            <iconify-icon icon="flat-color-icons:view-details" style="font-size: 16px;"></iconify-icon>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Mobile Order Card View (<=768px) -->
    <div class="mobile-order-grid">
        @foreach($orders as $order)
        <div class="mobile-order-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <a href="{{ route('admin.orders.show',$order) }}" style="font-family:monospace; color:var(--accent); font-weight:700; text-decoration:none; font-size:14px;">
                    {{ $order->invoice_number }}
                </a>
                <span class="badge {{ $statusColors[$order->status] ?? 'badge-inactive' }}">
                    {{ $statusLabels[$order->status] ?? $order->status }}
                </span>
            </div>
            <div style="font-size: 13px; font-weight: 700; color: var(--text-primary); margin-bottom: 2px;">
                {{ $order->user?->name ?? '—' }}
            </div>
            <div style="font-size: 11.5px; color: var(--text-muted); margin-bottom: 10px;">
                {{ $order->user?->email }}
            </div>
            @if($order->cancel_request_status === 'pending')
            <div style="background-color: rgba(245,158,11,0.12); color: #f59e0b; font-size: 11px; font-weight: 700; padding: 4px 8px; border-radius: 6px; margin-bottom: 10px; border: 1px solid rgba(245,158,11,0.25); display: flex; align-items: center; gap: 6px;">
                <iconify-icon icon="flat-color-icons:warning" style="font-size: 14px;"></iconify-icon> Pengajuan Pembatalan Menunggu Persetujuan
            </div>
            @endif
            <div style="background: var(--bg-input, #f8fafc); padding: 10px 12px; border-radius: 8px; font-size: 12px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 6px;"><iconify-icon icon="flat-color-icons:shipped" style="font-size: 16px;"></iconify-icon> {{ $order->expedition?->name ?? 'Ekspedisi' }}</div>
                <div style="font-weight: 800; color: var(--accent); font-size: 13.5px;">
                    Rp {{ number_format($order->grand_total,0,',','.') }}
                </div>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="font-size: 11px; color: var(--text-muted); display: inline-flex; align-items: center; gap: 4px;">
                    <iconify-icon icon="flat-color-icons:calendar" style="font-size: 13px;"></iconify-icon> {{ $order->created_at->format('d M Y, H:i') }}
                </div>
                <a href="{{ route('admin.orders.show',$order) }}" class="btn btn-info btn-sm" style="padding: 6px 12px; font-size: 12px; display: inline-flex; align-items: center; gap: 4px;">
                    Lihat Detail <iconify-icon icon="lucide:chevron-right" style="font-size: 14px;"></iconify-icon>
                </a>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

@if($orders->hasPages())
<div class="pagination-wrap">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
        <span style="font-size:13px;color:var(--text-muted);">{{ $orders->firstItem() }}–{{ $orders->lastItem() }} dari {{ $orders->total() }}</span>
        {{ $orders->links('admin.partials.pagination') }}
    </div>
</div>
@endif
</div>
@endsection