<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>
<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/service-table-ui.css?v=99') ?>">
<link rel="stylesheet" href="<?= base_url('apps/assets/extensions/filepond/filepond.css'); ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/disparitasdata/main.css') ?>">
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>
<div class="page-content py-4">
    <div class="container-fluid text-start mx-auto" style="padding: 0 .85rem 1.05rem; max-width: 1160px;">
        <div class="page-heading mb-0">
            <div class="row align-items-center d-flex justify-content-between">
                <div class="col-md-6 text-start">
                    <h3><b>Layanan Disparitas Data</b></h3>
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
                                        class="bi bi-upload me-2"></i>Upload Data Baru</button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="card-title">
                                    <div class="h4 mb-0">Upload Progress</div>
                                    <small class="text-muted">Halaman Upload Progress Disparitas Data</small>
                                </div>
                            </div>
                        </div>
                        
                        <div id="serviceUiRecap" class="row g-3 mb-4">
                            <div class="col-12 col-md-4">
                                <div class="card border h-100 mb-0 shadow-sm">
                                    <div class="card-body p-3 d-flex align-items-center gap-3">
                                        <span class="service-ui-recap-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"/><path d="M7 3v4"/><path d="M17 3v4"/><rect x="4" y="7" width="16" height="14" rx="2"/></svg></span>
                                        <div><p class="service-ui-recap-label mb-1">Total Upload</p><h6 class="service-ui-recap-value mb-0" id="dsp-total-upload">0</h6></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="card border h-100 mb-0 shadow-sm">
                                    <div class="card-body p-3 d-flex align-items-center gap-3">
                                        <span class="service-ui-recap-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3v18h18"/><path d="M8 14l3-3 3 2 4-5"/></svg></span>
                                        <div><p class="service-ui-recap-label mb-1">Total Anomali</p><h6 class="service-ui-recap-value mb-0" id="dsp-total-anomali">0</h6></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="card border h-100 mb-0 shadow-sm">
                                    <div class="card-body p-3 d-flex align-items-center gap-3">
                                        <span class="service-ui-recap-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
                                        <div><p class="service-ui-recap-label mb-1">Total Instansi</p><h6 class="service-ui-recap-value mb-0" id="dsp-total-instansi">0</h6></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="card border h-100 mb-0 shadow-sm">
                                    <div class="card-body p-3 d-flex align-items-center gap-3">
                                        <span class="service-ui-recap-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"/><path d="M7 3v4"/><path d="M17 3v4"/><rect x="4" y="7" width="16" height="14" rx="2"/></svg></span>
                                        <div><p class="service-ui-recap-label mb-1">Jenis Anomali</p><h6 class="service-ui-recap-value mb-0" id="dsp-total-jenis-anomali">0</h6></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="card border h-100 mb-0 shadow-sm">
                                    <div class="card-body p-3 d-flex align-items-center gap-3">
                                        <span class="service-ui-recap-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3v18h18"/><path d="M8 14l3-3 3 2 4-5"/></svg></span>
                                        <div><p class="service-ui-recap-label mb-1">Data Ditampilkan</p><h6 class="service-ui-recap-value mb-0" id="dsp-data-shown">0</h6></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="card border h-100 mb-0 shadow-sm">
                                    <div class="card-body p-3 d-flex align-items-center gap-3">
                                        <span class="service-ui-recap-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
                                        <div><p class="service-ui-recap-label mb-1">Update Terakhir</p><h6 class="service-ui-recap-value mb-0" id="dsp-last-update">-</h6></div>
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
                                        <th>File Unggah</th>
                                        <th>Periode</th>
                                        <th>Tanggal Mulai</th>
                                        <th>Tanggal Selesai</th>
                                        <th>Total Disparitas</th>
                                        <th>Tanggal Upload</th>
                                        <th>Pengupload</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th></th>
                                        <th>File Unggah</th>
                                        <th>Periode</th>
                                        <th>Tanggal Mulai</th>
                                        <th>Tanggal Selesai</th>
                                        <th>Total Disparitas</th>
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
        </section>
    </div>
</div>

<div class="modal fade" id="DataModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-4" id="DataModalLabel">Unggah File</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="UploadData" autocomplete="off">
                    <input type="hidden" name="layanan_id" id="layanan_id" value="9">
                    <input type="hidden" name="doc_type" class="doc_type"
                        value="Pendampingan Peningkatan Kualitas Data ASN">
                    <div class="row ps-4 pe-4">
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label class="form-label">Period</label>
                                <input type="month" class="form-control rounded" name="period"
                                    placeholder="Period Bulan" required>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control rounded" name="syncdate1"
                                    placeholder="Tanggal Tarikan Data" required>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label class="form-label">Tanggal Selesai</label>
                                <input type="date" class="form-control rounded" name="syncdate2"
                                    placeholder="Tanggal Tarikan Data" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label">Remarks</label>
                                <input type="text" class="form-control rounded" name="remarks"
                                    placeholder="Keterangan Tambahan">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="upload-card mt-3">
                                <div class="upload-card-body text-center">
                                    <i class="bi bi-upload me-3 fs-3 text-muted"></i>
                                    <h5 class="mt-3">Unggah File</h5>
                                    <p>Unduh format file
                                        <a href="<?= base_url('apps/samples/sample-disparitas.xlsx') ?>"
                                            download><strong>disini</strong></a>
                                    </p>
                                    <input type="file" class="basic-filepond" name="filepond" id="excelUpload"
                                        accept=".xls,.xlsx" />
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
    data-bs-keyboard="false" aria-labelledby="DataModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-fullscreen" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fileDetailModalLabel">Detail File</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table id="dataTableDetail" class="table table-bordered table-hover nowrap">
                    <thead>
                        <tr>
                            <th class="text-center"></th>
                            <th class="text-center">Kode Instansi</th>
                            <th class="text-center">Nama Instansi</th>
                            <th class="text-center">Jenis Anomali</th>
                            <th class="text-center">Total Anomali</th>
                            <th class="text-center">Tanggal Upload</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <th class="text-center"></th>
                            <th class="text-center">Kode Instansi</th>
                            <th class="text-center">Nama Instansi</th>
                            <th class="text-center">Jenis Anomali</th>
                            <th class="text-center">Total Anomali</th>
                            <th class="text-center">Tanggal Upload</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
<?= $this->section('scripts'); ?>
<script src="<?= base_url('apps/assets/extensions/filepond/filepond.js'); ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/service-table-ui.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/disparitasdata/main.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/disparitasdata/tables.js') ?>?v=<?= time() ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/disparitasdata/detail.js') ?>"></script>
<?= $this->endSection(); ?>

