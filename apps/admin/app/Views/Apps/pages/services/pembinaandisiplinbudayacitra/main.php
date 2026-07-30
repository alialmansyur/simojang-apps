<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>
<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/service-table-ui.css?v=2') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/pembinaandisiplinbudayacitra/main.css') ?>">
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>
<div class="page-content py-4">
    <div class="container-fluid text-start mx-auto tw-wrap" style="max-width: 1160px; padding: 0 .85rem 1.05rem;">
        <div class="page-heading mb-0">
            <div class="row align-items-center d-flex justify-content-between">
                <div class="col-md-8 text-start">
                    <h3 class="mt-3 mb-1"><b><?= $title; ?></b></h3>
                    <p class="text-subtitle text-muted">Kantor Regional III Badan Kepegawaian Negara</p>
                </div>
                <div class="col-md-4 text-md-end mt-2 mt-md-0">
                    <div class="service-page-inline-actions">
                        <button type="button" class="btn btn-outline-primary js-service-reload" id="pdbcReload">
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
                <div class="service-ui-topbar mb-3 service-ui-static-topbar pdbc-topbar">
                            <div class="service-ui-topbar-filters pdbc-topbar-group">
                                <div class="dropdown">
                                    <button
                                        class="btn btn-outline-primary dropdown-toggle px-3 fw-semibold d-flex align-items-center gap-2"
                                        type="button" id="pdbcMonthDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false">
                                        Pilih Bulan
                                    </button>
                                    <ul class="dropdown-menu shadow rounded-3 border-0 p-3 mt-2 service-ui-period-dropdown" id="pdbcMonthDropdown">
                                        <div id="pdbcMonthList"></div>
                                        <li><hr class="dropdown-divider my-2"></li>
                                        <li>
                                            <button type="button" class="btn btn-primary w-100 fw-semibold" id="pdbcApplyMonth">
                                                <i class="bi bi-check-circle me-1"></i> Terapkan
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                                <select id="pdbcJenisFilter" class="form-select form-select-sm pdbc-filter-jenis">
                                    <option value="ALL">Semua Jenis</option>
                                    <option value="ASISTENSI">Asistensi</option>
                                    <option value="KONSULTASI">Konsultasi</option>
                                    <option value="PEMBINAAN">Pembinaan</option>
                                </select>
                            </div>
                            <div class="service-ui-topbar-actions pdbc-topbar-actions">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#pdbcDataModal">
                                    <i class="bi bi-plus me-1"></i> Tambah Data
                                </button>
                            </div>
                        </div>

                        <div class="pdbc-category-overview" id="pdbcCategoryOverview"></div>
                          <div class="card border">
                              <div class="card-body p-3">
                                  <div class="table-responsive">
                            <table id="pdbcTable" class="table table-bordered table-hover nowrap">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th></th>
                                        <th>Instansi</th>
                                        <th>Total Riwayat</th>
                                        <th>Diperbaharui</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table> 
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="modal fade" id="pdbcDataModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="pdbcDataModalLabel">Tambah Data Pembinaan Disiplin</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="pdbcForm" autocomplete="off">
                            <input type="hidden" name="key" value="">
                            <div class="row ps-2 pe-2 g-2">
                                <div class="col-12">
                                    <label class="form-label mb-1">Kategori</label>
                                    <select name="kategori_id" id="pdbcKategoriInput" class="form-select" required></select>
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
                                <div class="col-12 pdbc-konsultasi-only d-none">
                                    <label class="form-label mb-1">Source Konsultasi</label>
                                    <select name="source_konsultasi" class="form-select">
                                        <option value="">Pilih Source</option>
                                        <option value="SURAT_MASUK">Surat Masuk</option>
                                        <option value="ZOOM">Zoom</option>
                                        <option value="PPT">PPT</option>
                                    </select>
                                </div>
                                <div class="col-12 pdbc-kegiatan-only d-none">
                                    <label class="form-label mb-1">Tempat Kegiatan</label>
                                    <input type="text" class="form-control" name="tempat_kegiatan" placeholder="Lokasi kegiatan...">
                                </div>
                                <div class="col-12 pdbc-kegiatan-only d-none">
                                    <label class="form-label mb-1">Judul Kegiatan</label>
                                    <input type="text" class="form-control" name="judul_kegiatan" placeholder="Judul kegiatan...">
                                </div>
                                <div class="col-12 pdbc-kegiatan-only d-none">
                                    <label class="form-label mb-1">No Surat Kegiatan</label>
                                    <input type="text" class="form-control" name="no_surat_kegiatan" placeholder="Nomor surat kegiatan...">
                                </div>
                                <div class="col-12 pdbc-kegiatan-only d-none">
                                    <label class="form-label mb-1">Pegawai Ditunjuk/Menghadiri</label>
                                    <select name="pegawai_ids[]" class="form-select select-pegawai" multiple="multiple"></select>
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
                        <button type="button" class="btn btn-primary" id="pdbcSubmitBtn">Simpan Data</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<?= $this->endSection(); ?>
<?= $this->section('scripts'); ?>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/service-table-ui.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/pembinaandisiplinbudayacitra/main.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/pembinaandisiplinbudayacitra/tables.js') ?>"></script>
<?= $this->endSection(); ?>
