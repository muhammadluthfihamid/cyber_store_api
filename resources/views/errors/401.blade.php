@extends('errors.layout')

@section('title', '401 - Belum Terautentikasi')

@section('content')
<div class="error-code-badge code-401">
    <iconify-icon icon="flat-color-icons:privacy"></iconify-icon> Error 401
</div>
<div class="error-icon-box">
    <iconify-icon icon="flat-color-icons:lock"></iconify-icon>
</div>
<h1 class="error-title">Sesi Tidak Ditemukan</h1>
<p class="error-message">
    Anda belum melakukan login atau sesi login Anda telah kadaluwarsa. Silakan masuk terlebih dahulu untuk mengakses halaman ini.
</p>
<div class="error-actions">
    <a href="{{ route('admin.login') }}" class="btn-error btn-primary-error">
        <iconify-icon icon="flat-color-icons:key"></iconify-icon> Halaman Login
    </a>
    <a href="{{ route('admin.dashboard') }}" class="btn-error btn-secondary-error">
        <iconify-icon icon="flat-color-icons:home"></iconify-icon> Ke Dashboard
    </a>
</div>
@endsection
