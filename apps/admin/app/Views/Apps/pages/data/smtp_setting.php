<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>

<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork-common.css') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork.css?v=99') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/role-manager.css?v=' . time()) ?>">
<style>
.smtp-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
    overflow: hidden;
}
.smtp-card-header {
    background-color: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: 1rem 1.5rem;
}
.smtp-card-body {
    padding: 1.5rem;
}
.smtp-card-footer {
    background-color: #f8fafc;
    border-top: 1px solid #e2e8f0;
    padding: 1rem 1.5rem;
}
.form-label {
    font-weight: 600;
    color: #334155;
    font-size: 0.9rem;
    margin-bottom: 0.35rem;
}
.form-control, .form-select {
    border-color: #cbd5e1;
    border-radius: 8px;
    padding: 0.55rem 0.85rem;
    font-size: 0.92rem;
}
.form-control:focus, .form-select:focus {
    border-color: #1040c1;
    box-shadow: 0 0 0 3px rgba(16, 64, 193, 0.12);
}
.input-hint {
    font-size: 0.8rem;
    color: #64748b;
    margin-top: 0.25rem;
}
.port-badge {
    cursor: pointer;
    font-size: 0.75rem;
    padding: 0.2rem 0.5rem;
    background-color: #f1f5f9;
    border: 1px solid #cbd5e1;
    border-radius: 4px;
    color: #475569;
    font-weight: 600;
    transition: all 0.15s ease;
}
.port-badge:hover {
    background-color: #e2e8f0;
    color: #0f172a;
}
</style>
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>
<main class="page-content" aria-labelledby="smtpPageTitle">
    <div class="text-start tw-wrap container-fluid role-manager-wrap">
        
        <!-- Header -->
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
                        <i class="bi bi-sliders d-inline-flex align-items-center" style="font-size: 1rem; line-height: 1;"></i>
                        <span>System Setting</span>
                    </a>
                    <a href="<?= base_url('manage-layanan') ?>" class="btn btn-light fw-bold px-3 d-inline-flex align-items-center justify-content-center gap-2" style="height: 42px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; line-height: 1;">
                        <i class="bi bi-gear-fill d-inline-flex align-items-center" style="font-size: 1rem; line-height: 1;"></i>
                        <span>Service Manager</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Summary Banner Card -->
        <div class="role-summary-card mb-4" id="smtpSummaryCard">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="role-banner-icon-box" style="background-color: #f0fdf4; color: #16a34a;">
                        <i class="bi bi-envelope-at-fill" style="line-height: 1;"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h4 class="mb-0 fw-bold" id="cardSmtpStatus" style="color: #0f172a; font-size: 1.25rem;">Email Gateway Configuration</h4>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-1" style="font-size: 0.75rem;">SMTP Service</span>
                        </div>
                        <p class="text-secondary mb-0 small" id="cardSmtpDescription" style="max-width: 600px;">
                            Pastikan parameter server SMTP valid agar notifikasi tugas, reset sandi, dan broadcast email dapat terkirim.
                        </p>
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-3 border-start-md ps-md-4">
                    <button type="button" class="btn btn-outline-primary d-inline-flex align-items-center justify-content-center gap-2 px-3 fw-bold" id="btnOpenTestModal" data-bs-toggle="modal" data-bs-target="#modalTestSmtp" style="height: 42px; border-radius: 8px; font-size: 0.92rem;">
                        <i class="bi bi-send-check" style="font-size: 1.05rem;"></i>
                        <span>Uji Coba Pengiriman Email</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Form Card -->
        <div class="smtp-card mb-4">
            <div class="smtp-card-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-hdd-network text-primary fs-5"></i>
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
                            <input type="text" class="form-control font-monospace" id="smtpHost" name="smtp__host" placeholder="contoh: smtp.gmail.com atau mail.kanreg.bkn.go.id" required>
                            <div class="input-hint">Alamat hostname server mail outgoing Anda.</div>
                        </div>

                        <!-- Port & Enkripsi -->
                        <div class="col-12 col-md-6">
                            <div class="row g-2">
                                <div class="col-12 col-sm-6">
                                    <label for="smtpPort" class="form-label">
                                        SMTP Port <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" class="form-control font-monospace" id="smtpPort" name="smtp__port" placeholder="587" required>
                                    <div class="input-hint d-flex align-items-center gap-1 mt-1">
                                        <span>Pilihan:</span>
                                        <span class="port-badge js-port-hint" data-port="587" data-enc="tls">587 (TLS)</span>
                                        <span class="port-badge js-port-hint" data-port="465" data-enc="ssl">465 (SSL)</span>
                                        <span class="port-badge js-port-hint" data-port="25" data-enc="">25 (None)</span>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label for="smtpEncryption" class="form-label">
                                        Enkripsi Keamanan
                                    </label>
                                    <select class="form-select" id="smtpEncryption" name="smtp__encryption">
                                        <option value="tls">TLS (Direkomendasikan)</option>
                                        <option value="ssl">SSL</option>
                                        <option value="">None (Tanpa Enkripsi)</option>
                                    </select>
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
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" id="smtpUsername" name="smtp__username" placeholder="user@domain.com atau akun SMTP" required>
                            </div>
                            <div class="input-hint">Username atau alamat email akun pengirim otentikasi.</div>
                        </div>

                        <!-- Password with toggle visibility -->
                        <div class="col-12 col-md-6">
                            <label for="smtpPassword" class="form-label">
                                SMTP Password
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-key"></i></span>
                                <input type="password" class="form-control font-monospace" id="smtpPassword" name="smtp__password" placeholder="••••••••••••" autocomplete="new-password">
                                <button type="button" class="btn btn-outline-secondary" id="btnToggleSmtpPwd" title="Lihat / Sembunyikan Password">
                                    <i class="bi bi-eye" id="iconToggleSmtpPwd"></i>
                                </button>
                            </div>
                            <div class="input-hint">Biarkan kosong jika tidak ingin mengubah password yang sudah tersimpan.</div>
                        </div>

                        <div class="col-12"><hr class="my-1 text-muted" style="opacity: 0.15;"></div>

                        <!-- From Name -->
                        <div class="col-12 col-md-6">
                            <label for="smtpFromName" class="form-label">
                                Nama Pengirim (From Name)
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-card-text"></i></span>
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
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" id="smtpFromEmail" name="smtp__from_email" placeholder="contoh: no-reply@kanreg.bkn.go.id" required>
                            </div>
                            <div class="input-hint">Alamat email resmi yang akan tertera sebagai pengirim pesan.</div>
                        </div>
                    </div>
                </div>
                
                <div class="smtp-card-footer d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3">
                    <div class="text-muted small d-flex align-items-center gap-2">
                        <i class="bi bi-shield-check text-success fs-5"></i>
                        <span>Konfigurasi tersimpan terenkripsi dan diterapkan otomatis untuk semua pengiriman email.</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="submit" class="btn btn-primary fw-bold px-4 d-inline-flex align-items-center justify-content-center gap-2" id="smtpSettingSaveBtn" style="height: 42px; background-color: #1040c1; border-color: #1040c1; border-radius: 8px;">
                            <i class="bi bi-check2-circle fs-6"></i>
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
<div class="modal fade" id="modalTestSmtp" tabindex="-1" aria-labelledby="modalTestSmtpLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; background-color: #ffffff;">
            <div class="modal-header border-bottom px-4 py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-send-check text-primary fs-4"></i>
                    <h5 class="modal-title fw-bold mb-0" id="modalTestSmtpLabel" style="color: #0f172a; font-size: 1.2rem;">Uji Coba Pengiriman Email</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formTestSmtp" autocomplete="off">
                <div class="modal-body p-4">
                    <p class="text-secondary small mb-3">
                        Kirimkan email uji coba ke alamat email Anda untuk memastikan koneksi ke server SMTP, otentikasi, dan sertifikat enkripsi berfungsi normal.
                    </p>

                    <div class="mb-3">
                        <label for="testRecipientEmail" class="form-label mb-1">
                            Alamat Email Penerima <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" id="testRecipientEmail" name="test_email" placeholder="nama@domain.com" required style="border-radius: 0 8px 8px 0 !important;">
                        </div>
                        <div class="input-hint">Gunakan email yang dapat Anda akses langsung untuk mengecek kotak masuk/spam.</div>
                    </div>

                    <div id="testResultBox" class="p-3 rounded-3 mt-3 d-none border" style="font-size: 0.88rem;"></div>
                </div>
                <div class="modal-footer border-top px-4 py-3">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4" id="btnSendTestEmail" style="background-color: #1040c1; border-color: #1040c1;">
                        <i class="bi bi-send me-1"></i> Kirim Email Uji Coba
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
