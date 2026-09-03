<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Mutasi Stok</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333333;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        .header {
            margin-bottom: 20px;
            border-bottom: 2px solid #0d47a1;
            padding-bottom: 10px;
        }

        .header h1 {
            font-size: 20px;
            color: #0d47a1;
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }

        .header p {
            margin: 0;
            color: #666;
            font-size: 11px;
        }

        .meta-info {
            margin-bottom: 15px;
        }

        .meta-info table {
            width: 100%;
        }

        .meta-info td {
            padding: 2px 0;
        }

        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .main-table th {
            background-color: #0d47a1;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 6px 8px;
            font-size: 10px;
            border: 1px solid #0d47a1;
        }

        .main-table td {
            padding: 6px 8px;
            border: 1px solid #e0e0e0;
            font-size: 10px;
            vertical-align: top;
        }

        .main-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
        }

        .badge-in {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .badge-out {
            background-color: #ffebee;
            color: #c62828;
        }

        .text-success {
            color: #2e7d32;
            font-weight: bold;
        }

        .text-danger {
            color: #c62828;
            font-weight: bold;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #999;
            border-top: 1px solid #e0e0e0;
            padding-top: 5px;
        }
    </style>
</head>

<body>

    @php
    $logoSetting = \App\Models\Setting::get('store_logo');
    $logoPath = null;
    if ($logoSetting && \Storage::disk('public')->exists($logoSetting)) {
    $logoPath = storage_path('app/public/' . $logoSetting);
    } elseif (file_exists(public_path('assets/img/logo-cyberstore.jpg'))) {
    $logoPath = public_path('assets/img/logo-cyberstore.jpg');
    }
    @endphp

    <div class="header" style="text-align: center;">
        @if($logoPath && file_exists($logoPath))
        <img src="{{ $logoPath }}" alt="Logo Toko" style="max-height: 55px; width: auto; margin-bottom: 6px; display: inline-block;">
        @endif
        <h1 style="text-align: center; margin: 4px 0 2px 0;">{{ strtoupper(\App\Models\Setting::get('store_name', 'BSI Cyber Store')) }}</h1>
        <p style="font-size: 10.5px; color: #444; margin-top: 2px; margin-bottom: 6px; text-align: center;">
            {{ \App\Models\Setting::get('store_address', 'Jl. Kramat Raya No.98, Senen, Jakarta Pusat') }}
        </p>
        <p style="font-weight: bold; color: #0d47a1; text-align: center; font-size: 12.5px; margin-top: 6px;">
            LAPORAN MUTASI STOK BARANG
        </p>
    </div>

    <div class="meta-info">
        <table cellspacing="0" cellpadding="0">
            <tr>
                <td style="width: 15%; font-weight: bold;">Dicetak Oleh:</td>
                <td style="width: 35%;">{{ auth()->user()?->name ?? 'Administrator' }}</td>
                <td style="width: 18%; font-weight: bold; text-align: right;">Waktu Cetak:</td>
                <td style="width: 32%; text-align: right;">{{ now()->format('d M Y, H:i:s') }} WIB</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Total Data:</td>
                <td>{{ $movements->count() }} records</td>
                <td></td>
                <td></td>
            </tr>
        </table>
    </div>

    <table class="main-table" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 25%;">Produk</th>
                <th style="width: 10%;">Tipe</th>
                <th style="width: 10%; text-align: right;">Qty</th>
                <th style="width: 15%;">Referensi</th>
                <th style="width: 18%;">Catatan</th>
                <th style="width: 17%;">Waktu &amp; Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movements as $index => $mov)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                    <strong>{{ $mov->product?->name ?? '—' }}</strong>
                    @if($mov->product)
                    <div style="font-size: 8px; color: #777;">Stok Akhir: {{ $mov->product->stock }}</div>
                    @endif
                </td>
                <td>
                    <span class="badge {{ $mov->type === 'in' ? 'badge-in' : 'badge-out' }}">
                        {{ $mov->type === 'in' ? 'Masuk' : 'Keluar' }}
                    </span>
                </td>
                <td style="text-align: right;" class="{{ $mov->type === 'in' ? 'text-success' : 'text-danger' }}">
                    {{ $mov->type === 'in' ? '+' : '-' }}{{ $mov->quantity }}
                </td>
                <td>{{ $mov->reference ?? '—' }}</td>
                <td>{{ $mov->note ?? '—' }}</td>
                <td>{{ $mov->created_at->format('d M Y, H:i:s') }} WIB</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center;">Tidak ada data mutasi stok ditemukan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dokumen ini dibuat otomatis oleh Sistem {{ \App\Models\Setting::get('store_name', 'BSI Cyber Store') }} pada {{ now()->format('d M Y, H:i:s') }} WIB.
    </div>

</body>

</html>