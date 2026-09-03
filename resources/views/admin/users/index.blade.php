@extends('admin.layouts.app')
@section('title', 'Kelola Pengguna')
@section('page-title', 'Kelola Pengguna')
@section('breadcrumb')
<span class="breadcrumb-sep">›</span>
<span>Pengguna</span>
@endsection

@section('content')
<!-- Stats Grid -->
<div class="user-stats-grid">
    <div class="user-stat-card">
        <div class="user-stat-icon" style="background:#eff6ff; color:#2563eb;">👥</div>
        <div>
            <div style="font-size:20px; font-weight:800; color:var(--text-primary);">{{ $users->total() }}</div>
            <div style="font-size:12px; font-weight:600; color:var(--text-muted);">Total Pengguna</div>
        </div>
    </div>
    <div class="user-stat-card">
        <div class="user-stat-icon" style="background:#f0fdf4; color:#16a34a;">🛡️</div>
        <div>
            <div style="font-size:20px; font-weight:800; color:var(--text-primary);">
                {{ $users->whereIn('role', ['admin', 'superadmin'])->count() }}
            </div>
            <div style="font-size:12px; font-weight:600; color:var(--text-muted);">Admin Panel</div>
        </div>
    </div>
    <div class="user-stat-card">
        <div class="user-stat-icon" style="background:#fffbeb; color:#d97706;">👤</div>
        <div>
            <div style="font-size:20px; font-weight:800; color:var(--text-primary);">
                {{ $users->where('role', 'customer')->count() }}
            </div>
            <div style="font-size:12px; font-weight:600; color:var(--text-muted);">Pelanggan</div>
        </div>
    </div>
</div>

<!-- Filter Bar Card -->
<div class="user-filter-card">
    <form method="GET" action="{{ route('admin.users.index') }}" class="filter-grid">
        <div class="filter-item" style="flex: 2 1 250px;">
            <input type="text" name="search" class="form-input-custom"
                placeholder="🔍 Cari nama, email, atau telepon..." value="{{ request('search') }}">
        </div>
        <div class="filter-item">
            <select name="role" class="form-input-custom">
                <option value="">Semua Role</option>
                <option value="superadmin" {{ request('role')==='superadmin'?'selected':'' }}>Superadmin</option>
                <option value="admin" {{ request('role')==='admin'?'selected':'' }}>Admin</option>
                <option value="customer" {{ request('role')==='customer'?'selected':'' }}>Customer</option>
            </select>
        </div>
        <div class="filter-item">
            <select name="status" class="form-input-custom">
                <option value="">Semua Status</option>
                <option value="active" {{ request('status')==='active'?'selected':'' }}>Aktif</option>
                <option value="inactive" {{ request('status')==='inactive'?'selected':'' }}>Nonaktif</option>
            </select>
        </div>
        <div class="filter-btn-group">
            <button type="submit" class="btn-primary-gradient" style="padding: 10px 18px;">
                Filter
            </button>
            @if(request()->hasAny(['search','role','status']))
            <a href="{{ route('admin.users.index') }}" class="btn" style="background: #f1f5f9; color: #475569; padding: 10px 16px; border-radius: 10px; font-weight: 700; text-decoration: none;">
                Reset
            </a>
            @endif
        </div>
    </form>
</div>

<!-- User List Container -->
<div class="user-card-wrapper">
    <div class="user-card-header">
        <div style="font-weight: 800; font-size: 16px; color: var(--text-primary); display: flex; align-items: center; gap: 8px;">
            <i class="bi bi-people-fill"></i> Daftar Pengguna Toko
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn-primary-gradient">
            <i class="bi bi-plus-lg"></i> Tambah User Baru
        </a>
    </div>

    @if($users->isEmpty())
    <div style="text-align: center; padding: 48px 24px; color: #94a3b8;">
        <div style="font-size: 40px; margin-bottom: 10px;">👤</div>
        <div style="font-size: 16px; font-weight: 700; color: #475569;">Tidak ada pengguna</div>
        <div style="font-size: 12.5px;">Coba ubah kata kunci atau filter pencarian Anda.</div>
    </div>
    @else

    <!-- Desktop Table View (>768px) -->
    <div class="table-wrapper-custom desktop-table-container">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; font-size: 12px; color: #64748b; text-transform: uppercase;">
                    <th style="padding: 14px 20px;">#</th>
                    <th style="padding: 14px 20px;">Pengguna</th>
                    <th style="padding: 14px 20px;">Telepon</th>
                    <th style="padding: 14px 20px;">Role</th>
                    <th style="padding: 14px 20px;">Status</th>
                    <th style="padding: 14px 20px;">Bergabung</th>
                    <th style="padding: 14px 20px; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr class="table-row-custom" style="border-bottom: 1px solid #f1f5f9; font-size: 13.5px;">
                    <td style="padding: 14px 20px; color: var(--text-muted); font-size: 12px;">{{ $user->id }}</td>
                    <td style="padding: 14px 20px;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 40px; height: 40px; border-radius: 50%; overflow: hidden; flex-shrink: 0; border: 2px solid var(--border);">
                                @if($user->photo)
                                <img src="{{ Storage::disk('public')->url($user->photo) }}" style="width: 100%; height: 100%; object-fit: cover;" alt="{{ $user->name }}">
                                @else
                                <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #0B023E, #2C2458); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 15px; color: #fff;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                @endif
                            </div>
                            <div>
                                <div style="font-weight: 700; color: var(--text-primary);">{{ $user->name }}</div>
                                <div style="font-size: 12px; color: var(--text-muted);">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="padding: 14px 20px; font-size: 12.5px; color: var(--text-muted);">{{ $user->phone ?? '—' }}</td>
                    <td style="padding: 14px 20px;">
                        <span class="badge-type {{ $user->role === 'admin' || $user->role === 'superadmin' ? 'badge-promo' : 'badge-info' }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td style="padding: 14px 20px;">
                        <span class="badge-type {{ $user->is_active ? 'badge-success-custom' : 'badge-danger-custom' }}">
                            {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td style="padding: 14px 20px; font-size: 12.5px; color: var(--text-muted);">{{ $user->created_at->format('d M Y') }}</td>
                    <td style="padding: 14px 20px; text-align: right;">
                        <div class="actions" style="justify-content: flex-end; gap: 6px;">
                            @if($user->role === 'customer')
                            <a href="{{ route('admin.chats.index', ['search' => $user->email]) }}" class="btn btn-sm btn-icon" style="background-color: #0B023E; color: #ffffff;" title="Chat Customer">
                                <iconify-icon icon="lucide:message-square" style="font-size: 16px;"></iconify-icon>
                            </a>
                            @endif
                            @if($user->role !== 'superadmin' || auth()->user()->role === 'superadmin')
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-secondary btn-sm btn-icon" title="Edit">
                                <iconify-icon icon="flat-color-icons:edit-image" style="font-size: 16px;"></iconify-icon>
                            </a>
                            <form method="POST" action="{{ route('admin.users.toggle', $user) }}" style="display:inline;">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-icon {{ $user->is_active?'btn-warning':'btn-success' }}" title="{{ $user->is_active?'Nonaktifkan':'Aktifkan' }}" {{ $user->id===auth()->id()?'disabled':'' }}>
                                    <iconify-icon icon="{{ $user->is_active ? 'flat-color-icons:cancel' : 'flat-color-icons:ok' }}" style="font-size: 16px;"></iconify-icon>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin me-reset password user {{ $user->name }} menjadi \'password\'?')">
                                @csrf
                                <button type="submit" class="btn btn-secondary btn-sm btn-icon" title="Reset Password">
                                    <iconify-icon icon="flat-color-icons:key" style="font-size: 16px;"></iconify-icon>
                                </button>
                            </form>
                            @endif
                            @if(auth()->user()->role==='superadmin')
                            <button type="button" class="btn btn-danger btn-sm btn-icon" title="Hapus" data-url="{{ route('admin.users.destroy',$user) }}" data-name="{{ $user->name }}" onclick="confirmDelete(this.dataset.url, this.dataset.name)" {{ $user->id===auth()->id()?'disabled':'' }}>
                                <iconify-icon icon="fluent-emoji-flat:wastebasket" style="font-size: 16px;"></iconify-icon>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Mobile Card View (<=768px) -->
    <div class="mobile-user-grid">
        @foreach($users as $user)
        <div class="mobile-user-card">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 12px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 44px; height: 44px; border-radius: 50%; overflow: hidden; flex-shrink: 0; border: 2px solid var(--border);">
                        @if($user->photo)
                        <img src="{{ Storage::disk('public')->url($user->photo) }}" style="width: 100%; height: 100%; object-fit: cover;" alt="{{ $user->name }}">
                        @else
                        <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #0B023E, #2C2458); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 16px; color: #fff;">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        @endif
                    </div>
                    <div>
                        <div style="font-weight: 800; font-size: 14px; color: var(--text-primary);">{{ $user->name }}</div>
                        <div style="font-size: 11.5px; color: var(--text-muted);">{{ $user->email }}</div>
                    </div>
                </div>
                <span class="badge-type {{ $user->is_active ? 'badge-success-custom' : 'badge-danger-custom' }}">
                    {{ $user->is_active ? 'Aktif' : 'Off' }}
                </span>
            </div>

            <div style="background: #f8fafc; padding: 10px 12px; border-radius: 10px; font-size: 12px; margin-bottom: 14px; display: flex; justify-content: space-between; align-items: center;">
                <div>📞 {{ $user->phone ?? '—' }}</div>
                <div><span class="badge-type {{ $user->role === 'admin' || $user->role === 'superadmin' ? 'badge-promo' : 'badge-info' }}">{{ ucfirst($user->role) }}</span></div>
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                <div style="font-size: 11px; color: var(--text-muted);">📅 {{ $user->created_at->format('d M Y') }}</div>
                <div class="actions" style="gap: 6px;">
                    @if($user->role === 'customer')
                    <a href="{{ route('admin.chats.index', ['search' => $user->email]) }}" class="btn btn-sm btn-icon" style="background-color: #0B023E; color: #ffffff;" title="Chat Customer">
                        <iconify-icon icon="lucide:message-square" style="font-size: 15px;"></iconify-icon>
                    </a>
                    @endif
                    @if($user->role !== 'superadmin' || auth()->user()->role === 'superadmin')
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-secondary btn-sm btn-icon" title="Edit">
                        <iconify-icon icon="flat-color-icons:edit-image" style="font-size: 15px;"></iconify-icon>
                    </a>
                    <form method="POST" action="{{ route('admin.users.toggle', $user) }}" style="display:inline;">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-sm btn-icon {{ $user->is_active?'btn-warning':'btn-success' }}" title="{{ $user->is_active?'Nonaktifkan':'Aktifkan' }}" {{ $user->id===auth()->id()?'disabled':'' }}>
                            <iconify-icon icon="{{ $user->is_active ? 'flat-color-icons:cancel' : 'flat-color-icons:ok' }}" style="font-size: 15px;"></iconify-icon>
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" style="display:inline;" onsubmit="return confirm('Reset password user {{ $user->name }}?')">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm btn-icon" title="Reset Password">
                            <iconify-icon icon="flat-color-icons:key" style="font-size: 15px;"></iconify-icon>
                        </button>
                    </form>
                    @endif
                    @if(auth()->user()->role==='superadmin')
                    <button type="button" class="btn btn-danger btn-sm btn-icon" title="Hapus" data-url="{{ route('admin.users.destroy',$user) }}" data-name="{{ $user->name }}" onclick="confirmDelete(this.dataset.url, this.dataset.name)" {{ $user->id===auth()->id()?'disabled':'' }}>
                        <iconify-icon icon="fluent-emoji-flat:wastebasket" style="font-size: 15px;"></iconify-icon>
                    </button>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    @if($users->hasPages())
    <div style="padding: 16px 20px; border-top: 1px solid #f1f5f9;">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
            <span style="font-size:12.5px;color:var(--text-muted);">
                Menampilkan {{ $users->firstItem() }}–{{ $users->lastItem() }} dari {{ $users->total() }} pengguna
            </span>
            {{ $users->links('admin.partials.pagination') }}
        </div>
    </div>
    @endif
</div>
@endsection