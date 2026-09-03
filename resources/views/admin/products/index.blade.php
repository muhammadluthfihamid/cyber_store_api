@extends('admin.layouts.app')

@section('title', 'Kelola Produk')
@section('page-title', 'Kelola Produk')
@section('breadcrumb')
<span class="breadcrumb-sep">›</span>
<span>Produk</span>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <span class="card-title" style="display: flex; align-items: center; gap: 8px;">
            <iconify-icon icon="flat-color-icons:box" style="font-size: 22px;"></iconify-icon> Daftar Produk
        </span>
        <div style="display: flex; gap: 8px; align-items: center;">
            <button type="button" id="btn-bulk-delete" class="btn btn-danger" style="display: none; align-items: center; gap: 6px; background-color: #EF4444; border-color: #EF4444; color: white;" onclick="confirmBulkDelete()">
                <iconify-icon icon="fluent-emoji-flat:wastebasket" style="font-size: 16px;"></iconify-icon> Hapus Terpilih (<span id="selected-count">0</span>)
            </button>
            {{-- Tombol flush cache produk di Redis agar Flutter langsung segar --}}
            <form method="POST" action="{{ route('admin.cache.flush-products') }}" style="margin: 0;" onsubmit="return confirm('Bersihkan cache produk di server? Flutter akan langsung menampilkan data terbaru.')">
                @csrf
                <button type="submit" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; background-color: #F59E0B; border-color: #F59E0B; color: white;">
                    <iconify-icon icon="flat-color-icons:synchronize" style="font-size: 16px;"></iconify-icon> Bersihkan Cache
                </button>
            </form>
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('import-section').style.display = document.getElementById('import-section').style.display === 'none' ? 'block' : 'none'" style="display: inline-flex; align-items: center; gap: 6px;">
                <iconify-icon icon="flat-color-icons:download" style="font-size: 16px;"></iconify-icon> Impor Massal (CSV)
            </button>
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                <iconify-icon icon="flat-color-icons:plus" style="font-size: 16px;"></iconify-icon> Tambah Produk
            </a>
        </div>
    </div>



    @if(session('import_errors'))
    <div id="import-section" style="padding: 20px; background: #f8fafc; border-bottom: 1px solid var(--border);">
    @else
    <div id="import-section" style="display: none; padding: 20px; background: #f8fafc; border-bottom: 1px solid var(--border);">
    @endif
        <form action="{{ route('admin.products.import') }}" method="POST" enctype="multipart/form-data" id="importCsvForm" onsubmit="return handleCsvImportSubmit(this)" style="display: flex; flex-direction: column; gap: 16px;">
            @csrf
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px;">
                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <label style="font-weight: bold; font-size: 13px; color: #1E293B;">
                        📄 File Data (.csv / .xls / .xlsx) <span style="color: #EF4444;">*</span>
                    </label>
                    <input type="file" name="file" accept=".csv,.xls,.xlsx" required class="form-control" style="background: white;" id="csvFileInput">
                    <span style="font-size: 11px; color: #64748b;">File yang berisi data produk & nama file foto.</span>
                </div>

                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <label style="font-weight: bold; font-size: 13px; color: #1E293B;">
                        🖼️ Upload Berkas Gambar (Bisa Pilih Banyak)
                    </label>
                    <input type="file" name="images[]" accept="image/jpg,image/jpeg,image/png,image/webp" multiple class="form-control" style="background: white;" id="imagesFileInput">
                    <span style="font-size: 11px; color: #64748b;">Pilih foto-foto produk (.jpg, .png, .webp) sekaligus.</span>
                </div>

                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <label style="font-weight: bold; font-size: 13px; color: #1E293B;">
                        📦 Atau Upload Archive ZIP Gambar (.zip)
                    </label>
                    <input type="file" name="images_zip" accept=".zip" class="form-control" style="background: white;" id="imagesZipInput">
                    <span style="font-size: 11px; color: #64748b;">Alternatif: Kompres semua foto produk dalam 1 file .zip.</span>
                </div>
            </div>

            <div style="display: flex; gap: 12px; align-items: center; justify-content: flex-end; padding-top: 12px; border-top: 1px dashed #e2e8f0; flex-wrap: wrap;">
                <a href="{{ route('admin.products.import-template') }}" class="btn btn-link" style="font-size: 13px; text-decoration: underline; color: #0D47A1; font-weight: bold; display: inline-flex; align-items: center; gap: 6px;">
                    <iconify-icon icon="vscode-icons:file-type-excel" style="font-size: 18px;"></iconify-icon> Unduh Template Excel (.xls)
                </a>
                <button type="submit" id="importSubmitBtn" class="btn btn-success" style="background-color: #10B981; border-color: #10B981; color: white; display: inline-flex; align-items: center; gap: 8px; font-weight: bold; padding: 10px 20px;">
                    <span id="importBtnIcon"><iconify-icon icon="flat-color-icons:upload" style="font-size: 18px;"></iconify-icon></span> <span id="importBtnText">Proses Impor Data & Gambar</span>
                </button>
            </div>
        </form>

        <div id="import-status-box" style="display: none; align-items: center; gap: 12px; margin-top: 14px; padding: 12px 16px; background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 8px; color: #1E40AF; font-size: 13px;">
            <span class="spinner-border-custom" style="border-color: #1E40AF; border-right-color: transparent; width: 18px; height: 18px; border-width: 3px;"></span>
            <div>
                <strong>Sedang Memproses & Mengimpor File CSV dan Berkas Gambar...</strong><br>
                <span style="font-size: 11px; opacity: 0.85;">Mohon jangan memuat ulang atau menutup halaman sampai proses selesai.</span>
            </div>
        </div>

        <div style="margin-top: 14px; font-size: 11.5px; color: #475569; line-height: 1.6; background: #fff; padding: 12px 16px; border-radius: 8px; border: 1px solid #e2e8f0;">
            <strong>💡 Cara Mengimpor Data & Berkas Gambar Produk:</strong><br>
            1. <strong>Download Template</strong>: Klik <em>"Unduh Template Excel (.xls)"</em> di atas.<br>
            2. <strong>Isi Nama File Gambar</strong>: Pada kolom <code>Foto_Utama</code> di CSV/Excel, tuliskan nama file gambar produknya (contoh: <code>topi_ubsi_hitam.jpg</code>).<br>
            3. <strong>Upload Berkas Foto</strong>: Pilih/Upload berkas-berkas foto produk Anda pada kolom <em>"Upload Berkas Gambar"</em> (bisa pilih banyak foto sekaligus) atau upload file <em>.zip</em> berisi foto-foto tersebut.<br>
            4. Klik <strong>"Proses Impor Data & Gambar"</strong>. Sistem akan otomatis menyimpan foto ke direktori storage dan mencocokkannya ke produk sesuai nama filenya!
        </div>
    </div>

    @if (session('import_errors'))
    <div style="margin: 15px 20px; padding: 15px; background-color: #FEF2F2; border: 1px solid #FEE2E2; border-radius: 8px; position: relative;">
        <button onclick="this.parentElement.remove()" style="position: absolute; top: 12px; right: 12px; background: none; border: none; font-size: 16px; cursor: pointer; color: #991B1B;">✕</button>
        <h4 style="color: #991B1B; margin-top: 0; margin-bottom: 8px; font-size: 13px; font-weight: bold; display: flex; align-items: center; gap: 6px;">
            <iconify-icon icon="flat-color-icons:warning" style="font-size: 18px;"></iconify-icon> Beberapa baris memiliki kesalahan data dan dilewati:
        </h4>
        <ul style="color: #B91C1C; margin: 0; padding-left: 20px; font-size: 12px; line-height: 1.6;">
            @foreach (session('import_errors') as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <script>
    function handleCsvImportSubmit(form) {
        const fileInput = document.getElementById('csvFileInput');
        if (!fileInput || !fileInput.files || !fileInput.files.length) {
            alert('Silakan pilih file CSV terlebih dahulu.');
            return false;
        }

        const btn = document.getElementById('importSubmitBtn');
        const icon = document.getElementById('importBtnIcon');
        const text = document.getElementById('importBtnText');
        const statusBox = document.getElementById('import-status-box');

        if (btn) {
            btn.disabled = true;
            btn.style.opacity = '0.85';
            btn.style.cursor = 'not-allowed';
        }
        if (icon) {
            icon.className = 'spinner-border-custom';
            icon.innerHTML = '';
        }
        if (text) {
            text.innerText = 'Memproses Impor...';
        }
        if (statusBox) {
            statusBox.style.display = 'flex';
        }

        return true;
    }
    </script>

    @if(($outOfStockCount ?? 0) > 0 || ($lowStockCount ?? 0) > 0)
    <div style="margin: 16px 20px 0; display: flex; flex-wrap: wrap; gap: 12px;">
        @if(($outOfStockCount ?? 0) > 0)
        <div style="flex: 1; min-width: 260px; padding: 12px 16px; background: #FEF2F2; border: 1px solid #FCA5A5; border-radius: 10px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 6px rgba(239,68,68,0.08);">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; background: #FEE2E2; border-radius: 8px;">
                    <iconify-icon icon="flat-color-icons:cancel" style="font-size: 22px;"></iconify-icon>
                </span>
                <div>
                    <div style="font-weight: 700; color: #991B1B; font-size: 13px;">{{ $outOfStockCount }} Produk Stok Habis (0)</div>
                    <div style="font-size: 11.5px; color: #B91C1C;">Produk ini tidak dapat dibeli oleh customer.</div>
                </div>
            </div>
            <a href="{{ route('admin.products.index', ['stock_status' => 'out_of_stock']) }}" class="btn btn-sm btn-danger" style="font-size: 11px; padding: 4px 10px; font-weight: 700;">
                Lihat Produk
            </a>
        </div>
        @endif

        @if(($lowStockCount ?? 0) > 0)
        <div style="flex: 1; min-width: 260px; padding: 12px 16px; background: #FFFBEB; border: 1px solid #FCD34D; border-radius: 10px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 6px rgba(245,158,11,0.08);">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; background: #FEF3C7; border-radius: 8px;">
                    <iconify-icon icon="flat-color-icons:warning" style="font-size: 22px;"></iconify-icon>
                </span>
                <div>
                    <div style="font-weight: 700; color: #92400E; font-size: 13px;">{{ $lowStockCount }} Produk Stok Menipis (< 10)</div>
                    <div style="font-size: 11.5px; color: #B45309;">Segera lakukan restock sebelum persediaan habis.</div>
                </div>
            </div>
            <a href="{{ route('admin.products.index', ['stock_status' => 'low_stock']) }}" class="btn btn-sm btn-warning" style="font-size: 11px; padding: 4px 10px; font-weight: 700; color: #78350F; background: #FDE68A; border-color: #FCD34D;">
                Lihat Produk
            </a>
        </div>
        @endif
    </div>
    @endif

    <div style="padding: 16px 20px; border-bottom: 1px solid var(--border);">
        <form method="GET" action="{{ route('admin.products.index') }}" class="filter-bar">
            <div class="search-input-wrapper">
                <iconify-icon icon="flat-color-icons:search" class="search-icon" style="font-size: 16px;"></iconify-icon>
                <input type="text" name="search" class="form-control search-input"
                    placeholder="Cari nama produk, SKU..." value="{{ request('search') }}" data-suggestion-url="{{ route('admin.products.suggestions') }}" autocomplete="off">
            </div>
            <select name="category" class="form-control" style="min-width:150px;">
                <option value="">Semua Kategori</option>
                @foreach ($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name }}
                </option>
                @endforeach
            </select>
            <select name="status" class="form-control" style="min-width:120px;">
                <option value="">Semua Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
            </select>
            <select name="stock_status" class="form-control" style="min-width:140px;">
                <option value="">Semua Stok</option>
                <option value="low_stock" {{ request('stock_status') === 'low_stock' ? 'selected' : '' }}>⚠️ Menipis (&lt; 10)</option>
                <option value="out_of_stock" {{ request('stock_status') === 'out_of_stock' ? 'selected' : '' }}>❌ Habis (0)</option>
            </select>
            <select name="event" class="form-control" style="min-width:140px;">
                <option value="">Semua Event</option>
                <option value="maba" {{ request('event') === 'maba' ? 'selected' : '' }}>🎓 Event Maba</option>
            </select>
            <button type="submit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;"><iconify-icon icon="flat-color-icons:filter" style="font-size: 16px;"></iconify-icon> Filter</button>
            @if (request()->hasAny(['search', 'category', 'status', 'stock_status', 'event']))
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px;"><iconify-icon icon="flat-color-icons:undo" style="font-size: 16px;"></iconify-icon> Reset</a>
            @endif
        </form>
    </div>

    <div class="table-wrapper">
        @if ($products->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon" style="display: flex; justify-content: center;"><iconify-icon icon="flat-color-icons:opened-folder" style="font-size: 48px;"></iconify-icon></div>
            <h3>Tidak ada produk ditemukan</h3>
            <p>Coba ubah filter atau tambahkan produk baru.</p>
        </div>
        @else
        <!-- Desktop Table View (>768px) -->
        <div class="table-wrapper desktop-table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;"><input type="checkbox" id="check-all" style="cursor: pointer; width: 16px; height: 16px;"></th>
                        <th>No.</th>
                        <th>Produk</th>
                        <th>SKU</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                    <tr>
                        <td style="text-align: center;"><input type="checkbox" name="product_ids[]" value="{{ $product->id }}" class="product-checkbox" style="cursor: pointer; width: 16px; height: 16px;"></td>
                        <td style="color:var(--text-muted); font-size:12px;">
                            {{ $loop->iteration + ($products->firstItem() - 1) }}
                        </td>
                        <td>
                            <div style="display:flex; align-items:center; gap:12px;">
                                <div
                                    style="width:44px; height:44px; background:var(--bg-input); border:1px solid var(--border); border-radius:8px; display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink:0;">
                                    @if ($product->main_photo)
                                    <img src="{{ Storage::disk('public')->url($product->main_photo) }}"
                                        style="width:100%; height:100%; object-fit:cover;">
                                    @else
                                    <span style="display: flex; align-items: center; justify-content: center;">
                                        @if ($product->category?->slug === 'topi')
                                        <iconify-icon icon="flat-color-icons:businessman" style="font-size:24px;"></iconify-icon>
                                        @elseif($product->category?->slug === 'baju')
                                        <iconify-icon icon="flat-color-icons:kindle" style="font-size:24px;"></iconify-icon>
                                        @elseif($product->category?->slug === 'tumbler')
                                        <iconify-icon icon="flat-color-icons:cup" style="font-size:24px;"></iconify-icon>
                                        @else
                                        <iconify-icon icon="flat-color-icons:box" style="font-size:24px;"></iconify-icon>
                                        @endif
                                    </span>
                                    @endif
                                </div>
                                <div>
                                    <div style="font-weight:600; color:var(--text-primary);">
                                        {{ \Illuminate\Support\Str::limit($product->name, 35) }}
                                    </div>
                                    <div style="font-size:11px; color:var(--text-muted); margin-top:2px; display: flex; align-items: center; gap: 4px; flex-wrap: wrap;">
                                        <iconify-icon icon="flat-color-icons:rating" style="font-size: 13px;"></iconify-icon> {{ $product->rating }} · {{ $product->reviews_count }} ulasan
                                        @if ($product->is_recommended)
                                        <span class="badge badge-recommended"
                                            style="margin-left:4px;">Rekomendasi</span>
                                        @endif
                                        @if ($product->is_event_maba)
                                        <span class="badge"
                                            style="margin-left:4px; background: rgba(99, 102, 241, .15); color: #6366f1; font-weight: 700; border: 1px solid rgba(99, 102, 241, .3);">
                                            🎓 Event Maba
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td style="font-family:monospace; font-size:12px; color:var(--text-muted);">
                            {{ $product->sku }}
                        </td>
                        <td>
                            <span
                                style="font-size:13px; padding:3px 10px; background:var(--accent-light); color:var(--accent); border-radius:20px; font-weight:500;">
                                {{ $product->category?->name ?? '—' }}
                            </span>
                        </td>
                        <td>
                            <div style="font-weight:600; color:var(--text-primary);">
                                Rp {{ number_format($product->price, 0, ',', '.') }}
                            </div>
                            @if ($product->original_price)
                            <div style="font-size:11px; color:var(--text-muted); text-decoration:line-through;">
                                Rp {{ number_format($product->original_price, 0, ',', '.') }}
                            </div>
                            @endif
                        </td>
                        <td>
                            @if ($product->stock <= 0)
                                <span class="badge" style="background-color: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.35); font-weight: 700; display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; border-radius: 6px; white-space: nowrap;">
                                    <iconify-icon icon="flat-color-icons:cancel" style="font-size: 14px;"></iconify-icon> Habis (0)
                                </span>
                            @elseif ($product->stock < 10)
                                <span class="badge" style="background-color: rgba(245, 158, 11, 0.15); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.35); font-weight: 700; display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; border-radius: 6px; white-space: nowrap;">
                                    <iconify-icon icon="flat-color-icons:warning" style="font-size: 14px;"></iconify-icon> Sisa {{ $product->stock }}
                                </span>
                            @else
                                <span style="font-weight: 700; color: #10b981; display: inline-flex; align-items: center; gap: 4px;">
                                    <iconify-icon icon="flat-color-icons:checkmark" style="font-size: 13px;"></iconify-icon> {{ $product->stock }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $product->is_active ? 'badge-active' : 'badge-inactive' }}" style="display: inline-flex; align-items: center; gap: 4px;">
                                <iconify-icon icon="{{ $product->is_active ? 'flat-color-icons:checkmark' : 'flat-color-icons:cancel' }}" style="font-size: 13px;"></iconify-icon>
                                {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td>
                            <div class="actions">
                                <a href="{{ route('admin.products.show', $product) }}"
                                    class="btn btn-info btn-sm btn-icon" title="Lihat Detail Produk">
                                    <iconify-icon icon="flat-color-icons:view-details" style="font-size: 16px;"></iconify-icon>
                                </a>

                                <a href="{{ route('admin.products.edit', $product) }}"
                                    class="btn btn-secondary btn-sm btn-icon" title="Edit">
                                    <iconify-icon icon="flat-color-icons:edit-image" style="font-size: 16px;"></iconify-icon>
                                </a>

                                <form method="POST" action="{{ route('admin.products.toggle', $product) }}"
                                    style="display:inline;">
                                    @csrf @method('PATCH')
                                    <button type="submit"
                                        class="btn btn-sm btn-icon {{ $product->is_active ? 'btn-warning' : 'btn-success' }}"
                                        title="{{ $product->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        <iconify-icon icon="{{ $product->is_active ? 'flat-color-icons:cancel' : 'flat-color-icons:ok' }}" style="font-size: 16px;"></iconify-icon>
                                    </button>
                                </form>

                                <button type="button" class="btn btn-danger btn-sm btn-icon" title="Hapus"
                                    data-url="{{ route('admin.products.destroy', $product) }}"
                                    data-name="{{ $product->name }}"
                                    onclick="confirmDelete(this.dataset.url, this.dataset.name)">
                                    <iconify-icon icon="fluent-emoji-flat:wastebasket" style="font-size: 16px;"></iconify-icon>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Mobile Product Card View (<=768px) -->
        <div class="mobile-product-grid">
            @foreach ($products as $product)
            <div class="mobile-product-card">
                <div style="display: flex; gap: 12px; align-items: center; margin-bottom: 12px;">
                    <div style="width: 50px; height: 50px; background: var(--bg-input); border: 1px solid var(--border); border-radius: 10px; overflow: hidden; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                        @if ($product->main_photo)
                            <img src="{{ Storage::disk('public')->url($product->main_photo) }}" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            <iconify-icon icon="flat-color-icons:box" style="font-size: 28px;"></iconify-icon>
                        @endif
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 800; font-size: 14px; color: var(--text-primary);">
                            {{ $product->name }}
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted); font-family: monospace; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                            <span>SKU: {{ $product->sku }}</span>
                            @if ($product->is_event_maba)
                                <span class="badge" style="background: rgba(99, 102, 241, .15); color: #6366f1; font-weight: 700; font-size: 10px; padding: 2px 6px;">🎓 Event Maba</span>
                            @endif
                        </div>
                    </div>
                    <span class="badge {{ $product->is_active ? 'badge-active' : 'badge-inactive' }}">
                        {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>

                <div style="background: var(--bg-input, #f8fafc); padding: 10px 12px; border-radius: 10px; font-size: 12px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <span style="font-weight: 800; color: var(--accent); font-size: 14px;">
                            Rp {{ number_format($product->price, 0, ',', '.') }}
                        </span>
                    </div>
                    <div>
                        @if ($product->stock <= 0)
                            <span class="badge" style="background-color: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.35); font-weight: 700; padding: 2px 6px; border-radius: 4px;">
                                ❌ Habis (0)
                            </span>
                        @elseif ($product->stock < 10)
                            <span class="badge" style="background-color: rgba(245, 158, 11, 0.15); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.35); font-weight: 700; padding: 2px 6px; border-radius: 4px;">
                                ⚠️ Sisa {{ $product->stock }}
                            </span>
                        @else
                            <span style="font-weight: 700; color: #10b981;">Stok: {{ $product->stock }} Pcs</span>
                        @endif
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 12px; padding: 2px 8px; background: var(--accent-light); color: var(--accent); border-radius: 12px;">
                        {{ $product->category?->name ?? '—' }}
                    </span>
                    <div class="actions" style="gap: 6px;">
                        <a href="{{ route('admin.products.show', $product) }}" class="btn btn-info btn-sm btn-icon" title="Lihat Detail Produk">
                            <iconify-icon icon="flat-color-icons:view-details" style="font-size: 16px;"></iconify-icon>
                        </a>
                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-secondary btn-sm btn-icon" title="Edit">
                            <iconify-icon icon="flat-color-icons:edit-image" style="font-size: 16px;"></iconify-icon>
                        </a>
                        <form method="POST" action="{{ route('admin.products.toggle', $product) }}" style="display:inline;">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-icon {{ $product->is_active ? 'btn-warning' : 'btn-success' }}">
                                <iconify-icon icon="{{ $product->is_active ? 'flat-color-icons:cancel' : 'flat-color-icons:ok' }}" style="font-size: 16px;"></iconify-icon>
                            </button>
                        </form>
                        <button type="button" class="btn btn-danger btn-sm btn-icon" data-url="{{ route('admin.products.destroy', $product) }}" data-name="{{ $product->name }}" onclick="confirmDelete(this.dataset.url, this.dataset.name)">
                            <iconify-icon icon="fluent-emoji-flat:wastebasket" style="font-size: 16px;"></iconify-icon>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    @if ($products->hasPages())
    <div class="pagination-wrap">
        <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; margin: 0 16px;">
            <span style="font-size:13px; color:var(--text-muted);">
                Menampilkan {{ $products->firstItem() }}–{{ $products->lastItem() }} dari
                {{ $products->total() }} produk
            </span>
            {{ $products->links('admin.partials.pagination') }}
        </div>
    </div>
    @endif
</div>

<!-- Modal: Konfirmasi Hapus Massal -->
<div class="modal-overlay" id="bulkConfirmModal">
    <div class="modal-box">
        <div class="modal-icon-wrap danger-icon">
            <iconify-icon icon="lucide:trash-2" style="font-size: 32px; color: var(--danger);"></iconify-icon>
        </div>
        <div class="modal-title">Konfirmasi Hapus Massal</div>
        <div class="modal-body" id="bulkConfirmModalBody">Apakah Anda yakin ingin menghapus produk yang dipilih? Tindakan ini tidak dapat dibatalkan.</div>
        <div class="modal-actions">
            <button type="button" class="btn btn-secondary" onclick="_closeModal('bulkConfirmModal')" style="display: inline-flex; align-items: center; gap: 4px;">
                <iconify-icon icon="lucide:x" style="font-size: 16px;"></iconify-icon> Batal
            </button>
            <form id="bulk-delete-form" action="{{ route('admin.products.bulk-destroy') }}" method="POST">
                @csrf
                @method('DELETE')
                <div id="bulk-delete-ids"></div>
                <button type="submit" class="btn btn-danger" onclick="_closeModal('bulkConfirmModal'); showLoading('Menghapus produk terpilih…')" style="display: inline-flex; align-items: center; gap: 4px;">
                    <iconify-icon icon="fluent-emoji-flat:wastebasket" style="font-size: 16px;"></iconify-icon> Ya, Hapus Semua
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkAll = document.getElementById('check-all');
        const checkboxes = document.querySelectorAll('.product-checkbox');
        const btnBulkDelete = document.getElementById('btn-bulk-delete');
        const selectedCount = document.getElementById('selected-count');

        function updateBulkDeleteButton() {
            const checkedCount = document.querySelectorAll('.product-checkbox:checked').length;
            if (checkedCount > 0) {
                btnBulkDelete.style.display = 'inline-flex';
                selectedCount.textContent = checkedCount;
            } else {
                btnBulkDelete.style.display = 'none';
            }
        }

        if (checkAll) {
            checkAll.addEventListener('change', function() {
                checkboxes.forEach(cb => {
                    cb.checked = checkAll.checked;
                });
                updateBulkDeleteButton();
            });
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                // If any is unchecked, checkAll should be unchecked
                if (!this.checked) {
                    if (checkAll) checkAll.checked = false;
                } else {
                    // Check if all checkboxes are checked
                    const allChecked = Array.from(checkboxes).every(c => c.checked);
                    if (checkAll) checkAll.checked = allChecked;
                }
                updateBulkDeleteButton();
            });
        });

        window.confirmBulkDelete = function() {
            const checkedCheckboxes = document.querySelectorAll('.product-checkbox:checked');
            if (checkedCheckboxes.length === 0) return;

            const bulkConfirmModalBody = document.getElementById('bulkConfirmModalBody');
            if (bulkConfirmModalBody) {
                bulkConfirmModalBody.textContent = `Apakah Anda yakin ingin menghapus ${checkedCheckboxes.length} produk terpilih? Tindakan ini tidak dapat dibatalkan.`;
            }

            const idsContainer = document.getElementById('bulk-delete-ids');
            if (idsContainer) {
                idsContainer.innerHTML = '';
                checkedCheckboxes.forEach(cb => {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'ids[]';
                    hiddenInput.value = cb.value;
                    idsContainer.appendChild(hiddenInput);
                });
            }

            _openModal('bulkConfirmModal');
        }
    });
</script>
@endpush
@endsection