<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') — {{ \App\Models\Setting::get('store_name', 'BSI Cyber Store') }}</title>
    @php
        $faviconSetting = \App\Models\Setting::get('store_logo');
        $faviconUrl = $faviconSetting ? \Storage::disk('public')->url($faviconSetting) : asset('assets/img/logo-cyberstore.jpg');
    @endphp
    <link rel="icon" href="{{ $faviconUrl }}" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: #0b023e;
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow-x: hidden;
            position: relative;
        }

        .error-bg-glow {
            position: absolute;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(223, 11, 43, 0.25) 0%, rgba(37, 99, 235, 0.15) 50%, transparent 70%);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            filter: blur(40px);
            z-index: 0;
            pointer-events: none;
        }

        .error-container {
            position: relative;
            z-index: 1;
            max-width: 480px;
            width: 100%;
            background: rgba(30, 41, 59, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 24px;
            padding: 40px 32px;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(16px);
            animation: errorPop 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes errorPop {
            0% { transform: scale(0.9); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }

        .error-code-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 6px 16px;
            border-radius: 99px;
            margin-bottom: 20px;
        }

        .code-404 { background: rgba(37, 99, 235, 0.15); color: #60a5fa; border: 1px solid rgba(96, 165, 250, 0.3); }
        .code-500 { background: rgba(223, 11, 43, 0.15); color: #f87171; border: 1px solid rgba(248, 113, 113, 0.3); }
        .code-403 { background: rgba(217, 119, 6, 0.15); color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.3); }
        .code-419 { background: rgba(168, 85, 247, 0.15); color: #c084fc; border: 1px solid rgba(192, 132, 252, 0.3); }
        .code-401 { background: rgba(236, 72, 153, 0.15); color: #f472b6; border: 1px solid rgba(244, 114, 182, 0.3); }
        .code-405 { background: rgba(14, 165, 233, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); }
        .code-429 { background: rgba(234, 88, 12, 0.15); color: #fb923c; border: 1px solid rgba(251, 146, 60, 0.3); }
        .code-503 { background: rgba(100, 116, 139, 0.15); color: #94a3b8; border: 1px solid rgba(148, 163, 184, 0.3); }

        .error-icon-box {
            font-size: 72px;
            margin-bottom: 16px;
            display: inline-block;
        }

        .error-title {
            font-size: 22px;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 10px;
            line-height: 1.3;
        }

        .error-message {
            font-size: 14px;
            color: #94a3b8;
            line-height: 1.6;
            margin-bottom: 28px;
        }

        .error-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-error {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 22px;
            border-radius: 12px;
            font-size: 13.5px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
        }

        .btn-primary-error {
            background: #DF0B2B;
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(223, 11, 43, 0.35);
        }

        .btn-primary-error:hover {
            background: #ba0924;
            transform: translateY(-2px);
        }

        .btn-secondary-error {
            background: rgba(255, 255, 255, 0.08);
            color: #e2e8f0;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .btn-secondary-error:hover {
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="error-bg-glow"></div>
    <div class="error-container">
        @yield('content')
    </div>
</body>
</html>
