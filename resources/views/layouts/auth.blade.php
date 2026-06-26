{{-- resources/views/layouts/auth.blade.php --}}
@php
use App\Models\ESetting;
$siteLogo = ESetting::getLogo();
$siteFavicon = ESetting::getFavicon();
$websiteInfo = ESetting::getWebsiteInfo();
$siteName = $websiteInfo['name'] ?? 'Admin Panel';
@endphp
<!doctype html>
<html lang="vi" ng-app="@yield('ng-app', 'authApp')">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - {{ $siteName }}</title>

    <!-- Favicon -->
    @if($siteFavicon)
    <link rel="icon" type="image/x-icon" href="{{ $siteFavicon }}">
    <link rel="shortcut icon" href="{{ $siteFavicon }}">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.css">
    <style>
        :root {
            --primary-color: #006545;
            --primary-hover: #004d34;
            --primary-light: rgba(0, 101, 69, 0.1);
            --gradient-start: #0f172a;
            --gradient-end: #1e3a5f;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --text-muted: #9ca3af;
            --border-color: #e5e7eb;
            --border-focus: #006545;
            --bg-input: #f9fafb;
            --success-color: #10b981;
            --success-light: rgba(16, 185, 129, 0.1);
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-end) 50%, var(--primary-color) 100%);
            background-attachment: fixed;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image:
                radial-gradient(circle at 20% 80%, rgba(0, 101, 69, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(30, 58, 95, 0.4) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(0, 101, 69, 0.2) 0%, transparent 40%);
            animation: pulse-bg 15s ease-in-out infinite;
        }

        @keyframes pulse-bg {

            0%,
            100% {
                opacity: 0.8;
            }

            50% {
                opacity: 1;
            }
        }

        .bg-shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.1;
            animation: float 20s ease-in-out infinite;
        }

        .bg-shape-1 {
            width: 300px;
            height: 300px;
            background: var(--primary-color);
            top: -100px;
            right: -100px;
        }

        .bg-shape-2 {
            width: 200px;
            height: 200px;
            background: #fff;
            bottom: -50px;
            left: -50px;
            animation-delay: 5s;
        }

        .bg-shape-3 {
            width: 150px;
            height: 150px;
            background: var(--primary-color);
            top: 50%;
            left: 10%;
            animation-delay: 10s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translate(0, 0) rotate(0deg);
            }

            25% {
                transform: translate(10px, -20px) rotate(5deg);
            }

            50% {
                transform: translate(-10px, 10px) rotate(-5deg);
            }

            75% {
                transform: translate(20px, 10px) rotate(3deg);
            }
        }

        .auth-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 420px;
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: var(--shadow-xl), 0 0 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-section {
            padding: 40px 40px 24px;
            text-align: center;
            background: linear-gradient(180deg, var(--primary-light) 0%, transparent 100%);
        }

        .logo-wrapper {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
        }

        .logo-wrapper img {
            max-height: 80px;
            max-width: 200px;
            object-fit: contain;
            filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
            transition: transform 0.3s ease;
        }

        .logo-wrapper:hover img {
            transform: scale(1.05);
        }

        .logo-placeholder {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-hover) 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-md);
        }

        .logo-placeholder i {
            font-size: 36px;
            color: #fff;
        }

        .welcome-text {
            margin-top: 8px;
        }

        .welcome-text h1 {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 6px;
        }

        .welcome-text p {
            font-size: 14px;
            color: var(--text-secondary);
            line-height: 1.5;
        }

        .form-section {
            padding: 24px 40px 40px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
            letter-spacing: 0.02em;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i.input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 16px;
            transition: color 0.2s ease;
            z-index: 2;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            font-size: 15px;
            font-family: inherit;
            color: var(--text-primary);
            background: var(--bg-input);
            border: 2px solid var(--border-color);
            border-radius: 12px;
            outline: none;
            transition: all 0.2s ease;
        }

        .form-input:focus {
            background: #fff;
            border-color: var(--border-focus);
            box-shadow: 0 0 0 4px var(--primary-light);
        }

        .form-input:focus+.input-icon,
        .input-wrapper:focus-within i.input-icon {
            color: var(--primary-color);
        }

        .form-input::placeholder {
            color: var(--text-muted);
        }

        .form-input:disabled {
            background: var(--bg-input);
            color: var(--text-secondary);
            cursor: not-allowed;
        }

        .password-wrapper {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s ease;
            z-index: 2;
        }

        .password-toggle:hover {
            color: var(--primary-color);
        }

        .password-wrapper .form-input {
            padding-right: 48px;
        }

        .btn-submit {
            width: 100%;
            padding: 16px 24px;
            font-size: 15px;
            font-weight: 600;
            font-family: inherit;
            color: #fff;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-hover) 100%);
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 14px rgba(0, 101, 69, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 28px;
        }

        .btn-submit:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 101, 69, 0.45);
        }

        .btn-submit:active:not(:disabled) {
            transform: translateY(0);
        }

        .btn-submit:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .btn-submit i.fa-spinner {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .success-message {
            background: var(--success-light);
            border: 2px solid var(--success-color);
            border-radius: 12px;
            padding: 24px;
            text-align: center;
            margin-bottom: 20px;
        }

        .success-message .success-icon {
            width: 60px;
            height: 60px;
            background: var(--success-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
        }

        .success-message .success-icon i {
            font-size: 28px;
            color: #fff;
        }

        .success-message h3 {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .success-message p {
            font-size: 14px;
            color: var(--text-secondary);
            line-height: 1.5;
            margin-bottom: 16px;
        }

        .success-message .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: var(--primary-color);
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.2s ease;
        }

        .success-message .btn-action:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 28px 0 20px;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border-color);
        }

        .divider span {
            padding: 0 16px;
            font-size: 13px;
            color: var(--text-muted);
        }

        .auth-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 14px;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .auth-link:hover {
            color: var(--primary-hover);
            gap: 12px;
        }

        .auth-link i {
            transition: transform 0.2s ease;
        }

        .auth-link:hover i.fa-arrow-left {
            transform: translateX(-4px);
        }

        .auth-footer {
            text-align: center;
            padding: 24px 40px;
            background: var(--bg-input);
            border-top: 1px solid var(--border-color);
        }

        .auth-footer p {
            font-size: 13px;
            color: var(--text-muted);
        }

        .auth-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .auth-card {
                border-radius: 20px;
            }

            .logo-section {
                padding: 32px 24px 20px;
            }

            .logo-wrapper img {
                max-height: 60px;
            }

            .welcome-text h1 {
                font-size: 20px;
            }

            .form-section {
                padding: 20px 24px 32px;
            }

            .auth-footer {
                padding: 20px 24px;
            }
        }

        [ng-cloak],
        .ng-cloak {
            display: none !important;
        }

        @stack('styles')
    </style>
</head>

<body ng-controller="@yield('ng-controller', 'AuthCtrl')" ng-cloak>
    <div class="bg-shape bg-shape-1"></div>
    <div class="bg-shape bg-shape-2"></div>
    <div class="bg-shape bg-shape-3"></div>

    <div class="auth-container">
        <div class="auth-card">
            <!-- Logo Section -->
            <div class="logo-section">
                <div class="logo-wrapper">
                    @if($siteLogo)
                    <img src="{{ $siteLogo }}" alt="{{ $siteName }}">
                    @else
                    <div class="logo-placeholder">
                        <i class="fas fa-shield-halved"></i>
                    </div>
                    @endif
                </div>
                <div class="welcome-text">
                    <h1>@yield('heading')</h1>
                    <p>@yield('subheading')</p>
                </div>
            </div>

            <!-- Form Section -->
            <div class="form-section">
                @yield('content')
            </div>

            <!-- Footer -->
            <div class="auth-footer">
                <p>&copy; {{ date('Y') }} {{ $siteName }}. All rights reserved.</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.js"></script>
    @stack('scripts')
</body>

</html>