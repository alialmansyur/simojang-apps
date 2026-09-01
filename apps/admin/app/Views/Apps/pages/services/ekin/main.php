<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>
<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/service-table-ui.css?v=99') ?>">

<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/ekin/main.css') ?>">
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>
<div class="page-content p-2 p-md-4">
    <div class="container-fluid text-start mx-auto tw-wrap" style="max-width: 1160px;">
        <div class="page-heading mb-0">
            <div class="row align-items-center d-flex justify-content-between">
                <div class="col-md-6 text-start">
                    <h3 class="mt-3"><b>Manajemen E-Kinerja</b></h3>
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

        <div class="row mb-3 mt-4">
            <div class="col-12">
                <div class="alert shadow-sm border-0 rounded-3 p-3 position-relative" style="background-color: #fffbe4; border-left: 6px solid #f59e0b !important;" role="alert">
                    <div class="row align-items-center g-0 pe-5">
                        <div class="col-auto pe-3">
                            <i class="bi bi-exclamation-triangle-fill" style="color: #d97706; font-size: 2.2rem; line-height: 1;"></i>
                        </div>
                        <div class="col">
                            <h6 class="fw-bold mb-1" style="color: #92400e; font-size: 1rem; line-height: 1.2;">Himbauan: Ketentuan &amp; Format Unggah Data E-Kinerja</h6>
                            <div style="font-size: 0.85rem; color: #b45309; line-height: 1.2;">Pastikan berkas tarikan data E-Kinerja yang diunggah sesuai dengan periode harian dan menggunakan format template Excel resmi yang berlaku.</div>
                        </div>
                    </div>
                    
                    <div class="position-absolute top-0 end-0 p-3">
                        <button class="btn btn-sm text-nowrap fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#tataCaraCollapse" aria-expanded="false" aria-controls="tataCaraCollapse" style="background-color: #fef3c7; color: #92400e; border: 1px solid #fcd34d;">
                            <i class="bi bi-info-circle me-1"></i> Tata Cara
                        </button>
                    </div>
                    
                    <div class="collapse" id="tataCaraCollapse">
                        <hr style="border-color: #f59e0b; opacity: 0.2; margin-top: 1rem; margin-bottom: 1rem;">
                        <ol class="mb-0 ps-3" style="font-size: 0.85rem; line-height: 1.7; color: #78350f;">
                            <li><strong>Periksa Periode Data:</strong> Gunakan menu <em>Pilih Bulan</em> atau pencarian untuk memeriksa apakah data E-Kinerja pada periode yang bersangkutan sudah pernah diunggah sebelumnya.</li>
                            <li><strong>Unduh Format Template:</strong> Jika belum memiliki format resmi, unduh template melalui tautan <em>Unduh format file</em> pada modal unggah data.</li>
                            <li><strong>Unggah Data E-Kinerja:</strong> Klik tombol <strong>"Tambah Data"</strong>, isi <em>Period</em>, <em>Tanggal Tarikan Data</em>, <em>Keterangan</em>, lalu lampirkan file Excel (.xls/.xlsx).</li>
                            <li><strong>Lihat Rincian Kegiatan:</strong> Klik baris data pada tabel untuk menampilkan rincian capaian kegiatan harian, NIP pegawai, dan realisasi kinerja.</li>
                        </ol>
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
                        
                        <div id="activeFilterContainer" class="active-filters-container my-3 align-items-center flex-wrap gap-2" style="display: none;">
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
                                        <th></th>
                                        <th>Periode Harian</th>
                                        <th>Total Upload</th>
                                        <th>Total Sub Unit</th>
                                        <th>Total NIP</th>
                                        <th>Total Kegiatan</th>
                                        <th>Total Realisasi</th>
                                        <th>Upload Terakhir</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th></th>
                                        <th>Periode Harian</th>
                                        <th>Total Upload</th>
                                        <th>Total Sub Unit</th>
                                        <th>Total NIP</th>
                                        <th>Total Kegiatan</th>
                                        <th>Total Realisasi</th>
                                        <th>Upload Terakhir</th>
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
                                <div class="upload-card-body text-center dropzone-area" id="dropzoneArea" style="border: 2px dashed #1040c1; border-radius: 8px; padding: 30px; cursor: pointer; transition: all 0.3s ease;">
                                    <h5 class="mt-2">Drag & Drop file Anda di sini</h5>
                                    <p class="text-muted mb-2">atau</p>
                                    <button type="button" class="btn btn-outline-primary btn-sm mb-3" id="btnBrowse">Pilih File</button>
                                    <p class="text-muted small mb-0">Hanya file Excel (.xls, .xlsx)</p>
                                    
                                    <div id="filePreview" class="d-none mt-3 p-3 border rounded bg-light d-flex align-items-center justify-content-center">
                                        <i class="bi bi-file-text fs-4 text-success me-2"></i>
                                        <div class="text-start me-auto">
                                            <span id="fileName" class="fw-bold d-block"></span>
                                            <small id="fileSize" class="text-muted"></small>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-light text-danger ms-2" id="btnRemoveFile" aria-label="Remove">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                    <input type="file" class="d-none" name="file" id="excelUpload" accept=".xls,.xlsx" />
                                </div>
                            </div>
                            <div class="text-center mt-3">
                                <p class="text-muted">Unduh format file <a href="<?= base_url('apps/samples/sample-ekin.xlsx') ?>" download><strong>di sini</strong></a></p>
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
                            <th class="text-center">NIP</th>
                            <th class="text-center">Nama</th>
                            <th class="text-center">Waktu</th>
                            <th class="text-center">Kegiatan</th>
                            <th class="text-center">Realisasi</th>
                            <th class="text-center">PIC</th>
                            <th class="text-center">Tanggal Upload</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <th class="text-center">NIP</th>
                            <th class="text-center">Nama</th>
                            <th class="text-center">Waktu</th>
                            <th class="text-center">Kegiatan</th>
                            <th class="text-center">Realisasi</th>
                            <th class="text-center">PIC</th>
                            <th class="text-center">Tanggal Upload</th>
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

<script src="<?= asset_url('apps/assets/js/custom/pages/services/service-table-ui.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/ekin/main.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/ekin/tables.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/ekin/detail.js') ?>"></script>
<?= $this->endSection(); ?>
