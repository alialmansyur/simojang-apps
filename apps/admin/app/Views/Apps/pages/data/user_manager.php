<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>

<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork-common.css') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork.css?v=99') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/role-manager.css?v=' . time()) ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/user-manager.css?v=' . time()) ?>">
<style>
/* Table Row Padding & Spacing Enhancement */
#userTable {
    width: 100% !important;
    border-collapse: separate !important;
    border-spacing: 0 !important;
}
#userTable thead th {
    background-color: #f8fafc !important;
    color: #475569 !important;
    font-size: 0.85rem !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.04em !important;
    padding: 0.75rem 0.85rem !important;
    border-bottom: 1px solid #e2e8f0 !important;
    border-top: none !important;
    vertical-align: middle !important;
    white-space: nowrap !important;
}
#userTable tbody td {
    padding: 0.75rem 0.85rem !important;
    vertical-align: middle !important;
    border-bottom: 1px solid #edf2f7 !important;
    background-color: #ffffff !important;
    transition: background-color 0.15s ease !important;
}
#userTable tbody tr:hover td {
    background-color: #f8fafc !important;
}
#userTable th:first-child,
#userTable td:first-child {
    padding-left: 1.25rem !important;
}
#userTable th:last-child,
#userTable td:last-child {
    padding-right: 1.25rem !important;
}
#userPaginationWrapper {
    padding: 0.85rem 1.25rem !important;
}

/* User Search Input & Vertical-Centered Clear Button */
.user-search-wrap {
    position: relative !important;
    min-width: 250px !important;
    display: flex !important;
    align-items: center !important;
}

.user-search-icon-wrap {
    position: absolute !important;
    left: 0 !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    width: 40px !important;
    height: 100% !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    pointer-events: none !important;
    z-index: 5 !important;
    color: #94a3b8 !important;
}

.user-search-icon-wrap i {
    font-size: 0.95rem !important;
    line-height: 1 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

#btnClearSearch {
    position: absolute !important;
    right: 0.25rem !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    bottom: auto !important;
    width: 36px !important;
    height: 36px !important;
    padding: 0 !important;
    margin: 0 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    border: none !important;
    background: transparent !important;
    box-shadow: none !important;
    color: #64748b !important;
    cursor: pointer !important;
    z-index: 6 !important;
}

#btnClearSearch.d-none {
    display: none !important;
}

#btnClearSearch i {
    font-size: 1.05rem !important;
    line-height: 1 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

#btnClearSearch:hover {
    color: #1e293b !important;
}

/* User Stats 1-Row Strip */
.user-stats-strip {
    display: flex !important;
    flex-direction: row !important;
    flex-wrap: nowrap !important;
    align-items: center !important;
    gap: 1.25rem !important;
}

@media (min-width: 992px) {
    .border-start-lg {
        border-left: 1px solid #e2e8f0 !important;
    }
}

.user-stats-strip .role-stat-item {
    white-space: nowrap !important;
    min-width: fit-content !important;
}

.user-stats-strip .stat-label {
    font-size: 0.72rem !important;
    text-transform: uppercase !important;
    letter-spacing: 0.04em !important;
    font-weight: 600 !important;
    color: #64748b !important;
    margin-bottom: 2px !important;
}

.user-stats-strip .stat-value {
    font-size: 1.15rem !important;
    font-weight: 800 !important;
    line-height: 1.2 !important;
}

.user-stats-strip .stat-unit {
    font-size: 0.8rem !important;
    font-weight: 500 !important;
    color: #64748b !important;
}

.stat-divider {
    width: 1px;
    height: 32px;
    background-color: #e2e8f0;
    flex-shrink: 0;
}

/* Guaranteed 1:1 Solid Slightly-Rounded Buttons */
.btn-action-sq {
    width: 32px !important;
    height: 32px !important;
    min-width: 32px !important;
    min-height: 32px !important;
    max-width: 32px !important;
    max-height: 32px !important;
    aspect-ratio: 1 / 1 !important;
    padding: 0 !important;
    margin: 0 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    border-radius: 10px !important;
    border: none !important;
    color: #ffffff !important;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06) !important;
    transition: all 0.15s ease !important;
    vertical-align: middle !important;
    cursor: pointer !important;
}
.btn-action-sq i {
    font-size: 0.95rem !important;
    line-height: 1 !important;
    color: #ffffff !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
}
.btn-action-sq.btn-primary, .btn-action-sq.btn-edit-user {
    background-color: #1040c1 !important;
    border-color: #1040c1 !important;
}
.btn-action-sq.btn-primary:hover, .btn-action-sq.btn-edit-user:hover {
    background-color: #0c2f5a !important;
    border-color: #0c2f5a !important;
    transform: translateY(-1px) !important;
}
.btn-action-sq.btn-warning, .btn-action-sq.btn-reset-pwd {
    background-color: #F59E0B !important;
    border-color: #F59E0B !important;
}
.btn-action-sq.btn-warning:hover, .btn-action-sq.btn-reset-pwd:hover {
    background-color: #D97706 !important;
    border-color: #D97706 !important;
    transform: translateY(-1px) !important;
}
.btn-action-sq.btn-danger, .btn-action-sq.btn-delete-user {
    background-color: #DC3545 !important;
    border-color: #DC3545 !important;
}
.btn-action-sq.btn-danger:hover, .btn-action-sq.btn-delete-user:hover {
    background-color: #BB2D3B !important;
    border-color: #BB2D3B !important;
    transform: translateY(-1px) !important;
}

/* Modal Form Styling - Matching /apps-statistik-internal */
.modal-content {
    background-color: #ffffff !important;
    border-radius: 14px !important;
    border: none !important;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
}

.modal-header {
    border-bottom: 1px solid #edf2f7 !important;
    padding: 1.15rem 1.5rem !important;
}

.modal-header .modal-title {
    color: #0f172a !important;
    font-weight: 700 !important;
    font-size: 1.25rem !important;
}

.modal-body {
    padding: 1.5rem !important;
    color: #212529 !important;
}

.modal-body label,
.modal-body .form-label,
.modal-body .user-modal-label {
    color: #1e293b !important;
    font-weight: 600 !important;
    font-size: 1.05rem !important;
    margin-bottom: 0.5rem !important;
    display: inline-block !important;
}

.modal-body .form-check-label {
    color: #1e293b !important;
    font-weight: 600 !important;
    font-size: 1.02rem !important;
}

.modal-body .form-control,
.modal-body .form-select {
    color: #0f172a !important;
    background-color: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 8px !important;
    padding: 0.65rem 0.95rem !important;
    font-size: 1rem !important;
    box-shadow: none !important;
}

.modal-body .form-control:focus,
.modal-body .form-select:focus {
    border-color: #1040c1 !important;
    box-shadow: 0 0 0 3px rgba(16, 64, 193, 0.12) !important;
}

.modal-body .form-control::placeholder {
    color: #94a3b8 !important;
    font-size: 0.95rem !important;
}

.modal-body .text-muted,
.modal-body .text-secondary,
.modal-body small {
    color: #64748b !important;
    font-size: 0.85rem !important;
}

/* Generate & Copy Password Buttons */
.btn-gen-pwd {
    height: 42px !important;
    border-radius: 8px !important;
    border: 1.5px solid #1040c1 !important;
    color: #1040c1 !important;
    background-color: #ffffff !important;
    font-weight: 600 !important;
    font-size: 0.9rem !important;
    padding: 0.5rem 1.15rem !important;
    transition: all 0.15s ease-in-out !important;
    cursor: pointer !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.btn-gen-pwd:hover,
.btn-gen-pwd:focus,
.btn-gen-pwd:active {
    background-color: #1040c1 !important;
    border-color: #1040c1 !important;
    color: #ffffff !important;
}

.btn-copy-pwd {
    height: 42px !important;
    border-radius: 8px !important;
    border: 1.5px solid #64748b !important;
    color: #475569 !important;
    background-color: #ffffff !important;
    font-weight: 600 !important;
    font-size: 0.9rem !important;
    padding: 0.5rem 1rem !important;
    transition: all 0.15s ease-in-out !important;
    cursor: pointer !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.btn-copy-pwd:hover,
.btn-copy-pwd:focus,
.btn-copy-pwd:active {
    background-color: #475569 !important;
    border-color: #475569 !important;
    color: #ffffff !important;
}

.modal-footer {
    border-top: 1px solid #edf2f7 !important;
    padding: 1.1rem 1.5rem !important;
    display: flex !important;
    align-items: center !important;
    justify-content: flex-end !important;
    gap: 0.75rem !important;
}

.modal-footer .btn {
    border-radius: 8px !important;
    font-weight: 600 !important;
    font-size: 0.95rem !important;
    padding: 0.55rem 1.35rem !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.modal-footer .btn-light {
    background-color: #f1f5f9 !important;
    border: 1px solid #e2e8f0 !important;
    color: #334155 !important;
}

.modal-footer .btn-light:hover {
    background-color: #e2e8f0 !important;
    color: #0f172a !important;
}

.modal-footer .btn-primary {
    background-color: #1040c1 !important;
    border-color: #1040c1 !important;
    color: #ffffff !important;
}

.modal-footer .btn-primary:hover {
    background-color: #0c2f5a !important;
    border-color: #0c2f5a !important;
}

.modal-footer .btn-warning {
    background-color: #F59E0B !important;
    border-color: #F59E0B !important;
    color: #ffffff !important;
}

.modal-footer .btn-warning:hover {
    background-color: #D97706 !important;
    border-color: #D97706 !important;
}
</style>
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>
<main class="page-content" aria-labelledby="userPageTitle">
    <div class="text-start tw-wrap container-fluid role-manager-wrap user-manager-wrap">
        
        <!-- Header Banner -->
        <div class="row align-items-center mt-4 mb-3" role="banner">
            <div class="col-12 col-md-8 text-start">
                <h1 class="tw-title lh-1" id="userPageTitle" style="color: #1a202c; font-size: 2.2rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.5rem;">
                    Kelola Pengguna
                </h1>
                <p class="tw-subtitle text-secondary mb-0" style="font-size: 1rem; font-weight: 400;">
                    Kelola akun pengguna, penugasan role, status aktivasi, dan reset password secara terpusat.
                </p>
            </div>
            <div class="col-12 col-md-4 text-md-end mt-2 mt-md-0">
                <div class="d-inline-flex align-items-center gap-2">
                    <a href="<?= base_url('manage-role') ?>" class="btn btn-light fw-bold px-3 d-inline-flex align-items-center justify-content-center gap-2" style="height: 42px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; line-height: 1;">
                        <i class="bi bi-shield-lock d-inline-flex align-items-center" style="font-size: 1rem; line-height: 1;"></i>
                        <span>Kelola Role</span>
                    </a>
                    <a href="<?= base_url('manage-layanan') ?>" class="btn btn-light fw-bold px-3 d-inline-flex align-items-center justify-content-center gap-2" style="height: 42px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; line-height: 1;">
                        <i class="bi bi-gear-fill d-inline-flex align-items-center" style="font-size: 1rem; line-height: 1;"></i>
                        <span>Service Manager</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Toolbar (Search, Filter, Actions) -->
        <div class="tw-head d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4" role="toolbar">
            <div class="d-flex flex-column flex-sm-row flex-wrap align-items-stretch align-items-sm-center gap-2 flex-grow-1" style="max-width: 750px;">
                <!-- Search Input with Vertically Centered Icon -->
                <div class="user-search-wrap flex-grow-1">
                    <span class="user-search-icon-wrap">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" id="userSearchInput" class="form-control tw-search-input w-100" placeholder="Cari nama, NIP, email..." style="padding-left: 2.6rem; padding-right: 2.5rem; height: 42px; font-size: 0.95rem;">
                    <button type="button" id="btnClearSearch" class="btn d-none" title="Bersihkan Pencarian" aria-label="Bersihkan Pencarian">
                        <i class="bi bi-x-circle-fill"></i>
                    </button>
                </div>

                <!-- Role Filter Dropdown -->
                <div style="min-width: 170px;">
                    <select id="filterRole" class="form-select tw-search-input fw-bold d-flex align-items-center" style="height: 42px; font-size: 0.95rem;">
                        <option value="">Semua Role</option>
                        <?php if (!empty($roles) && is_array($roles)): ?>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= esc($r['role_code']); ?>"><?= esc($r['role_name']); ?> (<?= esc($r['role_code']); ?>)</option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Status Filter Dropdown -->
                <div style="min-width: 140px;">
                    <select id="filterStatus" class="form-select tw-search-input fw-bold d-flex align-items-center" style="height: 42px; font-size: 0.95rem;">
                        <option value="">Semua Status</option>
                        <option value="active">Aktif</option>
                        <option value="inactive">Non-Aktif</option>
                    </select>
                </div>
            </div>

            <!-- Action Button: Tambah Pengguna -->
            <div class="d-flex flex-column flex-sm-row flex-wrap align-items-stretch align-items-sm-center gap-2">
                <button type="button" class="btn btn-primary d-inline-flex align-items-center justify-content-center gap-2 px-4" data-bs-toggle="modal" data-bs-target="#modalAddUser" style="height: 42px; border-radius: 8px; line-height: 1;">
                    <span class="fw-bold" style="font-size: 0.95rem;">Tambah Pengguna</span> 
                    <i class="bi bi-plus-lg d-inline-flex align-items-center" style="font-size: 1.05rem; line-height: 1;"></i>
                </button>
            </div>
        </div>

        <!-- Summary Banner Card -->
        <div class="role-summary-card mb-4" id="userSummaryCard">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="role-banner-icon-box flex-shrink-0">
                        <i class="bi bi-people-fill" style="line-height: 1;"></i>
                    </div>
                    <div>
                        <h4 class="mb-1 fw-bold" style="color: #0f172a; font-size: 1.2rem;">Manajemen Akun Pengguna</h4>
                        <p class="text-secondary mb-0 small" style="line-height: 1.4;">
                            Hak akses menu dan otorisasi halaman otomatis mengikuti role yang ditetapkan pada masing-masing akun pengguna.
                        </p>
                    </div>
                </div>

                <div class="user-stats-strip d-flex align-items-center flex-nowrap gap-3 gap-xl-4 ps-lg-4 border-start-lg flex-shrink-0">
                    <div class="role-stat-item">
                        <div>
                            <span class="d-block text-muted stat-label">Total Pengguna</span>
                            <div class="d-flex align-items-baseline gap-1">
                                <strong id="statTotalUsers" class="stat-value text-dark"><?= (int) ($stats['total_users'] ?? 0); ?></strong> 
                                <span class="stat-unit text-muted">Akun</span>
                            </div>
                        </div>
                    </div>
                    <div class="stat-divider"></div>
                    <div class="role-stat-item">
                        <div>
                            <span class="d-block text-muted stat-label">Pengguna Aktif</span>
                            <div class="d-flex align-items-baseline gap-1">
                                <strong id="statActiveUsers" class="stat-value text-success"><?= (int) ($stats['active_users'] ?? 0); ?></strong> 
                                <span class="stat-unit text-muted">User</span>
                            </div>
                        </div>
                    </div>
                    <div class="stat-divider"></div>
                    <div class="role-stat-item">
                        <div>
                            <span class="d-block text-muted stat-label">Non-Aktif</span>
                            <div class="d-flex align-items-baseline gap-1">
                                <strong id="statInactiveUsers" class="stat-value text-danger"><?= (int) ($stats['inactive_users'] ?? 0); ?></strong> 
                                <span class="stat-unit text-muted">User</span>
                            </div>
                        </div>
                    </div>
                    <div class="stat-divider"></div>
                    <div class="role-stat-item">
                        <div>
                            <span class="d-block text-muted stat-label">Total Role</span>
                            <div class="d-flex align-items-baseline gap-1">
                                <strong id="statTotalRoles" class="stat-value text-primary"><?= (int) ($stats['total_roles'] ?? 0); ?></strong> 
                                <span class="stat-unit text-muted">Role</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Table Card -->
        <div class="tree-table-card">
            <div class="table-responsive">
                <table class="table tree-table align-middle mb-0" id="userTable">
                    <thead>
                        <tr>
                            <th style="width: 30%; padding-left: 1.5rem;">Pengguna</th>
                            <th style="width: 24%;">Email</th>
                            <th style="width: 13%;" class="text-center">Role</th>
                            <th style="width: 12%;" class="text-center">Status</th>
                            <th style="width: 13%;">Last Login</th>
                            <th style="width: 8%; padding-right: 1.5rem;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="userTableBody">
                        <!-- Loaded dynamically via user-manager.js -->
                    </tbody>
                </table>
            </div>

            <!-- Skeleton Loading State -->
            <div id="userSkeleton" class="p-3 d-none">
                <?php for ($i = 0; $i < 5; $i++): ?>
                    <div class="d-flex align-items-center justify-content-between py-3 border-bottom">
                        <div class="d-flex align-items-center gap-3" style="width: 40%;">
                            <span class="skeleton-box" style="width: 40px; height: 40px; border-radius: 10px;"></span>
                            <div>
                                <span class="skeleton-box d-block mb-1" style="width: <?= 140 + ($i * 15) ?>px; height: 16px;"></span>
                                <span class="skeleton-box d-block" style="width: 100px; height: 12px;"></span>
                            </div>
                        </div>
                        <span class="skeleton-box" style="width: 120px; height: 16px;"></span>
                        <span class="skeleton-box" style="width: 80px; height: 24px; border-radius: 6px;"></span>
                        <span class="skeleton-box" style="width: 60px; height: 24px; border-radius: 20px;"></span>
                        <span class="skeleton-box" style="width: 80px; height: 16px;"></span>
                        <span class="skeleton-box" style="width: 90px; height: 32px; border-radius: 8px;"></span>
                    </div>
                <?php endfor; ?>
            </div>

            <!-- Empty State -->
            <div id="userEmptyState" class="text-center py-5 d-none">
                <div class="d-flex flex-column align-items-center justify-content-center text-center my-4" style="white-space: normal !important; width: 100%;">
                    <img src="<?= asset_url('apps/assets/images/empty-content-profile.png'); ?>" alt="Tidak Ditemukan" style="max-width: 250px; margin-bottom: 1.5rem;">
                    <h5 class="fw-bold" style="color: #1a202c; font-size: 1.25rem; margin-bottom: 0.5rem;">Pencarian Tidak Ditemukan</h5>
                    <p class="text-muted mb-3" style="font-size: 0.95rem; max-width: 400px; white-space: normal !important; word-wrap: break-word;">Data tidak ditemukan. Silakan periksa kembali kata kunci atau filter pencarian.</p>
                    <button type="button" class="btn btn-sm btn-outline-primary fw-semibold px-3" id="btnResetFilter" style="border-radius: 8px;">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filter
                    </button>
                </div>
            </div>

            <!-- Pagination & Entries Footer (Matching /apps-ikpa style) -->
            <div class="p-3 border-top bg-white d-flex flex-column flex-md-row align-items-center justify-content-between gap-3" id="userPaginationWrapper">
                <div class="d-flex align-items-center gap-3">
                    <!-- Show Entries Select -->
                    <select id="userPerPageSelect" class="form-select form-select-sm user-per-page-select" style="width: 72px; height: 36px; border: 1px solid #d8e3f5; border-radius: 8px; font-weight: 600; font-size: 0.85rem; color: #475569;">
                        <option value="10">10</option>
                        <option value="15" selected>15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>

                    <div class="text-secondary small fw-medium" id="userEntriesInfo" style="font-size: 0.85rem; color: #617392;">
                        Menampilkan <strong>0 - 0</strong> dari <strong>0</strong> data
                    </div>
                </div>

                <nav aria-label="Navigasi Halaman Pengguna">
                    <ul class="pagination pagination-sm mb-0 gap-1 service-ui-pagination" id="userPagination">
                        <!-- Dynamic pagination numbers styled exactly like /apps-ikpa -->
                    </ul>
                </nav>
            </div>
        </div>

    </div>
</main>

<!-- ============================================================== -->
<!-- MODAL 1: TAMBAH PENGGUNA BARU                                  -->
<!-- ============================================================== -->
<div class="modal fade" id="modalAddUser" tabindex="-1" aria-labelledby="modalAddUserLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; background-color: #ffffff;">
            <div class="modal-header border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold mb-0" id="modalAddUserLabel" style="color: #0f172a; font-size: 1.25rem;">Tambah Pengguna Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formAddUser" autocomplete="off">
                <div class="modal-body p-4">
                    <!-- Quick Pegawai Lookup without search icon -->
                    <div class="mb-3 position-relative">
                        <label for="addLookupPegawai" class="form-label mb-1">
                            Pencarian Cepat Data Pegawai (Opsional)
                        </label>
                        <input type="text" id="addLookupPegawai" class="form-control" placeholder="Ketik NIP atau Nama Pegawai untuk auto-fill..." autocomplete="off" style="color: #0f172a; border-radius: 8px !important;">
                        <div class="lookup-dropdown" id="lookupDropdown"></div>
                        <small class="text-muted mt-1 d-block">Pilih dari hasil pencarian untuk mengisi otomatis NIP, Nama, dan Email.</small>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="addUsername" class="form-label mb-1">
                                Username / NIP <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="addUsername" name="username" placeholder="Contoh: 199707252024211004" required style="color: #0f172a; border-radius: 8px !important;">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="addFullname" class="form-label mb-1">
                                Nama Lengkap <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="addFullname" name="fullname" placeholder="Nama lengkap beserta gelar" required style="color: #0f172a; border-radius: 8px !important;">
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="addEmail" class="form-label mb-1">
                                Alamat Email <span class="text-danger">*</span>
                            </label>
                            <input type="email" class="form-control" id="addEmail" name="email" placeholder="nama@instansi.go.id" required style="color: #0f172a; border-radius: 8px !important;">
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="addRole" class="form-label mb-1">
                                Role Hak Akses <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="addRole" name="role" required style="color: #0f172a; border-radius: 8px !important;">
                                <?php if (!empty($roles) && is_array($roles)): ?>
                                    <?php foreach ($roles as $r): ?>
                                        <option value="<?= esc($r['role_code']); ?>" <?= $r['role_code'] === 'USR' ? 'selected' : ''; ?>>
                                            <?= esc($r['role_name']); ?> (<?= esc($r['role_code']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="addPassword" class="form-label mb-1">
                                Password Awal <span class="text-danger">*</span>
                            </label>
                            <div class="d-flex align-items-center gap-2">
                                <div class="position-relative flex-grow-1">
                                    <input type="password" class="form-control pe-5" id="addPassword" name="password" placeholder="Minimal 6 karakter" required minlength="6" style="color: #0f172a; border-radius: 8px !important;">
                                    <button type="button" class="btn position-absolute end-0 top-50 translate-middle-y text-muted text-decoration-none pe-3" id="btnToggleAddPwd" title="Lihat Password" style="border: none; background: transparent; box-shadow: none;">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-gen-pwd fw-semibold text-nowrap px-3" id="btnGenAddPwd" title="Buat Password Acak Aman">
                                    Generate Password
                                </button>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch custom-user-switch d-flex align-items-center gap-2">
                                <input class="form-check-input mt-0" type="checkbox" role="switch" id="addActive" name="active" checked>
                                <label class="form-check-label mb-0" for="addActive">
                                    Aktifkan akun ini sekarang
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top px-4 py-3">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveAddUser" style="background-color: #1040c1; border-color: #1040c1;">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL 2: EDIT DATA PENGGUNA                                    -->
<!-- ============================================================== -->
<div class="modal fade" id="modalEditUser" tabindex="-1" aria-labelledby="modalEditUserLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; background-color: #ffffff;">
            <div class="modal-header border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold mb-0" id="modalEditUserLabel" style="color: #0f172a; font-size: 1.25rem;">Edit Data Pengguna</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditUser" autocomplete="off">
                <input type="hidden" id="editUserId" name="id">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="editUsername" class="form-label mb-1">
                                Username / NIP
                            </label>
                            <input type="text" class="form-control bg-light" id="editUsername" disabled readonly style="color: #475569; border-radius: 8px !important;">
                            <small class="text-muted mt-1 d-block">Username / NIP tidak dapat diubah.</small>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="editFullname" class="form-label mb-1">
                                Nama Lengkap <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="editFullname" name="fullname" required style="color: #0f172a; border-radius: 8px !important;">
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="editEmail" class="form-label mb-1">
                                Alamat Email <span class="text-danger">*</span>
                            </label>
                            <input type="email" class="form-control" id="editEmail" name="email" required style="color: #0f172a; border-radius: 8px !important;">
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="editRole" class="form-label mb-1">
                                Role Hak Akses <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="editRole" name="role" required style="color: #0f172a; border-radius: 8px !important;">
                                <?php if (!empty($roles) && is_array($roles)): ?>
                                    <?php foreach ($roles as $r): ?>
                                        <option value="<?= esc($r['role_code']); ?>">
                                            <?= esc($r['role_name']); ?> (<?= esc($r['role_code']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="editPassword" class="form-label mb-1">
                                Ubah Password (Opsional)
                            </label>
                            <input type="password" class="form-control" id="editPassword" name="password" placeholder="Kosongkan jika tidak ingin mengubah password" minlength="6" style="color: #0f172a; border-radius: 8px !important;">
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch custom-user-switch d-flex align-items-center gap-2">
                                <input class="form-check-input mt-0" type="checkbox" role="switch" id="editActive" name="active">
                                <label class="form-check-label mb-0" for="editActive">
                                    Status Akun Aktif
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top px-4 py-3">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveEditUser" style="background-color: #1040c1; border-color: #1040c1;">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL 3: RESET PASSWORD PENGGUNA                              -->
<!-- ============================================================== -->
<div class="modal fade" id="modalResetPassword" tabindex="-1" aria-labelledby="modalResetPasswordLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; background-color: #ffffff;">
            <div class="modal-header border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold mb-0" id="modalResetPasswordLabel" style="color: #0f172a; font-size: 1.25rem;">Reset Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formResetPassword" autocomplete="off">
                <input type="hidden" id="resetUserId" name="id">
                <div class="modal-body p-4">
                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <div class="text-secondary small fw-medium" style="color: #475569 !important;">Akun Pengguna:</div>
                        <div class="fw-bold mt-1" id="resetUserDisplay" style="color: #0f172a; font-size: 1rem;">-</div>
                    </div>

                    <div class="mb-3">
                        <label for="resetNewPassword" class="form-label mb-1">
                            Password Baru <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="text" class="form-control font-monospace flex-grow-1" id="resetNewPassword" name="password" required minlength="6" style="color: #0f172a; border-radius: 8px !important;">
                            <button type="button" class="btn btn-outline-primary btn-gen-pwd fw-semibold text-nowrap px-3" id="btnGenResetPwd" title="Buat Password Acak">
                                Generate Password
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-copy-pwd fw-semibold text-nowrap px-3" id="btnCopyResetPwd" title="Salin Password">
                                Salin
                            </button>
                        </div>
                        <small class="text-muted mt-1 d-block">Password dapat di-generate otomatis atau diketik manual.</small>
                    </div>
                </div>
                <div class="modal-footer border-top px-4 py-3">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning fw-bold text-white" id="btnSaveResetPwd" style="background-color: #F59E0B; border-color: #F59E0B;">Simpan Password Baru</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<!-- User Manager Specific JS -->
<script src="<?= asset_url('apps/assets/js/custom/pages/user-manager.js'); ?>"></script>
<?= $this->endSection(); ?>
