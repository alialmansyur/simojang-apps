<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>

<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork-common.css') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork-service.css') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/cat/main.css') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/pnbp/pnbp-main.css?v=' . time()) ?>">
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>
<main class="page-content" aria-labelledby="pnbpHeading">
    <div class="text-start tws-wrap container-fluid">
        
        <!-- Page Header -->
        <div class="row align-items-center mt-3 mb-2 tw-animate-entry" style="--animation-order: 1;">
            <div class="col-12 col-md-8">
                <h1 class="tw-title lh-1" id="pnbpHeading" style="color: #1a202c; font-size: 2.2rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.5rem;">
                    <?= esc($currentDocDetail['title'] ?? 'Daftar Dokumen') ?>
                </h1>
                <p class="tw-subtitle text-secondary mb-0" style="font-size: 1.05rem; font-weight: 500;">
                    <?= esc($currentDocDetail['desc'] ?? 'Kelola dan generate berkas pertanggungjawaban kegiatan.') ?>
                </p>
            </div>
            <div class="col-12 col-md-4 text-md-end mt-2 mt-md-0 d-flex align-items-center justify-content-md-end gap-2">
                <a href="<?= base_url('apps-pnbp') ?>" class="btn btn-primary px-3">
                    <i class="bi bi-chevron-left fs-6 me-1"></i> Kembali
                </a>
            </div>
        </div>

        <!-- Himbauan Alert Banner (Ala /apps-cat-tilok/*) -->
        <div class="row mb-3 mt-2">
            <div class="col-12">
                <div class="alert shadow-sm border-0 rounded-3 p-3 position-relative mb-0" style="background-color: #fffbe4; border-left: 6px solid #f59e0b !important;" role="alert">
                    <div class="row align-items-center g-0 pe-5">
                        <div class="col-auto pe-3">
                            <i class="bi bi-exclamation-triangle-fill" style="color: #d97706; font-size: 2.2rem; line-height: 1;"></i>
                        </div>
                        <div class="col">
                            <h6 class="fw-bold mb-1" style="color: #92400e; font-size: 1rem; line-height: 1.2;">
                                Panduan Pengelolaan <?= esc($currentDocDetail['title'] ?? 'Dokumen') ?>
                            </h6>
                            <div style="font-size: 0.85rem; color: #b45309; line-height: 1.2;">
                                Klik tombol <strong>"Tambah +"</strong> untuk membuat berkas baru dengan memilih Event CAT, Instansi, dan Titik Lokasi terkait.
                            </div>
                        </div>
                    </div>
                    
                    <div class="position-absolute top-0 end-0 p-3">
                        <button class="btn btn-sm text-nowrap fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#tataCaraCollapse" aria-expanded="false" aria-controls="tataCaraCollapse" style="background-color: #fef3c7; color: #92400e; border: 1px solid #fcd34d;">
                            <i class="bi bi-info-circle me-1"></i> Alur Kerja
                        </button>
                    </div>
                    
                    <div class="collapse" id="tataCaraCollapse">
                        <hr style="border-color: #f59e0b; opacity: 0.2; margin-top: 1rem; margin-bottom: 1rem;">
                        <ol class="mb-0 ps-3" style="font-size: 0.85rem; line-height: 1.7; color: #78350f;">
                            <li><strong>Tambah Data:</strong> Klik tombol biru di kanan atas untuk mengisi formulir pembuatan dokumen baru.</li>
                            <li><strong>Kelola Detail:</strong> Klik kartu dokumen yang muncul pada daftar untuk mengisi daftar personel/jamuan dan tanda tangan digital.</li>
                            <li><strong>Generate & Unduh:</strong> Lakukan generate PDF untuk melihat pratinjau dan mengunduh berkas final.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toolbar & Filter Bar (1 Baris Utuh) -->
        <div class="tw-head d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-2 mb-4 mt-4 tw-animate-entry" style="--animation-order: 2;" role="toolbar">
            <!-- Search Keyword -->
            <div class="flex-grow-1" style="max-width: 260px; min-width: 180px;">
                <div class="position-relative tws-search-wrap">
                    <input type="search" id="searchdata" class="form-control pe-7" style="height: 42px;"
                        placeholder="Cari nomor, judul, event, tilok...">
                    <button type="button" class="btn tws-search-indicator" disabled>
                        <i id="twsSearchIcon" class="bi bi-search fs-5 text-primary"></i>
                    </button>
                    <button type="button" id="twsClearSearch" class="btn tws-search-clear d-none" aria-label="Bersihkan pencarian">
                        <i class="bi bi-x-circle-fill fs-5"></i>
                    </button>
                </div>
            </div>

            <!-- Filter Controls (1 Baris Utuh) -->
            <div class="d-flex align-items-center gap-2 flex-nowrap">
                <!-- Status Filter Chips -->
                <div class="d-flex align-items-center gap-1 flex-nowrap" id="twsQuickFilters">
                    <button type="button" class="btn btn-outline-primary tws-filter-chip is-active px-3 text-nowrap" style="height: 42px; border-radius: 8px;" data-filter="all">Semua</button>
                    <button type="button" class="btn btn-outline-primary tws-filter-chip px-3 text-nowrap" style="height: 42px; border-radius: 8px;" data-filter="draft">Draft</button>
                    <button type="button" class="btn btn-outline-primary tws-filter-chip px-3 text-nowrap" style="height: 42px; border-radius: 8px;" data-filter="generated">Generated</button>
                </div>

                <!-- Filter Seleksi -->
                <select id="filterSeleksi" class="form-select fw-bold text-truncate" style="width: 185px !important; height: 42px; color: #1a202c !important; border: 1px solid #cbd5e1; border-radius: 8px;">
                    <option value="">Semua Event Seleksi</option>
                    <?php if (!empty($seleksiOptions)): ?>
                        <?php foreach ($seleksiOptions as $sel): ?>
                            <option value="<?= esc($sel['id']) ?>" class="text-truncate"><?= esc($sel['nama_seleksi']) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                
                <!-- Sort Dropdown -->
                <select id="twsSort" class="form-select fw-bold text-nowrap" style="width: 160px !important; height: 42px; color: #1a202c !important; border: 1px solid #cbd5e1; border-radius: 8px;">
                    <option value="updated_desc">Terbaru Diupdate</option>
                    <option value="date_desc">Tanggal Terbaru</option>
                    <option value="title_asc">Judul A-Z</option>
                </select>
                
                <!-- Tombol Tambah Dokumen -->
                <button type="button" class="btn btn-primary d-inline-flex align-items-center justify-content-center px-3 text-nowrap" data-bs-toggle="modal" data-bs-target="#pnbpDocModal" id="btnOpenCreateModal" style="height: 42px; border-radius: 8px;">
                    <span class="fw-bold" style="font-size: 0.95rem;">Tambah</span> <i class="bi bi-plus-lg ms-1 d-flex align-items-center" style="font-size: 1.1rem;"></i>
                </button>
            </div>
        </div>

        <!-- Container Card List Dokumen -->
        <div class="row tw-animate-entry tws-list-mode" id="loaded" style="--animation-order: 3; row-gap: 0;">
            <div class="col-12 text-center text-muted py-5">
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Memuat daftar <?= esc($currentDocDetail['title'] ?? 'dokumen') ?>...
            </div>
        </div>

        <!-- Pagination -->
        <div id="pnbpPaginationWrap" class="mt-4 mb-5 d-flex justify-content-center tw-animate-entry" style="--animation-order: 4;"></div>
    </div>

    <!-- Modals -->
    <?= $this->include('Apps/pages/services/pnbp/modal_form'); ?>
    <?= $this->include('Apps/pages/services/pnbp/modal_preview'); ?>
</main>
<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script>
    var CURRENT_DOC_TYPE = "<?= esc($currentDocType) ?>";
</script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/service-table-ui.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/pnbp/pnbp-generator.js?v=' . time()) ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/pnbp/pnbp-main.js?v=' . time()) ?>"></script>
<?= $this->endSection(); ?>
