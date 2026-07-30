<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simojang | Kanreg Tilu</title>
    <link rel="shortcut icon" href="<?= base_url('apps/');?>assets/images/logo/favicon.png?v=2" type="image/x-icon">
    <link rel="shortcut icon" href="<?= base_url('apps/');?>assets/images/logo/favicon.png?v=2" type="image/png">
    <link rel="stylesheet" href="<?= asset_url('apps/assets/css/main/app.css?v=' . time()) ?>">
    <link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/auth.css') ?>">
    <link rel="stylesheet" href="<?= base_url('apps/');?>assets/extensions/toastify-js/src/toastify.css">
    <link rel="stylesheet" href="<?= base_url('apps/');?>assets/extensions/sweetalert2/sweetalert2.min.css">
    <script src="<?= base_url('apps/'); ?>assets/extensions/jquery/jquery.min.js"></script>
    <script src="<?= base_url('apps/'); ?>assets/extensions/toastify-js/src/toastify.js"></script>
    <script src="<?= base_url('apps/'); ?>assets/extensions/sweetalert2/sweetalert2.all.min.js"></script>
    <script src="<?= base_url('apps/'); ?>assets/js/custom/config.js"></script>
    <script src="<?= asset_url('apps/assets/js/custom/authprocess.js') ?>"></script>
</head>

<body>
    <div id="auth">
        <div class="row h-100">
            <div class="col-lg-4 col-12 position-relative z-1">
                <div id="auth-left">
                    <div class="auth-logo mb-4 stagger-item" style="animation-delay: 0.1s;">
                        <a href="#"><img src="<?= base_url('apps/');?>assets/images/logo/logo.png" alt="Logo"></a>
                    </div>

                    <h1 class="auth-title mb-1 stagger-item" style="animation-delay: 0.2s;">Simojang</h1>
                    <p class="auth-subtitle mt-0 mb-2 stagger-item" style="animation-delay: 0.3s;" id="greeting-text">Masuk untuk mengelola layanan dan aktivitas harian.</p>
                    <form id="loginForm" class="login-form stagger-item" style="animation-delay: 0.4s;">
                        <label for="o_userlogin">Nama Pengguna</label>
                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="text" class="form-control form-control-lg rounded" id="o_userlogin" name="o_userlogin" required
                                placeholder="Masukkan username atau NIP" autocomplete="username">
                            <div class="form-control-icon">
                                <i class="bi bi-person"></i>
                            </div>
                            <div class="invalid-feedback" id="o_userlogin_feedback"></div>
                        </div>
                        <label for="passwordnew">Kata Sandi</label>
                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="password" class="form-control form-control-lg rounded" id="passwordnew"
                                name="o_password" required placeholder="Masukkan kata sandi" autocomplete="current-password">
                            <div class="form-control-icon">
                                <i class="bi bi-shield-lock"></i>
                            </div>
                            <button type="button" class="password-toggle" id="togglePassword" aria-label="Tampilkan kata sandi">
                                <i class="bi bi-eye"></i>
                            </button>
                            <div class="invalid-feedback" id="o_password_feedback"></div>
                            <small class="text-danger d-none caps-lock-hint" id="capsLockHint">
                                Caps Lock aktif.
                            </small>
                        </div>
                        <div class="form-check mb-2 d-none">
                            <input class="form-check-input" type="checkbox" value="1" id="rememberLogin" name="remember_login">
                            <label class="form-check-label" for="rememberLogin">Ingat saya di perangkat ini</label>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block btn-lg mt-2 rounded auth-submit-btn">
                            <b>Masuk</b> <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                        <div class="text-center mt-1"><small class="text-muted">2026 &copy; Didukung oleh SIDIGI - Kanreg III BKN</small></div>
                    </form>
                </div>
            </div>
            <div class="col-lg-8 d-none d-lg-block position-relative p-0">
                <div id="auth-right"></div>
            </div>
        </div>
    </div>
    <?php $flashError = session()->getFlashdata('error'); ?>
    <?php if (!empty($flashError)): ?>
    <script>
    (function () {
        var message = <?= json_encode((string) $flashError) ?>;
        var normalized = String(message || '').toLowerCase();
        var isSessionIssue =
            normalized.includes('token expired') ||
            normalized.includes('invalid token') ||
            normalized.includes('you must log in first') ||
            normalized.includes('sesi login tidak valid');

        if (!isSessionIssue || typeof Swal === 'undefined') return;

        var svg = `
            <svg class="auth-session-expired-icon" width="112" height="112" viewBox="0 0 112 112" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <circle cx="56" cy="56" r="56" fill="#FFF3F2"/>
                <circle cx="56" cy="56" r="37" fill="#FFE4E2"/>
                <path d="M56 37v18l10 7" stroke="#D63031" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="56" cy="56" r="24" stroke="#D63031" stroke-width="5"/>
            </svg>
        `;

        Swal.fire({
            html: '<div class="auth-session-expired-wrap">' +
                '<div class="auth-session-expired-visual">' + svg + '</div>' +
                '<strong class="auth-session-expired-heading">Sesi Berakhir</strong>' +
                '<p class="auth-session-expired-text">Sesi anda telah selesai, silahkan login kembali</p>' +
            '</div>',
            showCancelButton: false,
            confirmButtonText: 'Login Kembali',
            allowOutsideClick: false,
            allowEscapeKey: true,
            customClass: {
                popup: 'auth-session-expired-swal'
            }
        });
    })();
    </script>
    <?php endif; ?>
</body>

</html>
