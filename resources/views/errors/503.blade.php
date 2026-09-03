@extends('errors.layout')

@section('title', '503 - Sistem Dalam Pemeliharaan')

@section('content')
<div class="error-code-badge code-503">
    <iconify-icon icon="flat-color-icons:engineering"></iconify-icon> Maintenance 503
</div>
<div class="error-icon-box">
    <iconify-icon icon="flat-color-icons:services"></iconify-icon>
</div>
<h1 class="error-title">Sistem Dalam Pemeliharaan</h1>
<p class="error-message">
    Saat ini sistem sedang dalam pemeliharaan rutin atau peningkatan kualitas layanan. Silakan kembali dalam beberapa menit.
</p>
<div class="error-actions">
    <button type="button" onclick="location.reload()" class="btn-error btn-primary-error">
        <iconify-icon icon="lucide:refresh-cw"></iconify-icon> Coba Lagi
    </button>
</div>
@endsection
