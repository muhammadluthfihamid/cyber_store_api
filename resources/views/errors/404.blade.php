@extends('errors.layout')

@section('title', '404 - Halaman Tidak Ditemukan')

@section('content')
<div class="error-code-badge code-404">
    <iconify-icon icon="flat-color-icons:error"></iconify-icon> Error 404
</div>
<div class="error-icon-box">
    <iconify-icon icon="flat-color-icons:search"></iconify-icon>
</div>
<h1 class="error-title">Halaman Tidak Ditemukan</h1>
<p class="error-message">
    Maaf, halaman atau data yang Anda cari tidak tersedia, telah dihapus, atau alamat URL yang Anda masukkan salah.
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
