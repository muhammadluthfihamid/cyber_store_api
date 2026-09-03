@extends('errors.layout')

@section('title', '419 - Sesi Berakhir')

@section('content')
<div class="error-code-badge code-419">
    <iconify-icon icon="flat-color-icons:expired"></iconify-icon> Error 419
</div>
<div class="error-icon-box">
    <iconify-icon icon="flat-color-icons:alarm_clock"></iconify-icon>
</div>
<h1 class="error-title">Sesi Halaman Berakhir</h1>
<p class="error-message">
    Token keamanan (CSRF) atau sesi login Anda telah kedaluwarsa karena tidak ada aktivitas. Silakan muat ulang halaman untuk melanjutkan.
</p>
<div class="error-actions">
    <button type="button" onclick="location.reload()" class="btn-error btn-primary-error">
        <iconify-icon icon="lucide:refresh-cw"></iconify-icon> Muat Ulang Halaman
    </button>
    <a href="{{ route('admin.dashboard') }}" class="btn-error btn-secondary-error">
        <iconify-icon icon="flat-color-icons:home"></iconify-icon> Ke Dashboard
    </a>
</div>
@endsection
