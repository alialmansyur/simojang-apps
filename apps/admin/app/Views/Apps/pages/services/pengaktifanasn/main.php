<?php

$serviceTitle = 'Pengaktifan kembali sebagai ASN';
$layananId = 8;
$docType = 'Pengaktifan kembali sebagai ASN';
$docCategoryDefault = '';
$showCategoryFilter = false;

?>
<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>
<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/service-table-ui.css?v=99') ?>">
<link rel="stylesheet" href="<?= base_url('apps/assets/extensions/filepond/filepond.css'); ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/pengaktifanasn/main.css') ?>">
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>
<div class="page-content py-4">
    <div class="container-fluid text-start tw-wrap" style="padding: 0 .85rem 1.05rem;">
        <div class="page-heading mb-0">
            <div class="row align-items-center d-flex justify-content-between">
                <div class="col-md-8 text-start">
                    <h3 class="mt-3 mb-1"><b><?= esc($serviceTitle) ?></b></h3>
                    <p class="text-subtitle text-muted">Kantor Regional III Badan Kepegawaian Negara</p>
                </div>
                <div class="col-md-4 text-md-end mt-2 mt-md-0">
                    <div class="service-page-inline-actions">
                        <button type="button" class="btn btn-outline-primary js-service-reload" id="reloadData">
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
                <div class="service-ui-topbar service-ui-static-topbar module-topbar">
                            <div class="service-ui-topbar-filters module-filter-row">
                                <?php if (!empty($showCategoryFilter)): ?>
                                    <select id="docCategoryPicker" class="form-select form-select-sm module-category-picker">
                                        <?php if (!empty($categoryOptions) && is_array($categoryOptions)): ?>
                                            <?php foreach ($categoryOptions as $value => $label): ?>
                                                <option value="<?= esc((string) $value) ?>" <?= ((string) ($docCategoryDefault ?? '') === (string) $value) ? 'selected' : '' ?>>
                                                    <?= esc((string) $label) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <option value="Pengajuan CLTN" selected>Pengajuan CLTN</option>
                                            <option value="Perpanjangan CLTN">Perpanjangan CLTN</option>
                                            <option value="Pengaktifan dari CLTN">Pengaktifan dari CLTN</option>
                                        <?php endif; ?>
                                    </select>
                                <?php endif; ?>

                                <div class="dropdown">
                                    <button class="btn btn-outline-primary dropdown-toggle px-3 fw-semibold d-flex align-items-center gap-2"
                                        type="button" id="monthDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false">
                                        Pilih Bulan
                                    </button>
                                    <ul class="dropdown-menu shadow rounded-3 border-0 p-3 mt-2 service-ui-period-dropdown" id="monthDropdown">
                                        <div id="monthList"></div>
                                        <li><hr class="dropdown-divider my-2"></li>
                                        <li>
                                            <button type="button" class="btn btn-primary w-100 fw-semibold" id="applyMonth">
                                                <i class="bi bi-check-circle me-1"></i> Terapkan
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="service-ui-topbar-actions module-action-row">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                    <i class="bi bi-upload me-1"></i> Upload Data Baru
                                </button>
                            </div>
                        </div>

                        <!-- KPI Cards Section -->
                        <div id="uploadSummary" class="row g-3 mb-4">
                            <!-- KPI 1 -->
                            <div class="col-12 col-md-4">
                                <div class="card border h-100 mb-0 shadow-sm">
                                    <div class="card-body p-3 d-flex align-items-center gap-3">
                                        <span class="service-ui-recap-icon">
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"/><path d="M7 3v4"/><path d="M17 3v4"/><rect x="4" y="7" width="16" height="14" rx="2"/></svg>
                                        </span>
                                        <div>
                                            <p class="service-ui-recap-label mb-1">Total File</p>
                                            <h6 class="service-ui-recap-value js-total-file mb-0">0</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- KPI 2 -->
                            <div class="col-12 col-md-4">
                                <div class="card border h-100 mb-0 shadow-sm">
                                    <div class="card-body p-3 d-flex align-items-center gap-3">
                                        <span class="service-ui-recap-icon">
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3h18v18H3z"/><path d="M7 7h10"/><path d="M7 12h10"/><path d="M7 17h6"/></svg>
                                        </span>
                                        <div>
                                            <p class="service-ui-recap-label mb-1">Total Detail</p>
                                            <h6 class="service-ui-recap-value js-total-detail mb-0">0</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- KPI 3 -->
                            <div class="col-12 col-md-4">
                                <div class="card border h-100 mb-0 shadow-sm">
                                    <div class="card-body p-3 d-flex align-items-center gap-3">
                                        <span class="service-ui-recap-icon">
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                        </span>
                                        <div>
                                            <p class="service-ui-recap-label mb-1">Instansi Tercakup</p>
                                            <h6 class="service-ui-recap-value js-total-instansi mb-0">0</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- KPI 4 -->
                            <div class="col-12 col-md-4">
                                <div class="card border h-100 mb-0 shadow-sm">
                                    <div class="card-body p-3 d-flex align-items-center gap-3">
                                        <span class="service-ui-recap-icon">
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                                        </span>
                                        <div>
                                            <p class="service-ui-recap-label mb-1">Upload Terakhir</p>
                                            <h6 class="service-ui-recap-value js-last-upload mb-0">-</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- KPI 5 -->
                            <div class="col-12 col-md-4">
                                <div class="card border h-100 mb-0 shadow-sm">
                                    <div class="card-body p-3 d-flex align-items-center gap-3">
                                        <span class="service-ui-recap-icon">
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"/><path d="M12 7v10"/><path d="M8 13l4 4 4-4"/></svg>
                                        </span>
                                        <div>
                                            <p class="service-ui-recap-label mb-1">Upload Pertama</p>
                                            <h6 class="service-ui-recap-value js-first-upload mb-0">-</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- KPI 6 -->
                            <div class="col-12 col-md-4">
                                <div class="card border h-100 mb-0 shadow-sm">
                                    <div class="card-body p-3 d-flex align-items-center gap-3">
                                        <span class="service-ui-recap-icon">
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3v18h18"/><path d="M7 15l4-4 3 2 4-5"/></svg>
                                        </span>
                                        <div>
                                            <p class="service-ui-recap-label mb-1">Periode Aktif</p>
                                            <h6 class="service-ui-recap-value js-active-periods mb-0">0</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Table Card Section -->
                <!-- Active Filters Indicator -->
                                <div id="activeFiltersLabel" class="mb-3" style="display: none;">
                                    <span class="text-muted small me-2"><i class="bi bi-funnel me-1"></i>Filter Aktif:</span>
                                    <span id="filterCategoryBadge" class="badge bg-light text-primary border border-primary me-1 mb-1" style="font-weight: 500;"></span>
                                    <span id="filterMonthBadge" class="badge bg-light text-primary border border-primary mb-1" style="font-weight: 500; display: none;"></span>
                                </div>

                        <div class="card border shadow-sm">
                            <div class="card-body p-3">
<div class="table-responsive">
                                    <table id="dataTable" class="table table-bordered table-hover nowrap">
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <th>File Unggah</th>
                                                <th>Periode Bulan</th>
                                                <th>Periode Hari</th>
                                                <th>Tanggal Upload</th>
                                                <th>Pengupload</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot>
                                            <tr>
                                                <th></th>
                                                <th>File Unggah</th>
                                                <th>Periode Bulan</th>
                                                <th>Periode Hari</th>
                                                <th>Tanggal Upload</th>
                                                <th>Pengupload</th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                </div>
            </div>
        </section>

        <div class="modal fade" id="uploadModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5">Unggah File</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="UploadData" autocomplete="off">
                            <input type="hidden" name="layanan_id" id="layanan_id" value="<?= esc((string) $layananId) ?>">
                            <input type="hidden" name="doc_type" class="doc_type" value="<?= esc($docType) ?>">
                            <input type="hidden" name="doc_category" id="doc_category" value="<?= esc($docCategoryDefault ?? '') ?>">

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Period</label>
                                    <input type="month" class="form-control rounded" name="period" placeholder="Period Bulan" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tanggal Mulai</label>
                                    <input type="date" class="form-control rounded" name="syncdate1" placeholder="Tanggal Tarikan Data" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tanggal Selesai</label>
                                    <input type="date" class="form-control rounded" name="syncdate2" placeholder="Tanggal Tarikan Data" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Remarks</label>
                                    <input type="text" class="form-control rounded" name="remarks" placeholder="Keterangan Tambahan">
                                </div>
                                <div class="col-12">
                                    <div class="upload-card mt-1">
                                        <div class="upload-card-body text-center p-4">
                                            <i class="bi bi-upload fs-2 text-muted"></i>
                                            <h5 class="mt-3">Seret & taruh file di sini, atau klik untuk unggah</h5>
                                            <p class="mb-3">Unduh format file
                                                <a href="<?= base_url($sampleFile ?? 'apps/samples/sample-general.xlsx') ?>"
                                                download><strong>disini</strong></a>
                                            </p>
                                            <input type="file" class="basic-filepond" name="filepond" id="excelUpload" accept=".xls,.xlsx" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary sbmt">Simpan Data</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="fileDetailModal" tabindex="-1" role="dialog" data-bs-backdrop="static"
            data-bs-keyboard="false" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-fullscreen" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="fileDetailModalLabel">Detail File</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <table id="dataTableDetail" class="table table-bordered table-hover nowrap">
                            <thead></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <a href="#" id="dt-download" class="btn btn-primary" target="_blank">Download</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>
<?= $this->section('scripts'); ?>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/service-table-ui.js') ?>"></script>
<script src="<?= base_url('apps/assets/extensions/filepond/filepond.js'); ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/modules/fetchData.js') ?>"></script>
<?= $this->endSection(); ?>
