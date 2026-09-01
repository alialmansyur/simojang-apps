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
                    Dokumen PNBP CAT
                </h1>
                <p class="tw-subtitle text-secondary mb-0" style="font-size: 1.05rem; font-weight: 500;">
                    Pusat Pembuatan & Pengelolaan Dokumen Pertanggungjawaban Pelaksanaan CAT (SP, ST, Nominatif, Kwitansi, Jamuan).
                </p>
            </div>
            <div class="col-12 col-md-4 text-md-end mt-2 mt-md-0">
                <a href="<?= base_url('apps-cat') ?>" class="btn btn-primary px-3">
                    <i class="bi bi-chevron-left fs-6 me-1"></i> Kembali ke CAT
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
                                Panduan Pembuatan Dokumen PNBP CAT
                            </h6>
                            <div style="font-size: 0.85rem; color: #b45309; line-height: 1.2;">
                                Data Event, Titik Lokasi, dan Instansi dapat dipilih otomatis dari database CAT. Tanda tangan pejabat dapat dibubuhkan secara digital via scan QR code.
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
                            <li><strong>Buat Dokumen:</strong> Klik tombol biru <strong>"Buat Dokumen Baru"</strong>, lalu pilih Jenis Dokumen, Event Seleksi CAT, dan Titik Lokasi.</li>
                            <li><strong>Isi Data Personel / Jamuan:</strong> Klik kartu dokumen untuk membuka halaman Editor Detail. Tambahkan daftar tim pengawas atau rincian menu katering.</li>
                            <li><strong>Tanda Tangan Digital:</strong> Scan QR code pada halaman detail / dokumen menggunakan HP untuk menandatangani secara digital.</li>
                            <li><strong>Generate & Unduh:</strong> Klik tombol <strong>"Generate PDF"</strong> untuk melihat pratinjau PDF dan mengunduh berkas final.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toolbar & Filter Bar -->
        <div class="tw-head d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3 mb-4 mt-4 tw-animate-entry" style="--animation-order: 2;" role="toolbar">
            <!-- Search Keyword -->
            <div class="flex-grow-1" style="max-width: 400px;">
                <div class="position-relative tws-search-wrap">
                    <input type="search" id="searchdata" class="form-control pe-7" style="height: 42px; border-radius: 8px;"
                        placeholder="Cari dokumen, nomor, event, tilok...">
                    <button type="button" class="btn tws-search-indicator" disabled>
                        <i id="twsSearchIcon" class="bi bi-search fs-5 text-primary"></i>
                    </button>
                    <button type="button" id="twsClearSearch" class="btn tws-search-clear d-none" aria-label="Bersihkan pencarian">
                        <i class="bi bi-x-circle-fill fs-5"></i>
                    </button>
                </div>
            </div>

            <!-- Filter Controls -->
            <div class="d-flex flex-wrap align-items-center gap-2">
                <!-- Status Filter Chips -->
                <div class="d-flex flex-wrap align-items-center gap-2" id="twsQuickFilters">
                    <button type="button" class="btn btn-outline-primary tws-filter-chip is-active" style="height: 42px; border-radius: 8px;" data-filter="all">Semua</button>
                    <button type="button" class="btn btn-outline-primary tws-filter-chip" style="height: 42px; border-radius: 8px;" data-filter="draft">Draft</button>
                    <button type="button" class="btn btn-outline-primary tws-filter-chip" style="height: 42px; border-radius: 8px;" data-filter="generated">Generated</button>
                </div>

                <!-- Filter Jenis Dokumen -->
                <select id="filterDocType" class="form-select fw-bold" style="width: auto !important; height: 42px; color: #1a202c !important; border: 1px solid #cbd5e1; border-radius: 8px;">
                    <option value="">Semua Format Dokumen</option>
                    <optgroup label="Personel">
                        <option value="sp">Surat Perintah (SP)</option>
                        <option value="st">Surat Tugas (ST)</option>
                        <option value="nominatif">Daftar Nominatif</option>
                        <option value="kwitansi">Kwitansi Perjadin</option>
                        <option value="hadir">Daftar Hadir</option>
                    </optgroup>
                    <optgroup label="Jamuan">
                        <option value="kwitansi_jamuan">Kwitansi Jamuan</option>
                        <option value="surat_jalan">Surat Jalan Jamuan</option>
                        <option value="faktur">Faktur Jamuan</option>
                        <option value="hadir_jamuan">Daftar Hadir Jamuan</option>
                    </optgroup>
                </select>

                <!-- Filter Seleksi -->
                <select id="filterSeleksi" class="form-select fw-bold" style="width: auto !important; height: 42px; color: #1a202c !important; border: 1px solid #cbd5e1; border-radius: 8px;">
                    <option value="">Semua Event Seleksi</option>
                    <?php if (!empty($seleksiOptions)): ?>
                        <?php foreach ($seleksiOptions as $sel): ?>
                            <option value="<?= esc($sel['id']) ?>"><?= esc($sel['nama_seleksi']) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                
                <!-- Sort Dropdown -->
                <select id="twsSort" class="form-select fw-bold" style="width: auto !important; height: 42px; color: #1a202c !important; border: 1px solid #cbd5e1; border-radius: 8px;">
                    <option value="updated_desc">Terbaru Diupdate</option>
                    <option value="date_desc">Tanggal Dokumen Terbaru</option>
                    <option value="title_asc">Judul A-Z</option>
                </select>
                
                <!-- Tombol Tambah Dokumen -->
                <button type="button" class="btn btn-primary d-inline-flex align-items-center justify-content-center px-4" data-bs-toggle="modal" data-bs-target="#pnbpDocModal" id="btnOpenCreateModal" style="height: 42px; border-radius: 8px;">
                    <span class="fw-bold" style="font-size: 0.95rem;">Buat Dokumen</span> <i class="bi bi-plus-lg ms-2 d-flex align-items-center" style="font-size: 1.1rem;"></i>
                </button>
            </div>
        </div>

        <!-- Container Card List Dokumen -->
        <div class="row tw-animate-entry tws-list-mode" id="loaded" style="--animation-order: 3; row-gap: 0;">
            <div class="col-12 text-center text-muted py-5">
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Memuat data dokumen PNBP...
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
<script src="<?= asset_url('apps/assets/js/custom/pages/services/service-table-ui.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/pnbp/pnbp-generator.js?v=' . time()) ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/pnbp/pnbp-main.js?v=' . time()) ?>"></script>
<?= $this->endSection(); ?>
