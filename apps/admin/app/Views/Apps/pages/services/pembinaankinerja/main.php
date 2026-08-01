<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>
<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/service-table-ui.css?v=99') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/pembinaankinerja/main.css') ?>">
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>
<div class="page-content p-2 p-md-4">
    <div class="container-fluid" style="max-width: 1160px; margin: 0 auto;">
        <div class="page-heading mb-0">
            <div class="row align-items-center d-flex justify-content-between">
                <div class="col-md-8 text-start">
                    <h3 class="mt-3 mb-1"><b><?= $title; ?></b></h3>
                    <p class="text-subtitle text-muted">Kantor Regional III Badan Kepegawaian Negara</p>
                </div>
                <div class="col-md-4 text-md-end mt-2 mt-md-0">
                    <div class="service-page-inline-actions">
                        <button type="button" class="btn btn-outline-primary js-service-reload" id="pkReload">
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
                <div class="service-ui-topbar mb-3 service-ui-static-topbar pk-topbar">
                            <div class="service-ui-topbar-filters module-filter-row d-flex align-items-center gap-2">
                                <select id="pkYearFilter" class="form-select form-select-sm w-auto"></select>
                                <div class="dropdown">
                                    <button
                                        class="btn btn-outline-primary dropdown-toggle px-3 fw-semibold d-flex align-items-center gap-2"
                                        type="button" id="pkMonthDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false">
                                        Pilih Bulan
                                    </button>
                                    <ul class="dropdown-menu shadow rounded-3 border-0 p-3 mt-2 service-ui-period-dropdown" id="pkMonthDropdown">
                                        <div id="pkMonthList"></div>
                                        <li><hr class="dropdown-divider my-2"></li>
                                        <li>
                                            <button type="button" class="btn btn-primary w-100 fw-semibold" id="pkApplyMonth">
                                                <i class="bi bi-check-circle me-1"></i> Terapkan
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                                <select id="pkCategoryFilter" class="form-select form-select-sm w-auto">
                                    <option value="0">Semua Kategori</option>
                                </select>
                            </div>
                            <div class="service-ui-topbar-actions pk-topbar-actions">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#pkDataModal">
                                    <i class="bi bi-plus me-1"></i> Tambah Data
                                </button>
                            </div>
                        </div>

                        <div class="pk-category-overview mb-3" id="pkCategoryOverview"></div>
                        
                        <!-- Active Filters Indicator -->
                        <div id="activeFilterContainer" class="active-filters-container my-3 align-items-center flex-wrap gap-2" style="display: none;">
                            <span class="text-muted small me-2"><i class="bi bi-funnel me-1"></i>Filter Aktif:</span>
                            <span id="filterYearBadge" class="badge bg-light text-primary border border-primary me-1 mb-1" style="font-weight: 500; display: none;"></span>
                            <span id="filterKategoriBadge" class="badge bg-light text-primary border border-primary me-1 mb-1" style="font-weight: 500; display: none;"></span>
                            <span id="filterMonthBadge" class="badge bg-light text-primary border border-primary mb-1" style="font-weight: 500; display: none;"></span>
                        </div>

                          <div class="card border shadow-sm">
                              <div class="card-body p-3">
                                  <div class="table-responsive">
                            <table id="pkTable" class="table table-bordered table-hover nowrap">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Logo</th>
                                        <th>Instansi</th>
                                        <th>Kategori</th>
                                        <th>Periode</th>
                                        <th>Capaian</th>
                                        <th>Status</th>
                                        <th>Pendampingan</th>
                                        <th>Diperbaharui</th>
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

        <div class="modal fade" id="pkDataModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="pkDataModalLabel">Tambah Data Pembinaan Kinerja</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="pkForm" autocomplete="off">
                            <input type="hidden" name="key" value="">
                            <input type="hidden" name="kegiatan_code" value="">
                            <input type="hidden" name="aplikasi_code" value="">
                            <div class="row ps-2 pe-2 g-2">
                                <div class="col-12">
                                    <label class="form-label mb-1">Kategori</label>
                                    <select name="kategori_id" id="pkKategoriInput" class="form-select" required></select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label mb-1">Instansi</label>
                                    <select name="instansi" class="form-select select-instansi" required></select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label mb-1">Tahun Periode</label>
                                    <input type="number" min="2001" max="2100" class="form-control" name="period_year" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label mb-1">Tanggal Data</label>
                                    <input type="date" class="form-control" name="period_date" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label mb-1">Capaian (%)</label>
                                    <input type="number" class="form-control" min="0" max="100" step="0.01" name="capaian_percent" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label mb-1">Tanggal Pendampingan</label>
                                    <input type="date" class="form-control" name="pendampingan_date">
                                </div>
                                <div class="col-12">
                                    <label class="form-label mb-1">Catatan</label>
                                    <textarea class="form-control" rows="3" name="catatan" placeholder="Catatan opsional..."></textarea>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" id="pkSubmitBtn">Simpan Data</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>
<?= $this->section('scripts'); ?>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/service-table-ui.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/pembinaankinerja/main.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/pembinaankinerja/tables.js') ?>"></script>
<?= $this->endSection(); ?>
