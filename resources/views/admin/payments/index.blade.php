@extends('admin.layouts.app')
@section('title','Kelola Pembayaran')
@section('page-title','Pembayaran')
@section('breadcrumb')<span class="breadcrumb-sep">›</span><span>Pembayaran</span>@endsection
@section('content')

@php
$statusColors = ['waiting_payment'=>'badge-warning','paid'=>'badge-active','expired'=>'badge-inactive','failed'=>'badge-failed'];
$statusLabels = ['waiting_payment'=>'Menunggu Bayar','paid'=>'Dibayar','expired'=>'Kedaluwarsa','failed'=>'Gagal'];
$bankLabels = ['bca'=>'BCA','bni'=>'BNI','bri'=>'BRI','mandiri'=>'Mandiri','permata'=>'Permata'];
@endphp

<div class="card">
    <div class="card-header">
        <span class="card-title" style="display: flex; align-items: center; gap: 8px;"><iconify-icon icon="flat-color-icons:credit-card" style="font-size: 22px;"></iconify-icon> Daftar Pembayaran</span>
        <div style="display: flex; gap: 8px; align-items: center;">
            {{-- Tombol flush cache pembayaran di Redis --}}
            <form method="POST" action="{{ route('admin.cache.flush-payments') }}" style="margin: 0;" onsubmit="return confirm('Bersihkan cache pembayaran di Redis?')">
                @csrf
                <button type="submit" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; background-color: #F59E0B; border-color: #F59E0B; color: white;">
                    <iconify-icon icon="flat-color-icons:synchronize" style="font-size: 18px;"></iconify-icon> Bersihkan Cache
                </button>
            </form>
        </div>
    </div>

    <div style="padding:14px 20px;border-bottom:1px solid var(--border);">
        <form method="GET" action="{{ route('admin.payments.index') }}" class="filter-bar">
            <div class="search-input-wrapper">
                <iconify-icon icon="flat-color-icons:search" class="search-icon" style="font-size: 16px;"></iconify-icon>
                <input type="text" name="search" class="form-control search-input"
                    placeholder="Cari VA, invoice, nama..." value="{{ request('search') }}" data-suggestion-url="{{ route('admin.payments.suggestions') }}" autocomplete="off">
            </div>
            <select name="status" class="form-control">
                <option value="">Semua Status</option>
                @foreach($statusLabels as $v => $l)
                <option value="{{ $v }}" {{ request('status')===$v?'selected':'' }}>{{ $l }}</option>
                @endforeach
            </select>
            <select name="bank" class="form-control">
                <option value="">Semua Bank</option>
                @foreach($bankLabels as $v => $l)
                <option value="{{ $v }}" {{ request('bank')===$v?'selected':'' }}>{{ $l }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;"><iconify-icon icon="flat-color-icons:filter" style="font-size: 16px;"></iconify-icon> Filter</button>
            @if(request()->hasAny(['search','status','bank']))
            <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px;"><iconify-icon icon="flat-color-icons:undo" style="font-size: 16px;"></iconify-icon> Reset</a>
            @endif
        </form>
    </div>

    @if($payments->isEmpty())
    <div class="empty-state">
        <div class="empty-state-icon"><iconify-icon icon="flat-color-icons:credit-card" style="font-size: 48px;"></iconify-icon></div>
        <h3>Belum ada pembayaran</h3>
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
                    <th>Bank</th>
                    <th>Jumlah</th>
                    <th>Status</th>
                    <th>Dibayar</th>
                    <th>Kedaluwarsa</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $pay)
                <tr>
                    <td><code style="font-size:12px;color:var(--info);">{{ $payments->firstItem() + $loop->index}}</code></td>
                    <!-- <td><code style="font-size:12px;color:var(--info);">{{ $pay->virtual_account_number }}</code></td> -->
                    <td>
                        <a href="{{ route('admin.orders.show', $pay->order) }}"
                            style="font-family:monospace;font-size:12px;color:var(--accent);text-decoration:none;">
                            {{ $pay->order?->invoice_number }}
                        </a>
                    </td>
                    <td>
                        <div style="font-size:13px;font-weight:500;color:var(--text-primary);">{{ $pay->order?->user?->name }}</div>
                        <div style="font-size:11px;color:var(--text-muted);">{{ $pay->order?->user?->email }}</div>
                    </td>
                    <td>
                        <span class="badge badge-purple">{{ $bankLabels[$pay->bank_code] ?? strtoupper($pay->bank_code) }}</span>
                    </td>
                    <td style="font-weight:600;color:var(--text-primary);">Rp {{ number_format($pay->amount,0,',','.') }}</td>
                    <td><span class="badge {{ $statusColors[$pay->status]??'badge-inactive' }}">{{ $statusLabels[$pay->status]??$pay->status }}</span></td>
                    <td style="font-size:12px;color:var(--text-muted);">{{ $pay->paid_at?->format('d M Y H:i') ?? '—' }}</td>
                    <td style="font-size:12px;color:var(--text-muted);">{{ $pay->expired_at?->format('d M Y H:i') ?? '—' }}</td>
                    <td>
                        <a href="{{ route('admin.payments.show', $pay) }}" class="btn btn-info btn-sm btn-icon" title="Detail">
                            <iconify-icon icon="flat-color-icons:view-details" style="font-size: 16px;"></iconify-icon>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Mobile Payment Card View (<=768px) -->
    <div class="mobile-payment-grid">
        @foreach($payments as $pay)
        <div class="mobile-payment-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <code style="font-size: 13px; color: var(--info); font-weight: 800;">
                    VA: {{ $pay->virtual_account_number }}
                </code>
                <span class="badge {{ $statusColors[$pay->status]??'badge-inactive' }}">
                    {{ $statusLabels[$pay->status]??$pay->status }}
                </span>
            </div>
            <div style="font-size: 13px; font-weight: 700; color: var(--text-primary); margin-bottom: 2px; display: flex; align-items: center; gap: 6px;">
                <iconify-icon icon="flat-color-icons:businessman" style="font-size: 16px;"></iconify-icon> {{ $pay->order?->user?->name ?? '—' }}
            </div>
            <div style="font-size: 11.5px; color: var(--text-muted); margin-bottom: 10px;">
                Invoice: <a href="{{ route('admin.orders.show', $pay->order) }}" style="color: var(--accent); font-family: monospace;">{{ $pay->order?->invoice_number }}</a>
            </div>

            <div style="background: var(--bg-input, #f8fafc); padding: 10px 12px; border-radius: 8px; font-size: 12px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center;">
                <div><span class="badge badge-purple">{{ $bankLabels[$pay->bank_code] ?? strtoupper($pay->bank_code) }}</span></div>
                <div style="font-weight: 800; color: var(--accent); font-size: 14px;">
                    Rp {{ number_format($pay->amount,0,',','.') }}
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="font-size: 11px; color: var(--text-muted); display: inline-flex; align-items: center; gap: 4px;">
                    <iconify-icon icon="flat-color-icons:calendar" style="font-size: 13px;"></iconify-icon> {{ $pay->paid_at?->format('d M Y H:i') ?? $pay->created_at->format('d M Y H:i') }}
                </div>
                <a href="{{ route('admin.payments.show', $pay) }}" class="btn btn-info btn-sm" style="padding: 6px 12px; font-size: 12px; display: inline-flex; align-items: center; gap: 4px;">
                    Lihat Detail <iconify-icon icon="lucide:chevron-right" style="font-size: 14px;"></iconify-icon>
                </a>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    @if($payments->hasPages())
    <div class="pagination-wrap">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
            <span style="font-size:13px;color:var(--text-muted);">{{ $payments->firstItem() }}–{{ $payments->lastItem() }} dari {{ $payments->total() }}</span>
            {{ $payments->links('admin.partials.pagination') }}
        </div>
    </div>
    @endif
</div>
@endsection