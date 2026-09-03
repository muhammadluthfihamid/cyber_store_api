@extends('admin.layouts.app')
@section('title', 'Detail Pesanan — ' . $order->invoice_number)
@section('page-title', 'Order Details')
@section('breadcrumb')
<span class="breadcrumb-sep">/</span><a href="{{ route('admin.orders.index') }}">Orders</a>
<span class="breadcrumb-sep">/</span><span>{{ $order->invoice_number }}</span>
@endsection

@section('content')
@php
$statusBadgeStyles = [
'pending_payment' => 'background: #fffbeb; color: #d97706; border: 1px solid #fde68a;',
'paid' => 'background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0;',
'packed' => 'background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe;',
'shipped' => 'background: #f5f3ff; color: #7c3aed; border: 1px solid #ddd6fe;',
'arrived' => 'background: #ecfeff; color: #0891b2; border: 1px solid #a5f3fc;',
'completed' => 'background: #82d616; color: #ffffff;',
'cancelled' => 'background: #fef2f2; color: #dc2626; border: 1px solid #fecaca;',
];
@endphp

{{-- ─── 1. Pengajuan Pembatalan Alert (jika pending) ─────────────────────────── --}}
@if($order->cancel_request_status === 'pending')
<div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 1rem; padding: 20px; margin-bottom: 24px; box-shadow: var(--shadow);">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
        <div style="font-weight: 800; font-size: 15px; color: #991b1b; display: flex; align-items: center; gap: 8px;">
            <iconify-icon icon="solar:danger-triangle-bold-duotone" style="font-size: 18px; color: #dc2626;"></iconify-icon> Pengajuan Pembatalan Pesanan
        </div>
        <span style="background: #dc2626; color: #fff; font-size: 11px; font-weight: 800; padding: 3px 10px; border-radius: 20px; text-transform: uppercase;">MEMBUTUHKAN TINDAKAN</span>
    </div>
    <p style="margin: 0 0 14px 0; font-size: 13.5px; color: #7f1d1d; line-height: 1.5;">
        Customer telah mengajukan pembatalan untuk pesanan ini. Silakan tinjau alasan di bawah untuk menyetujui atau menolak pengajuan tersebut.
    </p>
    <div style="padding: 14px; background: #ffffff; border-radius: 0.75rem; border-left: 4px solid #ef4444; margin-bottom: 16px; box-shadow: 0 2px 4px rgba(0,0,0,0.04);">
        <div style="font-size: 11px; color: #9b1c1c; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">ALASAN PEMBATALAN</div>
        <div style="font-size: 13.5px; color: #1e293b; font-weight: 600;">"{{ $order->cancel_request_reason ?? 'Tidak ada alasan khusus' }}"</div>
    </div>

    <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
        {{-- Form Terima --}}
        <form id="approveCancelForm" method="POST" action="{{ route('admin.orders.cancel-approve', $order) }}" style="margin: 0;">
            @csrf @method('PATCH')
            <button type="button" class="btn" style="background: linear-gradient(310deg, #ea0606, #ff3b30); color: white; border: none; font-weight: 700; border-radius: 0.5rem; padding: 8px 16px; box-shadow: 0 4px 6px -1px rgba(234,6,6,0.3); display: inline-flex; align-items: center; gap: 6px;" onclick="confirmUpdate('approveCancelForm', 'Setujui Pembatalan', 'Apakah Anda yakin ingin menyetujui pembatalan pesanan ini? Stok produk akan dikembalikan otomatis.')">
                <iconify-icon icon="solar:check-read-bold" style="font-size: 16px;"></iconify-icon> Setujui & Batalkan
            </button>
        </form>

        {{-- Form Tolak --}}
        <div style="flex: 1; min-width: 260px;">
            <form id="rejectCancelForm" method="POST" action="{{ route('admin.orders.cancel-reject', $order) }}" style="display: flex; gap: 8px; margin: 0;">
                @csrf @method('PATCH')
                <input type="text" name="reject_reason" class="form-control" placeholder="Tulis alasan penolakan..." required style="flex: 1; border-radius: 0.5rem; border-color: #fca5a5; font-size: 13px;">
                <button type="button" class="btn" style="background: #64748b; color: white; border: none; font-weight: 700; border-radius: 0.5rem; padding: 8px 16px; display: inline-flex; align-items: center; gap: 6px;" onclick="confirmUpdate('rejectCancelForm', 'Tolak Pembatalan', 'Apakah Anda yakin ingin menolak pengajuan pembatalan pesanan ini?')">
                    <iconify-icon icon="solar:close-circle-bold" style="font-size: 16px;"></iconify-icon> Tolak
                </button>
            </form>
        </div>
    </div>
</div>
@endif


{{-- ─── 2. Top Main Header Card (Soft UI Order Details Card) ─────────────────── --}}
<div style="background: var(--bg-card); border-radius: 1rem; padding: 24px; box-shadow: var(--shadow); border: 1px solid var(--border); margin-bottom: 24px;">
    <div style="display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 16px; margin-bottom: 20px;">
        <div>
            <div style="font-size: 18px; font-weight: 800; color: var(--text-primary); letter-spacing: -0.3px;">Order Details</div>
            <div style="font-size: 13px; color: var(--text-muted); margin-top: 4px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <span>Order no. <strong style="color: var(--text-primary);">#{{ $order->invoice_number }}</strong> from <strong style="color: var(--text-primary);">{{ $order->created_at->format('d.m.Y') }}</strong></span>
                <span>•</span>
                <span>Code: <strong style="color: var(--text-primary);">{{ $order->resi_number ?? 'PENDING' }}</strong></span>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <a href="javascript:window.print();" style="background: linear-gradient(310deg, #7928CA 0%, #FF0080 100%); color: #ffffff; font-size: 11px; font-weight: 800; padding: 9px 18px; border-radius: 0.5rem; text-decoration: none; text-transform: uppercase; letter-spacing: 0.5px; box-shadow: 0 4px 10px rgba(236, 72, 153, 0.3); display: inline-flex; align-items: center; gap: 6px;">
                <iconify-icon icon="solar:printer-bold-duotone" style="font-size: 15px;"></iconify-icon> INVOICE
            </a>
            <a href="{{ route('admin.orders.index') }}" style="background: var(--bg-input); color: var(--text-primary); font-size: 11px; font-weight: 800; padding: 9px 16px; border-radius: 0.5rem; text-decoration: none; border: 1px solid var(--border); display: inline-flex; align-items: center; gap: 6px;">
                <iconify-icon icon="solar:alt-arrow-left-bold-duotone" style="font-size: 15px;"></iconify-icon> KEMBALI
            </a>
        </div>
    </div>

    <hr style="border: 0; border-top: 1px solid var(--border); margin: 16px 0 24px 0;">

    {{-- Product Items Showcase --}}
    <div style="display: flex; flex-direction: column; gap: 16px;">
        @foreach($order->items as $item)
        @php
        $itemPhotoUrl = null;
        if ($item->product && $item->product->main_photo) {
        $itemPhotoUrl = str_starts_with($item->product->main_photo, 'http') ? $item->product->main_photo : \Storage::disk('public')->url($item->product->main_photo);
        }
        @endphp
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; padding: 12px; background: var(--bg-input); border-radius: 0.75rem; border: 1px solid var(--border);">
            <div style="display: flex; align-items: center; gap: 16px;">
                @if($itemPhotoUrl)
                <img src="{{ $itemPhotoUrl }}" alt="{{ $item->product_name }}" style="width: 80px; height: 80px; border-radius: 0.75rem; object-fit: cover; border: 1px solid var(--border); flex-shrink: 0; background: #fff;">
                @else
                <div style="width: 80px; height: 80px; border-radius: 0.75rem; background: #e2e8f0; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"><iconify-icon icon="solar:box-bold-duotone" style="font-size: 32px; color: #64748b;"></iconify-icon></div>
                @endif

                <div>
                    <div style="font-size: 15px; font-weight: 800; color: var(--text-primary); line-height: 1.3;">{{ $item->product_name }}</div>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px; display: flex; align-items: center; gap: 8px;">
                        <span>Qty: <strong style="color: var(--text-primary);">{{ $item->quantity }}x</strong></span>
                        @if($item->size) <span>• Ukuran: <strong style="color: var(--text-primary);">{{ $item->size }}</strong></span> @endif
                        @if($item->color) <span>• Warna: <strong style="color: var(--text-primary);">{{ $item->color }}</strong></span> @endif
                        @if($item->nim) <span>• NIM: <strong style="color: var(--accent); background: rgba(99, 102, 241, .1); padding: 2px 6px; border-radius: 4px;">🎓 {{ $item->nim }}</strong></span> @endif
                    </div>
                    <div style="margin-top: 6px;">
                        <span @style([
                            $statusBadgeStyles[$order->status] ?? 'background: #e2e8f0; color: #475569;',
                            'font-size: 10px; font-weight: 800; padding: 3px 10px; border-radius: 20px; text-transform: uppercase;'
                            ])>
                            {{ $statusLabels[$order->status] ?? strtoupper($order->status) }}
                        </span>
                    </div>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 20px;">
                <div style="text-align: right;">
                    <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Harga Produk</div>
                    <div style="font-size: 16px; font-weight: 800; color: var(--text-primary);">Rp {{ number_format($item->total, 0, ',', '.') }}</div>
                </div>

            </div>
        </div>
        @endforeach
    </div>
</div>


{{-- ─── 3. Grid Lower 3-Columns Section (Soft UI Details Grid) ─────────────── --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; align-items: start;">

    {{-- ── COLUMN 1: Track order Timeline ── --}}
    <div style="background: var(--bg-card); border-radius: 1rem; padding: 24px; box-shadow: var(--shadow); border: 1px solid var(--border);">
        <div style="font-size: 15px; font-weight: 800; color: var(--text-primary); margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between;">
            <span>Track order</span>
            <span style="font-size: 12px; color: var(--text-muted); font-weight: 600;">{{ $order->expedition?->name ?? 'Ekspedisi' }}</span>
        </div>

        @if($order->trackings->isNotEmpty())
        <div style="display: flex; flex-direction: column; gap: 0;">
            @foreach($order->trackings as $i => $track)
            <div style="display: flex; gap: 16px;">
                <div style="display: flex; flex-direction: column; align-items: center;">
                    <div @style([ 'width: 32px; height: 32px; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0;' , 'background: linear-gradient(310deg, #DF0B2B 0%, #BA0924 100%); color: #ffffff; box-shadow: 0 4px 6px -1px rgba(223,11,43,0.3);'=> $i === 0,
                        'background: var(--bg-input); color: var(--text-muted); box-shadow: none;' => $i !== 0
                        ])>
                        @if($i === 0) <iconify-icon icon="solar:bell-bing-bold-duotone" style="font-size: 16px;"></iconify-icon> @elseif($loop->last) <iconify-icon icon="solar:box-bold-duotone" style="font-size: 16px;"></iconify-icon> @else <iconify-icon icon="solar:delivery-bold-duotone" style="font-size: 16px;"></iconify-icon> @endif
                    </div>
                    @if(!$loop->last)
                    <div style="width: 2px; flex: 1; background: var(--border); margin: 6px 0; min-height: 24px;"></div>
                    @endif
                </div>
                <div style="padding-bottom: 20px;">
                    <div style="font-size: 13.5px; font-weight: 800; color: var(--text-primary);">{{ $track->description }}</div>
                    <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 2px;">{{ $track->created_at->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</div>
                    @if($track->location)
                    <div style="font-size: 11.5px; color: var(--text-secondary); margin-top: 2px; display: flex; align-items: center; gap: 4px;"><iconify-icon icon="solar:map-point-wave-bold-duotone" style="font-size: 14px; color: var(--accent);"></iconify-icon> {{ $track->location }}</div>
                    @endif
                    @if($track->proof_photo)
                    <div style="margin-top: 8px;">
                        <a href="{{ \Storage::disk('public')->url($track->proof_photo) }}" target="_blank">
                            <img src="{{ \Storage::disk('public')->url($track->proof_photo) }}" style="max-width: 180px; max-height: 120px; border-radius: 0.5rem; border: 1px solid var(--border); display: block; object-fit: cover;">
                        </a>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div style="text-align: center; padding: 32px 10px; color: var(--text-muted); font-size: 13px;">
            Belum ada riwayat tracking pengiriman.
        </div>
        @endif
    </div>

    {{-- ── COLUMN 2: Payment Details & Billing Information ── --}}
    <div style="display: flex; flex-direction: column; gap: 24px;">
        {{-- Payment Details Box --}}
        <div style="background: var(--bg-card); border-radius: 1rem; padding: 24px; box-shadow: var(--shadow); border: 1px solid var(--border);">
            <div style="font-size: 15px; font-weight: 800; color: var(--text-primary); margin-bottom: 16px;">Payment details</div>

            @if($order->payment)
            <div style="background: var(--bg-input); border-radius: 0.75rem; padding: 16px; border: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 40px; height: 28px; background: linear-gradient(135deg, #1e293b, #0f172a); border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 800; font-size: 11px; letter-spacing: 0.5px;">
                        {{ strtoupper(substr($order->payment->bank_code ?? 'VA', 0, 4)) }}
                    </div>
                    <div>
                        <div style="font-family: monospace; font-size: 13px; font-weight: 700; color: var(--text-primary);">
                            **** **** **** {{ substr($order->payment->virtual_account_number ?? '0000', -4) }}
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 1px;">
                            {{ $order->payment->virtual_account_number }}
                        </div>
                    </div>
                </div>
                <div>
                    <span @style([ 'font-size: 10px; font-weight: 800; padding: 3px 8px; border-radius: 12px; text-transform: uppercase;' , 'background: #f0fdf4; color: #16a34a;'=> $order->payment->status === 'paid',
                        'background: #fffbeb; color: #d97706;' => $order->payment->status !== 'paid'
                        ])>
                        {{ ucwords(str_replace('_', ' ', $order->payment->status)) }}
                    </span>
                </div>
            </div>
            <div style="font-size: 12px; color: var(--text-muted); display: flex; flex-direction: column; gap: 4px;">
                <div>Jumlah Pembayaran: <strong style="color: var(--text-primary);">Rp {{ number_format($order->payment->amount, 0, ',', '.') }}</strong></div>
                @if($order->payment->paid_at)
                <div>Dibayar Pada: <strong style="color: var(--text-primary);">{{ $order->payment->paid_at->format('d M Y, H:i') }}</strong></div>
                @endif
            </div>
            @else
            <div style="text-align: center; padding: 20px 10px; color: var(--text-muted); font-size: 13px;">
                Belum ada transaksi pembayaran.
            </div>
            @endif
        </div>

        {{-- Billing Information Box --}}
        <div style="background: var(--bg-card); border-radius: 1rem; padding: 24px; box-shadow: var(--shadow); border: 1px solid var(--border);">
            <div style="font-size: 15px; font-weight: 800; color: var(--text-primary); margin-bottom: 16px;">Billing Information</div>

            <div style="background: var(--bg-input); border-radius: 0.75rem; padding: 16px; border: 1px solid var(--border);">
                <div style="font-weight: 800; font-size: 14px; color: var(--text-primary); margin-bottom: 8px;">
                    {{ $order->address?->receiver_name ?? $order->user?->name ?? 'Customer' }}
                </div>
                <div style="font-size: 12px; color: var(--text-secondary); line-height: 1.6; display: flex; flex-direction: column; gap: 3px;">
                    <div>Email: <strong style="color: var(--text-primary);">{{ $order->user?->email ?? '-' }}</strong></div>
                    <div>No. HP: <strong style="color: var(--text-primary);">{{ $order->address?->phone ?? '-' }}</strong></div>
                    @if($order->address)
                    <div style="margin-top: 4px;">Alamat: {{ $order->address->address }}, {{ $order->address->district }} {{ $order->address->city }}, {{ $order->address->notes ? '('.$order->address->notes.')' : '' }}, {{ $order->address->province }} {{ $order->address->postal_code }}</div>
                    @endif
                </div>

                @if($order->address && $order->address->latitude && $order->address->longitude)
                <div style="margin-top: 12px;">
                    <iframe
                        width="100%"
                        height="140"
                        frameborder="0"
                        style="border:0; border-radius: 0.5rem; display: block;"
                        src="https://maps.google.com/maps?q={{ $order->address->latitude }},{{ $order->address->longitude }}&hl=id&z=15&output=embed"
                        allowfullscreen>
                    </iframe>
                    <div style="margin-top: 6px; text-align: right;">
                        <a href="https://www.google.com/maps/search/?api=1&query={{ $order->address->latitude }},{{ $order->address->longitude }}"
                            target="_blank"
                            style="color: var(--accent); font-size: 11px; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                            <iconify-icon icon="solar:global-bold-duotone" style="font-size: 14px;"></iconify-icon> Buka Google Maps
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── COLUMN 3: Order Summary & Status Management ── --}}
    <div style="display: flex; flex-direction: column; gap: 24px;">
        {{-- Order Summary Box --}}
        <div style="background: var(--bg-card); border-radius: 1rem; padding: 24px; box-shadow: var(--shadow); border: 1px solid var(--border);">
            <div style="font-size: 15px; font-weight: 800; color: var(--text-primary); margin-bottom: 16px;">Order Summary</div>

            <div style="display: flex; flex-direction: column; gap: 10px; font-size: 13px; color: var(--text-secondary);">
                <div style="display: flex; justify-content: space-between;">
                    <span>Subtotal Produk:</span>
                    <strong style="color: var(--text-primary);">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>Biaya Pengiriman:</span>
                    <strong style="color: var(--text-primary);">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</strong>
                </div>
                @php
                $serviceFee = max(0, $order->grand_total - ($order->subtotal + $order->shipping_cost));
                if ($serviceFee == 0) $serviceFee = 2000;
                @endphp
                <div style="display: flex; justify-content: space-between;">
                    <span>Biaya Layanan:</span>
                    <strong style="color: var(--text-primary);">Rp {{ number_format($serviceFee, 0, ',', '.') }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>Ekspedisi:</span>
                    <strong style="color: var(--text-primary);">{{ $order->expedition?->name ?? '-' }}</strong>
                </div>
                <hr style="border: 0; border-top: 1px solid var(--border); margin: 6px 0;">
                <div style="display: flex; justify-content: space-between; font-size: 16px; font-weight: 800; color: var(--text-primary);">
                    <span>Total Pembayaran:</span>
                    <span style="color: var(--accent);">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- Status & Resi Form Box --}}
        <div style="background: var(--bg-card); border-radius: 1rem; padding: 24px; box-shadow: var(--shadow); border: 1px solid var(--border);">
            <div style="font-size: 15px; font-weight: 800; color: var(--text-primary); margin-bottom: 16px;">Ubah Status & Resi</div>

            {{-- Status Update Form --}}
            <form id="orderStatusForm" method="POST" action="{{ route('admin.orders.status', $order) }}" enctype="multipart/form-data" style="margin-bottom: 20px;">
                @csrf @method('PATCH')
                <div class="form-group" style="margin-bottom: 12px;">
                    <label class="form-label" style="font-size: 12px; font-weight: 700; color: var(--text-secondary);">Status Pesanan</label>
                    <select name="status" id="status-select" class="form-control" required onchange="toggleProofInput()" style="border-radius: 0.5rem; font-size: 13px; padding: 8px 12px;">
                        @foreach($statusLabels as $val => $label)
                        <option value="{{ $val }}" {{ $order->status === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" id="proof-photo-group" style="display: none; margin-bottom: 12px;">
                    <label class="form-label" style="font-size: 12px; font-weight: 700; display: flex; align-items: center; gap: 6px;"><iconify-icon icon="solar:camera-bold-duotone" style="font-size: 16px; color: var(--accent);"></iconify-icon> Bukti Pengiriman (Foto Kurir)</label>
                    <input type="file" name="proof_photo" class="form-control" accept="image/*" style="border-radius: 0.5rem; font-size: 12px;">
                    <small style="color: var(--text-muted); display: block; margin-top: 4px;">Upload foto kurir/bukti penerimaan saat paket tiba.</small>
                </div>
                <button type="button" class="btn" style="width: 100%; background: linear-gradient(310deg, #7928CA 0%, #FF0080 100%); color: #ffffff; border: none; font-weight: 800; font-size: 12px; border-radius: 0.5rem; padding: 10px; text-transform: uppercase; letter-spacing: 0.5px; box-shadow: 0 4px 10px rgba(236,72,153,0.3); display: inline-flex; align-items: center; justify-content: center; gap: 6px;" onclick="confirmUpdate('orderStatusForm', 'Konfirmasi Perbarui Status', 'Apakah Anda yakin ingin memperbarui status order ini?')">
                    <iconify-icon icon="solar:diskette-bold-duotone" style="font-size: 16px;"></iconify-icon> SIMPAN STATUS
                </button>
            </form>

            <script>
                function toggleProofInput() {
                    var status = document.getElementById('status-select').value;
                    var group = document.getElementById('proof-photo-group');
                    if (status === 'arrived') {
                        group.style.display = 'block';
                    } else {
                        group.style.display = 'none';
                    }
                }
                document.addEventListener('DOMContentLoaded', toggleProofInput);
            </script>

            <hr style="border: 0; border-top: 1px solid var(--border); margin: 16px 0;">

            {{-- Resi Form --}}
            <form id="orderResiForm" method="POST" action="{{ route('admin.orders.resi', $order) }}">
                @csrf @method('PATCH')
                <div class="form-group" style="margin-bottom: 12px;">
                    <label class="form-label" style="font-size: 12px; font-weight: 700; color: var(--text-secondary);">Nomor Resi Pengiriman</label>
                    <input type="text" name="resi_number" class="form-control" value="{{ $order->resi_number }}" placeholder="Masukkan nomor resi" style="border-radius: 0.5rem; font-size: 13px; padding: 8px 12px;">
                </div>
                <button type="button" class="btn" style="width: 100%; background: #10b981; color: #ffffff; border: none; font-weight: 800; font-size: 12px; border-radius: 0.5rem; padding: 10px; text-transform: uppercase; letter-spacing: 0.5px; display: inline-flex; align-items: center; justify-content: center; gap: 6px;" onclick="confirmUpdate('orderResiForm', 'Konfirmasi Simpan Resi', 'Apakah Anda yakin ingin menyimpan nomor resi ini?')">
                    <iconify-icon icon="solar:clipboard-check-bold-duotone" style="font-size: 16px;"></iconify-icon> SIMPAN RESI
                </button>
            </form>

            @if($order->resi_number)
            <form method="POST" action="{{ route('admin.orders.track', $order) }}" style="margin-top: 10px;">
                @csrf
                <button type="submit" class="btn" style="width: 100%; background: var(--bg-input); color: var(--text-primary); border: 1px solid var(--border); font-weight: 700; font-size: 12px; border-radius: 0.5rem; padding: 8px; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                    <iconify-icon icon="solar:restart-bold-duotone" style="font-size: 16px;"></iconify-icon> Cek Resi via RajaOngkir
                </button>
            </form>
            @endif

            {{-- ── Simulasi Kurir Serahkan Paket (Auto-POD) ── --}}
            @if(in_array($order->status, ['paid', 'packed', 'shipped', 'arrived']))
            <div style="margin-top: 18px; padding: 14px; background: rgba(59, 130, 246, 0.05); border: 1.5px dashed #3b82f6; border-radius: 0.75rem;">
                <div style="font-size: 12.5px; font-weight: 800; color: #1d4ed8; display: flex; align-items: center; gap: 6px; margin-bottom: 4px;">
                    <iconify-icon icon="solar:delivery-bold-duotone" style="font-size: 18px;"></iconify-icon> Simulasi Kurir Selesaikan Pengiriman
                </div>
                <p style="font-size: 11.5px; color: var(--text-muted); margin: 0 0 10px 0; line-height: 1.4;">
                    Uji coba serah terima paket oleh kurir. Foto bukti pengiriman (Proof of Delivery / POD) akan ter-upload secara otomatis tanpa perlu input manual admin.
                </p>
                <form id="simulatePodForm" method="POST" action="{{ route('admin.orders.simulate-pod', $order) }}">
                    @csrf
                    <button type="button" class="btn" style="width: 100%; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: #ffffff; border: none; font-weight: 800; font-size: 11.5px; border-radius: 0.5rem; padding: 9px; text-transform: uppercase; letter-spacing: 0.5px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; box-shadow: 0 4px 10px rgba(37,99,235,0.25);" onclick="confirmUpdate('simulatePodForm', 'Simulasi Auto-POD Kurir', 'Jalankan simulasi kurir menyerahkan paket dan upload otomatis foto bukti penerimaan (POD)?')">
                        <iconify-icon icon="solar:camera-minimalistic-bold-duotone" style="font-size: 16px;"></iconify-icon> 🚀 SIMULASIKAN AUTO-POD KURIR
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection