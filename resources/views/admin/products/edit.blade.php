@extends('admin.layouts.app')

@section('title', 'Edit Produk — ' . $product->name)
@section('page-title', 'Edit Produk')
@section('breadcrumb')
<span class="breadcrumb-sep">›</span>
<a href="{{ route('admin.products.index') }}">Produk</a>
<span class="breadcrumb-sep">›</span>
<span>Edit</span>
@endsection

@section('content')
<style>
    .size-options-container {
        margin-bottom: 20px;
        background: rgba(79, 70, 229, 0.03);
        border: 1px dashed rgba(79, 70, 229, 0.25);
        border-radius: var(--radius-md, 8px);
        padding: 16px;
    }
    .maba-config-container {
        margin-top: 18px;
        background: rgba(139, 92, 246, 0.05);
        border: 1.5px dashed rgba(139, 92, 246, 0.4);
        border-radius: var(--radius-md, 8px);
        padding: 18px;
    }
</style>
<div style="max-width: 1000px; margin: 0 auto;">
    <div class="card">
        <div class="card-header">
            <span class="card-title" style="display: flex; align-items: center; gap: 8px;">
                <iconify-icon icon="flat-color-icons:edit-image" style="font-size: 22px;"></iconify-icon> Edit: {{ \Illuminate\Support\Str::limit($product->name, 40) }}
            </span>
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px;">
                <iconify-icon icon="flat-color-icons:previous"></iconify-icon> Kembali
            </a>
        </div>
        <div class="card-body">
            <form id="productEditForm" method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
                @csrf @method('PUT')

                {{-- Gambar Produk --}}
                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);margin-bottom:14px;padding-bottom:8px;border-bottom:1px solid var(--border); display: flex; align-items: center; gap: 6px;">
                    <iconify-icon icon="flat-color-icons:multiple-cameras" style="font-size: 18px;"></iconify-icon> Gambar Produk
                </div>

                {{-- Bulk Upload Dropzone --}}
                <div id="bulkDropzone" class="bulk-dropzone"
                    style="border: 2px dashed #4f46e5; background: #f8fafc; border-radius: var(--radius-md, 8px); padding: 20px; text-align: center; margin-bottom: 20px; cursor: pointer; transition: all 0.2s ease;">
                    <input type="file" id="bulkImageInput" multiple accept="image/jpg,image/jpeg,image/png,image/webp" style="display:none;" onchange="handleBulkImageUpload(this.files)">
                    <div style="font-size: 36px; margin-bottom: 8px; color: #4f46e5; display: flex; justify-content: center; align-items: center;">
                        <iconify-icon icon="flat-color-icons:opened-folder"></iconify-icon>
                    </div>
                    <div style="font-weight: 700; font-size: 14px; color: #1e293b; margin-bottom: 4px;">Upload Banyak Foto Sekaligus (Drag & Drop)</div>
                    <div style="font-size: 12px; color: #64748b; margin-bottom: 12px;">Pilih atau tarik hingga 6 gambar sekaligus. Foto akan otomatis mengisi Gambar 1 (Utama) hingga Gambar 6.</div>
                    <button type="button" class="btn btn-primary" onclick="document.getElementById('bulkImageInput').click()" style="font-size: 12px; padding: 8px 16px; display: inline-flex; align-items: center; gap: 6px;">
                        <iconify-icon icon="flat-color-icons:folder"></iconify-icon> Pilih Banyak Foto Sekaligus
                    </button>
                </div>

                <div class="form-row" style="gap: 16px; margin-bottom: 20px;">
                    {{-- Gambar 1 (Utama) --}}
                    <div class="form-group" style="flex: 1; min-width: 220px;">
                        <label class="form-label" style="font-weight: 600;">Gambar 1 (Utama)</label>
                        <div class="avatar-upload-wrap">
                            <div class="avatar-preview" id="avatarPreviewWrap"
                                style="border-radius: var(--radius-sm); width: 100px; height: 100px;">
                                @if ($product->main_photo)
                                <img src="{{ Storage::disk('public')->url($product->main_photo) }}"
                                    alt="{{ $product->name }}"
                                    style="width: 100%; height: 100%; object-fit: cover; border-radius: var(--radius-sm);">
                                @else
                                <div class="avatar-initials" style="border-radius: var(--radius-sm); font-size: 28px;">
                                    <iconify-icon icon="flat-color-icons:box"></iconify-icon>
                                </div>
                                @endif
                            </div>
                            <div class="avatar-upload-actions">
                                <label style="cursor:pointer;">
                                    <input type="file" name="main_photo" id="mainPhotoInput"
                                        accept="image/jpg,image/jpeg,image/png,image/webp" style="display:none;"
                                        onchange="previewPhoto(this, 'avatarPreviewWrap', 'removePhoto')">
                                    <span class="btn btn-secondary" style="font-size: 11px; padding: 4px 8px; display: inline-flex; align-items: center; gap: 4px;">
                                        <iconify-icon icon="flat-color-icons:add-image"></iconify-icon> Ganti
                                    </span>
                                </label>
                                @if ($product->main_photo)
                                <label class="form-check" style="margin-top:4px;">
                                    <input type="checkbox" name="remove_main_photo" value="1"
                                        class="form-check-input" id="removePhoto" onchange="handleRemovePhoto(this, 'mainPhotoInput', 'avatarPreviewWrap', 'defaultPhotoHtml')">
                                    <span class="form-check-label" style="font-size:11px; color:var(--danger); display: inline-flex; align-items: center; gap: 2px;">
                                        <iconify-icon icon="fluent-emoji-flat:wastebasket"></iconify-icon> Hapus
                                    </span>
                                </label>
                                @endif
                            </div>
                        </div>
                        @error('main_photo')
                        <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Gambar 2 --}}
                    @php
                    $img2 = $product->images->where('sort_order', 1)->first();
                    @endphp
                    <div class="form-group" style="flex: 1; min-width: 220px;">
                        <label class="form-label" style="font-weight: 600;">Gambar 2</label>
                        <div class="avatar-upload-wrap">
                            <div class="avatar-preview" id="previewWrap2"
                                style="border-radius: var(--radius-sm); width: 100px; height: 100px;">
                                @if ($img2)
                                <img src="{{ Storage::disk('public')->url($img2->image) }}"
                                    style="width: 100%; height: 100%; object-fit: cover; border-radius: var(--radius-sm);">
                                @else
                                <div class="avatar-initials" style="border-radius: var(--radius-sm); font-size: 28px;">
                                    <iconify-icon icon="flat-color-icons:picture"></iconify-icon>
                                </div>
                                @endif
                            </div>
                            <div class="avatar-upload-actions">
                                <label style="cursor:pointer;">
                                    <input type="file" name="photo_2" id="photo2Input"
                                        accept="image/jpg,image/jpeg,image/png,image/webp" style="display:none;"
                                        onchange="previewPhoto(this, 'previewWrap2', 'removePhoto2')">
                                    <span class="btn btn-secondary" style="font-size: 11px; padding: 4px 8px; display: inline-flex; align-items: center; gap: 4px;">
                                        <iconify-icon icon="flat-color-icons:add-image"></iconify-icon> Ganti
                                    </span>
                                </label>
                                @if ($img2)
                                <label class="form-check" style="margin-top:4px;">
                                    <input type="checkbox" name="remove_photo_2" value="1"
                                        class="form-check-input" id="removePhoto2" onchange="handleRemovePhoto(this, 'photo2Input', 'previewWrap2', 'emptyPhotoHtml')">
                                    <span class="form-check-label" style="font-size:11px; color:var(--danger); display: inline-flex; align-items: center; gap: 2px;">
                                        <iconify-icon icon="fluent-emoji-flat:wastebasket"></iconify-icon> Hapus
                                    </span>
                                </label>
                                @endif
                            </div>
                        </div>
                        @error('photo_2')
                        <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Gambar 3 --}}
                    @php
                    $img3 = $product->images->where('sort_order', 2)->first();
                    @endphp
                    <div class="form-group" style="flex: 1; min-width: 220px;">
                        <label class="form-label" style="font-weight: 600;">Gambar 3</label>
                        <div class="avatar-upload-wrap">
                            <div class="avatar-preview" id="previewWrap3"
                                style="border-radius: var(--radius-sm); width: 100px; height: 100px;">
                                @if ($img3)
                                <img src="{{ Storage::disk('public')->url($img3->image) }}"
                                    style="width: 100%; height: 100%; object-fit: cover; border-radius: var(--radius-sm);">
                                @else
                                <div class="avatar-initials" style="border-radius: var(--radius-sm); font-size: 28px;">
                                    <iconify-icon icon="flat-color-icons:picture"></iconify-icon>
                                </div>
                                @endif
                            </div>
                            <div class="avatar-upload-actions">
                                <label style="cursor:pointer;">
                                    <input type="file" name="photo_3" id="photo3Input"
                                        accept="image/jpg,image/jpeg,image/png,image/webp" style="display:none;"
                                        onchange="previewPhoto(this, 'previewWrap3', 'removePhoto3')">
                                    <span class="btn btn-secondary" style="font-size: 11px; padding: 4px 8px; display: inline-flex; align-items: center; gap: 4px;">
                                        <iconify-icon icon="flat-color-icons:add-image"></iconify-icon> Ganti
                                    </span>
                                </label>
                                @if ($img3)
                                <label class="form-check" style="margin-top:4px;">
                                    <input type="checkbox" name="remove_photo_3" value="1"
                                        class="form-check-input" id="removePhoto3" onchange="handleRemovePhoto(this, 'photo3Input', 'previewWrap3', 'emptyPhotoHtml')">
                                    <span class="form-check-label" style="font-size:11px; color:var(--danger); display: inline-flex; align-items: center; gap: 2px;">
                                        <iconify-icon icon="fluent-emoji-flat:wastebasket"></iconify-icon> Hapus
                                    </span>
                                </label>
                                @endif
                            </div>
                        </div>
                        @error('photo_3')
                        <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row" style="gap: 16px; margin-bottom: 20px; flex-wrap: wrap;">
                    {{-- Gambar 4 --}}
                    @php
                    $img4 = $product->images->where('sort_order', 3)->first();
                    @endphp
                    <div class="form-group" style="flex: 1; min-width: 220px;">
                        <label class="form-label" style="font-weight: 600;">Gambar 4</label>
                        <div class="avatar-upload-wrap">
                            <div class="avatar-preview" id="previewWrap4"
                                style="border-radius: var(--radius-sm); width: 100px; height: 100px;">
                                @if ($img4)
                                <img src="{{ Storage::disk('public')->url($img4->image) }}"
                                    style="width: 100%; height: 100%; object-fit: cover; border-radius: var(--radius-sm);">
                                @else
                                <div class="avatar-initials" style="border-radius: var(--radius-sm); font-size: 28px;">
                                    <iconify-icon icon="flat-color-icons:picture"></iconify-icon>
                                </div>
                                @endif
                            </div>
                            <div class="avatar-upload-actions">
                                <label style="cursor:pointer;">
                                    <input type="file" name="photo_4" id="photo4Input"
                                        accept="image/jpg,image/jpeg,image/png,image/webp" style="display:none;"
                                        onchange="previewPhoto(this, 'previewWrap4', 'removePhoto4')">
                                    <span class="btn btn-secondary" style="font-size: 11px; padding: 4px 8px; display: inline-flex; align-items: center; gap: 4px;">
                                        <iconify-icon icon="flat-color-icons:add-image"></iconify-icon> Ganti
                                    </span>
                                </label>
                                @if ($img4)
                                <label class="form-check" style="margin-top:4px;">
                                    <input type="checkbox" name="remove_photo_4" value="1"
                                        class="form-check-input" id="removePhoto4" onchange="handleRemovePhoto(this, 'photo4Input', 'previewWrap4', 'emptyPhotoHtml')">
                                    <span class="form-check-label" style="font-size:11px; color:var(--danger); display: inline-flex; align-items: center; gap: 2px;">
                                        <iconify-icon icon="fluent-emoji-flat:wastebasket"></iconify-icon> Hapus
                                    </span>
                                </label>
                                @endif
                            </div>
                        </div>
                        @error('photo_4')
                        <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Gambar 5 --}}
                    @php
                    $img5 = $product->images->where('sort_order', 4)->first();
                    @endphp
                    <div class="form-group" style="flex: 1; min-width: 220px;">
                        <label class="form-label" style="font-weight: 600;">Gambar 5</label>
                        <div class="avatar-upload-wrap">
                            <div class="avatar-preview" id="previewWrap5"
                                style="border-radius: var(--radius-sm); width: 100px; height: 100px;">
                                @if ($img5)
                                <img src="{{ Storage::disk('public')->url($img5->image) }}"
                                    style="width: 100%; height: 100%; object-fit: cover; border-radius: var(--radius-sm);">
                                @else
                                <div class="avatar-initials" style="border-radius: var(--radius-sm); font-size: 28px;">
                                    <iconify-icon icon="flat-color-icons:picture"></iconify-icon>
                                </div>
                                @endif
                            </div>
                            <div class="avatar-upload-actions">
                                <label style="cursor:pointer;">
                                    <input type="file" name="photo_5" id="photo5Input"
                                        accept="image/jpg,image/jpeg,image/png,image/webp" style="display:none;"
                                        onchange="previewPhoto(this, 'previewWrap5', 'removePhoto5')">
                                    <span class="btn btn-secondary" style="font-size: 11px; padding: 4px 8px; display: inline-flex; align-items: center; gap: 4px;">
                                        <iconify-icon icon="flat-color-icons:add-image"></iconify-icon> Ganti
                                    </span>
                                </label>
                                @if ($img5)
                                <label class="form-check" style="margin-top:4px;">
                                    <input type="checkbox" name="remove_photo_5" value="1"
                                        class="form-check-input" id="removePhoto5" onchange="handleRemovePhoto(this, 'photo5Input', 'previewWrap5', 'emptyPhotoHtml')">
                                    <span class="form-check-label" style="font-size:11px; color:var(--danger); display: inline-flex; align-items: center; gap: 2px;">
                                        <iconify-icon icon="fluent-emoji-flat:wastebasket"></iconify-icon> Hapus
                                    </span>
                                </label>
                                @endif
                            </div>
                        </div>
                        @error('photo_5')
                        <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Gambar 6 --}}
                    @php
                    $img6 = $product->images->where('sort_order', 5)->first();
                    @endphp
                    <div class="form-group" style="flex: 1; min-width: 220px;">
                        <label class="form-label" style="font-weight: 600;">Gambar 6</label>
                        <div class="avatar-upload-wrap">
                            <div class="avatar-preview" id="previewWrap6"
                                style="border-radius: var(--radius-sm); width: 100px; height: 100px;">
                                @if ($img6)
                                <img src="{{ Storage::disk('public')->url($img6->image) }}"
                                    style="width: 100%; height: 100%; object-fit: cover; border-radius: var(--radius-sm);">
                                @else
                                <div class="avatar-initials" style="border-radius: var(--radius-sm); font-size: 28px;">
                                    <iconify-icon icon="flat-color-icons:picture"></iconify-icon>
                                </div>
                                @endif
                            </div>
                            <div class="avatar-upload-actions">
                                <label style="cursor:pointer;">
                                    <input type="file" name="photo_6" id="photo6Input"
                                        accept="image/jpg,image/jpeg,image/png,image/webp" style="display:none;"
                                        onchange="previewPhoto(this, 'previewWrap6', 'removePhoto6')">
                                    <span class="btn btn-secondary" style="font-size: 11px; padding: 4px 8px; display: inline-flex; align-items: center; gap: 4px;">
                                        <iconify-icon icon="flat-color-icons:add-image"></iconify-icon> Ganti
                                    </span>
                                </label>
                                @if ($img6)
                                <label class="form-check" style="margin-top:4px;">
                                    <input type="checkbox" name="remove_photo_6" value="1"
                                        class="form-check-input" id="removePhoto6" onchange="handleRemovePhoto(this, 'photo6Input', 'previewWrap6', 'emptyPhotoHtml')">
                                    <span class="form-check-label" style="font-size:11px; color:var(--danger); display: inline-flex; align-items: center; gap: 2px;">
                                        <iconify-icon icon="fluent-emoji-flat:wastebasket"></iconify-icon> Hapus
                                    </span>
                                </label>
                                @endif
                            </div>
                        </div>
                        @error('photo_6')
                        <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Info Dasar --}}
                <div style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.8px; color:var(--text-muted); margin-bottom:14px; padding-bottom:8px; border-bottom:1px solid var(--border); display: flex; align-items: center; gap: 6px;">
                    <iconify-icon icon="flat-color-icons:document" style="font-size: 18px;"></iconify-icon> Informasi Dasar
                </div>

                <div class="form-group">
                    <label class="form-label" for="name">Nama Produk <span
                            style="color:var(--danger)">*</span></label>
                    <input type="text" id="name" name="name" class="form-control"
                        value="{{ old('name', $product->name) }}" required>
                    @error('name')
                    <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="category_id">Kategori <span
                                style="color:var(--danger)">*</span></label>
                        <select id="category_id" name="category_id" class="form-control" required>
                            @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}"
                                {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('category_id')
                        <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="sku">SKU</label>
                        <input type="text" id="sku" name="sku" class="form-control"
                            value="{{ old('sku', $product->sku) }}">
                        @error('sku')
                        <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">Deskripsi Produk</label>
                    <textarea id="description" name="description" class="form-control" rows="4">{{ old('description', $product->description) }}</textarea>
                    @error('description')
                    <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Harga & Stok --}}
                <div style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.8px; color:var(--text-muted); margin:20px 0 14px; padding-bottom:8px; border-bottom:1px solid var(--border); display: flex; align-items: center; gap: 6px;">
                    <iconify-icon icon="flat-color-icons:money-transfer" style="font-size: 18px;"></iconify-icon> Harga & Stok
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="price">Harga Jual (Rp) <span
                                style="color:var(--danger)">*</span></label>
                        <input type="number" id="price" name="price" class="form-control"
                            value="{{ old('price', $product->price) }}" min="0" step="500" required>
                        @error('price')
                        <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="original_price">Harga Asli / Coret (Rp)</label>
                        <input type="number" id="original_price" name="original_price" class="form-control"
                            value="{{ old('original_price', $product->original_price) }}" min="0"
                            step="500">
                        @error('original_price')
                        <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="stock">Stok <span
                                style="color:var(--danger)">*</span></label>
                        <input type="number" id="stock" name="stock" class="form-control"
                            value="{{ old('stock', $product->stock) }}" min="0" required>
                        <div id="stock-alert-hint" style="margin-top: 6px;">
                            @if(old('stock', $product->stock) !== null)
                            @if(old('stock', $product->stock) <= 0)
                                <div style="font-size:12px; color:#ef4444; font-weight:700; display:flex; align-items:center; gap:4px;">
                                <iconify-icon icon="flat-color-icons:cancel" style="font-size:14px;"></iconify-icon> Peringatan: Stok produk kosong (0). Produk tidak dapat dibeli!
                        </div>
                        @elseif(old('stock', $product->stock) < 10)
                            <div style="font-size:12px; color:#d97706; font-weight:700; display:flex; align-items:center; gap:4px;">
                            <iconify-icon icon="flat-color-icons:warning" style="font-size:14px;"></iconify-icon> Perhatian: Stok di bawah 10 unit (Stok Menipis).
                    </div>
                    @else
                    <div style="font-size:12px; color:#10b981; font-weight:600; display:flex; align-items:center; gap:4px;">
                        <iconify-icon icon="flat-color-icons:checkmark" style="font-size:14px;"></iconify-icon> Stok aman ({{ old('stock', $product->stock) }} unit).
                    </div>
                    @endif
                    @endif
                </div>
                @error('stock')
                <div class="form-error">{{ $message }}</div>
                @enderror
        </div>
        <div class="form-group">
            <label class="form-label" for="weight">Berat (gram) <span
                    style="color:var(--danger)">*</span></label>
            <input type="number" id="weight" name="weight" class="form-control"
                value="{{ old('weight', $product->weight) }}" min="1" required>
            @error('weight')
            <div class="form-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label class="form-label" for="rating">Rating Produk (Bintang 0.0 - 5.0)</label>
            <input type="number" id="rating" name="rating" class="form-control"
                value="{{ old('rating', $product->rating) }}" placeholder="Contoh: 4.8 (Kosongkan/isi 0 untuk Produk Baru)" min="0" max="5" step="0.1">
            <div style="font-size:11px; color:var(--text-muted); margin-top:4px;">Isi rating kustom (misal 4.8). Kosongkan atau isi 0 untuk status <strong>Produk Baru</strong>.</div>
            @error('rating')
            <div class="form-error">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- Varian --}}
    <div style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.8px; color:var(--text-muted); margin:20px 0 14px; padding-bottom:8px; border-bottom:1px solid var(--border); display: flex; align-items: center; gap: 6px;">
        <iconify-icon icon="flat-color-icons:palette" style="font-size: 18px;"></iconify-icon> Varian Produk
    </div>

    @php
        $rawSizes = old('sizes', $product->sizes);
        $hasSizes = old('has_sizes', !empty($rawSizes) && (is_array($rawSizes) ? count($rawSizes) > 0 : trim($rawSizes) !== ''));
    @endphp

    {{-- Checkbox Toggle Memiliki Ukuran (Opsi B) --}}
    <div style="background: var(--bg-input, #f8fafc); border: 1px solid var(--border, #e2e8f0); border-radius: var(--radius-md, 8px); padding: 12px 16px; margin-bottom: 16px;">
        <label class="form-check" style="cursor: pointer; display: flex; align-items: center; gap: 10px; margin-bottom: 0;">
            <input type="checkbox" id="hasSizesToggle" name="has_sizes" value="1" class="form-check-input" onchange="toggleSizeFields(this.checked)" {{ $hasSizes ? 'checked' : '' }} style="width: 18px; height: 18px; cursor: pointer;">
            <span class="form-check-label" style="font-weight: 700; font-size: 13px; color: var(--text-primary);">
                <iconify-icon icon="flat-color-icons:ruler" style="font-size: 16px; vertical-align: middle;"></iconify-icon> Produk Memiliki Varian Ukuran (Pakaian, Jas, Jaket, Sepatu, dll)
            </span>
        </label>
        <div style="font-size: 11px; color: var(--text-muted); margin-left: 28px; margin-top: 4px;">
            Centang jika produk memiliki pilihan ukuran (misal S, M, L, XL). Jika tidak dicentang, pilihan ukuran dan panduan ukuran tidak akan muncul di aplikasi.
        </div>
    </div>

    {{-- Container Input Ukuran & Panduan Ukuran --}}
    <div id="sizeOptionsContainer" class="size-options-container" @if(!$hasSizes) style="display:none;" @endif>
        <div class="form-group" style="margin-bottom: 16px;">
            <label class="form-label" for="sizes" style="font-weight: 600;">Daftar Pilihan Ukuran</label>
            <input type="text" id="sizes" name="sizes" class="form-control"
                value="{{ old('sizes', is_array($product->sizes) ? implode(', ', $product->sizes) : $product->sizes) }}"
                placeholder="Contoh: S, M, L, XL, XXL (pisahkan dengan koma)">
            <div style="font-size:11px; color:var(--text-muted); margin-top:4px;">Contoh: S, M, L, XL atau All Size (pisahkan dengan koma)</div>
            @error('sizes')
            <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        {{-- Panduan Ukuran (Size Guide) --}}
        <div id="sizeChartHeader" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.8px; color:var(--text-muted); margin:16px 0 10px; padding-bottom:6px; border-bottom:1px solid var(--border); display: flex; align-items: center; gap: 6px; scroll-margin-top: 100px;">
            <iconify-icon icon="flat-color-icons:ruler" style="font-size: 16px;"></iconify-icon> Foto Panduan Ukuran (Size Chart)
        </div>
        <div id="sizeChartSection" class="form-group" style="margin-bottom: 0; scroll-margin-top: 100px; padding: 12px; border-radius: var(--radius-md, 8px); transition: all 0.4s ease; background: #fff; border: 1px solid var(--border);">
            <label class="form-label" style="font-weight: 600;">Foto Tabel / Diagram Panduan Ukuran <span style="font-size: 11px; color: var(--text-muted); font-weight: normal;">(Opsional)</span></label>
            <div class="avatar-upload-wrap">
                <div class="avatar-preview" id="sizeChartPreviewWrap" style="border-radius: var(--radius-sm); width: 140px; height: 100px; transition: all 0.3s ease;">
                    @if($product->size_chart)
                    <img src="{{ Storage::disk('public')->url($product->size_chart) }}" alt="Size Chart" style="width: 100%; height: 100%; object-fit: contain;">
                    @else
                    <div class="avatar-initials" style="border-radius: var(--radius-sm); font-size: 28px;">
                        <iconify-icon icon="flat-color-icons:ruler"></iconify-icon>
                    </div>
                    @endif
                </div>
                <div class="avatar-upload-actions">
                    <label style="cursor:pointer;">
                        <input type="file" name="size_chart" id="sizeChartInput" accept="image/jpg,image/jpeg,image/png,image/webp" style="display:none;" onchange="previewPhoto(this, 'sizeChartPreviewWrap', 'removeSizeChart')">
                        <span class="btn btn-secondary" style="font-size: 11px; padding: 4px 8px; display: inline-flex; align-items: center; gap: 4px;">
                            <iconify-icon icon="flat-color-icons:add-image"></iconify-icon> {{ $product->size_chart ? 'Ganti' : 'Pilih Foto' }}
                        </span>
                    </label>
                    @if($product->size_chart)
                    <label class="form-check" style="margin-top:4px;">
                        <input type="checkbox" name="remove_size_chart" value="1" class="form-check-input" id="removeSizeChart" onchange="handleRemovePhoto(this, 'sizeChartInput', 'sizeChartPreviewWrap', 'sizeChartFallbackHtml')">
                        <span class="form-check-label" style="font-size:11px; color:var(--danger); display: inline-flex; align-items: center; gap: 2px;">
                            <iconify-icon icon="fluent-emoji-flat:wastebasket"></iconify-icon> Hapus
                        </span>
                    </label>
                    @endif
                    <div style="font-size:11px; color:var(--text-muted); margin-top: 4px;">Upload gambar tabel/panduan ukuran khusus (maks 2MB). Jika dikosongkan, aplikasi akan otomatis menyajikan tabel ukuran interaktif berdasarkan varian ukuran yang diisi.</div>
                </div>
            </div>
            @error('size_chart')
            <div class="form-error" style="margin-top: 8px; font-weight: 600;">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- Varian Warna & Palet --}}
    <div class="form-group" style="margin-bottom: 20px;">
        <label class="form-label" style="font-weight: 600;">Warna & Palet</label>
        <div id="color-list" style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:8px;"></div>
        <div style="margin-bottom: 8px;">
            <select id="color-template" class="form-control" onchange="applyColorTemplate(this)" style="font-size: 13px; height: 38px; cursor: pointer;">
                <option value="">— Gunakan Template Warna —</option>
                <option value="Hitam|#080808">Hitam (#080808)</option>
                <option value="Putih|#FFFFFF">Putih (#FFFFFF)</option>
                <option value="Abu-abu|#808080">Abu-abu (#808080)</option>
                <option value="Merah|#FF0000">Merah (#FF0000)</option>
                <option value="Biru|#0400FF">Biru (#0400FF)</option>
                <option value="Hijau|#00FF00">Hijau (#00FF00)</option>
                <option value="Kuning|#FFFF00">Kuning (#FFFF00)</option>
                <option value="Orange|#FFA500">Orange (#FFA500)</option>
                <option value="Pink|#FF007B">Pink (#FF007B)</option>
                <option value="Ungu|#FF00EA">Ungu (#FF00EA)</option>
                <option value="Cokelat|#A52A2A">Cokelat (#A52A2A)</option>
                <option value="Navy|#000080">Navy (#000080)</option>
            </select>
        </div>
        <div style="display:flex; gap:8px; align-items:center;">
            <input type="text" id="color-name" class="form-control"
                placeholder="Nama Warna (Cth: Merah)" style="flex:1;">
            <input type="color" id="color-hex" class="form-control"
                style="width:50px; padding:2px; height:38px; cursor:pointer;" value="#ff0000">
            <button type="button" class="btn btn-secondary" onclick="addColor()"
                style="height:38px; padding: 0 16px; display: inline-flex; align-items: center; gap: 4px;">
                <iconify-icon icon="flat-color-icons:plus"></iconify-icon> Tambah
            </button>
        </div>
        <div style="font-size:11px; color:var(--text-muted); margin-top:4px;">Tambahkan warna dan pilih palet hex agar tampil di aplikasi.</div>
        @error('colors')
        <div class="form-error">{{ $message }}</div>
        @enderror
    </div>

    {{-- Pengaturan --}}
    <div style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.8px; color:var(--text-muted); margin:20px 0 14px; padding-bottom:8px; border-bottom:1px solid var(--border); display: flex; align-items: center; gap: 6px;">
        <iconify-icon icon="flat-color-icons:settings" style="font-size: 18px;"></iconify-icon> Pengaturan
    </div>

    <div style="display:flex; gap:24px; flex-wrap:wrap;">
        <div class="form-group" style="margin-bottom:0;">
            <label class="form-check">
                <input type="checkbox" name="is_active" value="1" class="form-check-input"
                    {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                <span class="form-check-label">Produk Aktif (tampil di toko)</span>
            </label>
        </div>
        <div class="form-group" style="margin-bottom:0;">
            <label class="form-check">
                <input type="checkbox" name="is_recommended" value="1" class="form-check-input"
                    {{ old('is_recommended', $product->is_recommended) ? 'checked' : '' }}>
                <span class="form-check-label">Tandai sebagai Rekomendasi</span>
            </label>
        </div>
        <div class="form-group" style="margin-bottom:0;">
            <label class="form-check">
                <input type="checkbox" id="isEventMabaToggle" name="is_event_maba" value="1" class="form-check-input"
                    onchange="toggleMabaConfig(this.checked)"
                    {{ old('is_event_maba', $product->is_event_maba) ? 'checked' : '' }}>
                <span class="form-check-label" style="display:inline-flex; align-items:center; gap:5px;">
                    <iconify-icon icon="flat-color-icons:graduation-cap" style="font-size:18px;"></iconify-icon>
                    <span>Produk Event Maba (Ormik & Semot)</span>
                </span>
            </label>
        </div>
    </div>

    {{-- Opsi Warna Khusus Event Maba (Ganjil & Genap) --}}
    <div id="mabaConfigContainer" class="maba-config-container" @if(!old('is_event_maba', $product->is_event_maba)) style="display:none;" @endif>
        <div style="font-size: 13px; font-weight: 700; color: #4338ca; display: flex; align-items: center; gap: 6px; margin-bottom: 6px;">
            <iconify-icon icon="flat-color-icons:graduation-cap" style="font-size: 20px;"></iconify-icon> Aturan Penentuan Warna Mahasiswa Baru (Ormik & Semot)
        </div>
        <div style="font-size: 11px; color: var(--text-muted); margin-bottom: 14px;">
            Tentukan warna seragam wajib yang otomatis terpilih dan terkunci di aplikasi toko sesuai digit terakhir NIM mahasiswa.
        </div>

        <div class="form-row" style="gap: 16px;">
            <div class="form-group" style="flex: 1; margin-bottom: 0;">
                <label class="form-label" for="maba_color_ganjil" style="font-weight: 600; display: flex; align-items: center; gap: 4px;">
                    <span>Warna untuk NIM Ganjil (1, 3, 5, 7, 9)</span>
                    <span style="color:var(--danger)">*</span>
                </label>
                <input type="text" id="maba_color_ganjil" name="maba_color_ganjil" class="form-control"
                    value="{{ old('maba_color_ganjil', $product->maba_color_ganjil ?? 'Putih') }}" placeholder="Contoh: Putih">
                <div style="display: flex; gap: 6px; margin-top: 6px; flex-wrap: wrap;">
                    <span style="font-size: 11px; color: var(--text-muted); align-self: center;">Pilihan Cepat:</span>
                    <button type="button" class="btn btn-secondary" style="font-size: 11px; padding: 2px 8px;" onclick="setMabaColor('ganjil', 'Putih')">Putih</button>
                    <button type="button" class="btn btn-secondary" style="font-size: 11px; padding: 2px 8px;" onclick="setMabaColor('ganjil', 'Biru')">Biru</button>
                    <button type="button" class="btn btn-secondary" style="font-size: 11px; padding: 2px 8px;" onclick="setMabaColor('ganjil', 'Kuning')">Kuning</button>
                    <button type="button" class="btn btn-secondary" style="font-size: 11px; padding: 2px 8px;" onclick="setMabaColor('ganjil', 'Hitam')">Hitam</button>
                </div>
                @error('maba_color_ganjil')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group" style="flex: 1; margin-bottom: 0;">
                <label class="form-label" for="maba_color_genap" style="font-weight: 600; display: flex; align-items: center; gap: 4px;">
                    <span>Warna untuk NIM Genap (0, 2, 4, 6, 8)</span>
                    <span style="color:var(--danger)">*</span>
                </label>
                <input type="text" id="maba_color_genap" name="maba_color_genap" class="form-control"
                    value="{{ old('maba_color_genap', $product->maba_color_genap ?? 'Biru') }}" placeholder="Contoh: Biru">
                <div style="display: flex; gap: 6px; margin-top: 6px; flex-wrap: wrap;">
                    <span style="font-size: 11px; color: var(--text-muted); align-self: center;">Pilihan Cepat:</span>
                    <button type="button" class="btn btn-secondary" style="font-size: 11px; padding: 2px 8px;" onclick="setMabaColor('genap', 'Biru')">Biru</button>
                    <button type="button" class="btn btn-secondary" style="font-size: 11px; padding: 2px 8px;" onclick="setMabaColor('genap', 'Putih')">Putih</button>
                    <button type="button" class="btn btn-secondary" style="font-size: 11px; padding: 2px 8px;" onclick="setMabaColor('genap', 'Kuning')">Kuning</button>
                    <button type="button" class="btn btn-secondary" style="font-size: 11px; padding: 2px 8px;" onclick="setMabaColor('genap', 'Hitam')">Hitam</button>
                </div>
                @error('maba_color_genap')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    {{-- Info produk --}}
    <div style="background:var(--bg-input); border:1px solid var(--border); border-radius:var(--radius-sm); padding:12px 16px; margin-top:20px; font-size:12px; color:var(--text-muted); display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
        <iconify-icon icon="flat-color-icons:rating" style="font-size: 18px;"></iconify-icon> Statistik: Rating <strong style="color:var(--text-secondary);">{{ $product->rating }}</strong>
        · {{ $product->reviews_count }} ulasan
        · Slug: <code style="color:var(--accent);">{{ $product->slug }}</code>
        · Dibuat: {{ $product->created_at->format('d M Y H:i') }}
    </div>

    <div style="display:flex; gap:12px; justify-content:flex-end; margin-top:24px;">
        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Batal</a>
        <button type="button" class="btn btn-primary" onclick="validateProductAndSubmit('productEditForm', 'Konfirmasi Edit Produk', 'Apakah Anda yakin ingin menyimpan perubahan produk ini?')" style="display: inline-flex; align-items: center; gap: 6px;">
            <iconify-icon icon="flat-color-icons:approval"></iconify-icon> Simpan Perubahan
        </button>
    </div>

    {{-- Hidden templates for JavaScript to avoid syntax linter issues --}}
    @php
    $img4 = $product->images->where('sort_order', 3)->first();
    $img5 = $product->images->where('sort_order', 4)->first();
    $img6 = $product->images->where('sort_order', 5)->first();
    @endphp
    <div id="originalPhotoHtml" style="display:none;">
        @if ($product->main_photo)
        <img id="avatarPreview" src="{{ Storage::disk('public')->url($product->main_photo) }}" style="width:100%;height:100%;object-fit:cover;border-radius: var(--radius-sm);">
        @endif
    </div>
    <div id="defaultPhotoHtml" style="display:none;">
        <div class="avatar-initials" style="border-radius: var(--radius-sm); font-size: 28px;">
            <iconify-icon icon="flat-color-icons:box"></iconify-icon>
        </div>
    </div>
    <div id="emptyPhotoHtml" style="display:none;">
        <div class="avatar-initials" style="border-radius: var(--radius-sm); font-size: 28px;">
            <iconify-icon icon="flat-color-icons:picture"></iconify-icon>
        </div>
    </div>
    <div id="originalPhoto2Html" style="display:none;">
        @if ($img2)
        <img src="{{ Storage::disk('public')->url($img2->image) }}" style="width:100%;height:100%;object-fit:cover;border-radius: var(--radius-sm);">
        @endif
    </div>
    <div id="originalPhoto3Html" style="display:none;">
        @if ($img3)
        <img src="{{ Storage::disk('public')->url($img3->image) }}" style="width:100%;height:100%;object-fit:cover;border-radius: var(--radius-sm);">
        @endif
    </div>
    <div id="originalPhoto4Html" style="display:none;">
        @if ($img4)
        <img src="{{ Storage::disk('public')->url($img4->image) }}" style="width:100%;height:100%;object-fit:cover;border-radius: var(--radius-sm);">
        @endif
    </div>
    <div id="originalPhoto5Html" style="display:none;">
        @if ($img5)
        <img src="{{ Storage::disk('public')->url($img5->image) }}" style="width:100%;height:100%;object-fit:cover;border-radius: var(--radius-sm);">
        @endif
    </div>
    <div id="originalPhoto6Html" style="display:none;">
        @if ($img6)
        <img src="{{ Storage::disk('public')->url($img6->image) }}" style="width:100%;height:100%;object-fit:cover;border-radius: var(--radius-sm);">
        @endif
    </div>
    <div id="originalSizeChartHtml" style="display:none;">
        @if ($product->size_chart)
        <img src="{{ Storage::disk('public')->url($product->size_chart) }}" style="width:100%;height:100%;object-fit:contain;border-radius: var(--radius-sm);">
        @endif
    </div>
    <div id="sizeChartFallbackHtml" style="display:none;">
        <div class="avatar-initials" style="border-radius: var(--radius-sm); font-size: 28px;">
            <iconify-icon icon="flat-color-icons:ruler"></iconify-icon>
        </div>
    </div>
    <div id="product-colors-data" data-colors="{{ json_encode(old('colors', $product->colors ?? [])) }}" style="display:none;"></div>
    <div id="validation-data" data-has-size-chart-error="{{ $errors->has('size_chart') ? '1' : '0' }}" data-size-chart-error="{{ $errors->first('size_chart') }}" data-has-any-error="{{ $errors->any() ? '1' : '0' }}" style="display:none;"></div>
    </form>
</div>
</div>
</div>

<script>
    function notifyAdminAlert(title, message) {
        if (typeof window.showAdminError === 'function') {
            window.showAdminError(title, message);
        } else {
            alert(`${title ? title + ': ' : ''}${message}`);
        }
    }

    function toggleSizeFields(isChecked) {
        const container = document.getElementById('sizeOptionsContainer');
        const sizesInput = document.getElementById('sizes');
        if (container) {
            container.style.display = isChecked ? 'block' : 'none';
        }
        if (!isChecked) {
            if (sizesInput) sizesInput.value = '';
            const sizeInput = document.getElementById('sizeChartInput');
            if (sizeInput) sizeInput.value = '';
            const removeCb = document.getElementById('removeSizeChart');
            if (removeCb) {
                removeCb.checked = true;
                handleRemovePhoto(removeCb, 'sizeChartInput', 'sizeChartPreviewWrap', 'sizeChartFallbackHtml');
            } else {
                const wrap = document.getElementById('sizeChartPreviewWrap');
                if (wrap) {
                    wrap.innerHTML = '<div class="avatar-initials" style="border-radius: var(--radius-sm); font-size: 28px;"><iconify-icon icon="flat-color-icons:ruler"></iconify-icon></div>';
                }
            }
        }
    }

    function toggleMabaConfig(isChecked) {
        const container = document.getElementById('mabaConfigContainer');
        if (container) {
            container.style.display = isChecked ? 'block' : 'none';
        }
    }

    function setMabaColor(type, colorName) {
        const target = document.getElementById(type === 'ganjil' ? 'maba_color_ganjil' : 'maba_color_genap');
        if (target) {
            target.value = colorName;
        }
    }

    function previewPhoto(input, targetId, removeCbId) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            if (file.size > 2 * 1024 * 1024) {
                notifyAdminAlert(
                    'Ukuran Gambar Melebihi 2MB',
                    `Ukuran file "${file.name}" adalah ${(file.size / (1024 * 1024)).toFixed(2)} MB. Maksimal ukuran yang diizinkan adalah 2MB.`
                );
                input.value = '';
                return;
            }

            const reader = new window.FileReader();
            reader.onload = function(e) {
                const wrap = document.getElementById(targetId);
                if (wrap) {
                    const fit = targetId === 'sizeChartPreviewWrap' ? 'contain' : 'cover';
                    wrap.innerHTML =
                        `<img src="${e.target.result}" style="width:100%; height:100%; object-fit:${fit}; border-radius: var(--radius-sm);">`;
                }
                if (removeCbId) {
                    const removePhotoCb = document.getElementById(removeCbId);
                    if (removePhotoCb) {
                        removePhotoCb.checked = false;
                    }
                }
            };
            reader.readAsDataURL(file);
        }
    }

    function handleRemovePhoto(cb, inputId, targetId, fallbackTemplateId) {
        const photoInput = document.getElementById(inputId);
        if (cb.checked) {
            if (photoInput) {
                photoInput.value = '';
                photoInput.disabled = true;
            }

            let fallbackIcon = '';
            if (fallbackTemplateId === 'defaultPhotoHtml') {
                fallbackIcon = document.getElementById('defaultPhotoHtml').innerHTML.trim();
            } else if (fallbackTemplateId === 'sizeChartFallbackHtml') {
                fallbackIcon = document.getElementById('sizeChartFallbackHtml').innerHTML.trim();
            } else {
                fallbackIcon = document.getElementById('emptyPhotoHtml').innerHTML.trim();
            }

            document.getElementById(targetId).innerHTML = fallbackIcon;
        } else {
            if (photoInput) {
                photoInput.disabled = false;
            }
            let originalPhoto = '';
            if (inputId === 'mainPhotoInput') {
                originalPhoto = document.getElementById('originalPhotoHtml')?.innerHTML.trim() || '';
            } else if (inputId === 'photo2Input') {
                originalPhoto = document.getElementById('originalPhoto2Html')?.innerHTML.trim() || '';
            } else if (inputId === 'photo3Input') {
                originalPhoto = document.getElementById('originalPhoto3Html')?.innerHTML.trim() || '';
            } else if (inputId === 'photo4Input') {
                originalPhoto = document.getElementById('originalPhoto4Html')?.innerHTML.trim() || '';
            } else if (inputId === 'photo5Input') {
                originalPhoto = document.getElementById('originalPhoto5Html')?.innerHTML.trim() || '';
            } else if (inputId === 'photo6Input') {
                originalPhoto = document.getElementById('originalPhoto6Html')?.innerHTML.trim() || '';
            } else if (inputId === 'sizeChartInput') {
                originalPhoto = document.getElementById('originalSizeChartHtml')?.innerHTML.trim() || '';
            }

            if (originalPhoto) {
                document.getElementById(targetId).innerHTML = originalPhoto;
            } else {
                if (fallbackTemplateId === 'sizeChartFallbackHtml') {
                    document.getElementById(targetId).innerHTML = document.getElementById('sizeChartFallbackHtml').innerHTML.trim();
                } else if (fallbackTemplateId === 'defaultPhotoHtml') {
                    document.getElementById(targetId).innerHTML = document.getElementById('defaultPhotoHtml').innerHTML.trim();
                } else {
                    document.getElementById(targetId).innerHTML = document.getElementById('emptyPhotoHtml').innerHTML.trim();
                }
            }
        }
    }

    function clearPhotoSlot(inputName, previewId, isMain = false) {
        const input = document.querySelector(`input[name="${inputName}"]`) || document.getElementById(inputName + 'Input');
        if (input) {
            input.value = '';
        }
        const wrap = document.getElementById(previewId);
        if (wrap) {
            let iconTag = '<iconify-icon icon="flat-color-icons:picture" style="font-size: 28px;"></iconify-icon>';
            if (isMain) {
                iconTag = '<iconify-icon icon="flat-color-icons:box" style="font-size: 28px;"></iconify-icon>';
            } else if (inputName === 'size_chart') {
                iconTag = '<iconify-icon icon="flat-color-icons:ruler" style="font-size: 28px;"></iconify-icon>';
            }
            wrap.innerHTML = `<div class="avatar-initials" style="border-radius: var(--radius-sm); font-size: 28px;">${iconTag}</div>`;
        }
        const resetBtn = document.getElementById('resetBtn_' + inputName);
        if (resetBtn) {
            resetBtn.style.display = 'none';
        }
    }

    function handleBulkImageUpload(files) {
        if (!files || files.length === 0) return;

        const slots = [{
                inputName: 'main_photo',
                inputId: 'mainPhotoInput',
                previewId: 'avatarPreviewWrap',
                removeId: 'removePhoto'
            },
            {
                inputName: 'photo_2',
                inputId: 'photo2Input',
                previewId: 'previewWrap2',
                removeId: 'removePhoto2'
            },
            {
                inputName: 'photo_3',
                inputId: 'photo3Input',
                previewId: 'previewWrap3',
                removeId: 'removePhoto3'
            },
            {
                inputName: 'photo_4',
                inputId: 'photo4Input',
                previewId: 'previewWrap4',
                removeId: 'removePhoto4'
            },
            {
                inputName: 'photo_5',
                inputId: 'photo5Input',
                previewId: 'previewWrap5',
                removeId: 'removePhoto5'
            },
            {
                inputName: 'photo_6',
                inputId: 'photo6Input',
                previewId: 'previewWrap6',
                removeId: 'removePhoto6'
            },
        ];

        const maxFiles = Math.min(files.length, slots.length);

        for (let i = 0; i < maxFiles; i++) {
            const file = files[i];
            if (file.size > 2 * 1024 * 1024) {
                notifyAdminAlert(
                    'Ukuran Gambar Melebihi 2MB',
                    `Gambar "${file.name}" dilewati karena berukuran ${(file.size / (1024 * 1024)).toFixed(2)} MB (maks 2MB).`
                );
                continue;
            }

            const slot = slots[i];
            const input = document.querySelector(`input[name="${slot.inputName}"]`) || document.getElementById(slot.inputId);

            if (input && file) {
                try {
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    input.files = dt.files;
                    input.disabled = false;

                    // Uncheck remove checkbox if checked
                    if (slot.removeId) {
                        const removeCb = document.getElementById(slot.removeId);
                        if (removeCb) removeCb.checked = false;
                    }

                    previewPhoto(input, slot.previewId, slot.removeId);
                } catch (e) {
                    console.error('Error assigning bulk image:', e);
                }
            }
        }

        if (files.length > 6) {
            notifyAdminAlert('Batas Upload Sekaligus', 'Hanya 6 gambar pertama yang diisikan ke slot yang tersedia.');
        }
    }

    function validateProductAndSubmit(formId, title, bodyText) {
        // Check size_chart file
        const sizeChartInput = document.getElementById('sizeChartInput') || document.querySelector('input[name="size_chart"]');
        if (sizeChartInput && sizeChartInput.files && sizeChartInput.files[0]) {
            const file = sizeChartInput.files[0];
            if (file.size > 2 * 1024 * 1024) {
                scrollToSizeChartError(file);
                return false;
            }
        }

        // Check other photo inputs
        const otherInputs = [{
                name: 'main_photo',
                label: 'Foto Utama',
                previewId: 'avatarPreviewWrap'
            },
            {
                name: 'photo_2',
                label: 'Foto 2',
                previewId: 'previewWrap2'
            },
            {
                name: 'photo_3',
                label: 'Foto 3',
                previewId: 'previewWrap3'
            },
            {
                name: 'photo_4',
                label: 'Foto 4',
                previewId: 'previewWrap4'
            },
            {
                name: 'photo_5',
                label: 'Foto 5',
                previewId: 'previewWrap5'
            },
            {
                name: 'photo_6',
                label: 'Foto 6',
                previewId: 'previewWrap6'
            },
        ];

        for (let item of otherInputs) {
            const input = document.querySelector(`input[name="${item.name}"]`);
            if (input && input.files && input.files[0]) {
                const f = input.files[0];
                if (f.size > 2 * 1024 * 1024) {
                    const preview = document.getElementById(item.previewId);
                    if (preview) {
                        preview.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        preview.style.transition = 'all 0.3s ease';
                        preview.style.boxShadow = '0 0 0 4px rgba(239, 68, 68, 0.6)';
                        setTimeout(() => {
                            preview.style.boxShadow = '';
                        }, 4000);
                    }
                    notifyAdminAlert(
                        'Ukuran Gambar Melebihi 2MB',
                        `Ukuran ${item.label} (${f.name}) adalah ${(f.size / (1024 * 1024)).toFixed(2)} MB. Silakan perbaiki sebelum menyimpan.`
                    );
                    return false;
                }
            }
        }

        // If all files are valid, proceed to confirmation modal
        if (typeof confirmUpdate === 'function') {
            confirmUpdate(formId, title, bodyText);
        } else {
            const form = document.getElementById(formId);
            if (form) form.submit();
        }
    }

    function scrollToSizeChartError(file) {
        const section = document.getElementById('sizeChartSection') || document.getElementById('sizeChartPreviewWrap');
        if (section) {
            section.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
            const preview = document.getElementById('sizeChartPreviewWrap');
            if (preview) {
                preview.style.transition = 'all 0.3s ease';
                preview.style.boxShadow = '0 0 0 4px rgba(239, 68, 68, 0.6)';
                preview.style.border = '2px solid #ef4444';
                setTimeout(() => {
                    preview.style.boxShadow = '';
                    preview.style.border = '';
                }, 5000);
            }
        }
        const sizeMsg = file ? ` (${(file.size / (1024 * 1024)).toFixed(2)} MB)` : '';
        notifyAdminAlert(
            'Foto Panduan Ukuran Melebihi 2MB',
            `Ukuran berkas melebihi batas maksimal 2MB${sizeMsg}. Layar telah diarahkan ke bagian Panduan Ukuran agar dapat diganti atau dihapus.`
        );
    }

    let colorIndex = 0;

    document.addEventListener("DOMContentLoaded", function() {
        const valData = document.getElementById('validation-data');
        if (valData) {
            if (valData.dataset.hasSizeChartError === '1') {
                setTimeout(function() {
                    const target = document.getElementById('sizeChartSection') || document.getElementById('sizeChartPreviewWrap');
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        const preview = document.getElementById('sizeChartPreviewWrap');
                        if (preview) {
                            preview.style.transition = 'all 0.3s ease';
                            preview.style.boxShadow = '0 0 0 4px rgba(239, 68, 68, 0.6)';
                            preview.style.border = '2px solid #ef4444';
                            setTimeout(() => {
                                preview.style.boxShadow = '';
                                preview.style.border = '';
                            }, 6000);
                        }
                    }
                    notifyAdminAlert(
                        'Validasi Panduan Ukuran',
                        valData.dataset.sizeChartError || 'Ukuran Foto Panduan Ukuran tidak boleh lebih dari 2MB.'
                    );
                }, 300);
            } else if (valData.dataset.hasAnyError === '1') {
                setTimeout(function() {
                    const firstErr = document.querySelector('.form-error');
                    if (firstErr) {
                        firstErr.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }
                }, 300);
            }
        }

        const dropzone = document.getElementById('bulkDropzone');
        if (dropzone) {
            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.style.background = '#e0e7ff';
                    dropzone.style.borderColor = '#4338ca';
                }, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.style.background = '#f8fafc';
                    dropzone.style.borderColor = '#4f46e5';
                }, false);
            });

            dropzone.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                const files = dt.files;
                handleBulkImageUpload(files);
            }, false);
        }

        const stockInput = document.getElementById('stock');
        const stockHint = document.getElementById('stock-alert-hint');
        if (stockInput && stockHint) {
            const updateStockHint = () => {
                const val = parseInt(stockInput.value, 10);
                if (isNaN(val) || val <= 0) {
                    stockHint.innerHTML = '<div style="font-size:12px; color:#ef4444; font-weight:700; display:flex; align-items:center; gap:4px;"><iconify-icon icon="flat-color-icons:cancel" style="font-size:14px;"></iconify-icon> Peringatan: Stok produk kosong (0). Produk tidak dapat dibeli!</div>';
                } else if (val < 10) {
                    stockHint.innerHTML = `<div style="font-size:12px; color:#d97706; font-weight:700; display:flex; align-items:center; gap:4px;"><iconify-icon icon="flat-color-icons:warning" style="font-size:14px;"></iconify-icon> Perhatian: Stok di bawah 10 unit (Stok Menipis: sisa ${val} unit).</div>`;
                } else {
                    stockHint.innerHTML = `<div style="font-size:12px; color:#10b981; font-weight:600; display:flex; align-items:center; gap:4px;"><iconify-icon icon="flat-color-icons:checkmark" style="font-size:14px;"></iconify-icon> Stok aman (${val} unit).</div>`;
                }
            };
            stockInput.addEventListener('input', updateStockHint);
        }

        const dataEl = document.getElementById('product-colors-data');
        const oldColors = JSON.parse(dataEl.dataset.colors || '[]');
        if (Array.isArray(oldColors)) {
            oldColors.forEach(c => {
                if (typeof c === 'object' && c.name) {
                    addColorData(c.name, c.hex || '#000000');
                } else if (typeof c === 'string') {
                    addColorData(c, '#000000');
                }
            });
        } else if (typeof oldColors === 'string' && oldColors.trim() !== '') {
            oldColors.split(',').forEach(c => {
                if (c.trim()) addColorData(c.trim(), '#000000');
            });
        }
    });

    function addColorData(name, hex) {
        const container = document.getElementById('color-list');
        const wrapper = document.createElement('div');
        wrapper.style.cssText =
            "display:flex; align-items:center; gap:6px; background:#f8f9fa; border:1px solid #dee2e6; padding:4px 8px; border-radius:4px;";
        wrapper.id = 'color-item-' + colorIndex;

        wrapper.innerHTML = `
        <div style="width:16px; height:16px; border-radius:50%; background-color:${hex}; border:1px solid #ccc;"></div>
        <span style="font-size:13px; font-weight:500;">${name}</span>
        <input type="hidden" name="colors[${colorIndex}][name]" value="${name}">
        <input type="hidden" name="colors[${colorIndex}][hex]" value="${hex}">
        <button type="button" style="background:none; border:none; color:red; cursor:pointer; font-weight:bold; margin-left:4px; font-size:16px; line-height:1;" onclick="removeColor(${colorIndex})">&times;</button>
    `;
        container.appendChild(wrapper);
        colorIndex++;
    }

    function addColor() {
        const nameInput = document.getElementById('color-name');
        const hexInput = document.getElementById('color-hex');
        const name = nameInput.value.trim();
        const hex = hexInput.value;

        if (!name) {
            notifyAdminAlert('Nama Warna Kosong', 'Silakan masukkan nama warna terlebih dahulu.');
            return;
        }

        addColorData(name, hex);
        nameInput.value = '';
    }

    function removeColor(index) {
        const el = document.getElementById('color-item-' + index);
        if (el) el.remove();
    }

    function applyColorTemplate(select) {
        if (!select.value) return;
        const parts = select.value.split('|');
        const name = parts[0];
        const hex = parts[1];

        document.getElementById('color-name').value = name;
        document.getElementById('color-hex').value = hex;

        addColor();
        select.value = '';
    }
</script>
@endsection