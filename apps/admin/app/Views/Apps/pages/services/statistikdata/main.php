<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>
<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/service-table-ui.css?v=99') ?>">
<link rel="stylesheet" href="<?= base_url('apps/assets/extensions/filepond/filepond.css'); ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/statistikdata/main.css') ?>">
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>
<div class="page-content py-4">
    <div class="container-sm px-3 text-start mx-auto">
        <div class="page-heading mb-0">
            <div class="row align-items-center d-flex justify-content-between">
                <div class="col-md-6 text-start">
                    <h3><b>Penyajian Statistik Data Kepegawaian Secara Periodik</b></h3>
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
            <div class="col-md-12 mt-0">
                <div class="service-ui-topbar mb-3 service-ui-static-topbar statistik-topbar">
                            <div class="service-ui-topbar-filters statistik-filter-row">
                                <select class="form-select" id="statJenis">
                                    <option value="">Pilih kategori data</option>
                                    <option value="Jumlah ASN">Jumlah ASN</option>
                                    <option value="Golongan ASN">Golongan ASN</option>
                                    <option value="Jenis Kelamin ASN">Jenis Kelamin ASN</option>
                                    <option value="Pendidikan ASN">Pendidikan ASN</option>
                                    <option value="Usia ASN">Usia ASN</option>
                                    <option value="Generasi ASN">Generasi ASN</option> 
                                    <option value="Kelompok Jabatan ASN">Kelompok Jabatan ASN</option>
                                    <option value="Masa Kerja ASN">Masa Kerja ASN</option>
                                </select>
                                <button type="button" class="btn btn-primary" id="applyJenis"> Terapkan
                                </button>
                                <a href="#" id="sampleFormatLink" class="btn btn-outline-secondary disabled" tabindex="-1" aria-disabled="true" target="_blank" rel="noopener">
                                    <i class="bi bi-download me-1"></i> Format Sampel
                                </a>
                            </div>
                            <div class="service-ui-topbar-actions statistik-action-row">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModalFullscreen" id="openUploadModal">
                                    <i class="bi bi-upload me-2"></i>Upload Data Baru
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
                                        <th>File Unggah</th>
                                        <th>Jenis</th>
                                        <th>Periode</th>
                                        <th>Tanggal Data</th>
                                        <th>Total Data</th>
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
                                        <th>Jenis</th>
                                        <th>Periode</th>
                                        <th>Tanggal Data</th>
                                        <th>Total Data</th>
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

<div class="modal fade" id="exampleModalFullscreen" data-bs-backdrop="static" data-bs-keyboard="false"
    tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-4" id="exampleModalFullscreenLabel">Unggah File</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="UploadData" autocomplete="off">
                    <input type="hidden" name="layanan_id" id="layanan_id" value="21">
                    <input type="hidden" name="doc_type" class="doc_type" value="Penyajian Statistik Data">
                    <input type="hidden" name="doc_category" id="doc_category" value="">
                    <div class="row ps-2 pe-2 g-3">
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label class="form-label">Period</label>
                                <input type="month" class="form-control" name="period" placeholder="Period Bulan" required>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control rounded" name="syncdate1" placeholder="Tanggal Tarikan Data" required>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label class="form-label">Tanggal Selesai</label>
                                <input type="date" class="form-control rounded" name="syncdate2" placeholder="Tanggal Tarikan Data" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label">Remarks</label>
                                <input type="text" class="form-control" name="remarks" placeholder="Keterangan Tambahan">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="upload-card mt-1">
                                <div class="upload-card-body text-center">
                                    <i class="bi bi-upload me-3 fs-3 text-muted"></i>
                                    <h5 class="mt-3">Seret & taruh file di sini, atau klik untuk unggah</h5>
                                    <p>Unduh format file <a href="#" id="modalSampleLink" target="_blank" rel="noopener">disini</a></p>
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
                <div class="table-responsive">
                    <table id="dataTableDetail" class="table table-bordered table-hover nowrap">
                        <thead id="dataTableDetailHead"></thead>
                        <tbody></tbody>
                    </table>
                </div>
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
<script src="<?= asset_url('apps/assets/js/custom/pages/services/statistikdata/main.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/statistikdata/tables.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/statistikdata/detail.js') ?>"></script>
<?= $this->endSection(); ?>
