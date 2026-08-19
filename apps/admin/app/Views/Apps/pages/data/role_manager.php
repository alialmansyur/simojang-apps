<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>

<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork-common.css') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork.css?v=99') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/role-manager.css?v=' . time()) ?>">
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>
<main class="page-content" aria-labelledby="rolePageTitle">
    <div class="text-start tw-wrap container-fluid role-manager-wrap">
        
        <!-- Header -->
        <div class="row align-items-center mt-4 mb-3" role="banner">
            <div class="col-12 col-md-8 text-start">
                <h1 class="tw-title lh-1" id="rolePageTitle" style="color: #1a202c; font-size: 2.2rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.5rem;">
                    Kelola Role & Hak Akses
                </h1>
                <p class="tw-subtitle text-secondary mb-0" style="font-size: 1rem; font-weight: 400;">
                    Kelola daftar role pengguna dan konfigurasi izin akses menu / halaman secara terpusat.
                </p>
            </div>
            <div class="col-12 col-md-4 text-md-end mt-2 mt-md-0">
                <a href="<?= base_url('manage-layanan') ?>" class="btn btn-light fw-bold px-3 d-inline-flex align-items-center justify-content-center gap-2" style="height: 42px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; line-height: 1;">
                    <i class="bi bi-gear-fill d-inline-flex align-items-center" style="font-size: 1rem; line-height: 1;"></i>
                    <span>Service Manager</span>
                </a>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="tw-head d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4" role="toolbar">
            <div class="d-flex flex-column flex-sm-row flex-wrap align-items-stretch align-items-sm-center gap-2 flex-grow-1" style="max-width: 650px;">
                <!-- Role Dropdown Selector -->
                <div style="min-width: 220px;">
                    <select class="form-select tw-search-input fw-bold d-flex align-items-center" id="selectRole" style="height: 42px; padding-top: 0.5rem; padding-bottom: 0.5rem; font-size: 0.95rem;">
                        <?php if (!empty($roles)): ?>
                            <?php foreach ($roles as $idx => $r): ?>
                                <option value="<?= $r['id'] ?>" data-code="<?= esc($r['role_code']) ?>" <?= $idx === 0 ? 'selected' : '' ?>>
                                    Role: <?= esc($r['role_name']) ?> (<?= esc($r['role_code']) ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">Belum ada role</option>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Search Input for Menu Tree with Vertically Centered Icon -->
                <div class="position-relative flex-grow-1 d-flex align-items-center">
                    <span class="position-absolute start-0 top-0 bottom-0 d-flex align-items-center justify-content-center ps-3 text-muted" style="pointer-events: none; width: 42px; z-index: 5;">
                        <i class="bi bi-search" style="font-size: 0.95rem; line-height: 1;"></i>
                    </span>
                    <input type="text" id="searchMenu" class="form-control tw-search-input w-100" placeholder="Cari menu atau URL..." style="padding-left: 2.6rem; height: 42px; font-size: 0.95rem;">
                </div>
            </div>

            <!-- Action Buttons with Vertical Centered Icons and Text -->
            <div class="d-flex flex-column flex-sm-row flex-wrap align-items-stretch align-items-sm-center gap-2">
                <button type="button" class="btn btn-light d-inline-flex align-items-center justify-content-center gap-2 px-3" id="btnToggleAllNodes" style="height: 42px; border: 1px solid #cbd5e1; border-radius: 8px; font-weight: 600; font-size: 0.95rem; line-height: 1;" title="Buka / Tutup Seluruh Submenu">
                    <i class="bi bi-chevron-bar-contract d-inline-flex align-items-center" id="iconToggleAllNodes" style="font-size: 1.05rem; line-height: 1;"></i>
                    <span id="lblToggleAllNodes">Tutup Semua</span>
                </button>
                <button type="button" class="btn btn-light d-inline-flex align-items-center justify-content-center gap-2 px-3" id="btnOpenManageUsers" style="height: 42px; border: 1px solid #cbd5e1; border-radius: 8px; font-weight: 600; font-size: 0.95rem; line-height: 1;">
                    <i class="bi bi-people d-inline-flex align-items-center" style="font-size: 1.1rem; line-height: 1;"></i>
                    <span>Daftar User</span>
                </button>
                <button type="button" class="btn btn-primary d-inline-flex align-items-center justify-content-center gap-2 px-4" data-bs-toggle="modal" data-bs-target="#modalAddRole" style="height: 42px; border-radius: 8px; line-height: 1;">
                    <span class="fw-bold" style="font-size: 0.95rem;">Tambah Role</span> 
                    <i class="bi bi-plus-lg d-inline-flex align-items-center" style="font-size: 1.05rem; line-height: 1;"></i>
                </button>
            </div>
        </div>

        <!-- Role Summary Banner Card -->
        <div class="role-summary-card mb-4" id="roleSummaryCard">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="role-banner-icon-box">
                        <i class="bi bi-shield-lock-fill" style="line-height: 1;"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h4 class="mb-0 fw-bold" id="cardRoleName" style="color: #0f172a; font-size: 1.25rem;">Administrator</h4>
                        </div>
                        <p class="text-secondary mb-0 small" id="cardRoleDescription" style="max-width: 600px;">
                            Akses penuh ke seluruh modul dan menu sistem.
                        </p>
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-4 border-start-md ps-md-4">
                    <div class="role-stat-item">
                        <div>
                            <span class="d-block text-muted" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.04em;">Menu Diizinkan</span>
                            <strong id="statActiveMenus" style="font-size: 1.05rem; color: #1e293b;">0</strong> <span class="text-muted small" id="statTotalMenus">/ 0</span>
                        </div>
                    </div>
                    <div class="role-stat-item">
                        <div>
                            <span class="d-block text-muted" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.04em;">Pengguna</span>
                            <strong id="statTotalUsers" style="font-size: 1.05rem; color: #1e293b;">0</strong> <span class="text-muted small">Pegawai</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-1 ms-md-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center justify-content-center p-2" id="btnEditRole" title="Edit Role" style="width: 36px; height: 36px; border-radius: 8px;">
                            <i class="bi bi-pencil-square" style="font-size: 1rem; line-height: 1;"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center justify-content-center p-2 d-none" id="btnDeleteRole" title="Hapus Role" style="width: 36px; height: 36px; border-radius: 8px;">
                            <i class="bi bi-trash3" style="font-size: 1rem; line-height: 1;"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tree Mapping Table Area -->
        <div class="tree-table-card">
            <div class="table-responsive">
                <table class="table tree-table" id="treeTable">
                    <thead>
                        <tr>
                            <th>Menu / Halaman</th>
                            <th style="width: 140px;" class="text-center">Tipe</th>
                            <th style="width: 250px;">URL / Route</th>
                            <th style="width: 180px;" class="text-center">Akses Role</th>
                        </tr>
                    </thead>
                    <tbody id="treeTableBody">
                        <!-- Loaded dynamically via AJAX -->
                    </tbody>
                </table>
            </div>

            <!-- Skeleton Loading State -->
            <div id="treeSkeleton" class="p-3 d-none">
                <?php for ($i = 0; $i < 6; $i++): ?>
                    <div class="d-flex align-items-center justify-content-between py-3 border-bottom">
                        <div class="d-flex align-items-center gap-3" style="width: 50%;">
                            <span class="skeleton-box" style="width: 24px; height: 24px; border-radius: 6px;"></span>
                            <span class="skeleton-box" style="width: <?= 150 + ($i * 20) ?>px; height: 18px;"></span>
                        </div>
                        <span class="skeleton-box" style="width: 80px; height: 20px;"></span>
                        <span class="skeleton-box" style="width: 140px; height: 16px;"></span>
                        <span class="skeleton-box" style="width: 90px; height: 24px;"></span>
                    </div>
                <?php endfor; ?>
            </div>

            <!-- Empty Search State -->
            <div class="p-5 text-center d-none" id="treeEmptyState">
                <i class="bi bi-search text-muted mb-2 d-block" style="font-size: 2.5rem;"></i>
                <h6 class="fw-bold text-dark">Tidak Ada Menu yang Cocok</h6>
                <p class="text-muted small mb-0">Coba gunakan kata kunci pencarian yang lain.</p>
            </div>
        </div>

    </div>
</main>

<!-- Modal Add Role (Flat Minimalist matching activity-gallery) -->
<div class="modal fade flat-modal" id="modalAddRole" tabindex="-1" aria-labelledby="modalAddRoleLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4 align-items-center">
                <h5 class="modal-title fw-bold" id="modalAddRoleLabel" style="color: #1a202c; font-size: 1.25rem;">
                    Tambah Role Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formAddRole" autocomplete="off">
                <div class="modal-body px-4 pt-3 pb-4">
                    <div class="row gy-3">
                        <div class="col-md-5">
                            <label for="inputRoleCode" class="form-label d-block fw-bold" style="font-size: 0.9rem; color: #1a202c; margin-bottom: 0.5rem;">
                                Kode Role <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="role_code" id="inputRoleCode" placeholder="Contoh: OPERATOR" style="text-transform: uppercase;" required>
                            <span class="text-muted" style="font-size: 0.75rem;">Huruf besar & underscore (2-30 karakter).</span>
                        </div>
                        <div class="col-md-7">
                            <label for="inputRoleName" class="form-label d-block fw-bold" style="font-size: 0.9rem; color: #1a202c; margin-bottom: 0.5rem;">
                                Nama Role <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="role_name" id="inputRoleName" placeholder="Contoh: Operator Tim Kerja" required>
                        </div>
                        <div class="col-12">
                            <label for="inputRoleDesc" class="form-label d-block fw-bold" style="font-size: 0.9rem; color: #1a202c; margin-bottom: 0.5rem;">
                                Deskripsi Role
                            </label>
                            <textarea class="form-control" name="description" id="inputRoleDesc" rows="2" placeholder="Jelaskan ruang lingkup dan tanggung jawab role ini..."></textarea>
                        </div>
                        <div class="col-12">
                            <label for="selectCopyRole" class="form-label d-block fw-bold" style="font-size: 0.9rem; color: #1a202c; margin-bottom: 0.5rem;">
                                Salin Hak Akses dari Role Lain <span class="text-muted fw-normal">(Opsional)</span>
                            </label>
                            <select class="form-select" name="copy_from_role_id" id="selectCopyRole">
                                <option value="">-- Kosongkan (Atur Manual Setelah Dibuat) --</option>
                                <?php foreach ($roles as $r): ?>
                                    <option value="<?= $r['id'] ?>"><?= esc($r['role_name']) ?> (<?= esc($r['role_code']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 pb-4 px-4 justify-content-end">
                    <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal" style="border: 1px solid #e2e8f0; border-radius: 8px;">Batal</button>
                    <button type="button" class="btn btn-primary fw-bold px-4" id="btnSaveRole" style="border-radius: 8px;">Simpan Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Role -->
<div class="modal fade flat-modal" id="modalEditRole" tabindex="-1" aria-labelledby="modalEditRoleLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4 align-items-center">
                <h5 class="modal-title fw-bold" id="modalEditRoleLabel" style="color: #1a202c; font-size: 1.25rem;">
                    Edit Informasi Role
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditRole" autocomplete="off">
                <input type="hidden" name="role_id" id="editRoleId">
                <div class="modal-body px-4 pt-3 pb-4">
                    <div class="row gy-3">
                        <div class="col-12">
                            <label for="editRoleName" class="form-label d-block fw-bold" style="font-size: 0.9rem; color: #1a202c; margin-bottom: 0.5rem;">
                                Nama Role <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="role_name" id="editRoleName" required>
                        </div>
                        <div class="col-12">
                            <label for="editRoleDesc" class="form-label d-block fw-bold" style="font-size: 0.9rem; color: #1a202c; margin-bottom: 0.5rem;">
                                Deskripsi Role
                            </label>
                            <textarea class="form-control" name="description" id="editRoleDesc" rows="3"></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch custom-role-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="editRoleActive" name="is_active" value="1" checked>
                                <label class="form-check-label fw-bold ms-2" for="editRoleActive">Role Aktif</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 pb-4 px-4 justify-content-end">
                    <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal" style="border: 1px solid #e2e8f0; border-radius: 8px;">Batal</button>
                    <button type="button" class="btn btn-primary fw-bold px-4" id="btnUpdateRole" style="border-radius: 8px;">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Manage Users for Role -->
<div class="modal fade flat-modal" id="modalManageUsers" tabindex="-1" aria-labelledby="modalManageUsersLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4 align-items-center">
                <div>
                    <h5 class="modal-title fw-bold mb-1" id="modalManageUsersLabel" style="color: #1a202c; font-size: 1.25rem;">
                        Pengguna dengan Role: <span class="text-primary" id="modalUsersRoleName">Administrator</span>
                    </h5>
                    <p class="text-secondary small mb-0">Kelola pegawai yang terdaftar pada role ini.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 pt-3 pb-4">
                <!-- Search within modal -->
                <div class="d-flex gap-2 mb-3">
                    <div class="position-relative flex-grow-1">
                        <i class="bi bi-search position-absolute text-muted" style="left: 1rem; top: 50%; transform: translateY(-50%); margin-top: -1px; pointer-events: none;"></i>
                        <input type="text" id="searchRoleUser" class="form-control" placeholder="Cari nama, NIP, atau email..." style="padding-left: 2.5rem; font-size: 0.9rem;">
                    </div>
                    <button type="button" class="btn btn-outline-primary fw-bold" id="btnToggleAssignNew">
                        <i class="bi bi-person-plus me-1"></i> Tambah User ke Role
                    </button>
                </div>

                <!-- Panel Assign New User (Collapsible) -->
                <div class="p-3 bg-light rounded-3 border mb-3 d-none" id="panelAssignNewUser">
                    <h6 class="fw-bold mb-2 text-dark" style="font-size: 0.92rem;">Pindahkan / Assign Pengguna ke Role Ini:</h6>
                    <div class="d-flex gap-2">
                        <select class="form-select flex-grow-1" id="selectAvailableUser" style="font-size: 0.9rem;">
                            <option value="">Pilih pengguna dari role lain...</option>
                        </select>
                        <button type="button" class="btn btn-primary fw-bold px-3 text-nowrap" id="btnConfirmAssignUser">
                            Assign ke Role
                        </button>
                    </div>
                </div>

                <!-- Users List Container -->
                <div id="roleUsersListContainer" style="max-height: 380px; overflow-y: auto;">
                    <!-- Populated via AJAX -->
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0 pb-4 px-4 justify-content-end">
                <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal" style="border: 1px solid #e2e8f0; border-radius: 8px;">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script src="<?= asset_url('apps/assets/js/custom/pages/role-manager.js?v=' . time()) ?>"></script>
<?= $this->endSection(); ?>
