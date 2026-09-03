@extends('errors.layout')

@section('title', '403 - Akses Ditolak')

@section('content')
<div class="error-code-badge code-403">
    <iconify-icon icon="flat-color-icons:key"></iconify-icon> Error 403
</div>
<div class="error-icon-box">
    <iconify-icon icon="flat-color-icons:lock"></iconify-icon>
</div>
<h1 class="error-title">Akses Ditolak</h1>
<p class="error-message">
    Anda tidak memiliki hak akses atau izin yang cukup untuk membuka halaman/tindakan ini.
</p>
<div class="error-actions">
    <a href="{{ route('admin.dashboard') }}" class="btn-error btn-primary-error">
        <iconify-icon icon="flat-color-icons:home"></iconify-icon> Kembali ke Dashboard
    </a>
    <button type="button" onclick="history.back()" class="btn-error btn-secondary-error">
        <iconify-icon icon="lucide:arrow-left"></iconify-icon> Kembali
    </button>
</div>
@endsection
