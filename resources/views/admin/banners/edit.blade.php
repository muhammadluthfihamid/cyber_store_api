@extends('admin.layouts.app')

@section('title', 'Edit Banner')
@section('page-title', 'Edit Banner')
@section('breadcrumb')
    <span class="breadcrumb-sep">›</span>
    <a href="{{ route('admin.banners.index') }}">Banner</a>
    <span class="breadcrumb-sep">›</span>
    <span>Edit</span>
@endsection

@section('content')
<div style="max-width:760px;">
    <div class="card">
        <div class="card-header">
            <span class="card-title" style="display: flex; align-items: center; gap: 8px;">
                <iconify-icon icon="flat-color-icons:edit-image" style="font-size: 22px;"></iconify-icon> Edit Banner
            </span>
            <a href="{{ route('admin.banners.index') }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px;">
                <iconify-icon icon="flat-color-icons:previous"></iconify-icon> Kembali
            </a>
        </div>

        <form id="bannerEditForm" action="{{ route('admin.banners.update', $banner) }}" method="POST" enctype="multipart/form-data"
              style="padding:24px; display:flex; flex-direction:column; gap:20px;">
            @csrf
            @method('PUT')

            {{-- Judul --}}
            <div class="form-group">
                <label class="form-label">Judul Banner <span style="color:var(--text-muted); font-weight:400;">(opsional)</span></label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                       placeholder="Contoh: Promo Akhir Tahun"
                       value="{{ old('title', $banner->title) }}">
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Deskripsi --}}
            <div class="form-group">
                <label class="form-label">Deskripsi <span style="color:var(--text-muted); font-weight:400;">(opsional)</span></label>
                <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                          rows="3" placeholder="Deskripsi singkat tentang banner ini..."
                          style="resize:vertical;">{{ old('description', $banner->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Urutan --}}
            <div class="form-group" style="max-width:160px;">
                <label class="form-label">Urutan Tampil</label>
                <input type="number" name="order" class="form-control @error('order') is-invalid @enderror"
                       placeholder="0" value="{{ old('order', $banner->order) }}" min="0">
                <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">
                    Angka lebih kecil = tampil lebih awal
                </div>
                @error('order')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Gambar --}}
            <div class="form-group">
                <label class="form-label">Gambar Banner</label>

                {{-- Current preview --}}
                @if ($banner->image_path)
                    <div style="margin-bottom:12px;">
                        <div style="font-size:12px; color:var(--text-muted); margin-bottom:6px;">Gambar saat ini:</div>
                        <div style="width:100%; max-width:400px; height:130px; border-radius:10px;
                                    overflow:hidden; border:1px solid var(--border);">
                            <img src="{{ Storage::disk('public')->url($banner->image_path) }}"
                                 alt="{{ $banner->title }}"
                                 style="width:100%; height:100%; object-fit:cover;">
                        </div>
                    </div>
                @endif

                {{-- Drop Zone --}}
                <div id="dropZone"
                     style="border:2px dashed var(--border); border-radius:12px; padding:32px;
                            text-align:center; cursor:pointer; transition:border-color .2s, background .2s;
                            background:var(--bg-input);"
                     onclick="document.getElementById('imageInput').click()"
                     ondragover="onDragOver(event)" ondragleave="onDragLeave(event)" ondrop="onDrop(event)">
                    <div id="dropContent">
                        <div style="margin-bottom:8px; display:flex; justify-content:center; align-items:center;">
                            <iconify-icon icon="flat-color-icons:upload" style="font-size: 48px;"></iconify-icon>
                        </div>
                        <div style="font-weight:600; color:var(--text-primary); margin-bottom:4px;">
                            Klik atau seret untuk ganti gambar
                        </div>
                        <div style="font-size:12px; color:var(--text-muted);">
                            PNG, JPG, WEBP — maks. 5 MB &nbsp;·&nbsp; Kosongkan jika tidak ingin mengganti
                        </div>
                    </div>
                    <img id="previewImg" src="" alt="Preview baru"
                         style="display:none; max-height:200px; width:100%; object-fit:cover;
                                border-radius:8px; margin-top:12px;">
                </div>

                <input type="file" id="imageInput" name="image" accept="image/png,image/jpeg,image/webp"
                       style="display:none;" onchange="previewImage(this)">

                @error('image')
                    <div style="color:var(--danger); font-size:13px; margin-top:6px;">{{ $message }}</div>
                @enderror
            </div>

            {{-- Submit --}}
            <div style="display:flex; gap:12px; padding-top:4px; flex-wrap:wrap;">
                <button type="button" class="btn btn-primary"
                        onclick="confirmUpdate('bannerEditForm', 'Konfirmasi Edit Banner', 'Apakah Anda yakin ingin menyimpan perubahan banner ini?')"
                        style="display: inline-flex; align-items: center; gap: 6px;">
                    <iconify-icon icon="flat-color-icons:approval"></iconify-icon> Simpan Perubahan
                </button>
                <a href="{{ route('admin.banners.index') }}" class="btn btn-secondary">Batal</a>

                {{-- Quick delete from edit page --}}
                <button type="button"
                    class="btn btn-danger"
                    style="margin-left:auto; display: inline-flex; align-items: center; gap: 6px;"
                    data-url="{{ route('admin.banners.destroy', $banner) }}"
                    data-name="{{ $banner->title ?? 'banner ini' }}"
                    onclick="confirmDelete(this.dataset.url, this.dataset.name)">
                    <iconify-icon icon="fluent-emoji-flat:wastebasket" style="font-size: 16px;"></iconify-icon> Hapus Banner
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function previewImage(input) {
        const file = input.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('previewImg').style.display = 'block';
            document.getElementById('dropContent').style.display = 'none';
        };
        reader.readAsDataURL(file);
    }
    function onDragOver(e) {
        e.preventDefault();
        document.getElementById('dropZone').style.borderColor = 'var(--accent)';
        document.getElementById('dropZone').style.background = 'var(--accent-light)';
    }
    function onDragLeave(e) {
        document.getElementById('dropZone').style.borderColor = 'var(--border)';
        document.getElementById('dropZone').style.background = 'var(--bg-input)';
    }
    function onDrop(e) {
        e.preventDefault();
        onDragLeave(e);
        const file = e.dataTransfer.files[0];
        if (!file) return;
        const dt = new DataTransfer();
        dt.items.add(file);
        document.getElementById('imageInput').files = dt.files;
        previewImage(document.getElementById('imageInput'));
    }
</script>
@endpush
@endsection
