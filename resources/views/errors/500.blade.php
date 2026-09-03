@extends('errors.layout')

@section('title', '500 - Kesalahan Server')

@section('content')
<div class="error-code-badge code-500">
    <iconify-icon icon="flat-color-icons:warning_2"></iconify-icon> Error 500
</div>
<div class="error-icon-box">
    <iconify-icon icon="flat-color-icons:broken_link"></iconify-icon>
</div>
<h1 class="error-title">Terjadi Kesalahan Server</h1>
<p class="error-message">
    Terjadi kendala internal pada sistem server. Tim kami atau sistem sedang menangani masalah ini secara otomatis. Silakan coba muat ulang halaman.
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
