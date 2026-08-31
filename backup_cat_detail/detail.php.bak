<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>
<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/service-table-ui.css?v=99') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/cat/detail.css') ?>">
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>
<main class="page-content py-4">
    <div class="container-fluid text-start mx-auto" style="max-width: 1160px; padding: 0 .85rem 1.05rem;">
        <div class="page-heading mb-0">
            <div class="row align-items-center d-flex justify-content-between">
                <div class="col-md-6 text-start">
                    <h3 class=""><b><span class="badge bg-primary rounded mb-2" id="catDetailBadge">Titik Lokasi</span><br><span
                                id="catDetailTilok">-</span></b></h3>
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

        <div class="row">
            <div class="col-md-12">
                <div class="service-ui-topbar mb-3 service-ui-static-topbar">
                            <div class="d-flex align-items-center flex-nowrap gap-2">
                                <div class="dropdown">
                                    <button
                                        class="btn btn-outline-primary dropdown-toggle px-4 py-2 fw-semibold d-flex align-items-center gap-2"
                                        type="button" id="dropdownBulan" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        Pilih Bulan
                                    </button>

                                    <ul class="dropdown-menu shadow rounded-3 border-0 p-3 mt-2 service-ui-period-dropdown"
                                        id="bulanDropdown">
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
                                <button type="button" class="btn btn-outline-primary" disabled>
                                    <i class="bi bi-filetype-doc me-1"></i> <strong>Form Sarpras</strong>
                                </button>
                                <button type="button" class="btn btn-outline-primary" disabled>
                                    <i class="bi bi-filetype-doc me-1"></i> <strong>Form Survey</strong>
                                </button>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#DataModal">
                                    <i class="bi bi-plus me-2"></i>Tambah Rekap Data
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
                                        <th><strong>Instansi Name</strong></th>
                                        <th><strong>Tanggal</strong></th>
                                        <th><strong>Sesi</strong></th>
                                        <th><strong>Nilai Terendah</strong></th>
                                        <th><strong>Nilai Tertinggi</strong></th>
                                        <th><strong>Hadir</strong></th>
                                        <th><strong>Tidak Hadir</strong></th>
                                        <th><strong>Penjadwalan Ulang</strong></th>
                                        <th><strong>Total Peserta</strong></th>
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
                                        <th><strong>Sesi</strong></th>
                                        <th><strong>Nilai Terendah</strong></th>
                                        <th><strong>Nilai Tertinggi</strong></th>
                                        <th><strong>Hadir</strong></th>
                                        <th><strong>Tidak Hadir</strong></th>
                                        <th><strong>Penjadwalan Ulang</strong></th>
                                        <th><strong>Total Peserta</strong></th>
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

    <div class="modal fade modal-smooth" id="DataModal" tabindex="-1" aria-labelledby="DataModalLabelCreate"
        aria-hidden="true">
        <div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="DataModalLabelCreate">Data Rekap Fasilitasi CAT</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" id="form-usulan">
                        <input type="hidden" name="key" id="detailKeyFormCreate" value="">
                        <button type="button" class="btn btn-primary mb-4" id="addRowBtn">
                            <i class="bi bi-plus"></i> Tambah Baris
                        </button>
                        <table class="table table-bordered" id="usulanTable">
                            <thead>
                                <tr>
                                    <th>Instansi</th>
                                    <th>Tanggal</th>
                                    <th>Sesi</th>
                                    <th>Nilai Terendah</th>
                                    <th>Nilai Tertinggi</th>
                                    <th>Hadir</th>
                                    <th>Tidak Hadir</th>
                                    <th>Penjadwalan Ulang</th>
<th>Memenuhi (Lulus)</th>
<th>Tidak Memenuhi</th>
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

    <div class="modal fade" id="DataModalDetail" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-4" id="DataModalLabel">Tambah Data</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="form-usulan-edit" autocomplete="off">
                        <input type="hidden" name="key">
                        <div class="row ps-2 pe-2">
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="form-label mb-1">Instansi</label>
                                    <select name="instansi" class="form-select select-instansi" required></select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="form-label mb-1">Tanggal</label>
                                    <input type="date" class="form-control rounded" name="tanggal" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="form-label mb-1">Sesi</label>
                                    <input type="number" class="form-control rounded" name="sesi" placeholder="0"
                                        min="0" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-label mb-1">Nilai Terendah</label>
                                    <input type="number" class="form-control rounded" name="nilai_min" placeholder="0"
                                        min="0" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-label mb-1">Nilai Tertinggi</label>
                                    <input type="number" class="form-control rounded" name="nilai_max" placeholder="0"
                                        min="0" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-label mb-1">Hadir</label>
                                    <input type="number" class="form-control rounded" name="hadir" placeholder="0"
                                        min="0" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-label mb-1">Tidak Hadir</label>
                                    <input type="number" class="form-control rounded" name="tidak_hadir" placeholder="0"
                                        min="0" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-label mb-1">Penjadwalan Ulang</label>
                                    <input type="number" class="form-control rounded" name="reschedule" placeholder="0"
                                        min="0" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-label mb-1">Memenuhi (Lulus)</label>
                                    <input type="number" class="form-control rounded" name="memenuhi" placeholder="0"
                                        min="0" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-label mb-1">Tidak Memenuhi</label>
                                    <input type="number" class="form-control rounded" name="tidak_memenuhi" placeholder="0"
                                        min="0" required>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary sbmt-edit">Simpan Data</button>
                </div>
            </div>
        </div>
    </div>

</div>
<?= $this->endSection(); ?>
<?= $this->section('scripts'); ?>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/service-table-ui.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/cat/entryRekap.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/cat/tablesRekap.js') ?>"></script>
<?= $this->endSection(); ?>

