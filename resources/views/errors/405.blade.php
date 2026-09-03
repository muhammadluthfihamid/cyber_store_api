@extends('errors.layout')

@section('title', '405 - Metode Tidak Diizinkan')

@section('content')
<div class="error-code-badge code-405">
    <iconify-icon icon="flat-color-icons:disclaimer"></iconify-icon> Error 405
</div>
<div class="error-icon-box">
    <iconify-icon icon="flat-color-icons:cancel"></iconify-icon>
</div>
<h1 class="error-title">Metode HTTP Tidak Diizinkan</h1>
<p class="error-message">
    Metode permintaan (HTTP Method) yang Anda gunakan tidak didukung untuk halaman ini. Silakan periksa kembali URL atau tombol aksi Anda.
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
