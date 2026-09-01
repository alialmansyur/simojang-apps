<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>
<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/service-table-ui.css?v=99') ?>">
<style>
/* Custom styles if needed for Karier */
.service-ui-topbar { margin-bottom: 1rem; }
.kpi-card { border-radius: 8px; padding: 1.5rem; background: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
.kpi-title { font-size: 0.85rem; color: #6c757d; font-weight: 600; text-transform: uppercase; }
.kpi-value { font-size: 1.8rem; font-weight: 700; color: #1040c1; margin-top: 0.5rem; }
</style>
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>
<main class="page-content p-2 p-md-4">
    <div class="container-fluid" style="max-width: 1160px; margin: 0 auto;">
        <div class="page-heading mb-0">
            <div class="row align-items-center d-flex justify-content-between">
                <div class="col-md-6 text-start">
                    <h3 class=""><b><span class="badge bg-primary rounded mb-2" id="karierBadge">Karier</span><br><span>Data Pembinaan Kompetensi Karier</span></b></h3>
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
                            <h6 class="fw-bold mb-1" style="color: #92400e; font-size: 0.95rem; line-height: 1.3;">Himbauan: Pencatatan &amp; Evaluasi Uji Kompetensi / Penilaian Karier ASN</h6>
                            <div style="font-size: 0.85rem; color: #b45309; line-height: 1.35;">Pastikan hasil asesmen dan uji kompetensi jabatan diinputkan lengkap dengan rincian kelulusan serta dokumen berita acara yang sah.</div>
                        </div>
                    </div>
                    
                    <div class="position-absolute top-0 end-0 p-3">
                        <button class="btn btn-sm text-nowrap fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#tataCaraCollapse" aria-expanded="false" aria-controls="tataCaraCollapse" style="background-color: #fef3c7; color: #92400e; border: 1px solid #fcd34d;">
                            <i class="bi bi-info-circle me-1"></i> Tata Cara
                        </button>
                    </div>
                    
                    <div class="collapse" id="tataCaraCollapse">
                        <hr style="border-color: #f59e0b; opacity: 0.2; margin-top: 0.85rem; margin-bottom: 0.85rem;">
                        <ol class="mb-0 ps-3" style="font-size: 0.85rem; line-height: 1.7; color: #78350f;">
                            <li><strong>Filter Periode &amp; Tinjau Rekap:</strong> Gunakan filter <em>Pilih Bulan</em> untuk menyaring riwayat penilaian serta tinjau rasio kelulusan peserta pada kartu rekapitulasi.</li>
                            <li><strong>Entri Data Penilaian Baru:</strong> Klik tombol <strong>"Tambah Data"</strong>, tentukan <em>Instansi</em>, <em>Tanggal Pelaksanaan</em>, dan <em>Jenis Penilaian</em>.</li>
                            <li><strong>Input Hasil Kelulusan / Import Excel:</strong> Masukkan jumlah peserta (Memenuhi/Lulus, Tidak Memenuhi, Tidak Hadir) atau gunakan <strong>"Import Excel"</strong> menggunakan template yang disediakan.</li>
                            <li><strong>Simpan &amp; Verifikasi:</strong> Klik <strong>"Simpan Data"</strong> untuk memperbarui rekapitulasi pembinaan kompetensi karier ASN di Kanreg III BKN.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="service-ui-topbar mb-3 service-ui-static-topbar">
                    <div class="d-flex align-items-center flex-nowrap gap-2">
                        <div class="dropdown">
                            <button class="btn btn-outline-primary dropdown-toggle px-4 py-2 fw-semibold d-flex align-items-center gap-2"
                                type="button" id="dropdownBulan" data-bs-toggle="dropdown" aria-expanded="false">
                                Pilih Bulan
                            </button>
                            <ul class="dropdown-menu shadow rounded-3 border-0 p-3 mt-2 service-ui-period-dropdown" id="bulanDropdown">
                                <div id="bulanList"></div>
                                <li><hr class="dropdown-divider my-2"></li>
                                <li>
                                    <button class="btn btn-primary w-100 fw-semibold" id="applyBulan">
                                        <i class="bi bi-check-circle me-1"></i> Terapkan
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-end gap-2 flex-nowrap">
                        <a href="<?= base_url('template/sample/template_import_karier.xlsx') ?>" target="_blank" class="btn btn-outline-success">
                            <i class="bi bi-download me-1"></i> <strong>Template Import</strong>
                        </a>
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#ImportModal">
                            <i class="bi bi-file-earmark-excel me-1"></i> <strong>Import Excel</strong>
                        </button>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#DataModal">
                            <i class="bi bi-plus me-2"></i>Tambah Data
                        </button>
                    </div>
                </div>

                <div id="karierdSummary" class="service-ui-recap mb-3">
                    <div class="service-ui-recap-card">
                        <span class="service-ui-recap-icon">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"/><path d="M7 3v4"/><path d="M17 3v4"/><rect x="4" y="7" width="16" height="14" rx="2"/></svg>
                        </span>
                        <div>
                            <p class="service-ui-recap-label">Total Rekap</p>
                            <h6 class="service-ui-recap-value" id="karierd-total-rekap">0</h6>
                        </div>
                    </div>
                    <div class="service-ui-recap-card">
                        <span class="service-ui-recap-icon">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                        </span>
                        <div>
                            <p class="service-ui-recap-label">Total Instansi</p>
                            <h6 class="service-ui-recap-value" id="karierd-total-instansi">0</h6>
                        </div>
                    </div>
                    <div class="service-ui-recap-card">
                        <span class="service-ui-recap-icon">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3h18v18H3z"/><path d="M7 7h10"/><path d="M7 12h10"/><path d="M7 17h6"/></svg>
                        </span>
                        <div>
                            <p class="service-ui-recap-label">Total Peserta</p>
                            <h6 class="service-ui-recap-value" id="karierd-total-peserta">0</h6>
                        </div>
                    </div>
                    <div class="service-ui-recap-card">
                        <span class="service-ui-recap-icon">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        </span>
                        <div>
                            <p class="service-ui-recap-label">Memenuhi / Lulus</p>
                            <h6 class="service-ui-recap-value" id="karierd-total-memenuhi">0</h6>
                        </div>
                    </div>
                    <div class="service-ui-recap-card">
                        <span class="service-ui-recap-icon">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                        </span>
                        <div>
                            <p class="service-ui-recap-label">Tdk Memenuhi / Tdk Lulus</p>
                            <h6 class="service-ui-recap-value" id="karierd-total-tidak-memenuhi">0</h6>
                        </div>
                    </div>
                    <div class="service-ui-recap-card">
                        <span class="service-ui-recap-icon">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                        </span>
                        <div>
                            <p class="service-ui-recap-label">Update Terakhir</p>
                            <h6 class="service-ui-recap-value" id="karierd-last-update">-</h6>
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
                                        <th><strong>Instansi Name</strong></th>
                                        <th><strong>Tanggal</strong></th>
                                        <th><strong>Jenis Penilaian</strong></th>
                                        <th><strong>Total Peserta</strong></th>
                                        <th><strong>Memenuhi</strong></th>
                                        <th><strong>Tdk Memenuhi</strong></th>
                                        <th><strong>Lulus</strong></th>
                                        <th><strong>Tdk Lulus</strong></th>
                                        <th><strong>Tdk Hadir</strong></th>
                                        <th><strong>Created By</strong></th>
                                        <th><strong>Created At</strong></th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th></th>
                                        <th><strong>Instansi Name</strong></th>
                                        <th><strong>Tanggal</strong></th>
                                        <th><strong>Jenis Penilaian</strong></th>
                                        <th><strong>Total Peserta</strong></th>
                                        <th><strong>Memenuhi</strong></th>
                                        <th><strong>Tdk Memenuhi</strong></th>
                                        <th><strong>Lulus</strong></th>
                                        <th><strong>Tdk Lulus</strong></th>
                                        <th><strong>Tdk Hadir</strong></th>
                                        <th><strong>Created By</strong></th>
                                        <th><strong>Created At</strong></th>
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

    <!-- Modal Form (Dynamic Append Row) -->
    <div class="modal fade modal-smooth" id="DataModal" tabindex="-1" aria-labelledby="DataModalLabelCreate" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="DataModalLabelCreate">Data Karier Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" id="form-usulan">
                        <button type="button" class="btn btn-primary mb-4" id="addRowBtn">
                            <i class="bi bi-plus"></i> Tambah Baris
                        </button>
                        <table class="table table-bordered" id="usulanTable">
                            <thead>
                                <tr>
                                    <th>Instansi</th>
                                    <th>Tanggal</th>
                                    <th>Jenis Penilaian</th>
                                    <th>Total Peserta</th>
                                    <th>Memenuhi</th>
                                    <th>Tdk Memenuhi</th>
                                    <th>Lulus</th>
                                    <th>Tdk Lulus</th>
                                    <th>Tdk Hadir</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="usulanTableBody"></tbody>
                        </table>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x d-block d-sm-none"></i>
                        <span class="d-none d-sm-block">Batalkan</span>
                    </button>
                    <button type="button" class="btn btn-primary ms-1 btn-submit-form">
                        <i class="bx bx-check d-block d-sm-none"></i>
                        <span class="d-none d-sm-block">Simpan</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Import -->
    <div class="modal fade" id="ImportModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-4">Import Data Excel</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="form-import" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Pilih File Excel (.xlsx, .xls)</label>
                            <input type="file" class="form-control" name="file" accept=".xlsx, .xls" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success btn-submit-import">Import Data</button>
                </div>
            </div>
        </div>
    </div>

</div>
<?= $this->endSection(); ?>
<?= $this->section('scripts'); ?>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/service-table-ui.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/karier/entry.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/karier/tables.js') ?>"></script>
<?= $this->endSection(); ?>
