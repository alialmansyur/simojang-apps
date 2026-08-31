<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>

<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork-common.css') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork.css?v=99') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/service-manager.css?v=' . time()) ?>">
<style>
    body, html {
        overflow-x: hidden !important;
        width: 100%;
    }
</style>
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>
<main class="page-content" aria-labelledby="servicePageTitle">
    <div class="text-start tw-wrap container-fluid service-manager-wrap" style="min-width: 0;">
        
        <!-- Header -->
        <div class="row align-items-center mt-4 mb-3" role="banner">
            <div class="col-12 col-md-8 text-start">
                <h1 class="tw-title lh-1" id="servicePageTitle" style="color: #0f172a; font-size: 2.2rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.5rem;">
                    Kelola Akses Layanan
                </h1>
                <p class="tw-subtitle mb-0" style="font-size: 1rem; font-weight: 500; color: #334155;">
                    Kelola izin akses modul layanan SIMOJANG per pegawai secara terpusat, konsisten, dan terintegrasi.
                </p>
            </div>
            <div class="col-12 col-md-4 mt-3 mt-md-0 d-flex flex-column flex-sm-row justify-content-md-end gap-2">
                <a href="<?= base_url('manage-role') ?>" class="btn btn-light fw-bold px-3 d-inline-flex align-items-center justify-content-center gap-2 w-100 w-sm-auto" style="height: 42px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; line-height: 1; color: #0f172a;">
                    <i class="bi bi-shield-lock-fill d-inline-flex align-items-center" style="font-size: 1rem; line-height: 1; color: #1040c1;"></i>
                    <span>Role Manager</span>
                </a>
                <a href="<?= base_url('manage-user') ?>" class="btn btn-light fw-bold px-3 d-inline-flex align-items-center justify-content-center gap-2 w-100 w-sm-auto" style="height: 42px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; line-height: 1; color: #0f172a;">
                    <i class="bi bi-people-fill d-inline-flex align-items-center" style="font-size: 1rem; line-height: 1; color: #1040c1;"></i>
                    <span>User Manager</span>
                </a>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="tw-head d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4" role="toolbar">
            <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2 flex-grow-1" style="max-width: 680px; min-width: 0;">
                <!-- Pegawai Dropdown Selector -->
                <div class="flex-grow-1" style="min-width: 0;">
                    <select class="form-select tw-search-input fw-bold w-100" id="selectPegawai" style="height: 42px; padding-top: 0.5rem; padding-bottom: 0.5rem; font-size: 0.92rem; color: #0f172a;">
                        <option value="">-- Cari & Pilih Pegawai --</option>
                        <?php if (!empty($selectedPegawai)): ?>
                            <option value="<?= esc($selectedPegawai['nip']) ?>" selected data-nama="<?= esc($selectedPegawai['nama']) ?>" data-unit="<?= esc($selectedPegawai['unit_kerja_nama']) ?>">
                                <?= esc($selectedPegawai['nama']) ?> (<?= esc($selectedPegawai['nip']) ?>) - <?= esc($selectedPegawai['unit_kerja_nama'] ?? '-') ?>
                            </option>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Search Input for Tree Hierarchy -->
                <div class="position-relative flex-grow-1 d-flex align-items-center" style="min-width: 0;">
                    <span class="position-absolute start-0 top-0 bottom-0 d-flex align-items-center justify-content-center ps-3 text-muted" style="pointer-events: none; width: 42px; z-index: 5;">
                        <i class="bi bi-search" style="font-size: 0.95rem; line-height: 1; color: #64748b;"></i>
                    </span>
                    <input type="text" id="searchService" class="form-control tw-search-input w-100" placeholder="Cari tim kerja, layanan, atau URL..." style="padding-left: 2.6rem; height: 42px; font-size: 0.95rem; color: #0f172a;" <?= empty($selectedNip) ? 'disabled' : '' ?>>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2 flex-shrink-0">
                <button type="button" class="btn btn-light d-inline-flex align-items-center justify-content-center gap-2 px-3" id="btnOpenPegawaiModal" style="height: 42px; border: 1px solid #cbd5e1; border-radius: 8px; font-weight: 600; font-size: 0.95rem; line-height: 1; color: #0f172a;">
                    <i class="bi bi-people d-inline-flex align-items-center" style="font-size: 1.1rem; line-height: 1; color: #1040c1;"></i>
                    <span>Daftar Pegawai</span>
                </button>
                <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center justify-content-center gap-2 px-3 <?= empty($selectedNip) ? 'opacity-50' : '' ?>" id="btnResetDefault" style="height: 42px; border-radius: 8px; font-weight: 600; font-size: 0.95rem; line-height: 1; color: #334155;" title="Kembalikan Izin ke Default Unit Kerja" <?= empty($selectedNip) ? 'disabled' : '' ?>>
                    <i class="bi bi-arrow-counterclockwise" style="font-size: 1.05rem; line-height: 1;"></i>
                    <span>Reset Default</span>
                </button>
                <button type="button" class="btn btn-primary d-inline-flex align-items-center justify-content-center gap-2 px-3 <?= empty($selectedNip) ? 'opacity-50' : '' ?>" id="btnOpenCopyModal" style="height: 42px; border-radius: 8px; line-height: 1;" <?= empty($selectedNip) ? 'disabled' : '' ?>>
                    <i class="bi bi-clipboard-check d-inline-flex align-items-center" style="font-size: 1.05rem; line-height: 1;"></i>
                    <span class="fw-bold" style="font-size: 0.95rem;">Salin Izin</span> 
                </button>
            </div>
        </div>

        <!-- Service Summary Banner Card -->
        <div class="service-summary-card mb-4 <?= empty($selectedNip) ? 'd-none' : '' ?>" id="serviceSummaryCard">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3" style="min-width: 0;">
                    <div class="service-banner-icon-box">
                        <i class="bi bi-person-workspace" style="line-height: 1;"></i>
                    </div>
                    <div style="min-width: 0;">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                            <h4 class="mb-0 fw-bold" id="cardPegawaiName" style="color: #0f172a; font-size: 1.25rem;">
                                <?= esc($selectedPegawai['nama'] ?? 'Pilih Pegawai') ?>
                            </h4>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 fw-bold" id="cardUnitKerjaBadge" style="font-size: 0.78rem; border-radius: 6px;">
                                <?= esc($selectedPegawai['unit_kerja_nama'] ?? '-') ?>
                            </span>
                        </div>
                        <p class="mb-0 small" id="cardPegawaiMeta" style="max-width: 650px; color: #334155; font-weight: 500;">
                            NIP: <span id="cardPegawaiNip" class="fw-bold" style="color: #0f172a;"><?= esc($selectedPegawai['nip'] ?? '-') ?></span> &bull; 
                            Role: <span id="cardPegawaiRole" class="badge bg-secondary-subtle fw-semibold" style="color: #334155; border: 1px solid #cbd5e1;"><?= esc($selectedPegawai['role_name'] ?? 'User') ?></span>
                        </p>
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-4 border-start-md ps-md-4">
                    <div class="service-stat-item">
                        <div>
                            <span class="d-block text-secondary" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.04em; font-weight: 700;">Layanan Diizinkan</span>
                            <strong id="statActiveServices" style="font-size: 1.15rem; color: #0f172a; font-weight: 800;"><?= (int) ($selectedPegawai['total_allowed_services'] ?? 0) ?></strong> 
                            <span class="text-secondary small fw-bold" id="statTotalServices">/ <?= (int) ($selectedPegawai['total_active_services'] ?? 0) ?> Modul</span>
                        </div>
                    </div>
                    <div class="service-stat-item">
                        <div>
                            <span class="d-block text-secondary" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.04em; font-weight: 700;">Tim Kerja Terkait</span>
                            <strong id="statActiveTimKerja" style="font-size: 1.15rem; color: #0f172a; font-weight: 800;"><?= (int) ($selectedPegawai['total_allowed_timkerja'] ?? 0) ?></strong> 
                            <span class="text-secondary small fw-bold" id="statTotalTimKerja">/ <?= (int) ($selectedPegawai['total_timkerja'] ?? 0) ?> Tim</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tree Mapping Table Area -->
        <div class="tree-table-card">
            <!-- Prompt State when no Pegawai is selected -->
            <div class="tree-select-prompt text-center py-5 <?= !empty($selectedNip) ? 'd-none' : '' ?>" id="treeSelectPrompt">
                <div class="d-flex flex-column align-items-center justify-content-center text-center my-4 tw-animate-entry">
                    <img src="<?= asset_url('apps/assets/images/empty-content-profile.png') ?>" alt="Pilih Pegawai" style="max-width: 280px; width: 100%; height: auto; margin-bottom: 1.5rem;">
                    <h5 class="fw-bold mb-1" style="color: #0f172a; font-size: 1.35rem;">Pilih Pegawai Terlebih Dahulu</h5>
                    <p class="small mb-3" style="max-width: 460px; margin: 0 auto; color: #475569; line-height: 1.6; font-size: 0.95rem;">
                        Silakan pilih pegawai melalui dropdown pencarian di atas atau klik tombol <strong>Daftar Pegawai</strong> untuk melihat dan mengelola hak akses modul layanannya.
                    </p>
                    <div class="mt-2">
                        <button type="button" class="btn btn-primary px-4 py-2 fw-bold" onclick="$('#btnOpenPegawaiModal').trigger('click')" style="border-radius: 8px; font-size: 0.92rem;">
                            <i class="bi bi-people me-1"></i> Buka Daftar Pegawai
                        </button>
                    </div>
                </div>
            </div>

            <div class="table-responsive <?= empty($selectedNip) ? 'd-none' : '' ?>" id="treeTableWrapper">
                <table class="table tree-table" id="treeTable">
                    <thead>
                        <tr>
                            <th>Tim Kerja / Layanan</th>
                            <th style="width: 140px;" class="text-center">Tipe</th>
                            <th style="width: 250px;">URL / Route</th>
                            <th style="width: 180px;" class="text-center">Akses Layanan</th>
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
                            <span class="skeleton-box" style="width: <?= 160 + ($i * 25) ?>px; height: 18px;"></span>
                        </div>
                        <span class="skeleton-box" style="width: 80px; height: 20px;"></span>
                        <span class="skeleton-box" style="width: 140px; height: 16px;"></span>
                        <span class="skeleton-box" style="width: 90px; height: 24px;"></span>
                    </div>
                <?php endfor; ?>
            </div>

            <!-- Empty Search State -->
            <div class="py-5 text-center d-none" id="treeEmptyState">
                <div class="d-flex flex-column align-items-center justify-content-center text-center my-4 tw-animate-entry">
                    <img src="<?= asset_url('apps/assets/images/empty-content-profile.png') ?>" alt="Pilih Pegawai" style="max-width: 280px; width: 100%; height: auto; margin-bottom: 1.5rem;">
                    <h5 class="fw-bold mb-1" style="color: #0f172a; font-size: 1.35rem;" id="treeEmptyTitle">Layanan Tidak Ditemukan</h5>
                    <p class="small mb-3" style="max-width: 450px; margin: 0 auto; color: #475569; line-height: 1.6; font-size: 0.95rem;" id="treeEmptyDesc">
                        Maaf, kami tidak dapat menemukan data layanan yang cocok dengan kata kunci pencarian Anda.
                    </p>
                    <div class="mt-2">
                        <button type="button" class="btn btn-outline-primary px-4 py-2 fw-semibold" id="btnResetSearchService" style="border-radius: 8px;">Reset Pencarian</button>
                    </div>
                </div>
            </div>
        </div>


    </div>
</main>

<!-- Modal Daftar Pegawai (Flat Minimalist) -->
<div class="modal fade flat-modal" id="modalPegawaiList" tabindex="-1" aria-labelledby="modalPegawaiListLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4 align-items-center">
                <div>
                    <h5 class="modal-title fw-bold mb-1" id="modalPegawaiListLabel" style="color: #0f172a; font-size: 1.25rem;">
                        Daftar Pegawai SIMOJANG
                    </h5>
                    <p class="small mb-0" style="color: #475569;">Pilih pegawai untuk melihat dan mengelola izin akses layanannya.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 pt-3 pb-4">
                <!-- Filter & Search within modal -->
                <div class="d-flex flex-column flex-sm-row gap-2 mb-3">
                    <div class="position-relative flex-grow-1">
                        <i class="bi bi-search position-absolute" style="left: 1rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: #64748b;"></i>
                        <input type="text" id="searchPegawaiModal" class="form-control" placeholder="Cari nama, NIP, atau unit kerja..." style="padding-left: 2.5rem; font-size: 0.9rem; height: 40px; color: #0f172a;">
                    </div>
                </div>

                <!-- Pegawai List Container -->
                <div id="pegawaiListContainer" style="max-height: 400px; overflow-y: auto;">
                    <!-- Populated via AJAX -->
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0 pb-4 px-4 justify-content-end">
                <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal" style="border: 1px solid #cbd5e1; border-radius: 8px; color: #0f172a;">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Salin Izin Antar Pegawai -->
<div class="modal fade flat-modal" id="modalCopyPermission" tabindex="-1" aria-labelledby="modalCopyPermissionLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4 align-items-center">
                <h5 class="modal-title fw-bold" id="modalCopyPermissionLabel" style="color: #0f172a; font-size: 1.25rem;">
                    Salin Izin Hak Akses Layanan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formCopyPermission" autocomplete="off">
                <div class="modal-body px-4 pt-3 pb-4">
                    <div class="alert alert-info py-2 px-3 small border-0 mb-3" style="background-color: #eff6ff; color: #1e40af; border-radius: 8px;">
                        <i class="bi bi-info-circle-fill me-1"></i>
                        Seluruh izin akses layanan dari <strong>Pegawai Sumber</strong> akan diduplikasi dan menggantikan izin pada <strong>Pegawai Tujuan</strong>.
                    </div>
                    <div class="row gy-3">
                        <div class="col-12">
                            <label for="selectSourcePegawai" class="form-label d-block fw-bold" style="font-size: 0.9rem; color: #0f172a; margin-bottom: 0.5rem;">
                                Salin Izin Dari Pegawai Sumber <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="selectSourcePegawai" name="source_nip" required style="color: #0f172a;">
                                <option value="">-- Cari & Pilih Pegawai Sumber --</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label d-block fw-bold" style="font-size: 0.9rem; color: #0f172a; margin-bottom: 0.5rem;">
                                Diterapkan Ke Pegawai Tujuan:
                            </label>
                            <div class="p-3 bg-light rounded-3 border">
                                <strong id="copyTargetName" class="d-block" style="color: #0f172a;"><?= esc($selectedPegawai['nama'] ?? '-') ?></strong>
                                <span class="small" style="color: #475569;">NIP: <span id="copyTargetNip" class="fw-bold" style="color: #0f172a;"><?= esc($selectedPegawai['nip'] ?? '-') ?></span></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 pb-4 px-4 justify-content-end">
                    <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal" style="border: 1px solid #cbd5e1; border-radius: 8px; color: #0f172a;">Batal</button>
                    <button type="button" class="btn btn-primary fw-bold px-4" id="btnConfirmCopy" style="border-radius: 8px;">Salin Izin Sekarang</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script src="<?= asset_url('apps/assets/js/custom/pages/service-manager.js?v=' . time()) ?>"></script>
<?= $this->endSection(); ?>
