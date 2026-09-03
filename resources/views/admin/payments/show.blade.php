@extends('admin.layouts.app')
@section('title','Detail Pembayaran')
@section('page-title','Detail Pembayaran')
@section('breadcrumb')
    <span class="breadcrumb-sep">›</span><a href="{{ route('admin.payments.index') }}">Pembayaran</a>
    <span class="breadcrumb-sep">›</span><span>Detail</span>
@endsection
@section('content')
@php
$statusColors = ['waiting_payment'=>'badge-warning','paid'=>'badge-active','expired'=>'badge-inactive','failed'=>'badge-failed'];
$statusLabels = ['waiting_payment'=>'Menunggu Bayar','paid'=>'Dibayar','expired'=>'Kedaluwarsa','failed'=>'Gagal'];
$bankLabels   = ['bca'=>'BCA','bni'=>'BNI','bri'=>'BRI','mandiri'=>'Mandiri','permata'=>'Permata'];
@endphp
<div style="max-width:760px;">
<div class="card">
    <div class="card-header">
        <span class="card-title" style="display: flex; align-items: center; gap: 8px;">
            <iconify-icon icon="flat-color-icons:credit-card" style="font-size: 22px;"></iconify-icon> Detail Pembayaran
        </span>
        <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px;">
            <iconify-icon icon="flat-color-icons:previous"></iconify-icon> Kembali
        </a>
    </div>
    <div class="card-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
            <div>
                <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px;">STATUS</div>
                <span class="badge {{ $statusColors[$payment->status]??'badge-inactive' }}" style="font-size:13px;padding:5px 14px;">
                    {{ $statusLabels[$payment->status]??$payment->status }}
                </span>
            </div>
            <div>
                <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px;">BANK</div>
                <div style="font-size:20px;font-weight:800;color:var(--text-primary);">{{ $bankLabels[$payment->bank_code]??strtoupper($payment->bank_code) }}</div>
            </div>
            <div>
                <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px;">NOMOR VIRTUAL ACCOUNT</div>
                <div style="font-size:18px;font-weight:700;font-family:monospace;color:var(--info);">{{ $payment->virtual_account_number }}</div>
            </div>
            <div>
                <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px;">JUMLAH</div>
                <div style="font-size:20px;font-weight:800;color:var(--success);">Rp {{ number_format($payment->amount,0,',','.') }}</div>
            </div>
            <div>
                <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px;">DIBAYAR PADA</div>
                <div>{{ $payment->paid_at?->format('d M Y, H:i') ?? '—' }}</div>
            </div>
            <div>
                <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px;">KEDALUWARSA</div>
                <div>{{ $payment->expired_at?->format('d M Y, H:i') ?? '—' }}</div>
            </div>
        </div>

        <div style="border-top:1px solid var(--border);padding-top:16px;">
            <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);margin-bottom:12px;display:flex;align-items:center;gap:6px;">
                <iconify-icon icon="flat-color-icons:opened-folder" style="font-size: 16px;"></iconify-icon> Order Terkait
            </div>
            <div style="background:var(--bg-input);border-radius:var(--radius-sm);padding:14px;">
                <a href="{{ route('admin.orders.show',$payment->order) }}" style="color:var(--accent);font-family:monospace;font-weight:700;font-size:15px;text-decoration:none;">
                    {{ $payment->order?->invoice_number }}
                </a>
                <div style="font-size:13px;color:var(--text-secondary);margin-top:8px;display:flex;align-items:center;gap:6px;">
                    <iconify-icon icon="flat-color-icons:businessman" style="font-size: 16px;"></iconify-icon> {{ $payment->order?->user?->name }} · {{ $payment->order?->user?->email }}
                </div>
                <div style="font-size:13px;color:var(--text-muted);margin-top:4px;">
                    Grand Total: <strong style="color:var(--text-primary);">Rp {{ number_format($payment->order?->grand_total,0,',','.') }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection
