<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>
<?= $this->section('style'); ?>
<?php
$anggaranCssVersion = @filemtime(FCPATH . 'apps/assets/css/pages/services/anggaran/main.css') ?: time();
$anggaranMainJsVersion = @filemtime(FCPATH . 'apps/assets/js/custom/pages/services/anggaran/main.js') ?: time();
$anggaranTablesJsVersion = @filemtime(FCPATH . 'apps/assets/js/custom/pages/services/anggaran/tables.js') ?: time();
$serviceTableUiJsVersion = @filemtime(FCPATH . 'apps/assets/js/custom/pages/services/service-table-ui.js') ?: time();
?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/service-table-ui.css?v=99') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/anggaran/main.css') ?>?v=<?= $anggaranCssVersion ?>">
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>
<div class="page-content py-4">
    <div class="container-fluid text-start mx-auto tw-wrap" style="max-width: 1160px; padding: 0 .85rem 1.05rem;">
        <div class="page-heading mb-0">
            <div class="row align-items-center d-flex justify-content-between">
                <div class="col-md-6 text-start">
                    <h3 class="mt-3"><b>Pagu &amp; Realisasi Anggaran</b></h3>
                    <p class="text-subtitle text-muted">Simojang | Kantor Regional III Badan Kepegawaian Negara</p>
                </div>
                <div class="col-md-6 text-end">
                    <div class="service-page-inline-actions">
                        <button type="button" id="anggaran-reload" class="btn btn-outline-primary js-service-reload">
                            <i class="bi bi-arrow-clockwise me-1"></i> Muat Ulang
                        </button>
                        <button type="button" id="anggaran-export-excel" class="btn btn-outline-success">
                            <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
                        </button>
                        <a href="javascript:history.back()" class="btn btn-primary">
                            <i class="bi bi-chevron-left fs-6"></i> <strong>Kembali</strong>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <section class="row">
            <div class="col-md-12">
                <div class="service-ui-topbar mb-3 service-ui-static-topbar anggaran-topbar">
                            <div class="anggaran-topbar-filters">
                                <div class="anggaran-topbar-filter">
                                    <select id="filterTahun" class="form-select">
                                        <option value="">Semua Tahun</option>
                                    </select>
                                </div>
                                <div class="anggaran-topbar-filter anggaran-date-mode">
                                    <select id="filterDateMode" class="form-select">
                                        <option value="">Semua Tanggal</option>
                                        <option value="spm">Tanggal SPM</option>
                                        <option value="sp2d">Tanggal SP2D</option>
                                    </select>
                                </div>
                                <div class="anggaran-topbar-filter anggaran-date-range">
                                    <input type="date" id="filterDateStart" class="form-control rounded" placeholder="Tanggal mulai">
                                </div>
                                <div class="anggaran-topbar-filter anggaran-date-range">
                                    <input type="date" id="filterDateEnd" class="form-control rounded" placeholder="Tanggal akhir">
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-wrap justify-content-md-end">
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#AnggaranMasterModal">
                                    <i class="bi bi-table me-1"></i> Master Data
                                </button>
                                <button type="button" class="btn btn-primary" id="openCreateAnggaran">
                                    <i class="bi bi-plus me-1"></i> Realisasi Baru
                                </button>
                            </div>
                        </div>
                          <div class="card border">
                              <div class="card-body p-3">
                                  <div class="service-ui-recap anggaran-recap">
                            <div class="service-ui-recap-card">
                                <div>
                                    <p class="service-ui-recap-label">Total Record Posted</p>
                                    <h6 class="service-ui-recap-value" id="sumTotalRecord">0</h6>
                                </div>
                            </div>
                            <div class="service-ui-recap-card">
                                <div>
                                    <p class="service-ui-recap-label">Total Akun Struktur</p>
                                    <h6 class="service-ui-recap-value" id="sumTotalAkun">0</h6>
                                </div>
                            </div>
                            <div class="service-ui-recap-card">
                                <div>
                                    <p class="service-ui-recap-label">Pagu Revisi</p>
                                    <h6 class="service-ui-recap-value" id="sumPaguRevisi">Rp0</h6>
                                </div>
                            </div>
                            <div class="service-ui-recap-card">
                                <div>
                                    <p class="service-ui-recap-label">Lock Pagu</p>
                                    <h6 class="service-ui-recap-value" id="sumLockPagu">Rp0</h6>
                                </div>
                            </div>
                            <div class="service-ui-recap-card">
                                <div>
                                    <p class="service-ui-recap-label">Pagu Efektif</p>
                                    <h6 class="service-ui-recap-value" id="sumPaguEfektif">Rp0</h6>
                                </div>
                            </div>
                            <div class="service-ui-recap-card">
                                <div>
                                    <p class="service-ui-recap-label">Total Realisasi Posted</p>
                                    <h6 class="service-ui-recap-value" id="sumRealisasi">Rp0</h6>
                                </div>
                            </div>
                            <div class="service-ui-recap-card">
                                <div>
                                    <p class="service-ui-recap-label">Capaian Realisasi</p>
                                    <h6 class="service-ui-recap-value" id="sumCapaian">0%</h6>
                                </div>
                            </div>
                            <div class="service-ui-recap-card">
                                <div>
                                    <p class="service-ui-recap-label">Target / Gap</p>
                                    <h6 class="service-ui-recap-value"><span id="sumTarget">0%</span> / <span id="sumGapTarget">0%</span></h6>
                                </div>
                            </div>
                        </div>
                                  <div class="table-responsive">
                            <table id="dataTableAnggaran" class="table table-bordered table-hover nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Tahun</th>
                                        <th>Periode</th>
                                        <th>No. SPM</th>
                                        <th>Tgl SPM</th>
                                        <th>No. SP2D</th>
                                        <th>Tgl SP2D</th>
                                        <th>Total Item</th>
                                        <th>Total Realisasi</th>
                                        <th>Status</th>
                                        <th>Update</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<div class="modal fade" id="AnggaranModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true" style="--bs-modal-border-radius:0px; --bs-modal-inner-border-radius:0px;">
    <div class="modal-dialog modal-dialog-scrollable modal-xl" style="border-radius:0px !important;">
        <div class="modal-content" style="border-radius:0px !important;">
            <div class="modal-header">
                <h5 class="modal-title" id="AnggaranModalLabel">Tambah Realisasi Anggaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-anggaran" autocomplete="off">
                    <input type="hidden" name="key" id="anggaran_key">
                    <div class="anggaran-form-section">
                        <div class="anggaran-form-section-title">
                            <h6 class="mb-1">Header Transaksi</h6>
                            <small class="text-muted">Satu transaksi dapat memiliki banyak item realisasi.</small>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Tahun Anggaran</label>
                                <select class="form-select" name="tahun_id" id="anggaran_tahun_id" required></select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Periode Bulan</label>
                                <input type="month" class="form-control" name="period_date" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Status</label>
                                <select class="form-select" name="status" id="anggaran_status">
                                    <option value="PENDING">PENDING</option>
                                    <option value="POSTED">POSTED</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">No. SPM</label>
                                <input type="text" class="form-control" name="no_spm" id="anggaran_no_spm" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Tanggal SPM</label>
                                <input type="date" class="form-control" name="spm_date" id="anggaran_spm_date" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">No. SP2D</label>
                                <input type="text" class="form-control" name="no_sp2d" id="anggaran_no_sp2d" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Tanggal SP2D</label>
                                <input type="date" class="form-control" name="sp2d_date" id="anggaran_sp2d_date" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Keterangan Header</label>
                                <textarea class="form-control" name="keterangan" id="anggaran_keterangan" rows="2" placeholder="Keterangan umum transaksi realisasi"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="anggaran-form-section">
                        <div class="anggaran-form-section-title anggaran-form-section-title-inline">
                            <div>
                                <h6 class="mb-1">Detail / Item Realisasi</h6>
                                <small class="text-muted">Tambahkan akun struktur, nominal realisasi, dan keterangan per item.</small>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="addAnggaranItemRow">
                                <i class="bi bi-plus-lg me-1"></i> Tambah Item
                            </button>
                        </div>
                        <div class="table-responsive anggaran-item-table-wrap">
                            <table class="table table-bordered align-middle mb-0 anggaran-item-table">
                                <thead>
                                    <tr>
                                        <th class="anggaran-col-structure">Akun Struktur</th>
                                        <th class="anggaran-col-nominal">Nominal Realisasi</th>
                                        <th class="anggaran-col-keterangan">Keterangan</th>
                                        <th class="anggaran-col-action"></th>
                                    </tr>
                                </thead>
                                <tbody id="anggaranItemTableBody"></tbody>
                            </table>
                        </div>
                        <div class="anggaran-item-empty d-none" id="anggaranItemEmpty">
                            Belum ada item realisasi. Tambahkan minimal satu item.
                        </div>
                        <div class="anggaran-item-footer">
                            <span>Total item: <strong id="anggaranItemCount">0</strong></span>
                            <span>Total nominal: <strong id="anggaranItemTotal">Rp0</strong></span>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary btn-save-anggaran">Simpan Data</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade modal-force-rounded" id="AnggaranViewModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Realisasi Anggaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="anggaran-detail-summary" id="anggaranDetailSummary"></div>
                <div class="table-responsive anggaran-detail-table-wrap">
                    <table class="table table-bordered align-middle mb-0 anggaran-detail-table">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="46%">Akun Struktur</th>
                                <th width="17%">Nominal</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody id="anggaranDetailTableBody">
                            <tr>
                                <td colspan="4" class="text-center text-muted">Belum ada item realisasi.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="anggaran-detail-footer">
                    <span>Total item: <strong id="anggaranDetailItemCount">0</strong></span>
                    <span>Total nominal: <strong id="anggaranDetailTotalNominal">Rp0</strong></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="AnggaranMasterModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true" style="--bs-modal-border-radius:0px !important; --bs-modal-inner-border-radius:0px !important;">
    <div class="modal-dialog modal-fullscreen modal-dialog-scrollable rounded-0" style="border-radius:0px !important;">
        <div class="modal-content rounded-0" style="border-radius:0px !important;">
            <div class="modal-header">
                <h5 class="modal-title">Master Data Anggaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-pills mb-3" id="anggaranMasterTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tabMasterStruktur" type="button">Struktur Anggaran</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tabMasterTahun" type="button">Master Tahun</button>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade" id="tabMasterTahun">
                        <div class="anggaran-master-panel">
                            <div class="anggaran-master-panel-header">
                                <div>
                                    <h6 class="mb-1">Master Tahun</h6>
                                    <small class="text-muted">Kelola target penyerapan dan tahun aktif tanpa perlu kembali ke bagian atas modal.</small>
                                </div>
                            </div>
                            <div class="anggaran-master-panel-body">
                                <div class="anggaran-master-filter-grid">
                                    <div class="anggaran-master-filter-item">
                                        <label class="form-label mb-1">Cari Tahun</label>
                                        <input type="search" class="form-control" id="tahunSearchFilter" placeholder="Cari tahun atau target">
                                    </div>
                                    <div class="anggaran-master-filter-item">
                                        <label class="form-label mb-1">Status Tahun</label>
                                        <select class="form-select" id="tahunStatusFilter">
                                            <option value="">Semua Status</option>
                                            <option value="active">Aktif</option>
                                            <option value="inactive">Nonaktif</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="anggaran-master-toolbar">
                                    <button type="button" class="btn btn-outline-primary" id="refreshTahunMaster">
                                        <i class="bi bi-arrow-clockwise me-1"></i>Muat Ulang
                                    </button>
                                    <button type="button" class="btn btn-primary" id="openCreateTahun">
                                        <i class="bi bi-plus-lg me-1"></i>Tambah Tahun
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive anggaran-master-table-scroll anggaran-master-table-scroll-year">
                            <table class="table table-sm table-bordered anggaran-master-table" id="tahunTable">
                                <thead>
                                    <tr>
                                        <th>Tahun</th>
                                        <th>Target %</th>
                                        <th>Status</th>
                                        <th>Dibuat</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade show active" id="tabMasterStruktur">
                        <div class="anggaran-structure-layout row g-2">
                            <div class="col-12">
                                <div class="anggaran-structure-panel anggaran-structure-table-panel">
                                    <div class="anggaran-master-panel">
                                        <div class="anggaran-master-panel-header">
                                            <div>
                                                <h6 class="mb-1">Struktur Anggaran</h6>
                                                <small class="text-muted">Filter dan aksi struktur dipusatkan pada panel ini, sedangkan tambah/ubah dilakukan lewat modal editor.</small>
                                            </div>
                                        </div>
                                        <div class="anggaran-master-panel-body">
                                            <div class="anggaran-master-filter-grid">
                                                <div class="anggaran-master-filter-item">
                                                    <label class="form-label mb-1">Filter Tahun Struktur</label>
                                                    <select class="form-select" id="masterFilterTahun">
                                                        <option value="">Semua Tahun</option>
                                                    </select>
                                                </div>
                                                <div class="anggaran-master-filter-item">
                                                    <label class="form-label mb-1">Cari Struktur</label>
                                                    <input type="search" class="form-control" id="masterSearchStruktur" placeholder="Cari kode atau nama struktur">
                                                </div>
                                                <div class="anggaran-master-filter-item">
                                                    <label class="form-label mb-1">Filter Level</label>
                                                    <select class="form-select" id="masterLevelFilter">
                                                        <option value="">Semua Level</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="anggaran-master-toolbar">
                                                <button type="button" class="btn btn-outline-secondary" id="expandAllStruktur">
                                                    <i class="bi bi-arrows-expand-vertical me-1"></i>Expand All
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary" id="collapseAllStruktur">
                                                    <i class="bi bi-arrows-collapse-vertical me-1"></i>Collapse All
                                                </button>
                                                <button type="button" class="btn btn-outline-primary" id="refreshStrukturMaster">
                                                    <i class="bi bi-arrow-clockwise me-1"></i>Muat Ulang
                                                </button>
                                                <button type="button" class="btn btn-primary" id="appendRootStruktur">
                                                    <i class="bi bi-plus-lg me-1"></i>Tambah Root
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="table-responsive anggaran-master-table-scroll anggaran-master-table-scroll-structure">
                                        <table class="table table-sm table-bordered anggaran-master-table" id="strukturTable">
                                            <thead>
                                                <tr>
                                                    <th>NAMA STRUKTUR</th>
                                                    <th>JENIS</th>
                                                    <th>TAHUN</th>
                                                    <th>REALISASI</th>
                                                    <th>PAGU REVISI</th>
                                                    <th>LOCK PAGU</th>
                                                    <th>PAGU EFEKTIF</th>
                                                    <th>AKSI</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="AnggaranYearEditorModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true" style="--bs-modal-border-radius:0px; --bs-modal-inner-border-radius:0px;">
    <div class="modal-dialog modal-dialog-centered" style="border-radius:0px !important;">
        <div class="modal-content" style="border-radius:0px !important;">
            <div class="modal-header">
                <h5 class="modal-title" id="AnggaranYearEditorLabel">Tambah Master Tahun</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-tahun-anggaran" class="row g-3">
                    <input type="hidden" name="key" id="tahun_key">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tahun</label>
                        <input type="number" class="form-control" name="tahun" placeholder="Tahun (contoh: 2026)" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Target Penyerapan %</label>
                        <input type="number" class="form-control" name="target_persen" step="0.01" min="0" max="100" placeholder="Contoh: 95.50" required>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="tahun_is_active" name="is_active" value="1">
                            <label class="form-check-label" for="tahun_is_active">Set sebagai tahun aktif</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" id="resetTahunForm">Reset</button>
                <button type="button" class="btn btn-primary" id="submitTahunForm">Simpan</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="AnggaranStrukturEditorModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true" style="--bs-modal-border-radius:0px; --bs-modal-inner-border-radius:0px;">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="border-radius:0px !important;">
        <div class="modal-content" style="border-radius:0px !important;">
            <div class="modal-header">
                <h5 class="modal-title" id="AnggaranStrukturEditorLabel">Tambah Struktur Anggaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="small text-primary mb-3" id="strukturParentHint">Mode: tambah root (level UNIT).</div>
                <form id="form-struktur-anggaran" class="row g-3 anggaran-editor-grid">
                    <input type="hidden" name="key" id="struktur_key">
                    <input type="hidden" name="parent_id" id="struktur_parent_id">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Tahun</label>
                        <input type="number" class="form-control" name="tahun" id="struktur_tahun" placeholder="Tahun" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Level</label>
                        <select class="form-select" name="level" id="struktur_level" required></select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Kode</label>
                        <input type="text" class="form-control" name="kode" placeholder="Kode">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Nama Struktur</label>
                        <input type="text" class="form-control" name="nama" placeholder="Nama Struktur" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Pagu Revisi</label>
                        <input type="text" class="form-control js-currency" name="pagu_revisi" inputmode="numeric" autocomplete="off" placeholder="Rp 0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Lock Pagu</label>
                        <input type="text" class="form-control js-currency" name="lock_pagu" inputmode="numeric" autocomplete="off" placeholder="Rp 0">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" id="resetStrukturForm">Reset</button>
                <button type="button" class="btn btn-primary" id="submitStrukturForm">Simpan</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/service-table-ui.js') ?>?v=<?= $serviceTableUiJsVersion ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/anggaran/main.js') ?>?v=<?= $anggaranMainJsVersion ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/anggaran/tables.js') ?>?v=<?= $anggaranTablesJsVersion ?>"></script>
<?= $this->endSection(); ?>
