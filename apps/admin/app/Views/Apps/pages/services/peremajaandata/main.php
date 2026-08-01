<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>
<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/service-table-ui.css?v=99') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/peremajaandata/main.css') ?>">
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>
<div class="page-content py-4">
    <div class="container-fluid text-start mx-auto" style="padding: 0 .85rem 1.05rem; max-width: 1160px;">
        <div class="page-heading mb-0">
            <div class="row align-items-center d-flex justify-content-between">
                <div class="col-md-6 text-start">
                    <h3><b>Peremajaan Data ASN</b></h3>
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
                            <div class="d-flex align-items-center flex-nowrap gap-2"><div class="dropdown">
                                    <button 
                                        class="btn btn-outline-primary dropdown-toggle px-4 py-2 fw-semibold d-flex align-items-center gap-2"
                                        type="button" id="dropdownBulan" data-bs-toggle="dropdown" aria-expanded="false">
                                        Pilih Bulan
                                    </button>

                                    <ul class="dropdown-menu shadow rounded-3 border-0 p-3 mt-2 service-ui-period-dropdown" id="bulanDropdown">
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
                            <div class="d-flex align-items-center justify-content-end gap-2 flex-nowrap">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#DataModal"><i
                                        class="bi bi-plus me-2"></i>Tambah
                                    Periode Data</button>
                            </div>
                        </div>
                        
                        <div id="serviceUiRecap" class="row g-3 mb-4">
                            <div class="col-12 col-md-4">
                                <div class="card border h-100 mb-0 shadow-sm">
                                    <div class="card-body p-3 d-flex align-items-center gap-3">
                                        <span class="service-ui-recap-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"/><path d="M7 3v4"/><path d="M17 3v4"/><rect x="4" y="7" width="16" height="14" rx="2"/></svg></span>
                                        <div><p class="service-ui-recap-label mb-1">Total Skema</p><h6 class="service-ui-recap-value mb-0" id="pdm-total-skema">0</h6></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="card border h-100 mb-0 shadow-sm">
                                    <div class="card-body p-3 d-flex align-items-center gap-3">
                                        <span class="service-ui-recap-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3v18h18"/><path d="M8 14l3-3 3 2 4-5"/></svg></span>
                                        <div><p class="service-ui-recap-label mb-1">Total ACC</p><h6 class="service-ui-recap-value mb-0" id="pdm-total-acc">0</h6></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="card border h-100 mb-0 shadow-sm">
                                    <div class="card-body p-3 d-flex align-items-center gap-3">
                                        <span class="service-ui-recap-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
                                        <div><p class="service-ui-recap-label mb-1">Total BTL</p><h6 class="service-ui-recap-value mb-0" id="pdm-total-btl">0</h6></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="card border h-100 mb-0 shadow-sm">
                                    <div class="card-body p-3 d-flex align-items-center gap-3">
                                        <span class="service-ui-recap-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"/><path d="M7 3v4"/><path d="M17 3v4"/><rect x="4" y="7" width="16" height="14" rx="2"/></svg></span>
                                        <div><p class="service-ui-recap-label mb-1">Total TMS</p><h6 class="service-ui-recap-value mb-0" id="pdm-total-tms">0</h6></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="card border h-100 mb-0 shadow-sm">
                                    <div class="card-body p-3 d-flex align-items-center gap-3">
                                        <span class="service-ui-recap-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3v18h18"/><path d="M8 14l3-3 3 2 4-5"/></svg></span>
                                        <div><p class="service-ui-recap-label mb-1">Data Ditampilkan</p><h6 class="service-ui-recap-value mb-0" id="pdm-data-shown">0</h6></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="card border h-100 mb-0 shadow-sm">
                                    <div class="card-body p-3 d-flex align-items-center gap-3">
                                        <span class="service-ui-recap-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
                                        <div><p class="service-ui-recap-label mb-1">Update Terakhir</p><h6 class="service-ui-recap-value mb-0" id="pdm-last-update">-</h6></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="activeFilterContainer" class="active-filters-container my-3 align-items-center flex-wrap gap-2" style="display: none;">
                            <span class="text-muted small me-2"><i class="bi bi-funnel me-1"></i>Filter Aktif:</span>
                        </div>
                          <div class="card border shadow-sm">
                              <div class="card-body p-3">
                                  <div class="table-responsive">
                            <table id="dataTable" class="table table-bordered table-hover nowrap">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th><strong>Scema</strong></th>
                                        <th><strong>Period</strong></th>
                                        <th><strong>Tanggal Mulai</strong></th>
                                        <th><strong>Tanggal Selesai</strong></th>
                                        <th><strong>Jenis</strong></th>
                                        <th><strong>ACC</strong></th>
                                        <th><strong>BTL</strong></th>
                                        <th><strong>TMS</strong></th>
                                        <th><strong>Created By</strong></th>

                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot> 
                                    <tr>
                                        <th></th>
                                        <th><strong>Scema</strong></th>
                                        <th><strong>Period</strong></th>
                                        <th><strong>Tanggal Mulai</strong></th>
                                        <th><strong>Tanggal Selesai</strong></th>
                                        <th><strong>Jenis</strong></th>
                                        <th><strong>ACC</strong></th>
                                        <th><strong>BTL</strong></th>
                                        <th><strong>TMS</strong></th>
                                        <th><strong>Created By</strong></th>

                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="DataModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-4" id="DataModalLabel">Tambah Data PDM</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="form-usulan" autocomplete="off">
                    <div class="modal-body">
                        <input type="hidden" name="layanan_id" id="layanan_id" value="22">
                        <div class="pdm-form-section">
                            <div class="pdm-form-section-title">
                                <h6 class="mb-1">Header Transaksi</h6>
                                <small class="text-muted">Satu periode peremajaan dapat memiliki beberapa item kategori tindak lanjut.</small>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label mb-1 fw-bold">Periode Bulan</label>
                                    <input type="month" class="form-control" name="period" placeholder="Period Bulan" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label mb-1 fw-bold">Tanggal Mulai</label>
                                    <input type="date" class="form-control" name="startdate" placeholder="Tanggal Mulai" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label mb-1 fw-bold">Tanggal Selesai</label>
                                    <input type="date" class="form-control" name="enddate" placeholder="Tanggal Selesai" required>
                                </div>
                            </div>
                        </div>

                        <div class="pdm-form-section">
                            <div class="pdm-form-section-title pdm-form-section-title-inline">
                                <div>
                                    <h6 class="mb-1">Detail / Item Peremajaan</h6>
                                    <small class="text-muted">Tambahkan jenis peremajaan beserta total ACC, BTL, dan TMS per item.</small>
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="addRowBtn">
                                    <i class="bi bi-plus-lg me-1"></i> Tambah Item
                                </button>
                            </div>
                            <div class="table-responsive pdm-item-table-wrap">
                                <table class="table table-bordered align-middle mb-0 pdm-item-table" id="usulanTable">
                                    <thead>
                                        <tr>
                                            <th width="34%">Jenis</th>
                                            <th width="16%">ACC</th>
                                            <th width="16%">BTL</th>
                                            <th width="16%">TMS</th>
                                            <th width="18%" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="usulanTableBody"></tbody>
                                </table>
                            </div>
                            <div class="pdm-item-empty d-none" id="pdmItemEmpty">
                                Belum ada item peremajaan. Tambahkan minimal satu item untuk disimpan.
                            </div>
                            <div class="pdm-item-footer">
                                <span>Total item: <strong id="pdmItemCount">0</strong></span>
                                <span>Total ACC: <strong id="pdmItemAcc">0</strong></span>
                                <span>Total BTL: <strong id="pdmItemBtl">0</strong></span>
                                <span>Total TMS: <strong id="pdmItemTms">0</strong></span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-submit-form">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
<?= $this->endSection(); ?>
<?= $this->section('scripts'); ?>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/service-table-ui.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/peremajaandata/main.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/peremajaandata/tables.js') ?>?v=<?= time() ?>"></script>
<?= $this->endSection(); ?>

