@php
$loginLogoSetting = \App\Models\Setting::get('store_logo');
$loginLogoUrl = $loginLogoSetting ? \Illuminate\Support\Facades\Storage::disk('public')->url($loginLogoSetting) : asset('assets/img/logo-cyberstore.jpg');
$storeName = \App\Models\Setting::get('store_name', 'BSI Cyber Store');
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login Admin — {{ $storeName }}</title>
    <link rel="icon" href="{{ $loginLogoUrl }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('/assets/css/login.css')}}?v=7">
    <script src="https://code.iconify.design/iconify-icon/1.0.8/iconify-icon.min.js"></script>
</head>

<body>
    <div class="login-wrap">
        <!-- Left Column: Logo & Brand Info -->
        <div class="login-left">
            <img src="{{ $loginLogoUrl }}" alt="{{ $storeName }} Logo" class="brand-logo" style="border-radius: 20px; max-height: 160px; object-fit: contain;">
            <h1 class="brand-title">{{ $storeName }}</h1>
            <p class="brand-subtitle">Panel Administrasi Toko</p>
        </div>

        <!-- Right Column: Form & Footer -->
        <div class="login-right">
            <div class="login-card">
                <div class="login-title">Masuk ke Admin Panel</div>

                @if($errors->any())
                <div class="alert-danger">{{ $errors->first() }}</div>
                @endif

                @if(session('error'))
                <div class="alert-danger">{{ session('error') }}</div>
                @endif

                @if(session('success'))
                <div class="alert-success">{{ session('success') }}</div>
                @endif

                <form method="POST" action="{{ route('admin.login.submit') }}" id="loginForm">
                    @csrf
                    <div class="form-group">
                        <label class="form-label" for="email">Email Admin</label>
                        <div class="input-icon-wrap">
                            <span class="input-icon">
                                <iconify-icon icon="solar:letter-bold-duotone" style="font-size: 18px;"></iconify-icon>
                            </span>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-control"
                                value="{{ old('email') }}"
                                placeholder="Masukan Email..."
                                required
                                autocomplete="email">
                        </div>
                        @error('email')
                        <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <div class="input-icon-wrap" style="position: relative;">
                            <span class="input-icon">
                                <iconify-icon icon="solar:lock-keyhole-bold-duotone" style="font-size: 18px;"></iconify-icon>
                            </span>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control"
                                placeholder="••••••••"
                                required
                                autocomplete="current-password"
                                style="padding-right: 42px;">
                            <button
                                type="button"
                                id="togglePassword"
                                style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text-muted); font-size: 16px; display: flex; align-items: center; justify-content: center; z-index: 10; padding: 4px;"
                                title="Tampilkan/Sembunyikan Password">
                                <iconify-icon icon="solar:eye-closed-bold-duotone" style="font-size: 20px;"></iconify-icon>
                            </button>
                        </div>
                    </div>

                    <div class="remember-row">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Ingat saya</label>
                    </div>

                    <button type="submit" class="btn-login" id="submitBtn">
                        Masuk ke Panel
                    </button>
                </form>

                <div class="demo-box">
                    <div class="demo-title" style="display: flex; align-items: center; gap: 6px;">
                        <iconify-icon icon="solar:rocket-bold-duotone" style="color: #DF0B2B; font-size: 18px;"></iconify-icon>
                        <span>Cyber Store Apps</span>
                    </div>
                    <div style="font-size: 13px; color: var(--text-secondary); line-height: 1.5; margin-bottom: 12px;">
                        Solusi E-Commerce modern menjual produk merchandise resmi Kampus.
                    </div>
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap;">
                        <span class="demo-badge demo-sa" style="background: rgba(223, 11, 43, 0.2); color: #ff4d6d; padding: 4px 10px; font-size: 11px; display: inline-flex; align-items: center; gap: 4px;">
                            <iconify-icon icon="solar:bolt-bold-duotone" style="font-size: 14px;"></iconify-icon> Mobile App Ready
                        </span>
                        <span style="font-size: 12px; color: var(--accent); font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                            Tingkatkan Penjualan 10x <iconify-icon icon="solar:graph-up-bold-duotone" style="font-size: 16px;"></iconify-icon>
                        </span>
                    </div>
                </div>
            </div>

            <div class="login-footer">
                © Powered by BTI-BSI {{ date('Y') }} . Admin Panel v1.0
            </div>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            btn.textContent = 'Memproses...';
            btn.disabled = true;
        });

        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');

            // Toggle Iconify icon dynamically
            this.innerHTML = isPassword ?
                '<iconify-icon icon="solar:eye-bold-duotone" style="font-size: 20px;"></iconify-icon>' :
                '<iconify-icon icon="solar:eye-closed-bold-duotone" style="font-size: 20px;"></iconify-icon>';
        });
    </script>
</body>

</html>