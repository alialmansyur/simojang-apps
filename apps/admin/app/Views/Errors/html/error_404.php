<?php
/**
 * SIMOJANG - Kanreg III BKN
 * Custom Global 404 Error Page
 */

$isLoggedIn = false;
$userFullname = '';

try {
    $session = \Config\Services::session();
    $jwtToken = $session->get('jwt_auth_token');
    $userId = $session->get('userid');

    if (!empty($jwtToken) && !empty($userId)) {
        $jwtSecret = getenv('JWT_TOKEN_SECRET') ?: (env('JWT_TOKEN_SECRET') ?: 'simojang_default_jwt_secret_key_2026');
        try {
            $decoded = \Firebase\JWT\JWT::decode($jwtToken, new \Firebase\JWT\Key($jwtSecret, 'HS256'));
            if (!empty($decoded->user_id)) {
                $isLoggedIn = true;
                $userFullname = (string) ($session->get('fullname') ?? $decoded->user_fullname ?? '');
            }
        } catch (\Throwable $e) {
            $isLoggedIn = false;
        }
    }
} catch (\Throwable $e) {
    $isLoggedIn = false;
}

$actionUrl = $isLoggedIn ? base_url('dashboard') : base_url('login');
$actionLabel = $isLoggedIn ? 'Kembali ke Home' : 'Login';
$actionIcon = $isLoggedIn ? 'bi-house-door-fill' : 'bi-box-arrow-in-right';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Halaman Tidak Ditemukan | Simojang</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="<?= asset_url('apps/assets/images/logo/favicon.svg') ?>" type="image/x-icon">
    <link rel="shortcut icon" href="<?= asset_url('apps/assets/images/logo/favicon.png') ?>" type="image/png">
    
    <!-- Fonts & External Stylesheets -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset_url('apps/assets/css/main/app.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('apps/assets/css/main/custom.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('apps/assets/extensions/bootstrap-icons/font/bootstrap-icons.css') ?>">

    <style>
        :root {
            --app-primary: #1040c1 !important;
            --app-primary-hover: #0d34a0 !important;
            --app-primary-active: #0b2e8d !important;
            --app-primary-rgb: 16, 64, 193 !important;
            --app-primary-soft: rgba(16, 64, 193, 0.08);
            --app-primary-soft-border: rgba(16, 64, 193, 0.18);
        }

        html, body {
            height: 100%;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        *, *::before, *::after {
            box-sizing: inherit;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
            background-image: 
                radial-gradient(at 0% 0%, rgba(16, 64, 193, 0.05) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(16, 64, 193, 0.03) 0px, transparent 50%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1e293b;
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        .error-wrapper {
            width: 100%;
            max-width: 520px;
            margin: auto;
            padding: 1.5rem 1rem;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        /* SVG Warning Illustration */
        .illustration-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.25rem;
        }

        .warning-illustration {
            width: 100%;
            max-width: 210px;
            height: auto;
            display: block;
            filter: drop-shadow(0 10px 20px rgba(16, 64, 193, 0.12));
            animation: floatIllustration 4s ease-in-out infinite;
        }

        @keyframes floatIllustration {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-6px);
            }
        }

        .error-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            background-color: var(--app-primary-soft);
            color: var(--app-primary);
            border: 1px solid var(--app-primary-soft-border);
            font-size: 0.775rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            margin-bottom: 0.85rem;
            text-transform: uppercase;
        }

        .error-heading {
            font-size: 1.65rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.25;
            margin-bottom: 0.5rem;
            letter-spacing: -0.02em;
        }

        .error-description {
            font-size: 0.925rem;
            color: #64748b;
            line-height: 1.55;
            max-width: 420px;
            margin: 0 auto 1.35rem auto;
            font-weight: 400;
        }

        .action-group {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-action-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            background-color: #1040c1 !important;
            color: #ffffff !important;
            border: 1px solid #1040c1 !important;
            font-size: 0.875rem;
            font-weight: 600;
            padding: 0.55rem 1.35rem;
            border-radius: 0.5rem;
            text-decoration: none;
            box-shadow: 0 4px 14px rgba(16, 64, 193, 0.28);
            transition: all 0.2s ease-in-out;
            cursor: pointer;
            width: auto;
        }

        .btn-action-primary:hover,
        .btn-action-primary:focus {
            background-color: #0d34a0 !important;
            border-color: #0d34a0 !important;
            color: #ffffff !important;
            box-shadow: 0 6px 18px rgba(16, 64, 193, 0.38);
            transform: translateY(-1.5px);
        }

        .btn-action-primary:active {
            background-color: #0b2e8d !important;
            transform: translateY(0);
            box-shadow: 0 2px 6px rgba(16, 64, 193, 0.2);
        }

        .btn-action-primary i {
            font-size: 0.95rem;
            line-height: 1;
        }

        .app-footer-note {
            margin-top: 1.75rem;
            font-size: 0.775rem;
            color: #94a3b8;
            font-weight: 500;
        }

        @media (max-width: 576px) {
            .warning-illustration {
                max-width: 175px;
            }

            .error-heading {
                font-size: 1.4rem;
            }

            .error-description {
                font-size: 0.875rem;
                margin-bottom: 1.25rem;
            }

            .btn-action-primary {
                padding: 0.5rem 1.25rem;
                font-size: 0.85rem;
            }
        }
    </style>
</head>

<body>
    <div class="error-wrapper">
        <!-- 1 SVG Warning Illustration -->
        <div class="illustration-wrapper">
            <svg class="warning-illustration" viewBox="0 0 280 200" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="Ilustrasi Peringatan Halaman 404">
                <defs>
                    <!-- Background Glow Gradients -->
                    <linearGradient id="bgGlow" x1="40" y1="20" x2="240" y2="180" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#1040C1" stop-opacity="0.12"/>
                        <stop offset="1" stop-color="#2563EB" stop-opacity="0.02"/>
                    </linearGradient>
                    <linearGradient id="primaryGrad" x1="60" y1="40" x2="220" y2="160" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#1040C1"/>
                        <stop offset="1" stop-color="#1D4ED8"/>
                    </linearGradient>
                    <linearGradient id="warningGrad" x1="140" y1="65" x2="140" y2="135" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#F59E0B"/>
                        <stop offset="1" stop-color="#D97706"/>
                    </linearGradient>
                    <linearGradient id="numberGrad" x1="0" y1="0" x2="280" y2="0" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#E2E8F0"/>
                        <stop offset="0.5" stop-color="#CBD5E1"/>
                        <stop offset="1" stop-color="#E2E8F0"/>
                    </linearGradient>
                    <!-- Drop Shadow Filter -->
                    <filter id="softShadow" x="90" y="45" width="100" height="105" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                        <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                        <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
                        <feOffset dy="8"/>
                        <feGaussianBlur stdDeviation="8"/>
                        <feComposite in2="hardAlpha" operator="out"/>
                        <feColorMatrix type="matrix" values="0 0 0 0 0.0627 0 0 0 0 0.251 0 0 0 0 0.757 0 0 0 0.22 0"/>
                        <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow"/>
                        <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow" result="shape"/>
                    </filter>
                </defs>

                <!-- Background Soft Shapes -->
                <rect x="25" y="20" width="230" height="155" rx="20" fill="url(#bgGlow)" />
                <circle cx="50" cy="45" r="4" fill="#1040C1" fill-opacity="0.25" />
                <circle cx="230" cy="45" r="4" fill="#1040C1" fill-opacity="0.25" />
                <circle cx="45" cy="155" r="5" fill="#F59E0B" fill-opacity="0.3" />
                <circle cx="235" cy="150" r="3" fill="#1040C1" fill-opacity="0.3" />

                <!-- Background Watermark 404 Text -->
                <text x="38" y="130" font-family="'Inter', sans-serif" font-size="78" font-weight="900" fill="url(#numberGrad)" letter-spacing="-3">4</text>
                <text x="186" y="130" font-family="'Inter', sans-serif" font-size="78" font-weight="900" fill="url(#numberGrad)" letter-spacing="-3">4</text>

                <!-- Central Warning Shield / Card -->
                <g filter="url(#softShadow)">
                    <!-- Outer Base -->
                    <rect x="105" y="55" width="70" height="70" rx="18" fill="#FFFFFF" />
                    <rect x="105" y="55" width="70" height="70" rx="18" stroke="#E2E8F0" stroke-width="1.5" />
                    
                    <!-- Inner Warning Triangle Container -->
                    <path d="M140 68L163 108C164.2 110.1 162.7 113 160.3 113H119.7C117.3 113 115.8 110.1 117 108L140 68Z" fill="url(#warningGrad)" />
                    
                    <!-- Warning Exclamation Mark inside Triangle -->
                    <rect x="138.5" y="81" width="3" height="16" rx="1.5" fill="#FFFFFF" />
                    <circle cx="140" cy="103" r="1.75" fill="#FFFFFF" />
                </g>

                <!-- Subtle Connecting Radar Circles -->
                <circle cx="140" cy="90" r="48" stroke="#1040C1" stroke-width="1.5" stroke-dasharray="4 4" stroke-opacity="0.35" />
                <circle cx="140" cy="90" r="62" stroke="#1040C1" stroke-width="1" stroke-dasharray="2 4" stroke-opacity="0.2" />

                <!-- Decorative Floating Elements -->
                <rect x="75" y="50" width="14" height="14" rx="4" fill="#1040C1" fill-opacity="0.1" transform="rotate(15 75 50)" />
                <rect x="195" y="130" width="16" height="16" rx="4" fill="#F59E0B" fill-opacity="0.15" transform="rotate(-12 195 130)" />
            </svg>
        </div>

        <!-- Badge -->
        <div class="error-badge">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>Galat 404</span>
        </div>

        <!-- Heading -->
        <h1 class="error-heading">Halaman Tidak Ditemukan</h1>

        <!-- Short Description -->
        <p class="error-description">
            Maaf, halaman yang Anda tuju tidak ditemukan, telah dipindahkan, atau alamat URL yang dimasukkan salah. Silakan periksa kembali tautan Anda.
        </p>

        <!-- Action Button -->
        <div class="action-group">
            <a href="<?= esc($actionUrl, 'attr') ?>" class="btn-action-primary">
                <i class="bi <?= esc($actionIcon, 'attr') ?>"></i>
                <span><?= esc($actionLabel) ?></span>
            </a>
        </div>

        <!-- Subtitle/Footer -->
        <div class="app-footer-note">
            2026 &copy; SIMOJANG &bull; Kantor Regional III BKN
        </div>
    </div>
</body>

</html>
