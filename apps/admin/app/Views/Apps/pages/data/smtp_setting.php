<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>

<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork-common.css') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork.css?v=99') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/role-manager.css?v=' . time()) ?>">
<style>
/* ==============================================================
   SMTP Setting Custom Stylesheet - SIMOJANG Design System
   ============================================================== */
.smtp-manager-wrap {
    padding-bottom: 2.5rem;
}

/* Summary Banner Card */
.smtp-summary-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.25rem 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    transition: all 0.2s ease;
}

.smtp-banner-icon-box {
    width: 58px;
    height: 58px;
    min-width: 58px;
    border-radius: 14px;
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    color: #1040c1;
    border: 1px solid #bfdbfe;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    line-height: 0;
    box-shadow: 0 2px 6px rgba(16, 64, 193, 0.08);
}

.smtp-banner-icon-box svg,
.smtp-banner-icon-box i {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
    margin: 0;
    padding: 0;
}

.smtp-stat-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.smtp-stat-label {
    display: block;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #64748b;
    margin-bottom: 0.15rem;
}

.smtp-stat-value {
    font-size: 0.95rem;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.2;
}

/* Card Form */
.smtp-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    overflow: hidden;
}

.smtp-card-header {
    background-color: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: 1.1rem 1.5rem;
}

.smtp-card-body {
    padding: 1.75rem 1.5rem;
}

.smtp-card-footer {
    background-color: #f8fafc;
    border-top: 1px solid #e2e8f0;
    padding: 1.1rem 1.5rem;
}

/* Form Controls */
.form-label,
label {
    font-family: inherit;
    font-weight: 600;
    color: #1e293b;
    font-size: 0.95rem;
    line-height: 1.4;
    margin-bottom: 0.45rem;
    display: inline-block;
}

.modal-body .form-label,
.modal-body label {
    font-family: inherit;
    font-weight: 600;
    color: #1e293b;
    font-size: 0.95rem;
    line-height: 1.4;
    margin-bottom: 0.45rem;
    display: inline-block;
}

.input-group-text {
    background-color: #f8fafc;
    border-color: #cbd5e1;
    color: #64748b;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 42px;
    font-size: 1rem;
    line-height: 1;
}

.input-group-text i,
.input-group-text svg {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}

.form-control, .form-select {
    border-color: #cbd5e1;
    border-radius: 8px;
    height: 42px;
    font-size: 0.95rem;
    color: #0f172a;
}

.form-control:focus, .form-select:focus {
    border-color: #1040c1;
    box-shadow: 0 0 0 3px rgba(16, 64, 193, 0.12);
}

.input-hint {
    font-size: 0.82rem;
    color: #64748b;
    margin-top: 0.35rem;
    line-height: 1.4;
}

/* Port Quick Pills */
.port-badge {
    cursor: pointer;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.22rem 0.55rem;
    background-color: #f1f5f9;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    color: #475569;
    transition: all 0.15s ease;
    user-select: none;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

.port-badge:hover {
    background-color: #e2e8f0;
    color: #0f172a;
    border-color: #94a3b8;
}

.port-badge.is-active {
    background-color: #eff6ff;
    color: #1040c1;
    border-color: #93c5fd;
    font-weight: 700;
}

/* Button & Icon Alignment */
.btn-toggle-pwd {
    border-color: #cbd5e1;
    background-color: #f8fafc;
    color: #64748b;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    padding: 0;
    border-radius: 0 8px 8px 0 !important;
}

.btn-toggle-pwd:hover {
    background-color: #e2e8f0;
    color: #0f172a;
}

.btn-toggle-pwd i {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}

@media (max-width: 767.98px) {
    .border-start-md {
        border-left: none !important;
        border-top: 1px solid #e2e8f0;
        padding-top: 1rem;
        padding-left: 0 !important;
    }
}
</style>
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>
<main class="page-content" aria-labelledby="smtpPageTitle">
    <div class="text-start tw-wrap container-fluid smtp-manager-wrap">
        
        <!-- Header Section -->
        <div class="row align-items-center mt-4 mb-3" role="banner">
            <div class="col-12 col-md-8 text-start">
                <h1 class="tw-title lh-1" id="smtpPageTitle" style="color: #1a202c; font-size: 2.2rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.5rem;">
                    SMTP Setting
                </h1>
                <p class="tw-subtitle text-secondary mb-0" style="font-size: 1rem; font-weight: 400;">
                    Konfigurasi server mail transfer protocol untuk pengiriman notifikasi email sistem secara terpusat.
                </p>
            </div>
            <div class="col-12 col-md-4 text-md-end mt-2 mt-md-0">
                <div class="d-flex flex-wrap align-items-center justify-content-md-end gap-2">
                    <a href="<?= base_url('manage-setting') ?>" class="btn btn-light fw-bold px-3 d-inline-flex align-items-center justify-content-center gap-2" style="height: 42px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; line-height: 1;">
                        <i class="bi bi-sliders d-inline-flex align-items-center justify-content-center" style="font-size: 1.05rem; line-height: 1;"></i>
                        <span>System Setting</span>
                    </a>
                    <a href="<?= base_url('manage-layanan') ?>" class="btn btn-light fw-bold px-3 d-inline-flex align-items-center justify-content-center gap-2" style="height: 42px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; line-height: 1;">
                        <i class="bi bi-gear-fill d-inline-flex align-items-center justify-content-center" style="font-size: 1.05rem; line-height: 1;"></i>
                        <span>Service Manager</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Summary Banner Card -->
        <div class="smtp-summary-card mb-4" id="smtpSummaryCard">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="smtp-banner-icon-box" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="d-inline-flex align-items-center justify-content-center">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h4 class="mb-0 fw-bold" id="cardSmtpStatus" style="color: #0f172a; font-size: 1.25rem;">Email Gateway Configuration</h4>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1" id="bannerSmtpStatusBadge" style="font-size: 0.75rem; font-weight: 600;">SMTP Service</span>
                        </div>
                        <p class="text-secondary mb-0 small" id="cardSmtpDescription" style="max-width: 620px;">
                            Pastikan parameter server SMTP valid agar notifikasi tugas, reset sandi, dan broadcast email dapat terkirim secara tepat.
                        </p>
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-4 border-start-md ps-md-4">
                    <div class="smtp-stat-item">
                        <div>
                            <span class="smtp-stat-label">Host Server</span>
                            <strong id="bannerHost" class="smtp-stat-value font-monospace">-</strong>
                        </div>
                    </div>
                    <div class="smtp-stat-item">
                        <div>
                            <span class="smtp-stat-label">Port & Enkripsi</span>
                            <strong id="bannerPortEnc" class="smtp-stat-value font-monospace">-</strong>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-primary d-inline-flex align-items-center justify-content-center gap-2 px-3 fw-bold" id="btnOpenTestModal" data-bs-toggle="modal" data-bs-target="#modalTestSmtp" style="height: 42px; border-radius: 8px; font-size: 0.92rem; white-space: nowrap;">
                        <i class="bi bi-send-check d-inline-flex align-items-center justify-content-center" style="font-size: 1.05rem; line-height: 1;"></i>
                        <span>Uji Coba Pengiriman</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Form Card -->
        <div class="smtp-card mb-4">
            <div class="smtp-card-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-hdd-network text-primary fs-5 d-inline-flex align-items-center justify-content-center" style="line-height: 1;"></i>
                    <h5 class="mb-0 fw-bold text-dark" style="font-size: 1.05rem;">Parameter Server & Otentikasi SMTP</h5>
                </div>
                <span class="text-muted small"><span class="text-danger">*</span> Wajib diisi</span>
            </div>
            
            <form id="smtpSettingForm" autocomplete="off">
                <div class="smtp-card-body">
                    <div class="row g-4">
                        <!-- Host -->
                        <div class="col-12 col-md-6">
                            <label for="smtpHost" class="form-label">
                                SMTP Host <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-hdd-stack"></i></span>
                                <input type="text" class="form-control font-monospace" id="smtpHost" name="smtp__host" placeholder="contoh: smtp.gmail.com atau mail.kanreg.bkn.go.id" required>
                            </div>
                            <div class="input-hint">Alamat hostname atau IP server mail outgoing Anda.</div>
                        </div>

                        <!-- Port & Enkripsi -->
                        <div class="col-12 col-md-6">
                            <div class="row g-2">
                                <div class="col-12 col-sm-6">
                                    <label for="smtpPort" class="form-label">
                                        SMTP Port <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-ethernet"></i></span>
                                        <input type="number" class="form-control font-monospace" id="smtpPort" name="smtp__port" placeholder="587" required>
                                    </div>
                                    <div class="input-hint d-flex align-items-center gap-1 mt-1 flex-wrap">
                                        <span class="small text-muted">Preset:</span>
                                        <span class="port-badge js-port-hint" data-port="587" data-enc="tls">587 (TLS)</span>
                                        <span class="port-badge js-port-hint" data-port="465" data-enc="ssl">465 (SSL)</span>
                                        <span class="port-badge js-port-hint" data-port="25" data-enc="">25 (None)</span>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label for="smtpEncryption" class="form-label">
                                        Enkripsi Keamanan
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                                        <select class="form-select" id="smtpEncryption" name="smtp__encryption">
                                            <option value="tls">TLS (Direkomendasikan)</option>
                                            <option value="ssl">SSL</option>
                                            <option value="">None (Tanpa Enkripsi)</option>
                                        </select>
                                    </div>
                                    <div class="input-hint">Protokol keamanan komunikasi SMTP.</div>
                                </div>
                            </div>
                        </div>

                        <!-- Username -->
                        <div class="col-12 col-md-6">
                            <label for="smtpUsername" class="form-label">
                                SMTP Username / Email Login <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" id="smtpUsername" name="smtp__username" placeholder="user@domain.com atau username SMTP" required>
                            </div>
                            <div class="input-hint">Username atau alamat email akun pengirim otentikasi.</div>
                        </div>

                        <!-- Password with toggle visibility -->
                        <div class="col-12 col-md-6">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label for="smtpPassword" class="form-label mb-0">
                                    SMTP Password
                                </label>
                                <span id="pwdSavedBadge" class="badge bg-success-subtle text-success border border-success-subtle d-none" style="font-size: 0.72rem; font-weight: 600;">
                                    <i class="bi bi-check2-circle me-1"></i>Tersimpan di Server
                                </span>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-key"></i></span>
                                <input type="password" class="form-control font-monospace" id="smtpPassword" name="smtp__password" placeholder="••••••••••••" autocomplete="new-password">
                                <button type="button" class="btn btn-toggle-pwd" id="btnToggleSmtpPwd" title="Lihat / Sembunyikan Password" tabindex="-1">
                                    <i class="bi bi-eye" id="iconToggleSmtpPwd"></i>
                                </button>
                            </div>
                            <div class="input-hint">Biarkan kosong jika tidak ingin mengubah password yang sudah tersimpan.</div>
                        </div>

                        <div class="col-12"><hr class="my-1 text-muted" style="opacity: 0.12;"></div>

                        <!-- From Name -->
                        <div class="col-12 col-md-6">
                            <label for="smtpFromName" class="form-label">
                                Nama Pengirim (From Name)
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                                <input type="text" class="form-control" id="smtpFromName" name="smtp__from_name" placeholder="contoh: SIMOJANG - Kanreg III BKN">
                            </div>
                            <div class="input-hint">Nama instansi/aplikasi yang muncul sebagai pengirim di inbox penerima.</div>
                        </div>

                        <!-- From Email -->
                        <div class="col-12 col-md-6">
                            <label for="smtpFromEmail" class="form-label">
                                Email Pengirim (From Email) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" id="smtpFromEmail" name="smtp__from_email" placeholder="contoh: no-reply@kanreg.bkn.go.id" required>
                            </div>
                            <div class="input-hint">Alamat email resmi yang akan tertera sebagai pengirim pesan.</div>
                        </div>
                    </div>
                </div>
                
                <div class="smtp-card-footer d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3">
                    <div class="text-muted small d-flex align-items-center gap-2">
                        <i class="bi bi-shield-check text-success fs-5 d-inline-flex align-items-center justify-content-center" style="line-height: 1;"></i>
                        <span>Konfigurasi tersimpan terenkripsi dan diterapkan otomatis untuk semua pengiriman email.</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-light fw-bold px-3 d-inline-flex align-items-center justify-content-center gap-1.5" id="btnResetSmtpForm" style="height: 42px; border: 1px solid #cbd5e1; border-radius: 8px; color: #475569;">
                            <i class="bi bi-arrow-clockwise d-inline-flex align-items-center justify-content-center" style="line-height: 1;"></i>
                            <span>Muat Ulang</span>
                        </button>
                        <button type="submit" class="btn btn-primary fw-bold px-4 d-inline-flex align-items-center justify-content-center gap-2" id="smtpSettingSaveBtn" style="height: 42px; background-color: #1040c1; border-color: #1040c1; border-radius: 8px;">
                            <i class="bi bi-check2-circle fs-6 d-inline-flex align-items-center justify-content-center" style="line-height: 1;"></i>
                            <span>Simpan Konfigurasi SMTP</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

    </div>
</main>

<!-- ============================================================== -->
<!-- MODAL: UJI COBA PENGIRIMAN EMAIL (TEST SMTP)                  -->
<!-- ============================================================== -->
<div class="modal fade flat-modal" id="modalTestSmtp" tabindex="-1" aria-labelledby="modalTestSmtpLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; background-color: #ffffff;">
            <div class="modal-header border-bottom px-4 py-3 align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-send-check text-primary fs-4 d-inline-flex align-items-center justify-content-center" style="line-height: 1;"></i>
                    <h5 class="modal-title fw-bold mb-0" id="modalTestSmtpLabel" style="color: #0f172a; font-size: 1.15rem;">Uji Coba Pengiriman Email</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formTestSmtp" autocomplete="off">
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 px-3 small border-0 mb-3 d-flex align-items-start gap-2" style="background-color: #eff6ff; color: #1e40af; border-radius: 8px;">
                        <i class="bi bi-info-circle-fill mt-0.5 flex-shrink-0"></i>
                        <div>Kirimkan email uji coba ke alamat email Anda untuk memastikan koneksi ke server SMTP, otentikasi, dan enkripsi berfungsi dengan baik.</div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label for="testRecipientEmail" class="form-label mb-0">
                                Alamat Email Penerima <span class="text-danger">*</span>
                            </label>
                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none small" id="btnUseFromEmail" style="font-size: 0.78rem;">
                                <i class="bi bi-arrow-down-left-square me-1"></i>Gunakan Email Pengirim
                            </button>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" id="testRecipientEmail" name="test_email" placeholder="nama@domain.com" required style="border-radius: 0 8px 8px 0 !important;">
                        </div>
                        <div class="input-hint">Gunakan email yang aktif untuk memeriksa pesan di kotak masuk / folder spam.</div>
                    </div>

                    <div id="testResultBox" class="p-3 rounded-3 mt-3 d-none border" style="font-size: 0.88rem;"></div>
                </div>
                <div class="modal-footer border-top px-4 py-3 d-flex align-items-center justify-content-end gap-2">
                    <button type="button" class="btn btn-light fw-bold px-3" data-bs-dismiss="modal" style="border: 1px solid #cbd5e1; border-radius: 8px; color: #0f172a; height: 40px;">Tutup</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4 d-inline-flex align-items-center justify-content-center gap-2" id="btnSendTestEmail" style="background-color: #1040c1; border-color: #1040c1; border-radius: 8px; height: 40px;">
                        <i class="bi bi-send d-inline-flex align-items-center justify-content-center" style="line-height: 1;"></i>
                        <span>Kirim Email Uji Coba</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script src="<?= asset_url('apps/assets/js/custom/pages/settings/smtp-setting.js?v=' . time()); ?>"></script>
<?= $this->endSection(); ?>

