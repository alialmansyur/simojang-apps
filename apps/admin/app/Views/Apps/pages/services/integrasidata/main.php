<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>
<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/service-table-ui.css?v=99') ?>">
<link rel="stylesheet" href="<?= base_url('apps/assets/extensions/filepond/filepond.css'); ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/integrasidata/main.css') ?>">
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>
<div class="page-content py-4">
    <div class="container-fluid text-start mx-auto" style="padding: 0 .85rem 1.05rem; max-width: 1160px;">

        <div class="page-heading mb-0">
            <div class="row align-items-center d-flex justify-content-between">
                <div class="col-md-6 text-start">
                    <h3><b>Pendampingan Integrasi SIASN dengan SIMPEG Instansi Daerah</b></h3>
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
                            <input type="hidden" id="jenis" value=0>
                            <ul class="nav nav-pills" id="predikatTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active tab-btn" data-key="0" id="semua-tab"
                                        data-bs-toggle="pill" data-bs-target="#jabatan" type="button" role="tab"
                                        aria-controls="jabatan" aria-selected="true">
                                        Semua Riwayat
                                    </button>
                                </li>

                                <li class="nav-item" role="presentation">
                                    <button class="nav-link tab-btn" data-key="1" id="jabatan-tab" data-bs-toggle="pill"
                                        data-bs-target="#jabatan" type="button" role="tab" aria-controls="jabatan"
                                        aria-selected="false">
                                        Jabatan
                                    </button>
                                </li>

                                <li class="nav-item" role="presentation">
                                    <button class="nav-link tab-btn" data-key="2" id="diklat-tab" data-bs-toggle="pill"
                                        data-bs-target="#diklat" type="button" role="tab" aria-controls="diklat"
                                        aria-selected="false">
                                        Diklat
                                    </button>
                                </li>

                                <li class="nav-item" role="presentation">
                                    <button class="nav-link tab-btn" data-key="3" id="hukdis-tab" data-bs-toggle="pill"
                                        data-bs-target="#hukdis" type="button" role="tab" aria-controls="hukdis"
                                        aria-selected="false">
                                        Hukdis
                                    </button>
                                </li>

                                <li class="nav-item" role="presentation">
                                    <button class="nav-link tab-btn" data-key="4" id="cpns-pns-tab"
                                        data-bs-toggle="pill" data-bs-target="#cpns-pns" type="button" role="tab"
                                        aria-controls="cpns-pns" aria-selected="false">
                                        CPNS/PNS
                                    </button>
                                </li>

                                <li class="nav-item" role="presentation">
                                    <button class="nav-link tab-btn" data-key="5" id="penghargaan-tab"
                                        data-bs-toggle="pill" data-bs-target="#penghargaan" type="button" role="tab"
                                        aria-controls="penghargaan" aria-selected="false">
                                        Penghargaan
                                    </button>
                                </li>

                                <li class="nav-item" role="presentation">
                                    <button class="nav-link tab-btn" data-key="6" id="angka-kredit-tab"
                                        data-bs-toggle="pill" data-bs-target="#angka-kredit" type="button" role="tab"
                                        aria-controls="angka-kredit" aria-selected="false">
                                        Angka Kredit
                                    </button>
                                </li>

                                <li class="nav-item" role="presentation">
                                    <button class="nav-link tab-btn" data-key="7" id="kinerja-tab" data-bs-toggle="pill"
                                        data-bs-target="#kinerja" type="button" role="tab" aria-controls="kinerja"
                                        aria-selected="false">
                                        Kinerja
                                    </button>
                                </li>

                                <li class="nav-item" role="presentation">
                                    <button class="nav-link tab-btn" data-key="8" id="profil-tab" data-bs-toggle="pill"
                                        data-bs-target="#profil" type="button" role="tab" aria-controls="profil"
                                        aria-selected="false">
                                        Profil
                                    </button>
                                </li>
                            </ul>
                            <div class="d-flex align-items-center justify-content-end gap-2 flex-nowrap">
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal"
                                    data-bs-target="#DataModal"><i class="bi bi-plus-circle me-2"></i>Tambah
                                    Data</button>
                            </div>
                        </div>
                        
                        <div id="serviceUiRecap" class="row g-3 mb-4">
                            <div class="col-12 col-md-4">
                                <div class="card border h-100 mb-0 shadow-sm">
                                    <div class="card-body p-3 d-flex align-items-center gap-3">
                                        <span class="service-ui-recap-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"/><path d="M7 3v4"/><path d="M17 3v4"/><rect x="4" y="7" width="16" height="14" rx="2"/></svg></span>
                                        <div><p class="service-ui-recap-label mb-1">Total Data</p><h6 class="service-ui-recap-value mb-0" id="itg-total-data">0</h6></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="card border h-100 mb-0 shadow-sm">
                                    <div class="card-body p-3 d-flex align-items-center gap-3">
                                        <span class="service-ui-recap-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3v18h18"/><path d="M8 14l3-3 3 2 4-5"/></svg></span>
                                        <div><p class="service-ui-recap-label mb-1">Total Instansi</p><h6 class="service-ui-recap-value mb-0" id="itg-total-instansi">0</h6></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="card border h-100 mb-0 shadow-sm">
                                    <div class="card-body p-3 d-flex align-items-center gap-3">
                                        <span class="service-ui-recap-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
                                        <div><p class="service-ui-recap-label mb-1">Jenis Riwayat Aktif</p><h6 class="service-ui-recap-value mb-0" id="itg-total-riwayat">0</h6></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="card border h-100 mb-0 shadow-sm">
                                    <div class="card-body p-3 d-flex align-items-center gap-3">
                                        <span class="service-ui-recap-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"/><path d="M7 3v4"/><path d="M17 3v4"/><rect x="4" y="7" width="16" height="14" rx="2"/></svg></span>
                                        <div><p class="service-ui-recap-label mb-1">Cakupan Wilayah</p><h6 class="service-ui-recap-value mb-0" id="itg-total-wilayah">0</h6></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="card border h-100 mb-0 shadow-sm">
                                    <div class="card-body p-3 d-flex align-items-center gap-3">
                                        <span class="service-ui-recap-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3v18h18"/><path d="M8 14l3-3 3 2 4-5"/></svg></span>
                                        <div><p class="service-ui-recap-label mb-1">Data Ditampilkan</p><h6 class="service-ui-recap-value mb-0" id="itg-data-shown">0</h6></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="card border h-100 mb-0 shadow-sm">
                                    <div class="card-body p-3 d-flex align-items-center gap-3">
                                        <span class="service-ui-recap-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
                                        <div><p class="service-ui-recap-label mb-1">Update Terakhir</p><h6 class="service-ui-recap-value mb-0" id="itg-last-update">-</h6></div>
                                    </div>
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
                                        <th><strong></strong></th>
                                        <th><strong></strong></th>
                                        <th><strong>Instansi Nama</strong></th>
                                        <th><strong>Wilayah</strong></th>
                                        <th><strong>Tanggal</strong></th>
                                        <th><strong>Riwayat</strong></th>
                                        <th><strong>Remarks</strong></th>
                                        <th><strong>Bukti Dukung</strong></th>
                                        <th><strong>Diperbaharui</strong></th>
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
                                        <th><strong>Wilayah</strong></th>
                                        <th><strong>Tanggal</strong></th>
                                        <th><strong>Riwayat</strong></th>
                                        <th><strong>Remarks</strong></th>
                                        <th><strong>Bukti Dukung</strong></th>
                                        <th><strong>Diperbaharui</strong></th>
                                        <th><strong></strong></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="modal fade" id="DataModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-4" id="exampleModalFullscreenLabel">Tambah Data</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="form-usulan" autocomplete="off">
                        <input type="hidden" name="key" value="">
                        <div class="row ps-4 pe-4">
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="form-label mb-1">Instansi</label>
                                    <select name="instansi" class="form-select select-instansi" required></select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="form-label">Period</label>
                                    <input type="month" class="form-control rounded" name="period"
                                        placeholder="Period Bulan" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="form-label">Tanggal Data</label>
                                    <input type="date" class="form-control rounded" name="startdate"
                                        placeholder="Tanggal Tarikan Data" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="form-label mb-1">Riwayat Integrasi</label>
                                    <select name="riwayat[]" class="form-select select-step" required></select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="form-label">Remarks</label>
                                    <input type="text" class="form-control rounded" name="remarks"
                                        placeholder="Keterangan Tambahan">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="form-label">Bukti Dukung / URL Video</label>
                                    <input type="text" class="form-control rounded" name="video_url"
                                        placeholder="Bukti Dukung / URL Video">
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

</div>
<?= $this->endSection(); ?>
<?= $this->section('scripts'); ?>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/service-table-ui.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/integrasidata/main.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/integrasidata/tables.js') ?>?v=<?= time() ?>"></script>
<?= $this->endSection(); ?>
