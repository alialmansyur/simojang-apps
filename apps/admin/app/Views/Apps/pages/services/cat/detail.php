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
                        <span class="cat-detail-event-val" id="catDetailEvent">-</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="cat-detail-sub-label">Titik Lokasi :</span>
                        <span class="cat-detail-tilok-val" id="catDetailTilok">-</span>
                        <span class="d-none" id="catDetailPeriodeWrap">
                            <span class="cat-detail-dot">&bull;</span>
                            <span class="cat-detail-sub-label">Periode :</span>
                            <span class="cat-detail-sub-val" id="catDetailPeriodeText">-</span>
                        </span>
                        <span class="d-none" id="catDetailKapasitasWrap">
                            <span class="cat-detail-dot">&bull;</span>
                            <span class="cat-detail-sub-label">Kapasitas :</span>
                            <span class="cat-detail-sub-val" id="catDetailKapasitasText">-</span>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 text-md-end mt-3 mt-md-0">
                <div class="service-page-inline-actions d-inline-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-primary js-service-reload d-inline-flex align-items-center justify-content-center px-3 cat-btn-action" id="btnReloadData">
                        Muat Ulang
                    </button>
                    <a href="javascript:void(0)" class="btn btn-primary d-inline-flex align-items-center justify-content-center px-3 cat-btn-action" id="btnHeaderBack">
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
                        <option value="name_asc">Nama Instansi A-Z</option>
                        <option value="updated_desc">Terbaru Update</option>
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
        <!-- LEVEL 2: VIEW DETAIL REKAP SESI INSTANSI TERPILIH                         -->
        <!-- ========================================================================= -->
        <div id="viewLevelRekap" class="d-none">
            
            <!-- Breadcrumb Banner & Active Instansi Summary -->
            <div class="instansi-active-banner p-3 p-md-4 mb-3 shadow-sm">
                <div class="row align-items-center g-3">
                    <div class="col-12 col-xl-5">
                        <div class="d-flex align-items-center gap-3">
                            <div class="flex-shrink-0 d-flex align-items-center justify-content-center cat-active-instansi-logo-wrap" id="rekapActiveLogoWrap">
                                <i class="bi bi-buildings fs-2 text-primary"></i>
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
                    <button type="button" class="btn btn-outline-secondary fw-semibold js-back-to-instansi cat-btn-back-level2">
                        <i class="bi bi-chevron-left me-1"></i> Kembali ke Daftar Instansi
                    </button>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
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
                                            <th></th>
                                            <th><strong>Instansi Name</strong></th>
                                            <th><strong>Tanggal</strong></th>
                                            <th><strong>Sesi</strong></th>
                                            <th><strong>Nilai Terendah</strong></th>
                                            <th><strong>Nilai Tertinggi</strong></th>
                                            <th><strong>Hadir</strong></th>
                                            <th><strong>Tidak Hadir</strong></th>
                                            <th><strong>Penjadwalan Ulang</strong></th>
                                            <th><strong>Total Peserta</strong></th>
                                            <th><strong>Created By</strong></th>
                                            <th><strong>Created At</strong></th>
                                            <th><strong>Aksi</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th></th>
                                            <th><strong>Instansi Name</strong></th>
                                            <th><strong>Tanggal</strong></th>
                                            <th><strong>Sesi</strong></th>
                                            <th><strong>Nilai Terendah</strong></th>
                                            <th><strong>Nilai Tertinggi</strong></th>
                                            <th><strong>Hadir</strong></th>
                                            <th><strong>Tidak Hadir</strong></th>
                                            <th><strong>Penjadwalan Ulang</strong></th>
                                            <th><strong>Total Peserta</strong></th>
                                            <th><strong>Created By</strong></th>
                                            <th><strong>Created At</strong></th>
                                            <th><strong>Aksi</strong></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 1: TAMBAH DATA REKAP (INSTANSI BARU / TAMBAH SESI MULTI-ROW)        -->
    <!-- ========================================================================= -->
    <div class="modal fade modal-smooth" id="DataModal" tabindex="-1" aria-labelledby="DataModalLabelCreate" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="DataModalLabelCreate">Entri Rekap Fasilitasi CAT</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    
                    <!-- Section Pilihan Instansi (Hanya muncul jika mode Tambah Instansi Baru) -->
                    <div class="card border mb-3 shadow-none bg-light" id="instansiSelectorWrap">
                        <div class="card-body p-3 position-relative">
                            <label class="form-label fw-bold mb-1" for="selectNewInstansi">Pilih Instansi:</label>
                            <select id="selectNewInstansi" name="instansi_baru" class="form-select select-instansi cat-select-instansi-wrap"></select>
                            <small class="text-muted">Ketik nama instansi dari data master instansi BKN.</small>
                        </div>
                    </div>

                    <!-- Alert Instansi Aktif (Muncul saat mode Tambah Sesi pada Instansi Terpilih) -->
                    <div class="alert alert-info py-2 mb-3 d-none" id="activeInstansiAlert">
                        <i class="bi bi-building me-1"></i> <strong>Instansi Aktif:</strong> <span id="activeInstansiLabel">-</span>
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
                                        <th class="col-w-120">Nilai Min</th>
                                        <th class="col-w-120">Nilai Max</th>
                                        <th class="col-w-100">Hadir</th>
                                        <th class="col-w-100">Tidak Hadir</th>
                                        <th class="col-w-110">Reschedule</th>
                                        <th class="col-w-120">Memenuhi (Lulus)</th>
                                        <th class="col-w-120">Tidak Memenuhi</th>
                                        <th class="col-w-60 text-center">Aksi</th>
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
    <!-- MODAL 2: EDIT SINGLE ROW REKAP DATA SESI                                  -->
    <!-- ========================================================================= -->
    <div class="modal fade" id="DataModalDetail" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content cat-modal-rounded">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="DataModalLabel">Update Data Rekap Sesi</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
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
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary sbmt-edit">Simpan Perubahan</button>
                </div>
            </div>
        </div>
    </div>

</main>
<?= $this->endSection(); ?>
<?= $this->section('scripts'); ?>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/service-table-ui.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/cat/entryRekap.js?v=' . time()) ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/cat/tablesRekap.js?v=' . time()) ?>"></script>
<?= $this->endSection(); ?>

