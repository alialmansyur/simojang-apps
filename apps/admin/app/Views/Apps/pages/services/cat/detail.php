<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>
<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork-common.css') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork-service.css') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/service-table-ui.css?v=99') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/cat/main.css?v=' . time()) ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/cat/detail.css?v=' . time()) ?>">
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>
<main class="page-content py-4">
    <div class="container-fluid text-start mx-auto cat-detail-container">
        
        <!-- Page Heading (Sama seperti halaman /apps-cat-tilok/*) -->
        <div class="row align-items-center mt-2 mb-1 tw-animate-entry tw-anim-order-1">
            <div class="col-12 col-md-8">
                <h1 class="tw-title lh-1 cat-detail-header-title">
                    Rekapitulasi Data
                </h1>
                <div class="mb-0 d-flex flex-column gap-1">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="cat-detail-meta-label">Event :</span>
                        <span class="cat-detail-event-val" id="catDetailEvent"><?= esc($meta['nama_seleksi'] ?? '-') ?></span>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="cat-detail-sub-label">Titik Lokasi :</span>
                        <span class="cat-detail-tilok-val" id="catDetailTilok"><?= esc($meta['nama_tilok'] ?? '-') ?></span>
                        <span class="<?= empty($meta['period']) && empty($meta['period_start_date']) ? 'd-none' : '' ?>" id="catDetailPeriodeWrap">
                            <span class="cat-detail-dot">&bull;</span>
                            <span class="cat-detail-sub-label">Periode :</span>
                            <span class="cat-detail-sub-val" id="catDetailPeriodeText"><?= esc($meta['period'] ?? ((!empty($meta['period_start_date']) && !empty($meta['period_end_date'])) ? ($meta['period_start_date'] . ' s/d ' . $meta['period_end_date']) : '-')) ?></span>
                        </span>
                        <span class="<?= empty($meta['kapasitas']) ? 'd-none' : '' ?>" id="catDetailKapasitasWrap">
                            <span class="cat-detail-dot">&bull;</span>
                            <span class="cat-detail-sub-label">Kapasitas :</span>
                            <span class="cat-detail-sub-val" id="catDetailKapasitasText"><?= esc(!empty($meta['kapasitas']) ? ($meta['kapasitas'] . ' PC') : '-') ?></span>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 text-md-end mt-3 mt-md-0">
                <div class="service-page-inline-actions d-inline-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-primary js-service-reload d-inline-flex align-items-center justify-content-center px-3 cat-btn-action" id="btnReloadData">
                        Muat Ulang
                    </button>
                    <a href="<?= !empty($meta['seleksi_uid']) ? base_url('apps-cat-tilok/' . esc($meta['seleksi_uid'])) : base_url('apps-cat') ?>" class="btn btn-primary d-inline-flex align-items-center justify-content-center px-3 cat-btn-action" id="btnHeaderBack">
                        <i class="bi bi-chevron-left me-1 cat-btn-action-icon"></i> <span>Kembali</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Himbauan Alert & Panduan (Tetap Tampil di Semua Level) -->
        <div class="row mb-3 mt-2">
            <div class="col-12">
                <div class="alert shadow-sm border-0 rounded-3 p-3 position-relative cat-alert-himbauan mb-0" role="alert">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="row align-items-center g-0 flex-grow-1 pe-3">
                            <div class="col-auto pe-3">
                                <i class="bi bi-buildings-fill cat-alert-icon"></i>
                            </div>
                            <div class="col">
                                <h6 class="fw-bold mb-1 cat-alert-title">Panduan Rekapitulasi Data Instansi Peserta</h6>
                                <div class="cat-alert-desc">
                                    Pilih salah satu instansi untuk melihat dan mengelola rekap data sesi, atau klik <strong>"Tambah Instansi"</strong> jika instansi belum terdaftar.
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-sm text-nowrap fw-bold cat-alert-btn-panduan" type="button" data-bs-toggle="collapse" data-bs-target="#tataCaraCollapse" aria-expanded="false" aria-controls="tataCaraCollapse">
                            <i class="bi bi-info-circle me-1"></i> Panduan
                        </button>
                    </div>
                    
                    <div class="collapse" id="tataCaraCollapse">
                        <hr class="cat-alert-hr">
                        <ol class="mb-0 ps-3 cat-alert-list">
                            <li><strong>Pilih Instansi:</strong> Klik kartu instansi yang sudah ada untuk membuka detail rekap sesi instansi tersebut.</li>
                            <li><strong>Instansi Baru:</strong> Jika instansi peserta belum ada pada daftar, klik tombol biru <strong>"Tambah Instansi"</strong>.</li>
                            <li><strong>Pengisian Rekap:</strong> Lengkapi data sesi (tanggal, sesi, nilai terendah/tertinggi, kehadiran, kelulusan), lalu simpan rekap.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- LEVEL 1: VIEW DAFTAR INSTANSI (DEFAULT)                                   -->
        <!-- ========================================================================= -->
        <div id="viewLevelInstansi">
            <!-- Toolbar Level 1 -->
            <div class="tw-head d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3 mb-3 mt-1" role="toolbar">
                <div class="flex-grow-1 cat-toolbar-search-wrap">
                    <div class="position-relative tws-search-wrap">
                        <input type="search" id="searchInstansi" class="form-control pe-7 cat-search-input"
                            placeholder="Cari instansi...">
                        <button type="button" class="btn tws-search-indicator" disabled>
                            <i id="twsSearchIcon" class="bi bi-search fs-5 text-primary"></i>
                        </button>
                        <button type="button" id="clearSearchInstansi" class="btn tws-search-clear d-none" aria-label="Bersihkan pencarian">
                            <i class="bi bi-x-circle-fill fs-5"></i>
                        </button>
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <select id="sortInstansi" class="form-select fw-bold cat-sort-select">
                        <option value="updated_desc" selected>Terbaru Update</option>
                        <option value="name_asc">Nama Instansi A-Z</option>
                        <option value="sessions_desc">Sesi Terbanyak</option>
                        <option value="peserta_desc">Peserta Terbanyak</option>
                    </select>
                    
                    <button type="button" class="btn btn-primary d-inline-flex align-items-center justify-content-center px-4 cat-btn-tambah" id="btnOpenTambahInstansi">
                        <span class="fw-bold">Tambah Instansi</span> <i class="bi bi-plus-lg ms-2 d-flex align-items-center cat-btn-tambah-icon"></i>
                    </button>
                </div>
            </div>

            <!-- Container List Cards Instansi -->
            <div class="row tw-animate-entry tws-list-mode cat-list-row-gap-0 tw-anim-order-3" id="loaded">
                <div class="col-12 text-center text-muted py-5">
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Memuat daftar instansi...
                </div>
            </div>

            <!-- Pagination Instansi -->
            <div id="instansiPaginationWrap" class="mt-4 mb-4 d-flex justify-content-center tw-animate-entry"></div>
        </div>

        <!-- ========================================================================= -->
        <!-- LEVEL 2: VIEW DAFTAR EVENT (SELEKSI) UNTUK INSTANSI TERPILIH              -->
        <!-- ========================================================================= -->
        <div id="viewLevelEvent" class="d-none">
            <!-- Active Instansi Header Banner (Sebelum Pilih Event - Tanpa KPI Card) -->
            <div class="instansi-active-banner p-3 p-md-4 mb-3 shadow-sm" id="eventInstansiBanner">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="flex-shrink-0 d-flex align-items-center justify-content-center cat-active-instansi-logo-wrap" id="eventActiveLogoWrap">
                            <div class="d-flex align-items-center justify-content-center text-center bg-light border rounded-3 text-secondary fw-bold cat-no-logo-box">
                                No<br>Logo
                            </div>
                        </div>
                        <div class="min-w-0">
                            <h4 class="cat-active-instansi-title text-truncate mb-1" id="eventActiveInstansiNama">-</h4>
                            <div class="cat-active-instansi-desc" id="eventActiveInstansiMeta">
                                Silakan pilih event seleksi untuk melihat dan mengelola rekapitulasi data sesi instansi ini.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Toolbar Level 2 -->
            <div class="tw-head d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3 mt-1" role="toolbar">
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-secondary fw-semibold js-back-to-instansi cat-btn-back-level2">
                        <i class="bi bi-chevron-left me-1"></i> Kembali ke Daftar Instansi
                    </button>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <button type="button" class="btn btn-primary d-inline-flex align-items-center justify-content-center px-4 cat-btn-tambah" id="btnOpenTambahEvent">
                        <span class="fw-bold">Pilih / Tambah Event</span> <i class="bi bi-plus-lg ms-2 d-flex align-items-center cat-btn-tambah-icon"></i>
                    </button>
                </div>
            </div>

            <!-- Container List Cards Event -->
            <div class="row tw-animate-entry tws-list-mode cat-list-row-gap-0 tw-anim-order-3" id="loadedEvents">
                <div class="col-12 text-center text-muted py-5">
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Memuat daftar event...
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- LEVEL 3: VIEW DETAIL REKAP SESI INSTANSI & EVENT TERPILIH                 -->
        <!-- ========================================================================= -->
        <div id="viewLevelRekap" class="d-none">
            
            <!-- Breadcrumb Banner & Active Instansi Summary -->
            <div class="instansi-active-banner p-3 p-md-4 mb-3 shadow-sm">
                <div class="row align-items-center g-3">
                    <div class="col-12 col-xl-5">
                        <div class="d-flex align-items-center gap-3">
                            <div class="flex-shrink-0 d-flex align-items-center justify-content-center cat-active-instansi-logo-wrap" id="rekapActiveLogoWrap">
                                <div class="d-flex align-items-center justify-content-center text-center bg-light border rounded-3 text-secondary fw-bold cat-no-logo-box">
                                    No<br>Logo
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="cat-active-instansi-title text-truncate mb-1" id="rekapActiveInstansiNama">-</h4>
                                <div class="cat-active-instansi-desc" id="rekapActiveMeta">
                                    Rekap data kehadiran, sesi, dan skor tes CAT instansi.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-7">
                        <div class="row g-2 g-sm-3" id="rekapQuickStats">
                            <!-- Card 1: Total Sesi -->
                            <div class="col-6 col-sm-3">
                                <div class="cat-stat-card">
                                    <div class="cat-stat-icon-wrap cat-stat-icon-primary">
                                        <i class="bi bi-calendar-event"></i>
                                    </div>
                                    <div class="cat-stat-info">
                                        <span class="cat-stat-label">Total Sesi</span>
                                        <h5 class="cat-stat-value" id="statTotalSesi">0</h5>
                                    </div>
                                </div>
                            </div>
                            <!-- Card 2: Peserta Hadir -->
                            <div class="col-6 col-sm-3">
                                <div class="cat-stat-card">
                                    <div class="cat-stat-icon-wrap cat-stat-icon-success">
                                        <i class="bi bi-people-fill"></i>
                                    </div>
                                    <div class="cat-stat-info">
                                        <span class="cat-stat-label">Hadir</span>
                                        <h5 class="cat-stat-value text-success" id="statTotalHadir">0</h5>
                                    </div>
                                </div>
                            </div>
                            <!-- Card 3: Tidak Hadir -->
                            <div class="col-6 col-sm-3">
                                <div class="cat-stat-card">
                                    <div class="cat-stat-icon-wrap cat-stat-icon-danger">
                                        <i class="bi bi-person-x-fill"></i>
                                    </div>
                                    <div class="cat-stat-info">
                                        <span class="cat-stat-label">Tdk Hadir</span>
                                        <h5 class="cat-stat-value text-danger" id="statTotalTidakHadir">0</h5>
                                    </div>
                                </div>
                            </div>
                            <!-- Card 4: Rentang Skor -->
                            <div class="col-6 col-sm-3">
                                <div class="cat-stat-card">
                                    <div class="cat-stat-icon-wrap cat-stat-icon-warning">
                                        <i class="bi bi-award-fill"></i>
                                    </div>
                                    <div class="cat-stat-info">
                                        <span class="cat-stat-label">Skor CAT</span>
                                        <h5 class="cat-stat-value cat-stat-skor" id="statRentangNilai">-</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Toolbar Level 2 -->
            <div class="tw-head d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3" role="toolbar">
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-secondary fw-semibold js-back-to-event cat-btn-back-level2">
                        <i class="bi bi-chevron-left me-1"></i> Kembali ke Daftar Event
                    </button>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <!-- Filter Tanggal Sesi (Tinggi setara tombol) -->
                    <div class="cat-filter-date-wrapper">
                        <input type="date" id="filterTanggalSesi" class="form-control cat-filter-date-input" title="Filter Berdasarkan Tanggal">
                    </div>

                    <!-- Filter Bulan -->
                    <div class="dropdown">
                        <button class="btn btn-outline-primary dropdown-toggle fw-bold px-3 cat-btn-bulan" type="button" id="dropdownBulan" data-bs-toggle="dropdown" aria-expanded="false">
                            Pilih Bulan
                        </button>
                        <ul class="dropdown-menu shadow rounded-3 border-0 p-3 mt-2 service-ui-period-dropdown" id="bulanDropdown">
                            <div id="bulanList"></div>
                            <li><hr class="dropdown-divider my-2"></li>
                            <li>
                                <button class="btn btn-primary w-100 fw-semibold" id="applyBulan">
                                    <i class="bi bi-check-circle me-1"></i> Terapkan
                                </button>
                            </li>
                        </ul>
                    </div>

                    <!-- Reset Filter Button -->
                    <button type="button" class="btn btn-outline-secondary d-none" id="btnResetFilterSesi" title="Reset Filter">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </button>

                    <button type="button" class="btn btn-primary d-inline-flex align-items-center justify-content-center px-4 cat-btn-tambah" id="btnOpenTambahSesi">
                        <span class="fw-bold">Tambah Sesi</span> <i class="bi bi-plus-lg ms-2 d-flex align-items-center cat-btn-tambah-icon"></i>
                    </button>
                </div>
            </div>

            <!-- DataTable Container -->
            <div class="row" id="datatableContainer">
                <div class="col-md-12">
                    <div class="card border shadow-sm cat-card-table">
                        <div class="card-body p-3">
                            <div class="table-responsive">
                                <table id="dataTable" class="table table-bordered table-hover nowrap align-middle">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width: 35px;"><strong>#</strong></th>
                                            <th><strong>Tanggal</strong></th>
                                            <th class="text-center"><strong>Sesi</strong></th>
                                            <th class="text-center"><strong>Nilai Min</strong></th>
                                            <th class="text-center"><strong>Nilai Max</strong></th>
                                            <th class="text-center"><strong>Hadir</strong></th>
                                            <th class="text-center"><strong>Tidak Hadir</strong></th>
                                            <th class="text-center"><strong>Reschedule</strong></th>
                                            <th class="text-center"><strong>Total Peserta</strong></th>
                                            <th class="text-center"><strong>Memenuhi</strong></th>
                                            <th class="text-center"><strong>Tidak Memenuhi</strong></th>
                                            <th><strong>Petugas</strong></th>
                                            <th><strong>Waktu Rekap</strong></th>
                                            <th class="text-center" style="width: 80px;"><strong>Aksi</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- MODAL TAMBAH INSTANSI BARU                                                -->
    <!-- ========================================================================= -->
    <div class="modal fade" id="ModalTambahInstansi" tabindex="-1" aria-labelledby="ModalTambahInstansiLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ModalTambahInstansiLabel">Pilih Instansi Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold mb-1" for="selectNewInstansi">Instansi:</label>
                        <select id="selectNewInstansi" name="instansi_baru" class="form-select select-instansi cat-select-instansi-wrap"></select>
                        <small class="text-muted">Ketik nama instansi dari data master instansi BKN.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batalkan</button>
                    <button type="button" class="btn btn-primary" id="btnSubmitTambahInstansi">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL PILIH EVENT                                                         -->
    <!-- ========================================================================= -->
    <div class="modal fade" id="ModalTambahEvent" tabindex="-1" aria-labelledby="ModalTambahEventLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ModalTambahEventLabel">Pilih Event Seleksi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold mb-1" for="selectNewEvent">Event:</label>
                        <select id="selectNewEvent" class="form-select select-event"></select>
                        <small class="text-muted">Cari dan pilih event seleksi yang sudah tersedia.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batalkan</button>
                    <button type="button" class="btn btn-primary" id="btnSubmitTambahEvent">Pilih & Lanjutkan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 1: TAMBAH DATA REKAP (TAMBAH SESI MULTI-ROW)                        -->
    <!-- ========================================================================= -->
    <div class="modal fade modal-smooth" id="DataModal" tabindex="-1" aria-labelledby="DataModalLabelCreate" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="DataModalLabelCreate">Entri Rekap Fasilitasi CAT</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    
                    <!-- Header Informasi Event, Titik Lokasi & Instansi -->
                    <div class="card border mb-3 shadow-none rounded-3" style="background-color: #f8fafc; border-color: #e2e8f0 !important; border-left: 4px solid var(--bs-primary) !important;">
                        <div class="card-body py-2 px-3">
                            <div class="d-flex flex-column gap-1">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="fw-semibold text-secondary" style="font-size: 0.95rem; color: #475569 !important;">Nama Event :</span>
                                    <span class="fw-bold text-dark" id="modalInfoEvent" style="font-size: 1rem; color: #0f172a !important;"><?= esc($meta['nama_seleksi'] ?? '-') ?></span>
                                </div>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="fw-semibold text-secondary" style="font-size: 0.95rem; color: #475569 !important;">Nama Titik Lokasi :</span>
                                    <span class="fw-bold text-dark" id="modalInfoTilok" style="font-size: 1rem; color: #0f172a !important;"><?= esc($meta['nama_tilok'] ?? '-') ?></span>
                                </div>
                                <div class="d-flex align-items-center gap-2 flex-wrap d-none" id="activeInstansiAlert">
                                    <span class="fw-semibold text-secondary" style="font-size: 0.95rem; color: #475569 !important;">Instansi Aktif :</span>
                                    <span class="fw-bold text-dark" id="activeInstansiLabel" style="font-size: 1rem; color: #0f172a !important;">-</span>
                                </div>
                            </div>
                        </div>
                    </div>



                    <form method="POST" id="form-usulan">
                        <input type="hidden" name="key" id="detailKeyFormCreate" value="">
                        
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <button type="button" class="btn btn-primary" id="addRowBtn">
                                <i class="bi bi-plus-lg me-1"></i> Tambah Baris Sesi
                            </button>
                            <span class="text-muted small">Semua kolom sesi dan tanggal wajib diisi.</span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle" id="usulanTable">
                                <thead class="table-light">
                                    <tr>
                                        <th class="col-w-160">Tanggal</th>
                                        <th class="col-w-90">Sesi</th>
                                        <th class="col-w-110">Nilai Min</th>
                                        <th class="col-w-110">Nilai Max</th>
                                        <th class="col-w-90">Hadir</th>
                                        <th class="col-w-90">Tdk Hadir</th>
                                        <th class="col-w-90">Reschedule</th>
                                        <th class="col-w-90 text-center">Total</th>
                                        <th class="col-w-100">Memenuhi</th>
                                        <th class="col-w-100">Tdk Memenuhi</th>
                                        <th class="col-w-80 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="usulanTableBody"></tbody>
                            </table>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x d-block d-sm-none"></i>
                        <span class="d-none d-sm-block">Batalkan</span>
                    </button>
                    <button type="button" class="btn btn-primary ms-1 btn-submit-form">
                        <i class="bx bx-check d-block d-sm-none"></i>
                        <span class="d-none d-sm-block">Simpan Rekap</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 4: TAMBAH INSTANSI                                                  -->
    <!-- ========================================================================= -->
    <div class="modal fade modal-smooth" id="InstansiModal" tabindex="-1" aria-labelledby="InstansiModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="InstansiModalLabel">Tambah Instansi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="form-instansi-select">
                        <div class="mb-3">
                            <label class="form-label fw-bold mb-1">Cari & Pilih Instansi:</label>
                            <select id="selectOnlyInstansi" name="instansi_id" class="form-select select-instansi" style="width: 100%"></select>
                            <small class="text-muted">Ketik nama instansi dari data master BKN.</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnSubmitInstansi">Simpan Instansi</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 3: PILIH EVENT UNTUK INSTANSI                                       -->
    <!-- ========================================================================= -->
    <div class="modal fade modal-smooth" id="EventModal" tabindex="-1" aria-labelledby="EventModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="EventModalLabel">Pilih Event (Seleksi)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="form-event-select">
                        <div class="mb-3">
                            <label class="form-label fw-bold mb-1">Cari & Pilih Event:</label>
                            <select id="selectEvent" name="seleksi_id" class="form-select select-event" style="width: 100%"></select>
                            <small class="text-muted">Pilih event yang akan ditambahkan rekap sesinya.</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnSubmitEvent">Lanjutkan ke Sesi</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 2: EDIT SINGLE ROW REKAP DATA SESI                                  -->
    <!-- ========================================================================= -->
    <div class="modal fade modal-force-rounded" id="DataModalDetail" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content cat-modal-detail-content" style="border-radius: 18px !important; overflow: hidden !important;">
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <h1 class="modal-title fs-5 fw-bold text-primary" id="DataModalLabel">Update Data Rekap Sesi</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 py-3">
                    <form id="form-usulan-edit" autocomplete="off">
                        <input type="hidden" name="key">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label mb-1 fw-semibold">Instansi</label>
                                <select name="instansi" class="form-select select-instansi" required></select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label mb-1 fw-semibold">Tanggal</label>
                                <input type="date" class="form-control" name="tanggal" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label mb-1 fw-semibold">Sesi</label>
                                <input type="number" class="form-control" name="sesi" placeholder="1" min="1" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label mb-1 fw-semibold">Nilai Terendah</label>
                                <input type="number" class="form-control" name="nilai_min" placeholder="0" min="0">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label mb-1 fw-semibold">Nilai Tertinggi</label>
                                <input type="number" class="form-control" name="nilai_max" placeholder="0" min="0">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label mb-1 fw-semibold">Hadir</label>
                                <input type="number" class="form-control" name="hadir" placeholder="0" min="0">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label mb-1 fw-semibold">Tidak Hadir</label>
                                <input type="number" class="form-control" name="tidak_hadir" placeholder="0" min="0">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label mb-1 fw-semibold">Penjadwalan Ulang (Reschedule)</label>
                                <input type="number" class="form-control" name="reschedule" placeholder="0" min="0">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label mb-1 fw-semibold">Memenuhi (Lulus)</label>
                                <input type="number" class="form-control" name="memenuhi" placeholder="0" min="0">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label mb-1 fw-semibold">Tidak Memenuhi</label>
                                <input type="number" class="form-control" name="tidak_memenuhi" placeholder="0" min="0">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-2">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 sbmt-edit">Simpan Perubahan</button>
                </div>
            </div>
        </div>
    </div>

</main>
<?= $this->endSection(); ?>
<?= $this->section('scripts'); ?>
<script>
    var SELEKSI_UID = "<?= esc($meta['seleksi_uid'] ?? '') ?>";
    var TILOK_UID = "<?= esc($meta['uid'] ?? '') ?>";
</script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/service-table-ui.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/cat/tablesRekap.js?v=' . time()) ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/cat/entryRekap.js?v=' . time()) ?>"></script>
<?= $this->endSection(); ?>

