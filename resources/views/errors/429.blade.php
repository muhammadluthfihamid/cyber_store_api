@extends('errors.layout')

@section('title', '429 - Batas Request Terlampaui')

@section('content')
<div class="error-code-badge code-429">
    <iconify-icon icon="flat-color-icons:clock"></iconify-icon> Error 429
</div>
<div class="error-icon-box">
    <iconify-icon icon="flat-color-icons:medium_priority"></iconify-icon>
</div>
<h1 class="error-title">Batas Request Terlampaui</h1>
<p class="error-message">
    Anda telah melakukan terlalu banyak permintaan dalam waktu singkat. Untuk alasan keamanan, silakan tunggu beberapa saat sebelum mencoba kembali.
</p>
<div class="error-actions">
    <button type="button" onclick="location.reload()" class="btn-error btn-primary-error">
        <iconify-icon icon="lucide:refresh-cw"></iconify-icon> Coba Lagi
    </button>
    <a href="{{ route('admin.dashboard') }}" class="btn-error btn-secondary-error">
        <iconify-icon icon="flat-color-icons:home"></iconify-icon> Ke Dashboard
    </a>
</div>
@endsection
