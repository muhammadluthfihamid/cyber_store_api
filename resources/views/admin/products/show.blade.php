@extends('admin.layouts.app')

@section('title', 'Detail Produk — ' . $product->name)
@section('page-title', 'Detail Produk')
@section('breadcrumb')
    <span class="breadcrumb-sep">›</span>
    <a href="{{ route('admin.products.index') }}">Produk</a>
    <span class="breadcrumb-sep">›</span>
    <span>Detail</span>
@endsection

@section('content')
<div style="max-width: 1000px; margin: 0 auto;">
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <span class="card-title" style="display: flex; align-items: center; gap: 8px;">
                <iconify-icon icon="flat-color-icons:view-details" style="font-size: 24px;"></iconify-icon> 
                Detail Produk: {{ $product->name }}
            </span>
            <div style="display: flex; gap: 8px;">
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px;">
                    <iconify-icon icon="flat-color-icons:previous"></iconify-icon> Kembali
                </a>
                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                    <iconify-icon icon="flat-color-icons:edit-image"></iconify-icon> Edit Produk
                </a>
            </div>
        </div>

        <div class="card-body" style="padding: 24px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px;">
                
                {{-- Galeri Foto Produk --}}
                <div>
                    <div style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.8px; color:var(--text-muted); margin-bottom:14px; padding-bottom:8px; border-bottom:1px solid var(--border); display: flex; align-items: center; gap: 6px;">
                        <iconify-icon icon="flat-color-icons:multiple-cameras" style="font-size: 18px;"></iconify-icon> Galeri Gambar
                    </div>

                    <div style="width: 100%; height: 300px; background: var(--bg-input); border-radius: 14px; border: 1px solid var(--border); overflow: hidden; margin-bottom: 12px; display: flex; align-items: center; justify-content: center;">
                        @if ($product->main_photo)
                            <img id="mainDetailImage" src="{{ Storage::disk('public')->url($product->main_photo) }}" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            <iconify-icon icon="flat-color-icons:box" style="font-size: 64px;"></iconify-icon>
                        @endif
                    </div>

                    {{-- Extra Photos List --}}
                    @php
                        $allPhotos = [];
                        if ($product->main_photo) $allPhotos[] = $product->main_photo;
                        foreach ($product->images as $img) {
                            if ($img->image) $allPhotos[] = $img->image;
                        }
                    @endphp

                    @if(count($allPhotos) > 1)
                    <div style="display: flex; gap: 8px; overflow-x: auto; padding-bottom: 6px;">
                        @foreach($allPhotos as $idx => $photoUrl)
                            @php
                                $photoSrc = asset('storage/' . $photoUrl);
                            @endphp
                            <div onclick="changeMainDetailImage(this.getAttribute('data-img'))" data-img="{{ $photoSrc }}"
                                style="width: 56px; height: 56px; border-radius: 8px; border: 2px solid var(--border); overflow: hidden; cursor: pointer; flex-shrink: 0;">
                                <img src="{{ $photoSrc }}" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                        @endforeach
                    </div>
                    @endif
                </div>

                {{-- Informasi Rincian Produk --}}
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <div style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.8px; color:var(--text-muted); padding-bottom:8px; border-bottom:1px solid var(--border); display: flex; align-items: center; gap: 6px;">
                        <iconify-icon icon="flat-color-icons:document" style="font-size: 18px;"></iconify-icon> Informasi Rincian
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                        <span class="badge {{ $product->is_active ? 'badge-active' : 'badge-inactive' }}" style="font-size: 13px; padding: 6px 14px;">
                            <iconify-icon icon="{{ $product->is_active ? 'flat-color-icons:checkmark' : 'flat-color-icons:cancel' }}" style="font-size: 14px;"></iconify-icon>
                            {{ $product->is_active ? 'Aktif (Tampil di Toko)' : 'Nonaktif' }}
                        </span>
                        <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                            @if($product->is_recommended)
                                <span class="badge badge-recommended" style="font-size: 13px; padding: 6px 14px;">★ Rekomendasi</span>
                            @endif
                            @if($product->is_event_maba)
                                <span class="badge" style="font-size: 13px; padding: 6px 14px; background: rgba(99, 102, 241, .15); color: #6366f1; font-weight: 700; border: 1px solid rgba(99, 102, 241, .3);">
                                    🎓 Event Maba (Ganjil: {{ $product->maba_color_ganjil ?? 'Putih' }} · Genap: {{ $product->maba_color_genap ?? 'Biru' }})
                                </span>
                            @endif
                        </div>
                    </div>

                    <div style="background: var(--bg-input, #f8fafc); padding: 16px; border-radius: 12px; border: 1px solid var(--border);">
                        <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 2px;">Harga Jual</div>
                        <div style="font-size: 24px; font-weight: 800; color: var(--accent);">
                            Rp {{ number_format($product->price, 0, ',', '.') }}
                        </div>
                        @if($product->original_price)
                            <div style="font-size: 13px; color: var(--text-muted); text-decoration: line-through; margin-top: 2px;">
                                Rp {{ number_format($product->original_price, 0, ',', '.') }}
                            </div>
                        @endif
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;">
                        <div style="background: var(--bg-card); padding: 12px; border-radius: 10px; border: 1px solid var(--border);">
                            <div style="font-size: 11px; color: var(--text-muted);">SKU</div>
                            <div style="font-weight: 700; font-family: monospace; font-size: 13px;">{{ $product->sku }}</div>
                        </div>
                        <div style="background: var(--bg-card); padding: 12px; border-radius: 10px; border: 1px solid var(--border);">
                            <div style="font-size: 11px; color: var(--text-muted);">Kategori</div>
                            <div style="font-weight: 700; font-size: 13px; color: var(--accent);">{{ $product->category?->name ?? '—' }}</div>
                        </div>
                        <div style="background: var(--bg-card); padding: 12px; border-radius: 10px; border: 1px solid var(--border);">
                            <div style="font-size: 11px; color: var(--text-muted);">Stok Barang</div>
                            <div style="font-weight: 800; font-size: 15px;" class="{{ $product->stock <= 5 ? 'text-danger' : 'text-success' }}">
                                {{ $product->stock }} Pcs
                            </div>
                        </div>
                        <div style="background: var(--bg-card); padding: 12px; border-radius: 10px; border: 1px solid var(--border);">
                            <div style="font-size: 11px; color: var(--text-muted);">Berat</div>
                            <div style="font-weight: 700; font-size: 13px;">{{ $product->weight }} gram</div>
                        </div>
                    </div>

                    {{-- Varian Ukuran & Warna --}}
                    @if(!empty($product->sizes) || !empty($product->colors))
                    <div style="background: var(--bg-card); padding: 14px; border-radius: 10px; border: 1px solid var(--border); display: flex; flex-direction: column; gap: 10px;">
                        @if(!empty($product->sizes))
                        <div>
                            <div style="font-size: 11px; color: var(--text-muted); margin-bottom: 4px;">Pilihan Ukuran:</div>
                            <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                                @php
                                    $sizeArr = is_array($product->sizes) ? $product->sizes : explode(',', $product->sizes);
                                @endphp
                                @foreach($sizeArr as $sz)
                                    <span style="font-size: 12px; font-weight: 700; padding: 3px 10px; background: var(--bg-input); border: 1px solid var(--border); border-radius: 6px;">
                                        {{ trim(is_array($sz) ? ($sz['name'] ?? '') : $sz) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        @if(!empty($product->colors))
                        <div>
                            <div style="font-size: 11px; color: var(--text-muted); margin-bottom: 4px;">Pilihan Warna:</div>
                            <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                 @php
                                     $colorArr = is_array($product->colors) ? $product->colors : (json_decode($product->colors, true) ?? []);
                                 @endphp
                                 @foreach($colorArr as $clr)
                                     @php
                                         $hex = is_array($clr) ? ($clr['hex'] ?? '#000000') : '#000000';
                                         $name = is_array($clr) ? ($clr['name'] ?? '') : $clr;
                                     @endphp
                                     <div style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; background: var(--bg-input); border: 1px solid var(--border); border-radius: 20px; font-size: 12px; font-weight: 600;">
                                         <span style="width: 14px; height: 14px; border-radius: 50%; border: 1px solid rgba(0,0,0,0.15); background-color: {{ $hex }};"></span>
                                         {{ $name }}
                                     </div>
                                 @endforeach
                             </div>
                         </div>
                         @endif

                        @if(!empty($product->size_chart))
                        <div>
                            <div style="font-size: 11px; color: var(--text-muted); margin-bottom: 4px;">Foto Panduan Ukuran:</div>
                            <a href="{{ asset('storage/' . $product->size_chart) }}" target="_blank" style="display: inline-block;">
                                <img src="{{ asset('storage/' . $product->size_chart) }}" alt="Panduan Ukuran" style="max-height: 120px; border-radius: 8px; border: 1px solid var(--border); background: #fff; padding: 4px;">
                            </a>
                        </div>
                        @endif
                    </div>
                    @endif

                    {{-- Deskripsi --}}
                    <div>
                        <div style="font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 6px;">Deskripsi Produk:</div>
                        <div style="font-size: 13px; color: var(--text-primary); line-height: 1.6; background: var(--bg-input); padding: 14px; border-radius: 10px; border: 1px solid var(--border); white-space: pre-line;">
                            {{ $product->description ?: 'Tidak ada deskripsi.' }}
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>

<script>
    function changeMainDetailImage(src) {
        const img = document.getElementById('mainDetailImage');
        if (img) img.src = src;
    }
</script>
@endsection
