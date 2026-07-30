<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>
<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/service-table-ui.css?v=2') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/wasdal/main.css') ?>">
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>
<div class="page-content py-4">
    <div class="container-fluid text-start mx-auto tw-wrap" style="max-width: 1160px; padding: 0 .85rem 1.05rem;">
        <div class="page-heading mb-0">
            <div class="row align-items-center d-flex justify-content-between">
                <div class="col-md-6 text-start">
                    <h3 class="mt-3"><b>Pengawasan Penerapan Netralitas, Disiplin, Kode Etik dan Perilaku</b></h3>
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
                <div class="service-ui-topbar service-ui-static-topbar wasdal-topbar">
                            <div class="service-ui-topbar-filters wasdal-filter-row">
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
                            </div>
                            <div class="service-ui-topbar-actions wasdal-action-row">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#DataModal"><i
                                        class="bi bi-plus me-2"></i>Tambah Data</button>
                            </div>
                        </div>
                        <div id="wasdalSummary" class="service-ui-recap mb-3">
                          <div class="card border">
                              <div class="card-body p-3">
                                  <div class="service-ui-recap-card">
                                <span class="service-ui-recap-icon">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"/><path d="M7 3v4"/><path d="M17 3v4"/><rect x="4" y="7" width="16" height="14" rx="2"/></svg>
                                </span>
                                <div>
                                    <p class="service-ui-recap-label">Total Data</p>
                                    <h6 class="service-ui-recap-value js-total-data">0</h6>
                                </div>
                            </div>
                            <div class="service-ui-recap-card">
                                <span class="service-ui-recap-icon">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3h18v18H3z"/><path d="M7 7h10"/><path d="M7 12h10"/><path d="M7 17h6"/></svg>
                                </span>
                                <div>
                                    <p class="service-ui-recap-label">Total Kasus</p>
                                    <h6 class="service-ui-recap-value js-total-kasus">0</h6>
                                </div>
                            </div>
                            <div class="service-ui-recap-card">
                                <span class="service-ui-recap-icon">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                </span>
                                <div>
                                    <p class="service-ui-recap-label">Instansi Aktif</p>
                                    <h6 class="service-ui-recap-value js-total-instansi">0</h6>
                                </div>
                            </div>
                            <div class="service-ui-recap-card">
                                <span class="service-ui-recap-icon">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3v18h18"/><path d="M7 15l4-4 3 2 4-5"/></svg>
                                </span>
                                <div>
                                    <p class="service-ui-recap-label">Top Instansi</p>
                                    <h6 class="service-ui-recap-value js-top-instansi">-</h6>
                                </div>
                            </div>
                            <div class="service-ui-recap-card">
                                <span class="service-ui-recap-icon">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l8 4v6c0 5-3.5 8-8 10-4.5-2-8-5-8-10V6z"/><path d="M9 12h6"/></svg>
                                </span>
                                <div>
                                    <p class="service-ui-recap-label">Top Permasalahan</p>
                                    <h6 class="service-ui-recap-value js-top-permasalahan">-</h6>
                                </div>
                            </div>
                            <div class="service-ui-recap-card">
                                <span class="service-ui-recap-icon">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                                </span>
                                <div>
                                    <p class="service-ui-recap-label">Update Terakhir</p>
                                    <h6 class="service-ui-recap-value js-last-update">-</h6>
                                </div>
                            </div>
                        </div>
                                  <div class="table-responsive">
                            <table id="dataTable" class="table table-bordered table-hover nowrap">
                                <thead>
                                    <tr>
                                        <th><strong></strong></th>
                                        <th><strong></strong></th>
                                        <th><strong>Instansi Nama</strong></th>
                                        <th><strong>Periode</strong></th>
                                        <th><strong>Tanggal Mulai</strong></th>
                                        <th><strong>Tanggal Selesai</strong></th>
                                        <th><strong>Permasalahan</strong></th>
                                        <th><strong>Total</strong></th>
                                        <th><strong>Create By</strong></th>
                                        <th><strong>Create Date</strong></th>
                                        <th><strong></strong></th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th><strong></strong></th>
                                        <th><strong></strong></th>
                                        <th><strong>Instansi Nama</strong></th>
                                        <th><strong>Periode</strong></th>
                                        <th><strong>Tanggal Mulai</strong></th>
                                        <th><strong>Tanggal Selesai</strong></th>
                                        <th><strong>Permasalahan</strong></th>
                                        <th><strong>Total</strong></th>
                                        <th><strong>Create By</strong></th>
                                        <th><strong>Create Date</strong></th>
                                        <th><strong></strong></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="modal fade" id="DataModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
            aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-4" id="DataModalLabel">Tambah Data</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="form-usulan" autocomplete="off">
                            <input type="hidden" name="key">
                            <div class="row ps-2 pe-2">
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
                                        <label class="form-label mb-1">Instansi</label>
                                        <select name="instansi" class="form-select rounded select-instansi" required></select>
                                    </div>
                                </div>                                
                                <div class="col-12">
                                    <label class="form-label fw-bold">Permasalahan</label>
                                    <input type="text" class="form-control rounded" name="permasalahan"
                                        placeholder="Contoh : Netraliras" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Total</label>
                                    <input type="number" class="form-control rounded" name="total"
                                        placeholder="Jumlah Total" required>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-submit-form">Simpan Data</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>
<?= $this->section('scripts'); ?>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/wasdal/main.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/wasdal/tables.js') ?>"></script>
<?= $this->endSection(); ?>
