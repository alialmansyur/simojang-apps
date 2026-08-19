<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>

<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork-common.css') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork.css?v=99') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/role-manager.css?v=' . time()) ?>">
<style>
/* Service Manager Enhancements */
#serviceTable {
    width: 100% !important;
    border-collapse: separate !important;
    border-spacing: 0 !important;
}
#serviceTable thead th {
    background-color: #f8fafc !important;
    color: #475569 !important;
    font-size: 0.85rem !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.04em !important;
    padding: 0.85rem 1rem !important;
    border-bottom: 1px solid #e2e8f0 !important;
    vertical-align: middle !important;
    white-space: nowrap !important;
}
#serviceTable tbody td {
    padding: 0.85rem 1rem !important;
    vertical-align: middle !important;
    border-bottom: 1px solid #edf2f7 !important;
    background-color: #ffffff !important;
    transition: background-color 0.15s ease !important;
}
#serviceTable tbody tr:hover td {
    background-color: #f8fafc !important;
}
.mode-badge-everyone {
    background-color: #dcfce7;
    color: #15803d;
    border: 1px solid #bbf7d0;
    font-size: 0.8rem;
    font-weight: 700;
    padding: 0.3rem 0.65rem;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}
.mode-badge-assigned {
    background-color: #eff6ff;
    color: #1040c1;
    border: 1px solid #bfdbfe;
    font-size: 0.8rem;
    font-weight: 700;
    padding: 0.3rem 0.65rem;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}
.select2-container--bootstrap-5 .select2-selection {
    min-height: 42px !important;
    border-color: #cbd5e1 !important;
    border-radius: 8px !important;
    display: flex !important;
    align-items: center !important;
}
.select2-container--bootstrap-5 .select2-dropdown {
    border-radius: 8px !important;
    border-color: #cbd5e1 !important;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
    z-index: 1065 !important;
}
</style>
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>
<main class="page-content" aria-labelledby="servicePageTitle">
    <div class="text-start tw-wrap container-fluid role-manager-wrap">
        
        <!-- Header -->
        <div class="row align-items-center mt-4 mb-3" role="banner">
            <div class="col-12 col-md-8 text-start">
                <h1 class="tw-title lh-1" id="servicePageTitle" style="color: #1a202c; font-size: 2.2rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.5rem;">
                    Service Manager
                </h1>
                <p class="tw-subtitle text-secondary mb-0" style="font-size: 1rem; font-weight: 400;">
                    Konfigurasi hak akses dan penugasan NIP pegawai untuk seluruh modul layanan SIMOJANG.
                </p>
            </div>
            <div class="col-12 col-md-4 text-md-end mt-2 mt-md-0">
                <div class="d-flex flex-wrap align-items-center justify-content-md-end gap-2">
                    <a href="<?= base_url('manage-role') ?>" class="btn btn-light fw-bold px-3 d-inline-flex align-items-center justify-content-center gap-2" style="height: 42px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; line-height: 1;">
                        <i class="bi bi-shield-lock d-inline-flex align-items-center" style="font-size: 1rem; line-height: 1;"></i>
                        <span>Role Manager</span>
                    </a>
                    <a href="<?= base_url('manage-user') ?>" class="btn btn-light fw-bold px-3 d-inline-flex align-items-center justify-content-center gap-2" style="height: 42px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; line-height: 1;">
                        <i class="bi bi-people d-inline-flex align-items-center" style="font-size: 1rem; line-height: 1;"></i>
                        <span>User Manager</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Summary Banner Card -->
        <div class="role-summary-card mb-4" id="serviceSummaryCard">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="role-banner-icon-box" style="background-color: #faf5ff; color: #9333ea;">
                        <i class="bi bi-gear-wide-connected" style="line-height: 1;"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h4 class="mb-0 fw-bold" style="color: #0f172a; font-size: 1.25rem;">Manajemen Akses Layanan Tim Kerja</h4>
                            <span class="badge bg-purple-subtle text-purple border border-purple-subtle rounded-pill px-2 py-1" style="font-size: 0.75rem; background-color: #f3e8ff; color: #7e22ce;">Access Matrix</span>
                        </div>
                        <p class="text-secondary mb-0 small" style="max-width: 620px;">
                            Layanan dengan mode <strong>Everyone</strong> dapat diakses semua pegawai aktif, sedangkan <strong>Assigned</strong> khusus pegawai yang ditugaskan.
                        </p>
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-4 border-start-md ps-md-4">
                    <div class="role-stat-item">
                        <div>
                            <span class="d-block text-muted" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.04em;">Total Layanan</span>
                            <strong id="statTotalServices" style="font-size: 1.05rem; color: #1e293b;">0</strong> <span class="text-muted small">Modul</span>
                        </div>
                    </div>
                    <div class="role-stat-item">
                        <div>
                            <span class="d-block text-muted" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.04em;">Semua Pegawai</span>
                            <strong id="statPublicServices" style="font-size: 1.05rem; color: #16a34a;">0</strong> <span class="text-muted small">Layanan</span>
                        </div>
                    </div>
                    <div class="role-stat-item">
                        <div>
                            <span class="d-block text-muted" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.04em;">Pegawai Tertentu</span>
                            <strong id="statAssignedServices" style="font-size: 1.05rem; color: #1040c1;">0</strong> <span class="text-muted small">Layanan</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="tw-head d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4" role="toolbar">
            <div class="d-flex flex-column flex-sm-row flex-wrap align-items-stretch align-items-sm-center gap-2 flex-grow-1" style="max-width: 550px;">
                <!-- Live Search Box -->
                <div class="position-relative flex-grow-1 d-flex align-items-center">
                    <span class="position-absolute start-0 top-0 bottom-0 d-flex align-items-center justify-content-center ps-3 text-muted" style="pointer-events: none; width: 42px; z-index: 5;">
                        <i class="bi bi-search" style="font-size: 0.95rem; line-height: 1;"></i>
                    </span>
                    <input type="text" id="searchService" class="form-control tw-search-input w-100" placeholder="Cari nama layanan atau URL endpoint..." style="padding-left: 2.6rem; height: 42px; font-size: 0.95rem;">
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex flex-column flex-sm-row flex-wrap align-items-stretch align-items-sm-center gap-2">
                <button type="button" class="btn btn-light d-inline-flex align-items-center justify-content-center gap-2 px-3" id="btnRefreshServices" style="height: 42px; border: 1px solid #cbd5e1; border-radius: 8px; font-weight: 600; font-size: 0.95rem; line-height: 1;" title="Muat Ulang Data Layanan">
                    <i class="bi bi-arrow-clockwise d-inline-flex align-items-center" style="font-size: 1.05rem; line-height: 1;"></i>
                    <span>Refresh</span>
                </button>
            </div>
        </div>

        <!-- Services Table Card -->
        <div class="tree-table-card">
            <div class="table-responsive">
                <table class="table" id="serviceTable">
                    <thead>
                        <tr>
                            <th style="width: 70px;" class="text-center">ID</th>
                            <th>Nama Layanan</th>
                            <th style="width: 250px;">URL / Route Endpoint</th>
                            <th style="width: 200px;" class="text-center">Mode Akses</th>
                            <th style="width: 170px;" class="text-center">Ditugaskan</th>
                            <th style="width: 130px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="smServiceBody">
                        <!-- Loaded dynamically via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>

<!-- ============================================================== -->
<!-- MODAL: ATUR AKSES LAYANAN & PENUGASAN NIP                      -->
<!-- ============================================================== -->
<div class="modal fade" id="smServiceModal" tabindex="-1" aria-labelledby="smServiceModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; background-color: #ffffff;">
            <div class="modal-header border-bottom px-4 py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-shield-lock-fill text-primary fs-4"></i>
                    <h5 class="modal-title fw-bold mb-0" id="smServiceModalLabel" style="color: #0f172a; font-size: 1.25rem;">Pengaturan Akses Layanan</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <input type="hidden" id="smCurrentServiceId">

                <!-- Service Info Card -->
                <div class="p-3 bg-light rounded-3 mb-4 border d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2">
                    <div>
                        <div class="text-secondary small fw-medium">Nama Layanan:</div>
                        <div class="fw-bold mt-1 text-dark" id="smDetailName" style="font-size: 1.1rem;">-</div>
                    </div>
                    <div class="text-sm-end">
                        <div class="text-secondary small fw-medium">Endpoint URL:</div>
                        <code class="fw-bold mt-1 d-inline-block px-2 py-1 bg-white border rounded" id="smDetailUrl">-</code>
                    </div>
                </div>

                <!-- Access Mode Form -->
                <div class="card border mb-4 shadow-none">
                    <div class="card-header bg-white py-2 px-3 border-bottom d-flex align-items-center gap-2">
                        <i class="bi bi-person-check text-primary"></i>
                        <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.95rem;">Mode Akses Pengguna</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="row align-items-center g-3">
                            <div class="col-12 col-md-8">
                                <label for="smAccessMode" class="form-label small mb-1">Pilih Mode Akses Layanan:</label>
                                <select class="form-select" id="smAccessMode">
                                    <option value="everyone">Semua Pegawai (Terbuka untuk semua user aktif)</option>
                                    <option value="assigned">Pegawai Tertentu (Dibatasi hanya NIP yang ditugaskan)</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-4 text-md-end mt-md-auto">
                                <button type="button" class="btn btn-primary w-100 fw-bold d-inline-flex align-items-center justify-content-center gap-2" id="smSaveModeBtn" style="height: 40px; background-color: #1040c1; border-color: #1040c1; border-radius: 8px;">
                                    <i class="bi bi-check2"></i>
                                    <span>Simpan Mode</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assignee Section -->
                <div class="card border shadow-none" id="smAssigneeSection">
                    <div class="card-header bg-white py-2 px-3 border-bottom d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-people-fill text-primary"></i>
                            <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.95rem;">Daftar Pegawai yang Ditugaskan (Assignees)</h6>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <!-- Add Assignee Input Box -->
                        <div class="p-3 bg-light rounded-3 border mb-3">
                            <label for="smPegawaiSelect" class="form-label small mb-1">Tambah Pegawai Berdasarkan NIP / Nama:</label>
                            <div class="d-flex flex-column flex-sm-row gap-2">
                                <div class="flex-grow-1">
                                    <select class="form-select" id="smPegawaiSelect" style="width: 100%;">
                                        <option value="">Cari NIP atau nama pegawai...</option>
                                    </select>
                                </div>
                                <button type="button" class="btn btn-success fw-bold px-3 d-inline-flex align-items-center justify-content-center gap-2 text-nowrap" id="smAddAssignBtn" style="height: 42px; border-radius: 8px;">
                                    <i class="bi bi-person-plus-fill"></i>
                                    <span>Tambah NIP</span>
                                </button>
                            </div>
                        </div>

                        <!-- Assignees List Container -->
                        <div class="assigned-list-container" style="max-height: 280px; overflow-y: auto;">
                            <ul class="list-group list-group-flush rounded border" id="smAssignedList">
                                <!-- Populated dynamically via AJAX -->
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-top px-4 py-3">
                <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal" style="border: 1px solid #cbd5e1; border-radius: 8px;">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script src="<?= asset_url('apps/assets/js/custom/pages/settings/service-manager.js?v=' . time()); ?>"></script>
<?= $this->endSection(); ?>
