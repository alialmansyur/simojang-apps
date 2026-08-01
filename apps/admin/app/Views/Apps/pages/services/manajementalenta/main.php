<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>
<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/service-table-ui.css?v=99') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/manajementalenta/main.css') ?>">
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>
<div class="page-content p-2 p-md-4">
    <div class="container-fluid text-start tw-wrap" style="max-width: 1160px; margin: 0 auto;">
        <div class="page-heading mb-0">
            <div class="row align-items-center d-flex justify-content-between">
                <div class="col-md-6 text-start">
                    <h3 class="mt-3"><b><?= $title; ?></b></h3>
                    <p class="text-subtitle text-muted">Kantor Regional III Badan Kepegawaian Negara</p>
                </div>
                <div class="col-md-6 text-end">
                    <div class="service-page-inline-actions">
                        <button type="button" class="btn btn-outline-primary js-service-reload">
                            <i class="bi bi-arrow-clockwise me-1"></i> Muat Ulang
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
                <div class="service-ui-topbar mb-3 service-ui-static-topbar">
                            <div class="col">
                                <div class="dropdown">
                                    <button
                                        class="btn btn-outline-primary dropdown-toggle px-4 py-2 fw-semibold d-flex align-items-center gap-2"
                                        type="button" id="dropdownBulan" data-bs-toggle="dropdown" aria-expanded="false">
                                        Pilih Bulan
                                    </button>

                                    <ul class="dropdown-menu shadow rounded-3 border-0 p-3 mt-2 service-ui-period-dropdown" id="bulanDropdown">
                                        <!-- ISI BULAN DINAMIS -->
                                        <div id="bulanList"></div>
                                        <li>
                                            <hr class="dropdown-divider my-2">
                                        </li>
                                        <li>
                                            <button class="btn btn-primary w-100 fw-semibold" id="applyBulan">
                                                <i class="bi bi-check-circle me-1"></i> Terapkan
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col text-end">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#DataModal"><i
                                        class="bi bi-plus me-2"></i>Tambah Data</button>
                            </div>
                        </div>

                        <!-- KPI Cards Section -->
                        <div id="mtSummary" class="row g-3 mb-4">
                            <!-- KPI 1 -->
                            <div class="col-12 col-md-4">
                                <div class="card border h-100 mb-0 shadow-sm">
                                    <div class="card-body p-3 d-flex align-items-center gap-3">
                                        <span class="service-ui-recap-icon">
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                        </span>
                                        <div>
                                            <p class="service-ui-recap-label mb-1">Total Data</p>
                                            <h6 class="service-ui-recap-value js-total-data mb-0">0</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- KPI 2 -->
                            <div class="col-12 col-md-4">
                                <div class="card border h-100 mb-0 shadow-sm">
                                    <div class="card-body p-3 d-flex align-items-center gap-3">
                                        <span class="service-ui-recap-icon">
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                                        </span>
                                        <div>
                                            <p class="service-ui-recap-label mb-1">Data Ditampilkan</p>
                                            <h6 class="service-ui-recap-value js-data-ditampilkan mb-0">0</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- KPI 3 -->
                            <div class="col-12 col-md-4">
                                <div class="card border h-100 mb-0 shadow-sm">
                                    <div class="card-body p-3 d-flex align-items-center gap-3">
                                        <span class="service-ui-recap-icon">
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                        </span>
                                        <div>
                                            <p class="service-ui-recap-label mb-1">Update Terakhir</p>
                                            <h6 class="service-ui-recap-value js-last-update mb-0">-</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="activeFiltersLabel" class="active-filters-container my-3 align-items-center flex-wrap gap-2" style="display: none;">
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted small me-2"><i class="bi bi-funnel me-1"></i>Filter Aktif:</span>
                                <div class="active-filters-list d-flex flex-wrap gap-2"></div>
                            </div>
                        </div>

                        <div class="card border shadow-sm">
                            <div class="card-body p-3">
                                <div class="table-responsive">
                            <table id="dataTable" class="table table-bordered table-hover nowrap">
                                <thead>
                                    <tr>
                                        <th><strong></strong></th>
                                        <th><strong></strong></th>
                                        <th><strong>Instansi Nama</strong></th>
                                        <th><strong>Mulai Implemen</strong></th>
                                        <th><strong>Status</strong></th>
                                        <th><strong>Total Progress</strong></th>
                                        <th><strong>Diperbaharui</strong></th>
                                        <th><strong></strong></th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th><strong></strong></th>
                                        <th><strong></strong></th>
                                        <th><strong>Instansi Nama</strong></th>
                                        <th><strong>Mulai Implemen</strong></th>
                                        <th><strong>Status</strong></th>
                                        <th><strong>Total Progress</strong></th>
                                        <th><strong>Diperbaharui</strong></th>
                                        <th><strong></strong></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="modal fade" id="DataModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
            aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-4" id="DataModalLabel">Tambah Data</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="form-usulan" autocomplete="off">
                            <input type="hidden" name="key">
                            <div class="row ps-2 pe-2">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="form-label mb-1">Instansi</label>
                                        <select name="instansi" class="form-select select-instansi" required></select>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="form-label">Period</label>
                                        <input type="month" class="form-control rounded period" name="period"
                                            placeholder="Period Bulan" required>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="form-label">Tanggal Mulai Periode</label>
                                        <input type="date" class="form-control rounded" name="startdate"
                                            placeholder="Tanggal Mulai" required>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="form-label mb-1">Status Progress</label>
                                        <select name="stepProgress" class="form-select select-step" required></select>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-submit-form">Simpan Data</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<?= $this->endSection(); ?>
<?= $this->section('scripts'); ?>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/service-table-ui.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/manajementalenta/main.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/manajementalenta/tables.js') ?>"></script>
<?= $this->endSection(); ?>
