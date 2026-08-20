<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>

<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork-common.css') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork.css?v=99') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/role-manager.css?v=' . time()) ?>">
<style>
/* ==============================================================
   System Setting Custom Stylesheet - SIMOJANG Design System
   ============================================================== */
.setting-manager-wrap {
    padding-bottom: 2.5rem;
}

/* Summary Banner Card */
.setting-summary-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.25rem 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    transition: all 0.2s ease;
}

.setting-banner-icon-box {
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

.setting-banner-icon-box svg,
.setting-banner-icon-box i {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
    margin: 0;
    padding: 0;
}

.setting-stat-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.setting-stat-label {
    display: block;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #64748b;
    margin-bottom: 0.15rem;
}

.setting-stat-value {
    font-size: 0.95rem;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.2;
}

/* Card Form & Tabs */
.setting-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    overflow: hidden;
}

.nav-tabs-setting {
    border-bottom: 1px solid #e2e8f0;
    background-color: #f8fafc;
    padding: 0.5rem 1.25rem 0 1.25rem;
    gap: 0.4rem;
}

.nav-tabs-setting .nav-link {
    border: none;
    color: #64748b;
    font-weight: 600;
    font-size: 0.92rem;
    padding: 0.75rem 1.35rem;
    border-radius: 8px 8px 0 0;
    transition: all 0.15s ease;
    display: inline-block;
    line-height: 1.3;
}

.nav-tabs-setting .nav-link:hover {
    color: #0f172a;
    background-color: #f1f5f9;
}

.nav-tabs-setting .nav-link.active {
    color: #1040c1;
    background-color: #ffffff;
    border-bottom: 2px solid #1040c1;
    font-weight: 700;
}

.setting-card-body {
    padding: 1.75rem 1.5rem;
}

.setting-card-footer {
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

.brand-preview-box {
    width: 46px;
    height: 46px;
    min-width: 46px;
    border-radius: 10px;
    border: 1px solid #cbd5e1;
    background-color: #f8fafc;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
}

.brand-preview-box img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.brand-preview-box i,
.brand-preview-box svg {
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
<main class="page-content" aria-labelledby="settingPageTitle">
    <div class="text-start tw-wrap container-fluid setting-manager-wrap">
        
        <!-- Header Section -->
        <div class="row align-items-center mt-4 mb-3" role="banner">
            <div class="col-12 col-md-8 text-start">
                <h1 class="tw-title lh-1" id="settingPageTitle" style="color: #1a202c; font-size: 2.2rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.5rem;">
                    System Setting
                </h1>
                <p class="tw-subtitle text-secondary mb-0" style="font-size: 1rem; font-weight: 400;">
                    Kelola preferensi sistem, sesi, batasan keamanan, konfigurasi upload, dan identitas aplikasi SIMOJANG.
                </p>
            </div>
            <div class="col-12 col-md-4 text-md-end mt-2 mt-md-0">
                <div class="d-flex flex-wrap align-items-center justify-content-md-end gap-2">
                    <a href="<?= base_url('manage-smtp') ?>" class="btn btn-light fw-bold px-3 d-inline-flex align-items-center justify-content-center gap-2" style="height: 42px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; line-height: 1;">
                        <i class="bi bi-envelope-at d-inline-flex align-items-center justify-content-center" style="font-size: 1.05rem; line-height: 1;"></i>
                        <span>SMTP Setting</span>
                    </a>
                    <a href="<?= base_url('manage-layanan') ?>" class="btn btn-light fw-bold px-3 d-inline-flex align-items-center justify-content-center gap-2" style="height: 42px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; line-height: 1;">
                        <i class="bi bi-gear-fill d-inline-flex align-items-center justify-content-center" style="font-size: 1.05rem; line-height: 1;"></i>
                        <span>Service Manager</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Summary Banner Card -->
        <div class="setting-summary-card mb-4" id="settingSummaryCard">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="setting-banner-icon-box" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="d-inline-flex align-items-center justify-content-center">
                            <line x1="4" y1="21" x2="4" y2="14"></line>
                            <line x1="4" y1="10" x2="4" y2="3"></line>
                            <line x1="12" y1="21" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12" y2="3"></line>
                            <line x1="20" y1="21" x2="20" y2="16"></line>
                            <line x1="20" y1="12" x2="20" y2="3"></line>
                            <line x1="1" y1="14" x2="7" y2="14"></line>
                            <line x1="9" y1="8" x2="15" y2="8"></line>
                            <line x1="17" y1="16" x2="23" y2="16"></line>
                        </svg>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h4 class="mb-0 fw-bold" id="cardAppName" style="color: #0f172a; font-size: 1.25rem;">SIMOJANG System Configuration</h4>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1" id="cardEnvBadge" style="font-size: 0.75rem; font-weight: 600;">Production</span>
                        </div>
                        <p class="text-secondary mb-0 small" style="max-width: 620px;">
                            Perubahan konfigurasi disimpan terpusat dan diterapkan langsung pada sesi aplikasi dan seluruh modul operasional.
                        </p>
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-4 border-start-md ps-md-4">
                    <div class="setting-stat-item">
                        <div>
                            <span class="setting-stat-label">Zona Waktu</span>
                            <strong id="statTimezone" class="setting-stat-value font-monospace">Asia/Jakarta</strong>
                        </div>
                    </div>
                    <div class="setting-stat-item">
                        <div>
                            <span class="setting-stat-label">Timeout Sesi</span>
                            <strong id="statSessionTimeout" class="setting-stat-value font-monospace">60 Menit</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Tabs Card -->
        <div class="setting-card mb-4">
            <form id="systemSettingForm" autocomplete="off">
                <!-- Nav Tabs (Clean Text Only without icons) -->
                <ul class="nav nav-tabs nav-tabs-setting" id="settingTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tab-general" data-bs-toggle="tab" data-bs-target="#panel-general" type="button" role="tab" aria-controls="panel-general" aria-selected="true">
                            Umum & Aplikasi
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-security" data-bs-toggle="tab" data-bs-target="#panel-security" type="button" role="tab" aria-controls="panel-security" aria-selected="false">
                            Keamanan & Sesi
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-upload" data-bs-toggle="tab" data-bs-target="#panel-upload" type="button" role="tab" aria-controls="panel-upload" aria-selected="false">
                            File & Upload
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-branding" data-bs-toggle="tab" data-bs-target="#panel-branding" type="button" role="tab" aria-controls="panel-branding" aria-selected="false">
                            Branding & Logo
                        </button>
                    </li>
                </ul>

                <!-- Tab Panes -->
                <div class="tab-content setting-card-body" id="settingTabsContent">
                    
                    <!-- 1. GENERAL TAB -->
                    <div class="tab-pane fade show active" id="panel-general" role="tabpanel" aria-labelledby="tab-general">
                        <div class="row g-4">
                            <div class="col-12 col-md-6">
                                <label for="appName" class="form-label">
                                    Nama Aplikasi <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-window"></i></span>
                                    <input type="text" class="form-control" id="appName" name="app__name" placeholder="SIMOJANG" required>
                                </div>
                                <div class="input-hint">Nama sistem yang ditampilkan pada header, title bar, dan laporan.</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="appTimezone" class="form-label">
                                    Zona Waktu Sistem (Timezone) <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-clock"></i></span>
                                    <select class="form-select" id="appTimezone" name="app__timezone" required>
                                        <option value="Asia/Jakarta">Asia/Jakarta (WIB, UTC+7)</option>
                                        <option value="Asia/Makassar">Asia/Makassar (WITA, UTC+8)</option>
                                        <option value="Asia/Jayapura">Asia/Jayapura (WIT, UTC+9)</option>
                                    </select>
                                </div>
                                <div class="input-hint">Basis waktu operasional pencatatan log dan jadwal kegiatan.</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="envFlag" class="form-label">
                                    Environment Flag <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-cpu"></i></span>
                                    <select class="form-select" id="envFlag" name="env__flag" required>
                                        <option value="production">Production (Operasional Live)</option>
                                        <option value="staging">Staging (Uji Coba Server)</option>
                                        <option value="development">Development (Pengembangan)</option>
                                    </select>
                                </div>
                                <div class="input-hint">Status lingkungan server saat ini.</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="defaultPerPage" class="form-label">
                                    Jumlah Baris Default Pagination
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-list-ol"></i></span>
                                    <select class="form-select" id="defaultPerPage" name="pagination__default_per_page">
                                        <option value="10">10 data per halaman</option>
                                        <option value="25">25 data per halaman</option>
                                        <option value="50">50 data per halaman</option>
                                        <option value="100">100 data per halaman</option>
                                    </select>
                                </div>
                                <div class="input-hint">Batas default entri per halaman pada seluruh tabel data.</div>
                            </div>

                            <div class="col-12">
                                <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="p-2.5 rounded-3 bg-white border d-inline-flex align-items-center justify-content-center text-primary" style="width: 42px; height: 42px;">
                                            <i class="bi bi-tools fs-5"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-1 text-dark" style="font-size: 0.95rem;">Mode Pemeliharaan (Maintenance Mode)</h6>
                                            <p class="text-muted small mb-0">Jika aktif, hanya user dengan role Administrator yang dapat mengakses sistem.</p>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch fs-4 mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" id="appMaintenanceMode" name="app__maintenance_mode" value="1">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. SECURITY & SESSION TAB -->
                    <div class="tab-pane fade" id="panel-security" role="tabpanel" aria-labelledby="tab-security">
                        <div class="row g-4">
                            <div class="col-12 col-md-6">
                                <label for="sessionTimeout" class="form-label">
                                    Timeout Sesi Login (Menit) <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-hourglass-split"></i></span>
                                    <input type="number" class="form-control" id="sessionTimeout" name="session__timeout_minutes" min="5" max="1440" placeholder="60" required>
                                    <span class="input-group-text bg-light text-muted">Menit</span>
                                </div>
                                <div class="input-hint">Durasi inaktivitas sebelum sesi pengguna berakhir otomatis.</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="maxLoginAttempt" class="form-label">
                                    Batas Maksimal Percobaan Login Gagal
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-shield-x"></i></span>
                                    <input type="number" class="form-control" id="maxLoginAttempt" name="security__max_login_attempt" min="1" max="20" placeholder="5">
                                    <span class="input-group-text bg-light text-muted">Kali Percobaan</span>
                                </div>
                                <div class="input-hint">Jumlah kesalahan password berturut-turut sebelum akun terkunci.</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="lockDuration" class="form-label">
                                    Durasi Kunci Akun (Lockout Duration)
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="number" class="form-control" id="lockDuration" name="security__lock_duration_minutes" min="1" max="1440" placeholder="15">
                                    <span class="input-group-text bg-light text-muted">Menit</span>
                                </div>
                                <div class="input-hint">Lama waktu akun dikunci sementara jika melebihi batas login gagal.</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="defaultRoleCode" class="form-label">
                                    Default Role Code Pengguna Baru
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                    <input type="text" class="form-control font-monospace" id="defaultRoleCode" name="security__default_role_code" placeholder="USR">
                                </div>
                                <div class="input-hint">Kode role otomatis yang diberikan untuk akun baru terdaftar.</div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. UPLOAD & FILE TAB -->
                    <div class="tab-pane fade" id="panel-upload" role="tabpanel" aria-labelledby="tab-upload">
                        <div class="row g-4">
                            <div class="col-12 col-md-6">
                                <label for="maxUploadSize" class="form-label">
                                    Batas Maksimal Ukuran File (Max Size MB) <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-file-earmark-arrow-up"></i></span>
                                    <input type="number" class="form-control" id="maxUploadSize" name="upload__max_size_mb" min="1" max="200" placeholder="10" required>
                                    <span class="input-group-text bg-light text-muted">MB</span>
                                </div>
                                <div class="input-hint">Ukuran file maksimal per upload dokumen atau lampiran.</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="allowedTypes" class="form-label">
                                    Format File yang Diizinkan (Allowed Extensions)
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-filetype-pdf"></i></span>
                                    <input type="text" class="form-control font-monospace" id="allowedTypes" name="upload__allowed_types" placeholder="xls,xlsx,csv,pdf,jpg,jpeg,png,zip">
                                </div>
                                <div class="input-hint">Pisahkan ekstensi dengan tanda koma tanpa spasi.</div>
                            </div>
                        </div>
                    </div>

                    <!-- 4. BRANDING TAB -->
                    <div class="tab-pane fade" id="panel-branding" role="tabpanel" aria-labelledby="tab-branding">
                        <div class="row g-4">
                            <div class="col-12 col-md-6">
                                <label for="appLogo" class="form-label">
                                    URL / Path Logo Aplikasi
                                </label>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="brand-preview-box" id="logoPreviewBox">
                                        <i class="bi bi-image text-muted fs-5"></i>
                                    </div>
                                    <div class="input-group flex-grow-1">
                                        <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
                                        <input type="text" class="form-control" id="appLogo" name="app__logo" placeholder="apps/assets/images/logo/logo.png">
                                    </div>
                                </div>
                                <div class="input-hint mt-2">Path relatif file logo di direktori public atau URL gambar absolut.</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="appFavicon" class="form-label">
                                    URL / Path Favicon
                                </label>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="brand-preview-box" id="faviconPreviewBox">
                                        <i class="bi bi-browser-chrome text-muted fs-5"></i>
                                    </div>
                                    <div class="input-group flex-grow-1">
                                        <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
                                        <input type="text" class="form-control" id="appFavicon" name="app__favicon" placeholder="apps/assets/images/logo/favicon.ico">
                                    </div>
                                </div>
                                <div class="input-hint mt-2">Ikon kecil yang muncul pada tab browser (.ico, .png).</div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Footer Actions -->
                <div class="setting-card-footer d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3">
                    <div class="text-muted small d-flex align-items-center gap-2">
                        <i class="bi bi-shield-check text-success fs-5 d-inline-flex align-items-center justify-content-center" style="line-height: 1;"></i>
                        <span>Pengaturan disimpan terpusat di tabel konfigurasi sistem dan berlaku otomatis.</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-light fw-bold px-3 d-inline-flex align-items-center justify-content-center gap-1.5" id="btnResetSystemForm" style="height: 42px; border: 1px solid #cbd5e1; border-radius: 8px; color: #475569;">
                            <i class="bi bi-arrow-clockwise d-inline-flex align-items-center justify-content-center" style="line-height: 1;"></i>
                            <span>Muat Ulang</span>
                        </button>
                        <button type="submit" class="btn btn-primary fw-bold px-4 d-inline-flex align-items-center justify-content-center gap-2" id="systemSettingSaveBtn" style="height: 42px; background-color: #1040c1; border-color: #1040c1; border-radius: 8px;">
                            <i class="bi bi-check2-circle fs-6 d-inline-flex align-items-center justify-content-center" style="line-height: 1;"></i>
                            <span>Simpan Konfigurasi Sistem</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

    </div>
</main>
<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script src="<?= asset_url('apps/assets/js/custom/pages/settings/system-setting.js?v=' . time()); ?>"></script>
<?= $this->endSection(); ?>

