@extends('admin.layouts.app')
@section('title','Tambah Pengguna')
@section('page-title','Tambah Pengguna')
@section('breadcrumb')
    <span class="breadcrumb-sep">›</span>
    <a href="{{ route('admin.users.index') }}">Pengguna</a>
    <span class="breadcrumb-sep">›</span>
    <span>Tambah</span>
@endsection
@section('content')
<div style="max-width:760px;">
<div class="card">
    <div class="card-header">
        <span class="card-title" style="display: flex; align-items: center; gap: 8px;">
            <iconify-icon icon="flat-color-icons:businessman" style="font-size: 22px;"></iconify-icon> Tambah Pengguna Baru
        </span>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px;">
            <iconify-icon icon="flat-color-icons:previous"></iconify-icon> Kembali
        </a>
    </div>
    <div class="card-body">
        <form id="userCreateForm" method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data">
            @csrf

            {{-- Foto Profil --}}
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);margin-bottom:14px;padding-bottom:8px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:6px;">
                <iconify-icon icon="flat-color-icons:picture" style="font-size: 16px;"></iconify-icon> Foto Profil
            </div>
            <div class="form-group">
                <div class="avatar-upload-wrap">
                    <div class="avatar-preview" id="avatarPreviewWrap">
                        <div class="avatar-initials" id="avatarInitials" style="display: flex; align-items: center; justify-content: center;"><iconify-icon icon="flat-color-icons:businessman" style="font-size: 24px;"></iconify-icon></div>
                    </div>
                    <div class="avatar-upload-actions">
                        <label style="cursor:pointer;">
                            <input type="file" name="photo" accept="image/jpg,image/jpeg,image/png,image/webp"
                                   style="display:none;" onchange="previewAvatar(this)">
                            <span class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px;"><iconify-icon icon="flat-color-icons:add-image"></iconify-icon> Pilih Foto</span>
                        </label>
                        <div style="font-size:11px;color:var(--text-muted);">JPG/PNG/WebP, maks 2MB</div>
                        <div style="font-size:11px;color:var(--text-muted);">Jika kosong, akan menggunakan inisial nama sebagai avatar.</div>
                    </div>
                </div>
                @error('photo')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            {{-- Info Akun --}}
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);margin:20px 0 14px;padding-bottom:8px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:6px;">
                <iconify-icon icon="flat-color-icons:businessman" style="font-size: 16px;"></iconify-icon> Informasi Akun
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="name">Nama Lengkap <span style="color:var(--danger)">*</span></label>
                    <input type="text" id="name" name="name" class="form-control"
                        value="{{ old('name') }}" placeholder="Masukkan nama lengkap" required>
                    @error('name')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="phone">Nomor Telepon</label>
                    <input type="text" id="phone" name="phone" class="form-control"
                        value="{{ old('phone') }}" placeholder="08xxxxxxxxxx">
                    @error('phone')<div class="form-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email <span style="color:var(--danger)">*</span></label>
                <input type="email" id="email" name="email" class="form-control"
                    value="{{ old('email') }}" placeholder="user@example.com" required>
                @error('email')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="role">Role <span style="color:var(--danger)">*</span></label>
                <select id="role" name="role" class="form-control" required>
                    <option value="">— Pilih Role —</option>
                    <option value="customer"   {{ old('role')==='customer'  ?'selected':'' }}>Customer</option>
                    <option value="admin"      {{ old('role')==='admin'     ?'selected':'' }}>Admin</option>
                    @if(auth()->user()->role==='superadmin')
                        <option value="superadmin" {{ old('role')==='superadmin'?'selected':'' }}>Superadmin</option>
                    @endif
                </select>
                <div class="form-hint">Superadmin: akses penuh · Admin: kelola toko · Customer: belanja</div>
                @error('role')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            {{-- Password --}}
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="password">Password <span style="color:var(--danger)">*</span></label>
                    <input type="password" id="password" name="password" class="form-control"
                        placeholder="Minimal 6 karakter" required>
                    @error('password')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="password_confirmation">Konfirmasi Password <span style="color:var(--danger)">*</span></label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"
                        placeholder="Ulangi password" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-check">
                    <input type="checkbox" name="is_active" value="1" class="form-check-input"
                        {{ old('is_active','1') ? 'checked' : '' }}>
                    <span class="form-check-label">Akun Aktif</span>
                </label>
            </div>

            <div style="display:flex;gap:12px;justify-content:flex-end;padding-top:8px;border-top:1px solid var(--border);">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Batal</a>
                <button type="button" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;" onclick="confirmCreate('userCreateForm', 'Konfirmasi Tambah Pengguna', 'Apakah Anda yakin ingin menyimpan pengguna baru ini?')"><iconify-icon icon="flat-color-icons:approval"></iconify-icon> Simpan Pengguna</button>
            </div>
        </form>
    </div>
</div>
</div>
@endsection
