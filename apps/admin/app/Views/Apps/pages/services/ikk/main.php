<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>

<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork-common.css') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork.css?v=99') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/service-table-ui.css?v=99') ?>">
<style>
    /* =========================================================================
       1. OVERRIDE WARNA FONT AGAR HITAM PEKAT / GELAP TERBACA JELAS (TIDAK PUTIH)
       BAIK DI MODE THEME-LIGHT MAUPUN THEME-DARK (MAZER APP-DARK.CSS)
       ========================================================================= */

    /* Headings, Titles, Subtitles */
    body.theme-dark .cat-page-title,
    body.theme-dark h1.tw-title,
    body.theme-dark .tw-title,
    body.theme-dark .page-content h1,
    body.theme-dark .page-content h2,
    body.theme-dark .page-content h3,
    body.theme-dark .page-content h4,
    body.theme-dark .page-content h5,
    body.theme-dark .page-content h6,
    body.theme-dark .ikk-heading-dark,
    body .cat-page-title,
    body h1.tw-title,
    body .tw-title,
    body .page-content h1,
    body .page-content h2,
    body .page-content h3,
    body .page-content h4,
    body .page-content h5,
    body .page-content h6,
    .cat-page-title,
    h1.tw-title,
    .tw-title,
    .page-content h1,
    .page-content h2,
    .page-content h3,
    .page-content h4,
    .page-content h5,
    .page-content h6,
    .ikk-heading-dark {
        color: #111827 !important;
    }

    body.theme-dark .tw-subtitle,
    body.theme-dark .cat-page-subtitle,
    body.theme-dark .page-content .text-secondary,
    body.theme-dark .page-content .ikk-text-sub,
    body .tw-subtitle,
    body .cat-page-subtitle,
    body .page-content .text-secondary,
    body .page-content .ikk-text-sub,
    .tw-subtitle,
    .cat-page-subtitle,
    .ikk-text-sub {
        color: #374151 !important;
    }

    /* Override .text-dark dan .ikk-text-dark di page-content dan modal */
    body.theme-dark .page-content .text-dark,
    body.theme-dark .modal .text-dark,
    body.theme-dark .text-dark,
    body.theme-dark .ikk-text-dark,
    body .page-content .text-dark,
    body .modal .text-dark,
    .page-content .text-dark,
    .modal .text-dark,
    .ikk-text-dark {
        color: #111827 !important;
    }

    /* Form labels & titles di modal dan halaman */
    body.theme-dark .form-label,
    body.theme-dark .page-content label,
    body.theme-dark .modal label,
    body.theme-dark .modal-title,
    body.theme-dark .modal-body,
    body .form-label,
    body .page-content label,
    body .modal label,
    body .modal-title,
    body .modal-body,
    .form-label,
    .modal-title,
    .modal-body {
        color: #111827 !important;
    }

    /* Override teks tabel DataTables (Header, Baris, Seluruh Anak Elemen) */
    body.theme-dark table.dataTable,
    body.theme-dark table.dataTable th,
    body.theme-dark table.dataTable td,
    body.theme-dark table.dataTable thead th,
    body.theme-dark table.dataTable tbody td,
    body.theme-dark table.dataTable tbody tr td,
    body.theme-dark table.dataTable tbody tr td *,
    body.theme-dark .table,
    body.theme-dark .table th,
    body.theme-dark .table td,
    body.theme-dark .table thead th,
    body.theme-dark .table tbody td,
    body.theme-dark .table tbody tr td,
    body.theme-dark .table tbody tr td *,
    body table.dataTable,
    body table.dataTable th,
    body table.dataTable td,
    body table.dataTable thead th,
    body table.dataTable tbody td,
    body table.dataTable tbody tr td,
    body table.dataTable tbody tr td *,
    body .table,
    body .table th,
    body .table td,
    body .table thead th,
    body .table tbody td,
    body .table tbody tr td,
    body .table tbody tr td * {
        color: #111827 !important;
    }

    /* Pastikan saat hover baris tabel, teks tetap hitam pekat */
    body.theme-dark table.dataTable tbody tr:hover td,
    body.theme-dark table.dataTable tbody tr:hover td *,
    body.theme-dark .table-hover > tbody > tr:hover > *,
    body table.dataTable tbody tr:hover td,
    body table.dataTable tbody tr:hover td *,
    body .table-hover > tbody > tr:hover > * {
        color: #111827 !important;
    }

    /* Background tabel tetap putih bersih */
    table.dataTable tbody tr,
    table.dataTable tbody td {
        background-color: #ffffff !important;
    }
    table.dataTable tbody tr:hover td {
        background-color: #f8fafc !important;
    }
    table.dataTable thead th {
        background-color: #f1f5f9 !important;
        color: #111827 !important;
        font-weight: 700 !important;
    }

    /* Tombol Aksi Jump (Contoh: "X Indikator", "X Kegiatan") di dalam tabel */
    body.theme-dark .btn-outline-dark,
    body.theme-dark .js-jump-indikator,
    body.theme-dark .js-jump-kegiatan,
    body.theme-dark .ikk-btn-jump,
    body .btn-outline-dark,
    body .js-jump-indikator,
    body .js-jump-kegiatan,
    body .ikk-btn-jump,
    .btn-outline-dark,
    .js-jump-indikator,
    .js-jump-kegiatan,
    .ikk-btn-jump {
        color: #111827 !important;
        background-color: #f3f4f6 !important;
        border: 1px solid #9ca3af !important;
        font-weight: 600 !important;
    }
    body.theme-dark .btn-outline-dark:hover,
    body.theme-dark .js-jump-indikator:hover,
    body.theme-dark .js-jump-kegiatan:hover,
    body.theme-dark .ikk-btn-jump:hover,
    body .btn-outline-dark:hover,
    body .js-jump-indikator:hover,
    body .js-jump-kegiatan:hover,
    body .ikk-btn-jump:hover {
        color: #ffffff !important;
        background-color: #1f2937 !important;
        border-color: #1f2937 !important;
    }

    /* Badge Netral di Tabel (seperti Satuan, Indikator Induk, Tim Kerja) */
    body.theme-dark .page-content .badge.bg-light,
    body.theme-dark .table .badge.bg-light,
    body .page-content .badge.bg-light,
    body .table .badge.bg-light,
    .badge.bg-light {
        color: #111827 !important;
        background-color: #f1f5f9 !important;
        border: 1px solid #cbd5e1 !important;
    }

    /* DataTables Info & Pagination Labels */
    body.theme-dark .dataTables_wrapper .dataTables_info,
    body.theme-dark .dataTables_wrapper .dataTables_length label,
    body.theme-dark .dataTables_wrapper .dataTables_filter label,
    body .dataTables_wrapper .dataTables_info,
    body .dataTables_wrapper .dataTables_length label,
    body .dataTables_wrapper .dataTables_filter label {
        color: #374151 !important;
        font-weight: 600 !important;
    }

    /* KPI metric cards */
    .kpi-title {
        font-size: 0.8rem;
        color: #4b5563 !important;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .kpi-value {
        font-size: 1.6rem;
        font-weight: 700;
        color: #111827 !important;
        margin-top: 0.35rem;
    }
    .kpi-card {
        border-radius: 10px;
        padding: 1.25rem;
        background: #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        border: 1px solid #d1d5db;
    }
    .kpi-card small.text-secondary {
        color: #4b5563 !important;
    }

    /* Tab navigasi bersih tanpa icon */
    .custom-horizontal-nav .nav-link {
        font-weight: 600;
        color: #374151 !important;
        border: none;
        border-bottom: 3px solid transparent;
        padding: 0.85rem 1.25rem;
        transition: all 0.2s;
    }
    .custom-horizontal-nav .nav-link:hover {
        color: #111827 !important;
    }
    .custom-horizontal-nav .nav-link.active {
        color: #111827 !important;
        background: transparent !important;
        font-weight: 700 !important;
        border-bottom: 3px solid #111827 !important;
    }

    /* Filter card */
    .filter-card {
        background-color: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 12px 16px;
        margin-bottom: 16px;
    }
    .filter-card label {
        color: #111827 !important;
    }

    /* Tombol Aksi dengan Jarak / Gap yang Rapi */
    .table-action-gap {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    /* Matriks Tree block */
    .tree-sasaran-block {
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #ffffff;
        margin-bottom: 14px;
        overflow: hidden;
    }
    .tree-sasaran-header {
        background-color: #f1f5f9;
        padding: 12px 16px;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #e2e8f0;
    }
    .tree-sasaran-header,
    .tree-sasaran-header * {
        color: #111827 !important;
    }
    .tree-indikator-block {
        margin: 10px 16px;
        border-left: 3px solid #2563eb;
        padding-left: 12px;
    }
    .tree-indikator-block,
    .tree-indikator-block * {
        color: #111827 !important;
    }
    .tree-kegiatan-block {
        margin: 8px 0;
        background: #f8fafc;
        padding: 10px 14px;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
    }
    .tree-kegiatan-block,
    .tree-kegiatan-block * {
        color: #111827 !important;
    }

    /* Child row append / expand styling */
    .dt-toggle-cell {
        cursor: pointer;
    }
    .btn-toggle-child {
        transition: all 0.2s ease-in-out;
        width: 26px;
        height: 26px;
        font-weight: 700;
        border-radius: 6px;
    }
    .btn-toggle-child:hover {
        transform: scale(1.08);
    }
    table.dataTable tbody tr.shown {
        background-color: #f8fafc !important;
    }
    .child-detail-container {
        background-color: #f8fafc !important;
        border: 1px solid #cbd5e1 !important;
        border-left: 5px solid #2563eb !important;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.04);
        border-radius: 8px;
    }
    .child-detail-container,
    .child-detail-container * {
        color: #111827 !important;
    }
    .child-detail-container .text-primary {
        color: #2563eb !important;
    }
    .child-detail-container .text-success {
        color: #16a34a !important;
    }
    .child-detail-container .text-danger {
        color: #dc2626 !important;
    }
    .child-detail-container .text-warning {
        color: #d97706 !important;
    }
    .detail-label {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #4b5563 !important;
        margin-bottom: 3px;
    }
    .detail-value {
        color: #111827 !important;
        font-size: 0.88rem;
    }
</style>
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>
<main class="page-content" aria-labelledby="ikkPageTitle">
    <div class="text-start tw-wrap container-fluid">
        <!-- Top Banner Header -->
        <div class="row align-items-center mt-3 mb-3" role="banner">
            <div class="col-12 col-md-8 text-start">
                <h1 class="tw-title lh-1 cat-page-title" id="ikkPageTitle">
                    Manajemen Capaian IKK
                </h1>
                <p class="tw-subtitle mb-0 cat-page-subtitle">
                    Pengelolaan Sasaran Strategis, Indikator Kinerja, Kegiatan Operasional, Tim Pengampu, dan Transaksi Harian.
                </p>
            </div>
            <div class="col-12 col-md-4 text-md-end mt-2 mt-md-0 d-flex align-items-center justify-content-md-end gap-2">
                <button type="button" class="btn btn-outline-dark fw-semibold" id="btn-reload-all">
                    <i class="bi bi-arrow-clockwise me-1"></i> Muat Ulang
                </button>
                <a href="<?= base_url('timkerja') ?>" class="btn btn-primary">
                    <i class="bi bi-chevron-left fs-6"></i> Kembali
                </a>
            </div>
        </div>

        <!-- Himbauan & Panduan Tata Cara Pengisian Berjenjang -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert shadow-sm border-0 rounded-3 p-3 position-relative mb-0" style="background-color: #fffbe4; border-left: 6px solid #f59e0b !important;" role="alert">
                    <div class="row align-items-center g-0 pe-5">
                        <div class="col-auto pe-3">
                            <i class="bi bi-exclamation-triangle-fill" style="color: #d97706; font-size: 2.2rem; line-height: 1;"></i>
                        </div>
                        <div class="col">
                            <h6 class="fw-bold mb-1" style="color: #92400e; font-size: 0.95rem; line-height: 1.3;">
                                Panduan Alur Wajib Berjenjang Pengisian Capaian IKK
                            </h6>
                            <div style="font-size: 0.85rem; color: #b45309; line-height: 1.35;">
                                Pengisian data IKK wajib berurutan: <strong>Sasaran &rarr; Indikator &rarr; Kegiatan &rarr; Tim Pengampu &rarr; Transaksi Realisasi</strong>.
                            </div>
                        </div>
                    </div>
                    
                    <div class="position-absolute top-0 end-0 p-3">
                        <button class="btn btn-sm text-nowrap fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#tataCaraCollapse" aria-expanded="false" aria-controls="tataCaraCollapse" style="background-color: #fef3c7; color: #92400e; border: 1px solid #fcd34d;">
                            Tata Cara
                        </button>
                    </div>
                    
                    <div class="collapse" id="tataCaraCollapse">
                        <hr style="border-color: #f59e0b; opacity: 0.2; margin-top: 0.85rem; margin-bottom: 0.85rem;">
                        <ol class="mb-0 ps-3" style="font-size: 0.85rem; line-height: 1.7; color: #78350f;">
                            <li><strong>Sasaran:</strong> Tetapkan rumusan sasaran tingkat organisasi/instansi.</li>
                            <li><strong>Indikator:</strong> Hubungkan indikator terukur ke sasaran yang valid beserta target volume dan satuan.</li>
                            <li><strong>Kegiatan &amp; Pengampu:</strong> Rincikan kegiatan operasional, dan pilih Tim Kerja Pengampu (dapat lebih dari satu tim kerja).</li>
                            <li><strong>Transaksi:</strong> Catat progres realisasi mingguan/harian beserta bukti dukung pekerjaan.</li>
                            <li><strong>Matriks Monitoring:</strong> Pantau rekapitulasi hierarki lengkap capaian kinerja.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Global Filter Periode & Tahun Capaian -->
        <div class="card shadow-sm border rounded-3 mb-3 p-3 bg-white" style="border-left: 5px solid #2563eb !important;">
            <div class="row align-items-center g-2">
                <div class="col-12 col-md-5">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-calendar3 text-primary fs-4"></i>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark ikk-heading-dark">Periode &amp; Tahun Capaian</h6>
                            <small class="text-secondary ikk-text-sub">Saring capaian kinerja per tahun anggaran atau triwulan spesifik.</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold text-dark ikk-text-dark mb-1">Tahun Anggaran:</label>
                    <select class="form-select form-select-sm fw-semibold" id="filter-global-tahun">
                        <!-- Dimuat otomatis via AJAX -->
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold text-dark ikk-text-dark mb-1">Periode / Triwulan:</label>
                    <select class="form-select form-select-sm fw-semibold" id="filter-global-triwulan">
                        <option value="">Semua / Capaian Terkini</option>
                        <option value="1">Triwulan I (Jan - Mar)</option>
                        <option value="2">Triwulan II (Apr - Jun)</option>
                        <option value="3">Triwulan III (Jul - Sep)</option>
                        <option value="4">Triwulan IV (Okt - Des)</option>
                    </select>
                </div>
                <div class="col-12 col-md-1 text-md-end pt-md-3">
                    <button type="button" class="btn btn-sm btn-outline-secondary w-100" id="btn-reset-global-periode" title="Reset ke Capaian Terkini">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- KPI Metric Summary Cards -->
        <div class="row g-3 mb-4" id="ikkKpiCards">
            <div class="col-6 col-md-3">
                <div class="kpi-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="kpi-title">Sasaran</span>
                        <i class="bi bi-bullseye text-primary fs-5"></i>
                    </div>
                    <div class="kpi-value js-kpi-sasaran">0</div>
                    <small class="text-secondary">Level instansi</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="kpi-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="kpi-title">Indikator</span>
                        <i class="bi bi-graph-up text-success fs-5"></i>
                    </div>
                    <div class="kpi-value js-kpi-indikator">0</div>
                    <small class="text-secondary">Tolok ukur sasaran</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="kpi-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="kpi-title">Kegiatan</span>
                        <i class="bi bi-diagram-3 text-warning fs-5"></i>
                    </div>
                    <div class="kpi-value js-kpi-kegiatan">0</div>
                    <small class="text-danger fw-semibold js-kpi-tanpa-pengampu" style="display:none;">0 belum ada pengampu</small>
                    <small class="text-secondary js-kpi-kegiatan-sub">Kegiatan penugasan</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="kpi-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="kpi-title">Realisasi & Progres</span>
                        <i class="bi bi-check2-circle text-danger fs-5"></i>
                    </div>
                    <div class="kpi-value js-kpi-progres">0%</div>
                    <small class="text-secondary"><span class="js-kpi-transaksi">0</span> log transaksi harian</small>
                </div>
            </div>
        </div>

        <!-- Main Workspace Tab Card -->
        <div class="card shadow-sm border rounded-3 mb-4">
            <!-- Nav Header Sederhana Tanpa Icon -->
            <div class="card-header bg-white border-bottom p-0">
                <ul class="nav nav-tabs custom-horizontal-nav px-3 pt-2" id="ikkTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tab-sasaran-btn" data-bs-toggle="tab" data-bs-target="#pane-sasaran" type="button" role="tab" aria-controls="pane-sasaran" aria-selected="true">
                            Sasaran 
                            <span class="badge bg-dark rounded-pill ms-1 js-badge-count-sasaran">0</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-indikator-btn" data-bs-toggle="tab" data-bs-target="#pane-indikator" type="button" role="tab" aria-controls="pane-indikator" aria-selected="false">
                            Indikator 
                            <span class="badge bg-dark rounded-pill ms-1 js-badge-count-indikator">0</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-kegiatan-btn" data-bs-toggle="tab" data-bs-target="#pane-kegiatan" type="button" role="tab" aria-controls="pane-kegiatan" aria-selected="false">
                            Kegiatan &amp; Pengampu 
                            <span class="badge bg-dark rounded-pill ms-1 js-badge-count-kegiatan">0</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-transaksi-btn" data-bs-toggle="tab" data-bs-target="#pane-transaksi" type="button" role="tab" aria-controls="pane-transaksi" aria-selected="false">
                            Transaksi 
                            <span class="badge bg-dark rounded-pill ms-1 js-badge-count-transaksi">0</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-tree-btn" data-bs-toggle="tab" data-bs-target="#pane-tree" type="button" role="tab" aria-controls="pane-tree" aria-selected="false">
                            Matriks Monitoring
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Tab Content Panes -->
            <div class="card-body p-3 p-md-4">
                <div class="tab-content" id="ikkTabContent">
                    
                    <!-- TAB 1: SASARAN -->
                    <div class="tab-pane fade show active" id="pane-sasaran" role="tabpanel" aria-labelledby="tab-sasaran-btn">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                            <div>
                                <h5 class="fw-bold mb-1 text-dark ikk-heading-dark">Daftar Sasaran Strategis</h5>
                                <p class="text-secondary small mb-0 ikk-text-sub">Sasaran strategis sebagai dasar pembentukan indikator dan kegiatan kinerja.</p>
                            </div>
                            <div>
                                <button type="button" class="btn btn-primary fw-semibold btn-add-sasaran">
                                    <i class="bi bi-plus-lg me-1"></i> Tambah Sasaran
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover w-100 align-middle" id="table-sasaran">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 70px;">No Urut</th>
                                        <th>Nama Sasaran Strategis</th>
                                        <th style="width: 170px;">Kelengkapan</th>
                                        <th style="width: 140px;">Dibuat</th>
                                        <th style="width: 120px;" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 2: INDIKATOR -->
                    <div class="tab-pane fade" id="pane-indikator" role="tabpanel" aria-labelledby="tab-indikator-btn">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                            <div>
                                <h5 class="fw-bold mb-1 text-dark ikk-heading-dark">Daftar Indikator Kinerja Kegiatan</h5>
                                <p class="text-secondary small mb-0 ikk-text-sub">Indikator kinerja terikat langsung pada sasaran strategis yang telah ditetapkan.</p>
                            </div>
                            <div>
                                <button type="button" class="btn btn-primary fw-semibold btn-add-indikator">
                                    <i class="bi bi-plus-lg me-1"></i> Tambah Indikator
                                </button>
                            </div>
                        </div>

                        <!-- Filter Indikator -->
                        <div class="filter-card">
                            <div class="row align-items-center g-2">
                                <div class="col-12 col-md-5">
                                    <label class="form-label small fw-semibold text-dark ikk-text-dark mb-1">Filter Sasaran:</label>
                                    <select class="form-select form-select-sm" id="filter-indikator-sasaran">
                                        <option value="">-- Semua Sasaran --</option>
                                    </select>
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label small fw-semibold text-dark ikk-text-dark mb-1">Filter Tahun:</label>
                                    <select class="form-select form-select-sm" id="filter-indikator-tahun">
                                        <option value="">-- Semua Tahun --</option>
                                    </select>
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label small fw-semibold text-dark ikk-text-dark mb-1">Filter Triwulan:</label>
                                    <select class="form-select form-select-sm" id="filter-indikator-triwulan">
                                        <option value="">-- Semua TW --</option>
                                        <option value="1">Triwulan I</option>
                                        <option value="2">Triwulan II</option>
                                        <option value="3">Triwulan III</option>
                                        <option value="4">Triwulan IV</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-1 text-md-end pt-md-3">
                                    <button type="button" class="btn btn-sm btn-outline-secondary w-100" id="btn-reset-filter-indikator" title="Reset Filter">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover w-100 align-middle" id="table-indikator">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 38px;" class="text-center no-sort"><i class="bi bi-arrows-expand text-muted" title="Buka Detail"></i></th>
                                        <th style="width: 45px;" class="text-center">No</th>
                                        <th>Nama Indikator Kinerja &amp; Sasaran</th>
                                        <th style="width: 120px;">Target Induk</th>
                                        <th style="width: 140px;">Tim Pengampu</th>
                                        <th style="width: 160px;" class="text-center">Realisasi &amp; Progres</th>
                                        <th style="width: 160px;" class="text-center no-sort">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 3: KEGIATAN & PENGAMPU -->
                    <div class="tab-pane fade" id="pane-kegiatan" role="tabpanel" aria-labelledby="tab-kegiatan-btn">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                            <div>
                                <h5 class="fw-bold mb-1 text-dark ikk-heading-dark">Daftar Kegiatan Operasional &amp; Pengampu</h5>
                                <p class="text-secondary small mb-0 ikk-text-sub">Setiap kegiatan wajib memiliki Tim Kerja Pengampu sebelum transaksi realisasi dicatat.</p>
                            </div>
                            <div>
                                <button type="button" class="btn btn-primary fw-semibold btn-add-kegiatan">
                                    <i class="bi bi-plus-lg me-1"></i> Tambah Kegiatan
                                </button>
                            </div>
                        </div>

                        <!-- Filter Kegiatan -->
                        <div class="filter-card">
                            <div class="row align-items-center g-2">
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold text-dark ikk-text-dark mb-1">Filter Sasaran:</label>
                                    <select class="form-select form-select-sm" id="filter-kegiatan-sasaran">
                                        <option value="">-- Semua Sasaran --</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold text-dark ikk-text-dark mb-1">Filter Indikator:</label>
                                    <select class="form-select form-select-sm" id="filter-kegiatan-indikator">
                                        <option value="">-- Semua Indikator --</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold text-dark ikk-text-dark mb-1">Filter Tim Kerja Pengampu:</label>
                                    <select class="form-select form-select-sm" id="filter-kegiatan-timkerja">
                                        <option value="">-- Semua Tim Kerja --</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover w-100 align-middle" id="table-kegiatan">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 38px;" class="text-center no-sort"><i class="bi bi-arrows-expand text-muted" title="Buka Detail"></i></th>
                                        <th style="width: 50px;" class="text-center">No</th>
                                        <th>Nama Kegiatan Operasional</th>
                                        <th>Indikator Induk</th>
                                        <th style="width: 180px;">Tim Pengampu</th>
                                        <th style="width: 110px;" class="text-center">Status</th>
                                        <th style="width: 180px;" class="text-center no-sort">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 4: TRANSAKSI -->
                    <div class="tab-pane fade" id="pane-transaksi" role="tabpanel" aria-labelledby="tab-transaksi-btn">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                            <div>
                                <h5 class="fw-bold mb-1 text-dark ikk-heading-dark">Log Aktivitas &amp; Bukti Transaksi Kinerja</h5>
                                <p class="text-secondary small mb-0 ikk-text-sub">Riwayat operasional harian/mingguan pelaksanaan kegiatan sebagai dokumen pembuktian kinerja (evidence log).</p>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-outline-success fw-semibold btn-import-transaksi" data-bs-toggle="modal" data-bs-target="#modal-import-transaksi">
                                    <i class="bi bi-file-earmark-arrow-up me-1"></i> Import Excel
                                </button>
                                <button type="button" class="btn btn-primary fw-semibold btn-add-transaksi">
                                    <i class="bi bi-plus-lg me-1"></i> Input Transaksi Baru
                                </button>
                            </div>
                        </div>

                        <!-- Filter Transaksi -->
                        <div class="filter-card">
                            <div class="row align-items-center g-2">
                                <div class="col-12 col-md-3">
                                    <label class="form-label small fw-semibold text-dark ikk-text-dark mb-1">Filter Tim Kerja Pengampu:</label>
                                    <select class="form-select form-select-sm" id="filter-transaksi-timkerja">
                                        <option value="">-- Semua Tim Kerja --</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold text-dark ikk-text-dark mb-1">Filter Kegiatan:</label>
                                    <select class="form-select form-select-sm" id="filter-transaksi-kegiatan">
                                        <option value="">-- Semua Kegiatan --</option>
                                    </select>
                                </div>
                                <div class="col-6 col-md-2">
                                    <label class="form-label small fw-semibold text-dark ikk-text-dark mb-1">Filter Tahun:</label>
                                    <select class="form-select form-select-sm" id="filter-transaksi-tahun">
                                        <option value="">-- Semua Tahun --</option>
                                    </select>
                                </div>
                                <div class="col-6 col-md-2">
                                    <label class="form-label small fw-semibold text-dark ikk-text-dark mb-1">Filter Triwulan:</label>
                                    <select class="form-select form-select-sm" id="filter-transaksi-triwulan">
                                        <option value="">-- Semua TW --</option>
                                        <option value="1">Triwulan I</option>
                                        <option value="2">Triwulan II</option>
                                        <option value="3">Triwulan III</option>
                                        <option value="4">Triwulan IV</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-1 text-md-end pt-md-3">
                                    <button type="button" class="btn btn-sm btn-outline-secondary w-100" id="btn-reset-filter-transaksi" title="Reset Filter">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover w-100 align-middle" id="table-transaksi">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 38px;" class="text-center no-sort"><i class="bi bi-arrows-expand text-muted" title="Buka Detail"></i></th>
                                        <th>Pelaksana</th>
                                        <th>Kegiatan Operasional</th>
                                        <th style="width: 130px;">Periode</th>
                                        <th style="width: 130px;">Progres</th>
                                        <th style="width: 80px;" class="text-center">Bukti</th>
                                        <th style="width: 120px;" class="text-center no-sort">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 5: MATRIKS MONITORING -->
                    <div class="tab-pane fade" id="pane-tree" role="tabpanel" aria-labelledby="tab-tree-btn">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                            <div>
                                <h5 class="fw-bold mb-1 text-dark ikk-heading-dark">Matriks Monitoring Hierarki Capaian IKK</h5>
                                <p class="text-secondary small mb-0 ikk-text-sub">Visualisasi struktur berjenjang Sasaran, Indikator, Kegiatan, Tim Pengampu, dan Realisasi.</p>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-dark btn-sm fw-semibold" id="btn-expand-all-tree">
                                    Buka Semua
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm fw-semibold" id="btn-collapse-all-tree">
                                    Tutup Semua
                                </button>
                                <button type="button" class="btn btn-primary btn-sm fw-semibold" id="btn-reload-tree">
                                    <i class="bi bi-arrow-clockwise me-1"></i> Muat Ulang
                                </button>
                            </div>
                        </div>

                        <div id="hierarchy-tree-container" class="mt-3">
                            <div class="text-center py-5 text-muted">
                                <div class="spinner-border text-primary mb-2" role="status"></div>
                                <p>Memuat struktur hierarki IKK...</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</main>

<!-- ========================================================================= -->
<!-- MODALS -->
<!-- ========================================================================= -->

<!-- 1. Modal Sasaran -->
<div class="modal fade" id="modal-sasaran" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <form id="form-sasaran" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modal-sasaran-title">Form Sasaran Strategis</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="key" id="sasaran_key">
                <div class="mb-3">
                    <label class="form-label text-dark fw-semibold">No Urut</label>
                    <input type="number" class="form-control" name="no_urut" id="sasaran_no_urut" placeholder="Misal: 1">
                </div>
                <div class="mb-3">
                    <label class="form-label text-dark fw-semibold">Nama Sasaran Strategis <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="nama_sasaran" id="sasaran_nama_sasaran" rows="3" required placeholder="Tuliskan rumusan sasaran strategis..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary fw-semibold">Simpan Sasaran</button>
            </div>
        </form>
    </div>
</div>

<!-- 2. Modal Indikator -->
<div class="modal fade" id="modal-indikator" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <form id="form-indikator" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modal-indikator-title">Form Indikator Kinerja Kegiatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="key" id="indikator_key">
                <div class="mb-3">
                    <label class="form-label text-dark fw-semibold">Sasaran Induk <span class="text-danger">*</span></label>
                    <select class="form-select" name="sasaran_id" id="indikator_sasaran_id" required>
                        <option value="">-- Pilih Sasaran Strategis --</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label text-dark fw-semibold">No Urut</label>
                    <input type="number" class="form-control" name="no_urut" id="indikator_no_urut" placeholder="Misal: 1">
                </div>
                <div class="mb-3">
                    <label class="form-label text-dark fw-semibold">Nama Indikator Kinerja <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="nama_indikator" id="indikator_nama_indikator" rows="3" required placeholder="Tuliskan rumusan indikator kinerja..."></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-dark fw-semibold">Satuan</label>
                        <input type="text" class="form-control" name="satuan" id="indikator_satuan" placeholder="Misal: Dokumen, Laporan, %">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-dark fw-semibold">Target Volume</label>
                        <input type="text" class="form-control" name="target_volume" id="indikator_target_volume" placeholder="Misal: 1 Dokumen, 100%">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary fw-semibold">Simpan Indikator</button>
            </div>
        </form>
    </div>
</div>

<!-- 3. Modal Kegiatan (Dengan Field Tambahan 'Tim Kerja Pengampu' Multiple) -->
<div class="modal fade" id="modal-kegiatan" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <form id="form-kegiatan" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modal-kegiatan-title">Form Kegiatan Operasional</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="key" id="kegiatan_key">
                <div class="mb-3">
                    <label class="form-label text-dark fw-semibold">Indikator Kinerja Induk <span class="text-danger">*</span></label>
                    <select class="form-select" name="indikator_id" id="kegiatan_indikator_id" required>
                        <option value="">-- Pilih Indikator Kinerja --</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label text-dark fw-semibold">No Urut</label>
                    <input type="number" class="form-control" name="no_urut" id="kegiatan_no_urut" placeholder="Misal: 1">
                </div>
                <div class="mb-3">
                    <label class="form-label text-dark fw-semibold">Nama Kegiatan Operasional <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="nama_kegiatan" id="kegiatan_nama_kegiatan" rows="3" required placeholder="Tuliskan nama kegiatan operasional..."></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-dark fw-semibold">Satuan</label>
                        <input type="text" class="form-control" name="satuan" id="kegiatan_satuan" placeholder="Misal: Dokumen, Kegiatan">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-dark fw-semibold">Target Volume</label>
                        <input type="text" class="form-control" name="target_volume" id="kegiatan_target_volume" placeholder="Misal: 4 Laporan, 12">
                    </div>
                </div>

                <!-- Field Tambahan 'Tim Kerja Pengampu' (Multiple Select dari data_timkerja is_show=1) -->
                <div class="mb-3">
                    <label class="form-label text-dark fw-semibold">Tim Kerja Pengampu (Bisa Lebih Dari Satu)</label>
                    <select class="form-select" name="timkerja_id[]" id="kegiatan_timkerja_id" multiple="multiple" style="width: 100%;">
                    </select>
                    <small class="text-muted d-block mt-1">Pilih tim kerja aktif yang ditugaskan mengampu kegiatan ini.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary fw-semibold">Simpan Kegiatan</button>
            </div>
        </form>
    </div>
</div>

<!-- 4. Modal Pengampu Cepat -->
<div class="modal fade" id="modal-pengampu" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <form id="form-pengampu" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Atur Tim Kerja Pengampu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="relasi_id" id="pengampu_relasi_id">
                <input type="hidden" name="tipe_relasi" id="pengampu_tipe_relasi" value="kegiatan">
                
                <div class="alert alert-light border py-2 px-3 small mb-3">
                    <div class="fw-bold mb-1 text-dark" id="pengampu_label_info">Kegiatan / Indikator:</div>
                    <div id="pengampu_kegiatan_info" class="text-dark fw-semibold">-</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-dark">Pilih Tim Kerja Pengampu (Multiple) <span class="text-danger">*</span></label>
                    <select class="form-select" name="timkerja_id[]" id="pengampu_timkerja_id" multiple="multiple" style="width: 100%;"></select>
                    <small class="text-muted d-block mt-1">Daftar tim kerja diambil langsung dari data aktif.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary fw-semibold">Simpan Pengampu</button>
            </div>
        </form>
    </div>
</div>

<!-- 5. Modal Transaksi Harian -->
<div class="modal fade" id="modal-transaksi" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <form id="form-transaksi" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modal-transaksi-title">Form Transaksi &amp; Realisasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="key" id="transaksi_key">
                
                <div class="mb-3">
                    <label class="form-label text-dark fw-semibold">Pilih Kegiatan Operasional <span class="text-danger">*</span></label>
                    <select class="form-select" name="kegiatan_id" id="transaksi_kegiatan_id" required>
                        <option value="">-- Pilih Kegiatan Operasional --</option>
                    </select>
                    <small class="text-muted" id="transaksi_kegiatan_warning"></small>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label text-dark fw-semibold">Nama Pelaksana Kegiatan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="pelaksana_kegiatan_harian" id="transaksi_pelaksana" required placeholder="Misal: Nama ASN / Tim Pelaksana">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-dark fw-semibold">Tanggal Mulai Periode</label>
                        <input type="date" class="form-control" name="period_start_date" id="transaksi_start_date">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-dark fw-semibold">Tanggal Selesai Periode</label>
                        <input type="date" class="form-control" name="period_end_date" id="transaksi_end_date">
                        <div class="mt-1" id="transaksi_periode_detected">
                            <span class="badge bg-light text-primary border"><i class="bi bi-calendar-event me-1"></i>Periode terpetakan otomatis</span>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-dark fw-semibold">Target Mingguan</label>
                        <input type="text" class="form-control" name="target_mingguan" id="transaksi_target_mingguan" placeholder="Misal: 1 Laporan">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-dark fw-semibold">Jumlah Realisasi</label>
                        <input type="number" step="0.01" class="form-control" name="jumlah_realisasi" id="transaksi_jumlah_realisasi" placeholder="Misal: 1.0">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-dark fw-semibold">Progress Capaian (%)</label>
                        <input type="number" step="0.1" min="0" max="100" class="form-control" name="progres_realisasi" id="transaksi_progres" placeholder="0 - 100">
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label text-dark fw-semibold">Bukti Dukung Pekerjaan (Tautan / Dokumen)</label>
                        <textarea class="form-control" name="bukti_pekerjaan" id="transaksi_bukti" rows="2" placeholder="Tautan Google Drive atau keterangan fisik dokumen..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary fw-semibold">Simpan Transaksi</button>
            </div>
        </form>
    </div>
</div>

<!-- 6. Modal Import Transaksi Excel -->
<div class="modal fade" id="modal-import-transaksi" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form id="form-import-transaksi" class="modal-content" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-dark ikk-heading-dark">
                    <i class="bi bi-file-earmark-excel text-success me-2"></i>Import Data Transaksi Kinerja IKK
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-primary d-flex align-items-center mb-3 p-3 border-0 bg-light-primary rounded" role="alert">
                    <i class="bi bi-info-circle-fill fs-4 text-primary me-3 flex-shrink-0"></i>
                    <div class="text-dark small">
                        Pastikan menggunakan format template resmi Simojang. <strong>ID Kegiatan</strong> dapat dicek langsung pada Sheet ke-2 file template. Sistem akan otomatis memetakan periode (Tahun, Triwulan, Bulan) berdasarkan tanggal kegiatan.
                    </div>
                </div>

                <div class="upload-card">
                    <div class="upload-card-body text-center dropzone-area p-4" id="dropzoneAreaTransaksi" style="border: 2px dashed #0d6efd; border-radius: 10px; background-color: #f8fafc; cursor: pointer; transition: all 0.3s ease;">
                        <i class="bi bi-cloud-arrow-up display-5 text-primary mb-2 d-block"></i>
                        <h6 class="fw-bold text-dark mb-1">Drag &amp; Drop file Excel di sini</h6>
                        <p class="text-muted small mb-2">atau klik tombol di bawah untuk memilih file</p>
                        <button type="button" class="btn btn-outline-primary btn-sm mb-2 px-3 fw-semibold" id="btnBrowseExcelTransaksi">
                            <i class="bi bi-folder2-open me-1"></i> Pilih File Excel
                        </button>
                        <p class="text-muted mb-0" style="font-size: 0.75rem;">Mendukung format: .xlsx, .xls (Maks. 10 MB)</p>
                        
                        <div id="filePreviewTransaksi" class="d-none mt-3 p-3 border rounded bg-white d-flex align-items-center justify-content-between shadow-sm text-start">
                            <div class="d-flex align-items-center overflow-hidden me-2">
                                <i class="bi bi-file-earmark-excel-fill fs-2 text-success me-3 flex-shrink-0"></i>
                                <div class="text-truncate">
                                    <span id="fileNameTransaksi" class="fw-bold text-dark d-block text-truncate"></span>
                                    <small id="fileSizeTransaksi" class="text-muted"></small>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger" id="btnRemoveFileTransaksi" title="Hapus file terpilih">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        <input type="file" class="d-none" name="file_excel" id="excelUploadTransaksi" accept=".xls,.xlsx" />
                    </div>
                </div>

                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center mt-3 pt-3 border-top gap-2">
                    <div class="text-muted small">
                        <i class="bi bi-question-circle me-1"></i> Belum punya format template?
                    </div>
                    <div>
                        <a href="<?= base_url('apps/samples/sample-transaksi-ikk.xlsx') ?>" class="btn btn-sm btn-outline-success fw-semibold" download>
                            <i class="bi bi-download me-1"></i> Unduh Template Excel (.xlsx)
                        </a>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary fw-semibold" id="btnSubmitImportTransaksi">
                    <i class="bi bi-upload me-1"></i> Proses Import
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 7. Modal Input / Update Realisasi Indikator -->
<div class="modal fade" id="modal-realisasi-indikator" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form id="form-realisasi-indikator" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-dark ikk-heading-dark">
                    <i class="bi bi-speedometer2 text-primary me-2"></i>Pencatatan Realisasi Indikator Kinerja
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" name="indikator_id" id="realisasi_ind_id">

                <!-- Header Info Indikator -->
                <div class="p-3 bg-light rounded border mb-3">
                    <div class="row g-2">
                        <div class="col-12">
                            <span class="detail-label d-block">Indikator Kinerja Kegiatan:</span>
                            <span class="fw-bold text-dark fs-6" id="realisasi_ind_nama">-</span>
                        </div>
                        <div class="col-md-7">
                            <span class="detail-label d-block">Sasaran Induk:</span>
                            <span class="small text-secondary" id="realisasi_ind_sasaran">-</span>
                        </div>
                        <div class="col-md-5">
                            <span class="detail-label d-block">Tim Pengampu:</span>
                            <div id="realisasi_ind_pengampu" class="mt-1">
                                <span class="badge bg-light text-dark border">Belum Ada Pengampu</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Inputs -->
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-dark fw-semibold">Tahun Pelaporan <span class="text-danger">*</span></label>
                        <select class="form-select" name="tahun" id="realisasi_ind_tahun" required>
                            <!-- Diisi via options tahun -->
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-dark fw-semibold">Triwulan Pelaporan <span class="text-danger">*</span></label>
                        <select class="form-select" name="triwulan" id="realisasi_ind_triwulan" required>
                            <option value="1">Triwulan I (TW 1 - Jan s/d Mar)</option>
                            <option value="2">Triwulan II (TW 2 - Apr s/d Jun)</option>
                            <option value="3">Triwulan III (TW 3 - Jul s/d Sep)</option>
                            <option value="4">Triwulan IV (TW 4 - Okt s/d Des)</option>
                        </select>
                    </div>

                    <!-- Smart Assist Notice & Button -->
                    <div class="col-12" id="smart-assist-box">
                        <div class="alert alert-info d-flex flex-column flex-sm-row align-items-sm-center justify-content-between p-3 border-0 bg-light-info rounded mb-0 gap-2">
                            <div class="d-flex align-items-center me-2">
                                <i class="bi bi-magic fs-4 text-primary me-2 flex-shrink-0"></i>
                                <div class="small text-dark">
                                    <strong id="smart-assist-keg-count">Indikator memiliki kegiatan terdaftar.</strong>
                                    <div class="text-muted" id="smart-assist-sub">Anda dapat menarik estimasi capaian otomatis dari log kegiatan operasional.</div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary fw-semibold text-nowrap" id="btn-tarik-kegiatan">
                                <i class="bi bi-arrow-repeat me-1"></i> Tarik dari Log Kegiatan
                            </button>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-dark fw-semibold">Target Periode Ini</label>
                        <input type="text" class="form-control" name="target_periode" id="realisasi_ind_target" placeholder="Misal: 1 Dokumen / 100%">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-dark fw-semibold">Jumlah Realisasi Angka</label>
                        <input type="number" step="0.01" class="form-control" name="jumlah_realisasi" id="realisasi_ind_jumlah" placeholder="Misal: 1.0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-dark fw-semibold">Progres Capaian (%) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.1" min="0" max="150" class="form-control" name="progres_capaian" id="realisasi_ind_progres" required placeholder="0 - 100">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label text-dark fw-semibold">Penjelasan Capaian / Analisis Kendala</label>
                        <textarea class="form-control" name="penjelasan_capaian" id="realisasi_ind_penjelasan" rows="2" placeholder="Faktor pendorong, hambatan/kendala, atau catatan analisis capaian pada periode ini..."></textarea>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label text-dark fw-semibold">Bukti Dukung / Tautan Dokumen</label>
                        <input type="text" class="form-control" name="bukti_dukung" id="realisasi_ind_bukti" placeholder="Tautan Google Drive dokumen laporan atau lokasi arsip fisik...">
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary fw-semibold" id="btnSubmitRealisasiIndikator">
                    <i class="bi bi-check-lg me-1"></i> Simpan Realisasi
                </button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/ikk/main_v3.js?v=' . time()) ?>"></script>
<?= $this->endSection(); ?>
