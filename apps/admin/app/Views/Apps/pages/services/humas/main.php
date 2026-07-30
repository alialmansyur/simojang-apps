<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>
<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/service-table-ui.css?v=2') ?>">
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/humas/main.css') ?>">
<div class="page-content py-4">
    <div class="container-fluid text-start mx-auto tw-wrap" style="max-width: 1160px; padding: 0 .85rem 1.05rem;">
        <div class="page-heading mb-0">
            <div class="row align-items-center d-flex justify-content-between">
                <div class="col-md-6 text-start">
                    <h3 class="mt-3"><b>Kehumasan &amp; Publikasi</b></h3>
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

                                <ul class="dropdown-menu shadow rounded-3 border-0 p-3 mt-2" id="bulanDropdown"
                                    style="min-width: 180px; border-radius:1.25em;">
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
                            <table id="dataTable" class="table table-bordered table-hover nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th><strong></strong></th>
                                        <th><strong>Period</strong></th>
                                        <th><strong>Tanggal Mulai</strong></th>
                                        <th><strong>Tanggal Selesai</strong></th>
                                        <th><strong>Media</strong></th>
                                        <th><strong>Konten</strong></th>
                                        <th><strong>Followers</strong></th>
                                        <!-- <th><strong>Viewers</strong></th> -->
                                        <th><strong>Created By</strong></th>
                                        <th><strong>Created Date</strong></th>
                                        <th><strong></strong></th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th><strong></strong></th>
                                        <th><strong>Period</strong></th>
                                        <th><strong>Tanggal Mulai</strong></th>
                                        <th><strong>Tanggal Selesai</strong></th>
                                        <th><strong>Media</strong></th>
                                        <th><strong>Konten</strong></th>
                                        <th><strong>Followers</strong></th>
                                        <!-- <th><strong>Viewers</strong></th> -->
                                        <th><strong>Created By</strong></th>
                                        <th><strong>Created Date</strong></th>
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
                <div class="modal-content" style="border-radius: 0px !important;">
                    <div class="modal-header">
                        <h1 class="modal-title fs-4" id="DataModalLabel">Tambah Data</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="form-usulan" autocomplete="off"> 
                            <input type="hidden" name="key">
                            <div class="row g-3">
                                <div class="col-12 col-md-4">
                                    <div class="form-group">
                                        <label class="form-label fw-bold">Period</label>
                                        <input type="month" class="form-control rounded period" name="period"
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
                                <div class="col-12 mt-1">
                                    <label class="form-label fw-bold">Media Konten</label>
                                    <select name="media" class="form-select rounded" required>
                                        <option value="">-- Pilih Media Sosial --</option>
                                        <option value="instagram">Instagram</option>
                                        <option value="facebook">Facebook</option>
                                        <option value="twitter">X (Twitter)</option>
                                        <option value="tiktok">TikTok</option>
                                        <option value="youtube">YouTube</option>
                                        <option value="linkedin">LinkedIn</option>
                                        <option value="website">Website</option>
                                        <option value="lainnya">Lainnya</option>
                                    </select>
                                </div>
                                <div class="col-12 mt-2">
                                    <div class="p-3 rounded bg-white border">
                                        <label class="form-label fw-bold d-block mb-2">Realisasi</label>
                                        <div class="row g-2">

                                            <div class="col-md-6">
                                                <label class="form-label">Jumlah Konten</label>
                                                <input type="text" class="form-control rounded number-format"
                                                    name="contens" placeholder="Contoh: 1,250">
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">Jumlah Followers</label>
                                                <input type="text" class="form-control rounded number-format"
                                                    name="followers" placeholder="Contoh: 25,000">
                                            </div>

                                            <div class="col-md-4 d-none">
                                                <label class="form-label">Jumlah Viewers</label>
                                                <input type="text" class="form-control rounded number-format"
                                                    name="viewers" placeholder="Contoh: 120,500">
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 mt-2">
                                    <label class="form-label fw-bold">Catatan</label>
                                    <textarea class="form-control rounded" name="notes"></textarea>
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
<script src="<?= asset_url('apps/assets/js/custom/pages/services/service-table-ui.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/humas/main.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/humas/tables.js') ?>"></script>
<?= $this->endSection(); ?>
