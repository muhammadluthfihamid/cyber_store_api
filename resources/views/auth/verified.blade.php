@php
    $storeName = \App\Models\Setting::get('store_name', 'Cyber Store');
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Akun Berhasil — {{ $storeName }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>
    <style>
        :root {
            --primary: #0d47a1;
            --success: #10b981;
            --success-light: rgba(16, 185, 129, 0.1);
            --bg: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-sub: #64748b;
            --border: #e2e8f0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .verified-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 20px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            max-width: 480px;
            width: 100%;
            padding: 40px 32px;
            text-align: center;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .icon-wrapper {
            width: 80px;
            height: 80px;
            background-color: var(--success-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            color: var(--success);
            font-size: 40px;
            animation: scaleIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) 0.2s both;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
            }

            to {
                transform: scale(1);
            }
        }

        .title {
            font-size: 22px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 12px;
        }

        .message {
            font-size: 14.5px;
            line-height: 1.6;
            color: var(--text-sub);
            margin-bottom: 32px;
        }

        .btn-ok {
            display: inline-block;
            background-color: var(--primary);
            color: #ffffff;
            text-decoration: none;
            padding: 12px 32px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(13, 71, 161, 0.2);
        }

        .btn-ok:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(13, 71, 161, 0.3);
            background-color: #0b3c8f;
        }

        .footer {
            margin-top: 32px;
            font-size: 11px;
            color: var(--text-sub);
            border-top: 1px solid var(--border);
            padding-top: 20px;
        }
    </style>
</head>

<body>
    <div class="verified-card">
        <div class="icon-wrapper">
            <iconify-icon icon="flat-color-icons:ok" style="font-size: 44px;"></iconify-icon>
        </div>
        <h1 class="title">Verifikasi Berhasil!</h1>
        <p class="message">
            Halo <strong>{{ $name }}</strong>, email Anda telah berhasil diverifikasi. Akun Anda sekarang telah aktif. Silakan kembali ke aplikasi <strong>{{ $storeName }}</strong> di handphone Anda untuk masuk.
        </p>
        <a href="cyberstore://" class="btn-ok">Buka Aplikasi {{ $storeName }}</a>
        <div class="footer">
            © {{ date('Y') }} {{ $storeName }}. Hak Cipta Dilindungi.
        </div>
    </div>

    <script>
        // Redirect otomatis ke aplikasi Cyber Store setelah 1.5 detik
        setTimeout(function() {
            window.location.href = "cyberstore://";
        }, 1500);
    </script>
</body>

</html>