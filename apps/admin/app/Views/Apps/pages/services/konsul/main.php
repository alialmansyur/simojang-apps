<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>
<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/service-table-ui.css?v=99') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/konsul/main.css') ?>">
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>
<div class="page-content p-2 p-md-4">
    <div class="container-fluid text-start mx-auto tw-wrap" style="max-width: 1160px;">
        <div class="page-heading mb-0">
            <div class="row align-items-center d-flex justify-content-between">
                <div class="col-md-6 text-start">
                    <h3 class="mt-3"><b>Pelayanan Konsultasi</b></h3>
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
                            <h6 class="fw-bold mb-1" style="color: #92400e; font-size: 1rem; line-height: 1.2;">Himbauan: Pencatatan Layanan Konsultasi Kepegawaian</h6>
                            <div style="font-size: 0.85rem; color: #b45309; line-height: 1.2;">Pastikan setiap rekap layanan konsultasi dicatat sesuai kanal layanan yang digunakan serta periode waktu pelaksanaan yang valid.</div>
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
                            <li><strong>Periksa Periode &amp; Kanal:</strong> Gunakan filter <em>Pilih Bulan</em> atau pencarian untuk memastikan rekap konsultasi pada kanal dan periode terkait belum tercatat.</li>
                            <li><strong>Entri Data Konsultasi:</strong> Klik tombol <strong>"Tambah Data"</strong>, tentukan <em>Period</em>, <em>Rentang Tanggal</em>, dan pilih <em>Jenis Layanan Konsultasi</em> (PTSP, WhatsApp, Helpdesk, Media Sosial).</li>
                            <li><strong>Input Jumlah &amp; Catatan:</strong> Masukkan total <em>Jumlah Pelayanan</em> yang terselesaikan beserta catatan ringkas terkait substansi konsultasi.</li>
                            <li><strong>Simpan &amp; Evaluasi:</strong> Klik <strong>"Simpan Data"</strong> untuk memperbarui data rekapitulasi pelayanan konsultasi di Kanreg III BKN.</li>
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
                        
                        <div id="activeFilterContainer" class="active-filters-container my-3 align-items-center flex-wrap gap-2" style="display: none;">
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted small me-2"><i class="bi bi-funnel me-1"></i>Filter Aktif:</span>
                                <div class="active-filters-list d-flex flex-wrap gap-2"></div>
                            </div>
                        </div>

                          <div class="card border shadow-sm">
                              <div class="card-body p-3">
                                  <div class="table-responsive">
                            <table id="dataTable" class="table table-bordered table-hover nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th><strong></strong></th>
                                        <th><strong>Period</strong></th>
                                        <th><strong>Tanggal Mulai</strong></th>
                                        <th><strong>Tanggal Selesai</strong></th>
                                        <th><strong>Pelayanan</strong></th>
                                        <th><strong>Jumlah</strong></th>
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
                                        <th><strong>Pelayanan</strong></th>
                                        <th><strong>Jumlah</strong></th>
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
                                    <label class="form-label fw-bold">Jenis Layanan Konsultasi</label>
                                    <select name="media" class="form-select rounded" required>
                                        <option value="">-- Pilih Jenis Layanan --</option>
                                        <option value="PTSP">PTSP</option>
                                        <option value="WhatsApp">WhatsApp</option>
                                        <option value="Helpdesk">Helpdesk</option>
                                        <option value="Media Sosial">Media Sosial</option>
                                        <!-- <optgroup label="PTSP">
                                            <option value="PTSP - Kepegawaian">PTSP - Kepegawaian</option>
                                            <option value="PTSP - Kenaikan Pangkat">PTSP - Kenaikan Pangkat</option>
                                            <option value="PTSP - Pensiun">PTSP - Pensiun</option>
                                            <option value="PTSP - Mutasi & Promosi">PTSP - Mutasi & Promosi</option>
                                        </optgroup>

                                        <optgroup label="WhatsApp">
                                            <option value="WhatsApp - Konsultasi Umum">WhatsApp - Konsultasi Umum</option>
                                            <option value="WhatsApp - Kepegawaian">WhatsApp - Kepegawaian</option>
                                            <option value="WhatsApp - Pengaduan">WhatsApp - Pengaduan</option>
                                        </optgroup>

                                        <optgroup label="Helpdesk">
                                            <option value="Helpdesk - Aplikasi">Helpdesk - Aplikasi</option>
                                            <option value="Helpdesk - Akun & Akses">Helpdesk - Akun & Akses</option>
                                            <option value="Helpdesk - Error Sistem">Helpdesk - Error Sistem</option>
                                        </optgroup>

                                        <optgroup label="Media Sosial">
                                            <option value="Instagram">Instagram</option>
                                            <option value="Facebook">Facebook</option>
                                            <option value="X / Twitter">X / Twitter</option>
                                            <option value="TikTok">TikTok</option>
                                        </optgroup>

                                        <optgroup label="SP4N LAPOR!">
                                            <option value="Pengaduan">Pengaduan</option>
                                            <option value="Aspirasi">Aspirasi</option>
                                            <option value="Permintaan Informasi">Permintaan Informasi</option>
                                        </optgroup> -->

                                    </select>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="form-label fw-bold">Jumlah Pelayanan</label>
                                        <input type="number" class="form-control rounded" name="summary"
                                            placeholder="Contoh : 10" required>
                                    </div>
                                </div>
                                <div class="col-12 mt-2">
                                    <label class="form-label fw-bold">Catatan</label>
                                    <textarea class="form-control rounded" name="notes" rows=4></textarea>
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
<script src="<?= asset_url('apps/assets/js/custom/pages/services/konsul/main.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/konsul/tables.js') ?>"></script>
<?= $this->endSection(); ?>
