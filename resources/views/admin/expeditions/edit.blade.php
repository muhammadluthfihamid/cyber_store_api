@extends('admin.layouts.app')
@section('title','Edit Ekspedisi')
@section('page-title','Edit Ekspedisi')
@section('breadcrumb')
    <span class="breadcrumb-sep">›</span><a href="{{ route('admin.expeditions.index') }}">Ekspedisi</a>
    <span class="breadcrumb-sep">›</span><span>Edit</span>
@endsection
@section('content')
<div style="max-width:640px;">
<div class="card">
    <div class="card-header">
        <span class="card-title" style="display: flex; align-items: center; gap: 8px;">
            <iconify-icon icon="flat-color-icons:edit-image" style="font-size: 22px;"></iconify-icon> Edit: {{ $expedition->name }}
        </span>
        <a href="{{ route('admin.expeditions.index') }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px;">
            <iconify-icon icon="flat-color-icons:previous"></iconify-icon> Kembali
        </a>
    </div>
    <div class="card-body">
        <form id="expeditionEditForm" method="POST" action="{{ route('admin.expeditions.update', $expedition) }}">
            @csrf @method('PUT')
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="name">Nama Ekspedisi <span style="color:var(--danger)">*</span></label>
                    <input type="text" id="name" name="name" class="form-control"
                        value="{{ old('name', $expedition->name) }}" required>
                    @error('name')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="code">Kode Unik <span style="color:var(--danger)">*</span></label>
                    <input type="text" id="code" name="code" class="form-control"
                        value="{{ old('code', $expedition->code) }}" required>
                    @error('code')<div class="form-error">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="service">Tipe Layanan <span style="color:var(--danger)">*</span></label>
                    <input type="text" id="service" name="service" class="form-control"
                        value="{{ old('service', $expedition->service) }}" required>
                    @error('service')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="estimated_days">Estimasi Hari <span style="color:var(--danger)">*</span></label>
                    <input type="number" id="estimated_days" name="estimated_days" class="form-control"
                        value="{{ old('estimated_days', $expedition->estimated_days) }}" min="1" required>
                    @error('estimated_days')<div class="form-error">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="form-group">
                <label class="form-label" for="base_cost">Biaya Dasar (Rp) <span style="color:var(--danger)">*</span></label>
                <input type="number" id="base_cost" name="base_cost" class="form-control"
                    value="{{ old('base_cost', $expedition->base_cost) }}" min="0" step="500" required>
                @error('base_cost')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-check">
                    <input type="checkbox" name="is_active" value="1" class="form-check-input"
                        {{ old('is_active', $expedition->is_active) ? 'checked' : '' }}>
                    <span class="form-check-label">Ekspedisi Aktif</span>
                </label>
            </div>
            <div style="display:flex;gap:12px;justify-content:flex-end;">
                <a href="{{ route('admin.expeditions.index') }}" class="btn btn-secondary">Batal</a>
                <button type="button" class="btn btn-primary" onclick="confirmUpdate('expeditionEditForm', 'Konfirmasi Edit Ekspedisi', 'Apakah Anda yakin ingin menyimpan perubahan ekspedisi ini?')" style="display: inline-flex; align-items: center; gap: 6px;">
                    <iconify-icon icon="flat-color-icons:approval"></iconify-icon> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
</div>
@endsection
