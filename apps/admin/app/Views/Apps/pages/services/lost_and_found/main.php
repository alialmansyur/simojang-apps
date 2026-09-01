<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>
<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/service-table-ui.css?v=99') ?>">
<style>
.search-input-wrapper {
    position: relative;
    max-width: 400px;
    width: 100%;
}
.search-input-wrapper .bi-search {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #a0aec0;
}
.search-input-wrapper input {
    padding-left: 40px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}
.header-title-section h3 {
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 0.25rem;
}
.header-title-section p {
    color: #718096;
    margin-bottom: 0;
}
</style>
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>
<div class="page-content p-2 p-md-4">
    <div class="container-fluid mx-auto tw-wrap" style="max-width: 1160px;">
        <div class="page-heading mb-0">
            <div class="row align-items-center d-flex justify-content-between">
                <div class="col-md-6 text-start">
                    <h3 class="mt-3"><b>Informasi Barang Hilang</b></h3>
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

        <div class="row mb-3 mt-2">
            <div class="col-12">
                <div class="alert shadow-sm border-0 rounded-3 p-3 position-relative mb-0" style="background-color: #fffbe4; border-left: 6px solid #f59e0b !important;" role="alert">
                    <div class="row align-items-center g-0 pe-5">
                        <div class="col-auto pe-3">
                            <i class="bi bi-exclamation-triangle-fill" style="color: #d97706; font-size: 2.2rem; line-height: 1;"></i>
                        </div>
                        <div class="col">
                            <h6 class="fw-bold mb-1" style="color: #92400e; font-size: 1rem; line-height: 1.2;">Himbauan: Pencatatan &amp; Pengelolaan Barang Temuan (Lost &amp; Found)</h6>
                            <div style="font-size: 0.85rem; color: #b45309; line-height: 1.2;">Pastikan setiap barang temuan dicatat dengan informasi lokasi dan tanggal yang jelas serta dokumentasi foto untuk mempermudah proses klaim pemilik barang.</div>
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
                            <li><strong>Cari &amp; Periksa Data:</strong> Gunakan menu <em>Pilih Bulan</em> atau pencarian untuk memastikan barang temuan yang bersangkutan belum terdaftar dalam sistem.</li>
                            <li><strong>Pencatatan Barang Temuan:</strong> Klik tombol <strong>"Tambah Barang"</strong>, lengkapi <em>Nama Barang</em>, <em>Lokasi Ditemukan</em>, <em>Tanggal Ditemukan</em>, dan unggah foto dokumentasi barang.</li>
                            <li><strong>Pembaruan Status Penyerahan:</strong> Saat pemilik mengambil barang, ubah status menjadi <em>Diserahkan</em>, serta lengkapi <em>Tanggal Diserahkan</em> dan <em>Nama Penerima</em>.</li>
                            <li><strong>Verifikasi &amp; Dokumentasi:</strong> Simpan data untuk memperbarui riwayat serah terima barang temuan secara transparan dan akuntabel.</li>
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
                        <button type="button" class="btn btn-primary" id="btnAddData">
                            <i class="bi bi-plus me-2"></i>Tambah Barang
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
                        <div class="table-responsive mt-2">
                            <table id="dataTable" class="table table-bordered table-hover nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th><strong>ID</strong></th>
                                        <th><strong>Nama Barang</strong></th>
                                        <th><strong>Tgl Ditemukan</strong></th>
                                        <th><strong>Lokasi</strong></th>
                                        <th><strong>Status</strong></th>
                                        <th><strong>Tgl Diserahkan</strong></th>
                                        <th><strong>Penerima</strong></th>
                                        <th><strong>Keterangan</strong></th>
                                        <th><strong>Aksi</strong></th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Modal Form -->
        <div class="modal fade" id="DataModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
            aria-labelledby="DataModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content" style="border-radius: 10px !important;">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5 fw-bold" id="DataModalLabel">Form Barang Hilang</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="form-lostfound" autocomplete="off" enctype="multipart/form-data">
                            <input type="hidden" name="key" id="inputKey">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label class="form-label fw-bold">Nama Barang</label>
                                        <input type="text" class="form-control rounded" name="nama_barang" id="nama_barang" required>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label class="form-label fw-bold">Lokasi Ditemukan</label>
                                        <input type="text" class="form-control rounded" name="lokasi_ditemukan" id="lokasi_ditemukan" required>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label class="form-label fw-bold">Tanggal Ditemukan</label>
                                        <input type="date" class="form-control rounded" name="tanggal_ditemukan" id="tanggal_ditemukan" required>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label class="form-label fw-bold">Status Penyerahan</label>
                                        <select class="form-select rounded" name="status_penyerahan" id="status_penyerahan" required>
                                            <option value="Belum Diserahkan">Belum Diserahkan</option>
                                            <option value="Diserahkan">Diserahkan</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="form-label fw-bold">Upload Gambar</label>
                                        <div class="upload-card mt-2">
                                            <div class="upload-card-body text-center dropzone-area" id="dropzoneArea" style="border: 2px dashed #1040c1; border-radius: 8px; padding: 30px; cursor: pointer; transition: all 0.3s ease;">
                                                <h5 class="mt-2">Drag & Drop file Anda di sini</h5>
                                                <p class="text-muted mb-2">atau</p>
                                                <button type="button" class="btn btn-outline-primary btn-sm mb-3" id="btnBrowse">Pilih File</button>
                                                <p class="text-muted small mb-0">Maksimal 2MB (.jpg, .png, .webp)</p>
                                                
                                                <div id="filePreview" class="d-none mt-3 p-3 border rounded bg-light d-flex align-items-center justify-content-center">
                                                    <i class="bi bi-image fs-4 text-success me-2"></i>
                                                    <div class="text-start me-auto">
                                                        <span id="fileName" class="fw-bold d-block"></span>
                                                        <small id="fileSize" class="text-muted"></small>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-light text-danger ms-2" id="btnRemoveFile" aria-label="Remove">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                                <input type="file" class="d-none" name="gambar" id="gambarUpload" accept="image/png, image/jpeg, image/jpg, image/webp" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 section-diserahkan" style="display:none;">
                                    <div class="form-group">
                                        <label class="form-label fw-bold">Tanggal Diserahkan</label>
                                        <input type="date" class="form-control rounded" name="tanggal_diserahkan" id="tanggal_diserahkan">
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 section-diserahkan" style="display:none;">
                                    <div class="form-group">
                                        <label class="form-label fw-bold">Penerima</label>
                                        <input type="text" class="form-control rounded" name="penerima" id="penerima">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="form-label fw-bold">Keterangan</label>
                                        <textarea class="form-control rounded" name="keterangan" id="keterangan" rows="2"></textarea>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" id="btnSubmitForm">Simpan Data</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Image Preview Modal -->
<div class="modal fade" id="ImagePreviewModal" tabindex="-1" aria-labelledby="ImagePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 10px !important;">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="ImagePreviewModalLabel">Preview Foto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-3 bg-light">
                <img id="previewModalImage" src="" class="img-fluid rounded shadow-sm" alt="Preview Foto" style="max-height: 70vh; object-fit: contain;">
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
<?= $this->section('scripts'); ?>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/service-table-ui.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/lost_and_found/main.js') ?>"></script>
<?= $this->endSection(); ?>
