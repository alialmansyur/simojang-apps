<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>
<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork-common.css') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork.css?v=99') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/service-table-ui.css?v=99') ?>">
<style>
    td.details-control {
        text-align: center;
        color: #0d6efd;
        cursor: pointer;
        width: 40px;
    }
    tr.shown td.details-control i {
        transform: rotate(90deg);
        transition: transform 0.2s;
    }
    td.details-control i {
        transition: transform 0.2s;
    }
    .nested-table-container {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        border: 1px solid #dee2e6;
        margin: 10px 0;
    }
    .nested-table-container table {
        background-color: #fff;
    }
</style>
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>
<main class="page-content" aria-labelledby="ikkPageTitle">
    <div class="text-start tw-wrap container-fluid">
        <div class="row align-items-center mt-4 mb-3" role="banner">
            <div class="col-12 col-md-8 text-start">
                <h1 class="tw-title lh-1 cat-page-title" id="ikkPageTitle">
                    Manajemen IKK
                </h1>
                <p class="tw-subtitle text-secondary mb-0 cat-page-subtitle">
                    Kelola daftar Sasaran Strategis, Indikator, Kegiatan, dan Transaksi Harian.
                </p>
            </div>
            <div class="col-12 col-md-4 text-md-end mt-2 mt-md-0 d-flex align-items-center justify-content-md-end gap-2">
                <button type="button" class="btn btn-outline-primary fw-semibold" id="btn-reload-tables">
                    <i class="bi bi-arrow-clockwise me-1"></i> Muat Ulang
                </button>
                <a href="javascript:history.back()" class="btn btn-primary">
                    <i class="bi bi-chevron-left fs-6"></i> Kembali
                </a>
            </div>
        </div>

        <div class="tw-head d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3" role="toolbar">
            <div class="flex-grow-1 cat-search-wrap">
                <div class="position-relative">
                    <i class="bi bi-search position-absolute text-muted cat-search-icon" style="left: 12px; top: 10px;"></i>
                    <input type="text" id="searchInput" class="form-control tw-search-input cat-search-input ps-5" placeholder="Cari berdasarkan nama sasaran...">
                </div>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <button class="btn btn-primary fw-bold px-3 btn-add-sasaran">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Sasaran
                </button>
            </div>
        </div>

        <div class="card border shadow-sm mt-3">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered w-100" id="table-sasaran">
                        <thead class="table-light">
                            <tr>
                                <th></th>
                                <th>No Urut</th>
                                <th>Nama Sasaran</th>
                                <th style="width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Modals -->
<!-- Modal Sasaran -->
<div class="modal fade" id="modal-sasaran" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="form-sasaran" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Form Sasaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="key" id="sasaran_key">
                <div class="mb-3">
                    <label class="form-label">No Urut</label>
                    <input type="number" class="form-control" name="no_urut" id="sasaran_no_urut">
                </div>
                <div class="mb-3">
                    <label class="form-label">Nama Sasaran <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="nama_sasaran" id="sasaran_nama_sasaran" rows="3" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Indikator -->
<div class="modal fade" id="modal-indikator" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="form-indikator" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Form Indikator</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="key" id="indikator_key">
                <input type="hidden" name="sasaran_id" id="indikator_sasaran_id_hidden">
                <div class="mb-3">
                    <label class="form-label">No Urut</label>
                    <input type="number" class="form-control" name="no_urut" id="indikator_no_urut">
                </div>
                <div class="mb-3">
                    <label class="form-label">Nama Indikator <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="nama_indikator" id="indikator_nama_indikator" rows="3" required></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Satuan</label>
                        <input type="text" class="form-control" name="satuan" id="indikator_satuan">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Target Volume</label>
                        <input type="text" class="form-control" name="target_volume" id="indikator_target_volume">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Kegiatan -->
<div class="modal fade" id="modal-kegiatan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="form-kegiatan" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Form Kegiatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="key" id="kegiatan_key">
                <input type="hidden" name="indikator_id" id="kegiatan_indikator_id_hidden">
                <div class="mb-3">
                    <label class="form-label">No Urut</label>
                    <input type="number" class="form-control" name="no_urut" id="kegiatan_no_urut">
                </div>
                <div class="mb-3">
                    <label class="form-label">Nama Kegiatan <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="nama_kegiatan" id="kegiatan_nama_kegiatan" rows="3" required></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Satuan</label>
                        <input type="text" class="form-control" name="satuan" id="kegiatan_satuan">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Target Volume</label>
                        <input type="number" class="form-control" name="target_volume" id="kegiatan_target_volume">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Pengampu (Khusus Kegiatan) -->
<div class="modal fade" id="modal-pengampu" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="form-pengampu" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Atur Pengampu Kegiatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="key" id="pengampu_key">
                <input type="hidden" name="tipe_relasi" value="kegiatan">
                <input type="hidden" name="relasi_id" id="pengampu_kegiatan_id_hidden">
                <div class="mb-3">
                    <label class="form-label">Pilih Tim Kerja (Bisa Lebih Dari Satu) <span class="text-danger">*</span></label>
                    <select class="form-select select2-modal" name="timkerja_id[]" id="pengampu_timkerja_id" multiple="multiple" required></select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Transaksi Harian (Entry) -->
<div class="modal fade" id="modal-transaksi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="form-transaksi" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Form Transaksi Harian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="key" id="transaksi_key">
                <input type="hidden" name="kegiatan_id" id="transaksi_kegiatan_id_hidden">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Pelaksana Kegiatan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="pelaksana_kegiatan_harian" id="transaksi_pelaksana" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control" name="period_start_date" id="transaksi_start_date">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" class="form-control" name="period_end_date" id="transaksi_end_date">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Target Mingguan</label>
                        <input type="text" class="form-control" name="target_mingguan" id="transaksi_target_mingguan">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Jml Realisasi</label>
                        <input type="number" step="0.01" class="form-control" name="jumlah_realisasi" id="transaksi_jumlah_realisasi">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Progress (%)</label>
                        <input type="number" step="0.01" max="100" class="form-control" name="progres_realisasi" id="transaksi_progres">
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Bukti Pekerjaan (Tautan/Keterangan)</label>
                        <textarea class="form-control" name="bukti_pekerjaan" id="transaksi_bukti" rows="2"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/ikk/main_v3.js') ?>"></script>
<?= $this->endSection(); ?>
