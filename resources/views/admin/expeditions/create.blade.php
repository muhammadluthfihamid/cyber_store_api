@extends('admin.layouts.app')
@section('title','Tambah Ekspedisi')
@section('page-title','Tambah Ekspedisi')
@section('breadcrumb')
<span class="breadcrumb-sep">›</span><a href="{{ route('admin.expeditions.index') }}">Ekspedisi</a>
<span class="breadcrumb-sep">›</span><span>Tambah</span>
@endsection
@section('content')
<div style="max-width:640px;">
    <div class="card">
        <div class="card-header">
            <span class="card-title"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-van-icon lucide-van">
                    <path d="M13 6v5a1 1 0 0 0 1 1h6.102a1 1 0 0 1 .712.298l.898.91a1 1 0 0 1 .288.702V17a1 1 0 0 1-1 1h-3" />
                    <path d="M5 18H3a1 1 0 0 1-1-1V8a2 2 0 0 1 2-2h12c1.1 0 2.1.8 2.4 1.8l1.176 4.2" />
                    <path d="M9 18h5" />
                    <circle cx="16" cy="18" r="2" />
                    <circle cx="7" cy="18" r="2" />
                </svg> Tambah Ekspedisi</span>
            <a href="{{ route('admin.expeditions.index') }}" class="btn btn-secondary">← Kembali</a>
        </div>
        <div class="card-body">
            <form id="expeditionCreateForm" method="POST" action="{{ route('admin.expeditions.store') }}">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="name">Nama Ekspedisi <span style="color:var(--danger)">*</span></label>
                        <input type="text" id="name" name="name" class="form-control"
                            value="{{ old('name') }}" placeholder="JNE Regular" required>
                        @error('name')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="code">Kode Unik <span style="color:var(--danger)">*</span></label>
                        <input type="text" id="code" name="code" class="form-control"
                            value="{{ old('code') }}" placeholder="jne_reg" required>
                        <div class="form-hint">Huruf kecil, tanpa spasi</div>
                        @error('code')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="service">Tipe Layanan <span style="color:var(--danger)">*</span></label>
                        <input type="text" id="service" name="service" class="form-control"
                            value="{{ old('service') }}" placeholder="REG" required>
                        <div class="form-hint">Contoh: REG, EZ, YES</div>
                        @error('service')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="estimated_days">Estimasi Hari <span style="color:var(--danger)">*</span></label>
                        <input type="number" id="estimated_days" name="estimated_days" class="form-control"
                            value="{{ old('estimated_days', 3) }}" min="1" required>
                        @error('estimated_days')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="base_cost">Biaya Dasar (Rp) <span style="color:var(--danger)">*</span></label>
                    <input type="number" id="base_cost" name="base_cost" class="form-control"
                        value="{{ old('base_cost', 8000) }}" min="0" step="500" required>
                    @error('base_cost')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-check">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input"
                            {{ old('is_active','1') ? 'checked' : '' }}>
                        <span class="form-check-label">Ekspedisi Aktif</span>
                    </label>
                </div>
                <div style="display:flex;gap:12px;justify-content:flex-end;">
                    <a href="{{ route('admin.expeditions.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="button" class="btn btn-primary" onclick="confirmCreate('expeditionCreateForm', 'Konfirmasi Tambah Ekspedisi', 'Apakah Anda yakin ingin menyimpan ekspedisi baru ini?')">💾 Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection