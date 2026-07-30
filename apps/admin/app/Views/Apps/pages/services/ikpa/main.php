<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>
<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/service-table-ui.css?v=2') ?>">
<link rel="stylesheet" href="<?= base_url('apps/assets/extensions/filepond/filepond.css'); ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/ikpa/main.css') ?>">
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>
<div class="page-content py-4">
    <div class="container-fluid text-start mx-auto tw-wrap" style="max-width: 1160px; padding: 0 .85rem 1.05rem;">
        <div class="page-heading mb-0">
            <div class="row align-items-center d-flex justify-content-between">
                <div class="col-md-6 text-start">
                    <h3 class="mt-3"><b>Analisa Kinerja Pelaksanaan Anggaran</b></h3>
                    <p class="text-subtitle text-muted">Simojang | Kantor Regional III Badan Kepegawaian Negara</p>
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
                            <div class="dropdown">
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
                            <div class="d-flex align-items-center justify-content-end">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#DataModal">
                                    <i class="bi bi-plus me-2"></i>Tambah Data
                                </button>
                            </div>
                        </div>
                          <div class="card border">
                              <div class="card-body p-3">
                                  <div class="table-responsive">
                            <table id="dataTable" class="table table-bordered table-hover nowrap">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Nilai</th>
                                        <th>Periode</th>
                                        <th>Tanggal Mulai</th>
                                        <th>Tanggal Selesai</th>
                                        <th>Satker</th>
                                        <th>KPPN</th>
                                        <th>BA</th>
                                        <th>Nilai Total</th>
                                        <th>Konversi</th>
                                        <th>Dispensasi</th>
                                        <th>Nilai Akhir</th>
                                        <th>Tanggal Upload</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th></th>
                                        <th>Nilai</th>
                                        <th>Periode</th>
                                        <th>Tanggal Mulai</th>
                                        <th>Tanggal Selesai</th>
                                        <th>Satker</th>
                                        <th>KPPN</th>
                                        <th>BA</th>
                                        <th>Nilai Total</th>
                                        <th>Konversi</th>
                                        <th>Dispensasi</th>
                                        <th>Nilai Akhir</th>
                                        <th>Tanggal Upload</th>
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
                    <input type="hidden" name="layanan_id" id="layanan_id" value="26">
                    <input type="hidden" name="doc_type" class="doc_type"
                        value="Digitalisasi arsip kepegawaian">
                    <div class="row ps-4 pe-4">
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label class="form-label">Period</label>
                                <input type="month" class="form-control rounded" name="period" placeholder="Period Bulan"
                                    required>
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
                                        <a href="<?= base_url('apps/samples/sample-ikpa.xlsx') ?>"
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
                            <th class="text-center">Kelompok</th>
                            <th class="text-center">Indikator</th>
                            <th class="text-center">Nilai</th>
                            <th class="text-center">Bobot</th>
                            <th class="text-center">Nilai Akhir</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <th class="text-center">Kelompok</th>
                            <th class="text-center">Indikator</th>
                            <th class="text-center">Nilai</th>
                            <th class="text-center">Bobot</th>
                            <th class="text-center">Nilai Akhir</th>
                        </tr>
                    </tfoot>             
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <!-- <a href="#" id="dt-download" class="btn btn-primary" target="_blank">Download</a> -->
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
<?= $this->section('scripts'); ?>
<script src="<?= base_url('apps/assets/extensions/filepond/filepond.js'); ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/service-table-ui.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/ikpa/main.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/ikpa/tables.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/ikpa/detail.js') ?>"></script>
<?= $this->endSection(); ?>
