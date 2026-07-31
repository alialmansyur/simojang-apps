<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>
<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/service-table-ui.css?v=99') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/pembinaankompetensikarier/main.css') ?>">
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
                        <button type="button" class="btn btn-outline-primary js-service-reload" id="pkkReload">
                            <i class="bi bi-arrow-clockwise me-1"></i> Muat Ulang
                        </button>
                        <a href="javascript:history.back()" class="btn btn-primary">
                            <i class="bi bi-chevron-left fs-6"></i> <strong>Kembali</strong>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="service-ui-topbar mb-3 service-ui-static-topbar pkk-topbar">
            <div class="service-ui-topbar-filters module-filter-row d-flex align-items-center gap-2 pkk-topbar-group">
                <select id="pkkYearFilter" class="form-select form-select-sm w-auto pkk-filter-year"></select>
                <div class="dropdown">
                    <button
                        class="btn btn-outline-primary dropdown-toggle px-3 fw-semibold d-flex align-items-center gap-2 w-auto"
                        type="button" id="pkkMonthDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false">
                        Pilih Bulan
                    </button>
                    <ul class="dropdown-menu shadow rounded-3 border-0 p-3 mt-2 service-ui-period-dropdown" id="pkkMonthDropdown">
                        <div id="pkkMonthList"></div>
                        <li><hr class="dropdown-divider my-2"></li>
                        <li>
                            <button type="button" class="btn btn-primary w-100 fw-semibold" id="pkkApplyMonth">
                                <i class="bi bi-check-circle me-1"></i> Terapkan
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="service-ui-topbar-actions pkk-topbar-actions">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#pkkDataModal">
                    <i class="bi bi-plus me-1"></i> Tambah Data
                </button>
            </div>
        </div>

        <div class="active-filters-container mt-4 mb-3 align-items-center flex-wrap gap-2" id="activeFilterContainer" style="display: none;">
            <span class="text-muted small me-2"><i class="bi bi-funnel me-1"></i>Filter Aktif:</span>
        </div>

        <div class="card border">
            <div class="card-body p-3">
                <div class="table-responsive">
                            <table id="pkkTable" class="table table-bordered table-hover nowrap">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Judul</th>
                                        <th>Materi</th>
                                        <th>Tanggal</th>
                                        <th>Jumlah Peserta</th>
                                        <th>Metode</th>
                                        <th>Eviden</th>
                                        <th>Diperbaharui</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        <div class="modal fade" id="pkkDataModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="pkkDataModalLabel">Tambah Data Pengembangan Kompetensi</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="pkkForm" autocomplete="off">
                            <input type="hidden" name="key" value="">
                            <div class="row ps-2 pe-2 g-2">
                                <div class="col-12">
                                    <label class="form-label mb-1">Judul Kegiatan</label>
                                    <input type="text" class="form-control" name="judul_kegiatan" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label mb-1">Materi</label>
                                    <textarea class="form-control" name="materi" rows="3" required></textarea>
                                </div>
                                <div class="col-6">
                                    <label class="form-label mb-1">Tahun Periode</label>
                                    <input type="number" class="form-control" name="period_year" min="2001" max="2100" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label mb-1">Tanggal Kegiatan</label>
                                    <input type="date" class="form-control" name="tanggal_kegiatan" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label mb-1">Total Partisipan</label>
                                    <input type="number" class="form-control" name="total_partisipan" min="0" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label mb-1">Metode</label>
                                    <select name="metode" class="form-select" required>
                                        <option value="">Pilih Metode</option>
                                        <option value="Tatap Muka">Tatap Muka</option>
                                        <option value="Hybrid">Hybrid</option>
                                        <option value="Online">Online</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label mb-1">Lokasi</label>
                                    <input type="text" class="form-control" name="lokasi" placeholder="Lokasi kegiatan...">
                                </div>
                                <div class="col-6">
                                    <label class="form-label mb-1">Penyelenggara</label>
                                    <input type="text" class="form-control" name="penyelenggara" placeholder="Penyelenggara...">
                                </div>
                                <div class="col-12">
                                    <label class="form-label mb-1">Eviden (Link)</label>
                                    <input type="url" class="form-control" name="eviden_link" placeholder="https://...">
                                </div>
                                <div class="col-12">
                                    <label class="form-label mb-1">Catatan</label>
                                    <textarea class="form-control" rows="2" name="catatan" placeholder="Catatan opsional..."></textarea>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" id="pkkSubmitBtn">Simpan Data</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>
<?= $this->section('scripts'); ?>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/service-table-ui.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/pembinaankompetensikarier/main.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/pembinaankompetensikarier/tables.js') ?>"></script>
<?= $this->endSection(); ?>
