@extends('admin.layouts.app')

@section('title', 'Edit Pengguna — ' . $user->name)
@section('page-title', 'Edit Pengguna')
@section('breadcrumb')
    <span class="breadcrumb-sep">›</span>
    <a href="{{ route('admin.users.index') }}">Pengguna</a>
    <span class="breadcrumb-sep">›</span>
    <span>Edit</span>
@endsection

@section('content')
<div style="max-width: 760px;">
<div class="card">
    <div class="card-header">
        <div style="display:flex; align-items:center; gap:12px;">
            <div style="width:42px; height:42px; border-radius:50%; overflow:hidden; flex-shrink:0;">
                @if($user->photo)
                    <img src="{{ Storage::disk('public')->url($user->photo) }}" style="width:100%; height:100%; object-fit:cover;">
                @else
                    <div style="width:100%; height:100%; background:linear-gradient(135deg,#4f6ef7,#7c3aed); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:16px; color:white;">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif
            </div>
            <div>
                <span class="card-title" style="display: flex; align-items: center; gap: 8px;">
                    <iconify-icon icon="flat-color-icons:edit-image" style="font-size: 22px;"></iconify-icon> Edit: {{ $user->name }}
                </span>
                <div style="font-size:12px; color:var(--text-muted); margin-top:2px;">
                    ID #{{ $user->id }} · Bergabung {{ $user->created_at->format('d M Y') }}
                </div>
            </div>
        </div>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px;">
            <iconify-icon icon="flat-color-icons:previous"></iconify-icon> Kembali
        </a>
    </div>
    <div class="card-body">
        <form id="userEditForm" method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data">
            @csrf @method('PUT')

            {{-- ── Foto Profil ── --}}
            <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.8px; color:var(--text-muted); margin-bottom:14px; padding-bottom:8px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:6px;">
                <iconify-icon icon="flat-color-icons:picture" style="font-size: 16px;"></iconify-icon> Foto Profil
            </div>

            <div class="form-group">
                <div class="avatar-upload-wrap">
                    {{-- Preview --}}
                    <div class="avatar-preview" id="avatarPreviewWrap">
                        @if($user->photo)
                            <img id="avatarPreview" src="{{ Storage::disk('public')->url($user->photo) }}"
                                 alt="{{ $user->name }}">
                        @else
                            <div class="avatar-initials" id="avatarPreviewWrap">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>

                    <div class="avatar-upload-actions">
                        <label style="cursor:pointer;">
                            <input type="file" name="photo" id="photoInput" accept="image/jpg,image/jpeg,image/png,image/webp"
                                   style="display:none;" onchange="previewAvatar(this)">
                            <span class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px;"><iconify-icon icon="flat-color-icons:add-image"></iconify-icon> Ganti Foto</span>
                        </label>
                        <div style="font-size:11px; color:var(--text-muted);">JPG/PNG/WebP, maks 2MB</div>
                        @if($user->photo)
                            <label class="form-check" style="margin-top:4px;">
                                <input type="checkbox" name="remove_photo" value="1" class="form-check-input"
                                       id="removePhoto" onchange="handleRemovePhoto(this)">
                                <span class="form-check-label" style="font-size:12px; color:var(--danger);">Hapus foto</span>
                            </label>
                        @endif
                        <div style="font-size:11px; color:var(--text-muted);">
                            Jika kosong, akan menggunakan inisial nama sebagai avatar default.
                        </div>
                    </div>
                </div>
                @error('photo')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            {{-- ── Info Dasar ── --}}
            <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.8px; color:var(--text-muted); margin:20px 0 14px; padding-bottom:8px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:6px;">
                <iconify-icon icon="flat-color-icons:businessman" style="font-size: 16px;"></iconify-icon> Informasi Akun
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="name">Nama Lengkap <span style="color:var(--danger)">*</span></label>
                    <input type="text" id="name" name="name" class="form-control"
                        value="{{ old('name', $user->name) }}" required>
                    @error('name')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="phone">Nomor Telepon</label>
                    <input type="text" id="phone" name="phone" class="form-control"
                        value="{{ old('phone', $user->phone) }}">
                    @error('phone')<div class="form-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email <span style="color:var(--danger)">*</span></label>
                <input type="email" id="email" name="email" class="form-control"
                    value="{{ old('email', $user->email) }}" required>
                @error('email')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="role">Role <span style="color:var(--danger)">*</span></label>
                <select id="role" name="role" class="form-control" required
                    {{ (auth()->user()->role !== 'superadmin' && $user->role === 'superadmin') ? 'disabled' : '' }}>
                    <option value="customer"   {{ old('role', $user->role) === 'customer'   ? 'selected' : '' }}>Customer</option>
                    <option value="admin"      {{ old('role', $user->role) === 'admin'      ? 'selected' : '' }}>Admin</option>
                    @if(auth()->user()->role === 'superadmin')
                        <option value="superadmin" {{ old('role', $user->role) === 'superadmin' ? 'selected' : '' }}>Superadmin</option>
                    @endif
                </select>
                @error('role')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            {{-- ── Ubah Password ── --}}
            <div style="background:var(--bg-input); border:1px solid var(--border); border-radius:var(--radius-sm); padding:14px 16px; margin-bottom:20px;">
                <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:var(--text-muted); margin-bottom:10px; display:flex; align-items:center; gap:6px;">
                    <iconify-icon icon="flat-color-icons:key" style="font-size: 16px;"></iconify-icon> Ubah Password (Opsional)
                </div>
                <div class="form-row">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" for="password">Password Baru</label>
                        <input type="password" id="password" name="password" class="form-control"
                            placeholder="Kosongkan jika tidak diubah">
                        @error('password')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" for="password_confirmation">Konfirmasi Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"
                            placeholder="Ulangi password baru">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-check">
                    <input type="checkbox" name="is_active" value="1" class="form-check-input"
                        {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                        {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                    <span class="form-check-label">
                        Akun Aktif
                        @if($user->id === auth()->id())
                            <span style="font-size:11px; color:var(--text-muted);">(Tidak dapat menonaktifkan akun sendiri)</span>
                        @endif
                    </span>
                </label>
                @if($user->id === auth()->id())
                    <input type="hidden" name="is_active" value="1">
                @endif
            </div>

            <div style="display:flex; gap:12px; justify-content:flex-end; padding-top:8px; border-top:1px solid var(--border);">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Batal</a>
                <button type="button" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;" onclick="confirmUpdate('userEditForm', 'Konfirmasi Edit Pengguna', 'Apakah Anda yakin ingin menyimpan perubahan data pengguna ini?')"><iconify-icon icon="flat-color-icons:approval"></iconify-icon> Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
</div>

<script>
function handleRemovePhoto(cb) {
    const photoInput = document.getElementById('photoInput');
    const userHasPhoto = Boolean({{ $user->photo ? 1 : 0 }});
    const userPhotoUrl = "{{ $user->photo ? \Storage::disk('public')->url($user->photo) : '' }}";

    if (cb.checked) {
        photoInput.value = '';
        photoInput.disabled = true;
        document.getElementById('avatarPreviewWrap').innerHTML =
            `<div class="avatar-initials">{{ strtoupper(substr($user->name, 0, 1)) }}</div>`;
    } else {
        photoInput.disabled = false;
        if (userHasPhoto) {
            document.getElementById('avatarPreviewWrap').innerHTML =
                `<img id="avatarPreview" src="${userPhotoUrl}" style="width:100%;height:100%;object-fit:cover;">`;
        }
    }
}
</script>
@endsection
