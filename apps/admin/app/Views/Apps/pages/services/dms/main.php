<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>
<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/service-table-ui.css?v=2') ?>">
<link rel="stylesheet" href="<?= base_url('apps/assets/extensions/filepond/filepond.css'); ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/dms/main.css') ?>">
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>
<div class="page-content py-4">
    <div class="container-sm px-3 text-start mx-auto">
        <div class="page-heading mb-0">
            <div class="row align-items-center d-flex justify-content-between">
                <div class="col-md-6 text-start">
                    <h3><b>Digitalisasi Arsip Kepegawaian</b></h3>
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
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#DataModal"><i class="bi bi-plus me-2"></i>Tambah
                                    Periode Data</button>
                            </div>
                        </div>
                          <div class="card border">
                              <div class="card-body p-3">
                                  <div class="table-responsive">
                            <table id="dataTable" class="table table-bordered table-hover nowrap">
                                 <thead>
                                    <tr>
                                        <th></th>
                                        <th><strong></strong></th>
                                        <th><strong>Nama Instansi</strong></th>
                                        <th><strong>Period</strong></th>
                                        <th><strong>Tanggal Mulai</strong></th>
                                        <th><strong>Tanggal Selesai</strong></th>
                                        <th><strong>Jenis</strong></th>
                                        <th><strong>Total</strong></th>
                                        <th><strong>Created By</strong></th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th></th>
                                        <th><strong></strong></th>
                                        <th><strong>Nama Instansi</strong></th>
                                        <th><strong>Period</strong></th>
                                        <th><strong>Tanggal Mulai</strong></th>
                                        <th><strong>Tanggal Selesai</strong></th>
                                        <th><strong>Jenis</strong></th>
                                        <th><strong>Total</strong></th>
                                        <th><strong>Created By</strong></th>
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
                <h1 class="modal-title fs-4" id="DataModalLabel">Tambah Data DMS</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-usulan" autocomplete="off">
                <div class="modal-body">
                    <input type="hidden" name="layanan_id" id="layanan_id" value="26">
                    <div class="dms-form-section">
                        <div class="dms-form-section-title">
                            <h6 class="mb-1">Header Transaksi</h6>
                            <small class="text-muted">Satu periode DMS dapat memiliki beberapa item jenis dokumen.</small>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label mb-1 fw-bold">Instansi</label>
                                <select name="instansi" class="form-select select-instansi" required></select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label mb-1 fw-bold">Periode Bulan</label>
                                <input type="month" class="form-control" name="period" placeholder="Period Bulan" required>
                            </div> 
                            <div class="col-md-6">
                                <label class="form-label mb-1 fw-bold">Tanggal Mulai</label>
                                <input type="date" class="form-control" name="startdate" placeholder="Tanggal Mulai" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label mb-1 fw-bold">Tanggal Selesai</label>
                                <input type="date" class="form-control" name="enddate" placeholder="Tanggal Selesai" required>
                            </div>
                        </div>
                    </div>

                    <div class="dms-form-section">
                        <div class="dms-form-section-title dms-form-section-title-inline">
                            <div>
                                <h6 class="mb-1">Detail / Item Dokumen</h6>
                                <small class="text-muted">Tambahkan jenis arsip dan total dokumen per item.</small>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="addRowBtn">
                                <i class="bi bi-plus-lg me-1"></i> Tambah Item
                            </button>
                        </div>
                        <div class="table-responsive dms-item-table-wrap">
                            <table class="table table-bordered align-middle mb-0 dms-item-table" id="usulanTable">
                                <thead>
                                    <tr>
                                        <th width="58%">Jenis Dokumen</th>
                                        <th width="24%">Total Dokumen</th>
                                        <th width="18%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="usulanTableBody"></tbody>
                            </table>
                        </div>
                        <div class="dms-item-empty d-none" id="dmsItemEmpty">
                            Belum ada item dokumen. Tambahkan minimal satu item untuk disimpan.
                        </div>
                        <div class="dms-item-footer">
                            <span>Total item: <strong id="dmsItemCount">0</strong></span>
                            <span>Total dokumen: <strong id="dmsItemTotal">0</strong></span>
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
                            <th class="text-center"><strong>NIP</strong></th>                             
                            <th class="text-center"><strong>D2NIP</strong></th>
                            <th class="text-center"><strong>Ijazah</strong></th>
                            <th class="text-center"><strong>DRH</strong></th>
                            <th class="text-center"><strong>CPNS</strong></th>
                            <th class="text-center"><strong>PNS</strong></th>
                            <th class="text-center"><strong>KP</strong></th>
                            <th class="text-center"><strong>Jabatan</strong></th>
                            <th class="text-center"><strong>Perubahan</strong></th>
                            <th class="text-center"><strong>Berhenti</strong></th>
                            <th class="text-center"><strong>Pensiun</strong></th>
                            <th class="text-center"><strong>Total</strong></th>
                            <th class="text-center"><strong>PIC</strong></th>      
                            <th class="text-center">Tanggal Upload</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <th class="text-center"></th>
                            <th class="text-center">Kode Instansi</th>
                            <th class="text-center">Nama Instansi</th>
                            <th class="text-center"><strong>NIP</strong></th>                             
                            <th class="text-center"><strong>D2NIP</strong></th>
                            <th class="text-center"><strong>Ijazah</strong></th>
                            <th class="text-center"><strong>DRH</strong></th>
                            <th class="text-center"><strong>CPNS</strong></th>
                            <th class="text-center"><strong>PNS</strong></th>
                            <th class="text-center"><strong>KP</strong></th>
                            <th class="text-center"><strong>Jabatan</strong></th>
                            <th class="text-center"><strong>Perubahan</strong></th>
                            <th class="text-center"><strong>Berhenti</strong></th>
                            <th class="text-center"><strong>Pensiun</strong></th>
                            <th class="text-center"><strong>Total</strong></th>
                            <th class="text-center"><strong>PIC</strong></th>      
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
<script src="<?= asset_url('apps/assets/js/custom/pages/services/dms/main.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/dms/tables.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/dms/detail.js') ?>"></script>
<?= $this->endSection(); ?>

