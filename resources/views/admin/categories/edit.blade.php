@extends('admin.layouts.app')
@section('title','Edit Kategori')
@section('page-title','Edit Kategori')
@section('breadcrumb')
    <span class="breadcrumb-sep">›</span><a href="{{ route('admin.categories.index') }}">Kategori</a>
    <span class="breadcrumb-sep">›</span><span>Edit</span>
@endsection
@section('content')
<div style="max-width:600px;">
<div class="card">
    <div class="card-header">
        <span class="card-title" style="display: flex; align-items: center; gap: 8px;">
            <iconify-icon icon="flat-color-icons:edit-image" style="font-size: 22px;"></iconify-icon> Edit: {{ $category->name }}
        </span>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px;">
            <iconify-icon icon="flat-color-icons:previous"></iconify-icon> Kembali
        </a>
    </div>
    <div class="card-body">
        <form id="categoryEditForm" method="POST" action="{{ route('admin.categories.update', $category) }}">
            @csrf @method('PUT')
            <div class="form-group">
                <label class="form-label" for="name">Nama Kategori <span style="color:var(--danger)">*</span></label>
                <input type="text" id="name" name="name" class="form-control"
                    value="{{ old('name', $category->name) }}" required>
                @error('name')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Slug Saat Ini</label>
                <code style="display:block;background:var(--bg-input);border:1px solid var(--border);border-radius:var(--radius-sm);padding:10px 14px;color:var(--accent);font-size:13px;">{{ $category->slug }}</code>
                <div class="form-hint">Slug akan diperbarui otomatis jika nama berubah.</div>
            </div>
            <div class="form-group">
                <label class="form-label" for="description">Deskripsi</label>
                <textarea id="description" name="description" class="form-control" rows="3">{{ old('description', $category->description) }}</textarea>
                @error('description')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-check">
                    <input type="checkbox" name="is_active" value="1" class="form-check-input"
                        {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                    <span class="form-check-label">Kategori Aktif</span>
                </label>
            </div>
            <div style="display:flex;gap:12px;justify-content:flex-end;">
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Batal</a>
                <button type="button" class="btn btn-primary" onclick="confirmUpdate('categoryEditForm', 'Konfirmasi Edit Kategori', 'Apakah Anda yakin ingin menyimpan perubahan kategori ini?')" style="display: inline-flex; align-items: center; gap: 6px;">
                    <iconify-icon icon="flat-color-icons:approval"></iconify-icon> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
</div>
@endsection
