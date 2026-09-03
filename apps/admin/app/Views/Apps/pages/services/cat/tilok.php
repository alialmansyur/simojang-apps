<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>
<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork-common.css') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork-service.css') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/cat/main.css') ?>">
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>
<main class="page-content" aria-labelledby="twsHeading">
    <div class="text-start tws-wrap container-fluid">
        <div class="row align-items-center mt-3 mb-2 tw-animate-entry" style="--animation-order: 1;">
            <div class="col-12 col-md-8">
                <h1 class="tw-title lh-1 cat-page-title" id="twsHeading">
                    Titik Lokasi
                </h1>
                <p class="tw-subtitle text-secondary mb-0 cat-tilok-subtitle">
                    <?= esc($catPeriode['jenis_tes_nama'] ?? ($jenisTes['nama'] ?? 'Jenis Tes CAT')) ?> <?= !empty($catPeriode['jenis_tes_kode'] ?? ($jenisTes['kode'] ?? '')) ? '('.esc($catPeriode['jenis_tes_kode'] ?? $jenisTes['kode']).')' : '' ?> - Tahun <?= esc($catPeriode['periode'] ?? ($periodeTahun ?? date('Y'))) ?>
                </p>
            </div>
            <div class="col-12 col-md-4 text-md-end mt-2 mt-md-0 d-flex align-items-center justify-content-md-end gap-2">
                <a href="<?= base_url('apps-pnbp') ?>" class="btn btn-outline-primary fw-semibold">
                    <i class="bi bi-file-earmark-text-fill me-1"></i> Dokumen PNBP
                </a>
                <a href="<?= base_url('apps-cat') ?>" class="btn btn-primary">
                    <i class="bi bi-chevron-left fs-6"></i> Kembali
                </a>
            </div>
        </div>

        <div class="row mb-3 mt-2">
            <div class="col-12">
                <div class="alert shadow-sm border-0 rounded-3 p-3 position-relative mb-0 cat-alert-himbauan" role="alert">
                    <div class="row align-items-center g-0 pe-5">
                        <div class="col-auto pe-3">
                            <i class="bi bi-exclamation-triangle-fill cat-alert-icon"></i>
                        </div>
                        <div class="col">
                            <h6 class="fw-bold mb-1 cat-alert-title">Himbauan: Pengelolaan Titik Lokasi Ujian CAT</h6>
                            <div class="cat-alert-desc">Periksa daftar Titik Lokasi yang sudah terdaftar terlebih dahulu untuk mencegah duplikasi data titik lokasi ujian.</div>
                        </div>
                    </div>
                    
                    <div class="position-absolute top-0 end-0 p-3">
                        <button class="btn btn-sm text-nowrap fw-bold cat-btn-tatacara" type="button" data-bs-toggle="collapse" data-bs-target="#tataCaraCollapse" aria-expanded="false" aria-controls="tataCaraCollapse">
                            <i class="bi bi-info-circle me-1"></i> Tata Cara
                        </button>
                    </div>
                    
                    <div class="collapse" id="tataCaraCollapse">
                        <hr class="cat-tatacara-divider">
                        <ol class="mb-0 ps-3 cat-tatacara-list">
                            <li><strong>Cari Titik Lokasi:</strong> Gunakan kolom pencarian dan filter status update pada toolbar untuk mencari Titik Lokasi yang diinginkan.</li>
                            <li><strong>Kelola Rekapitulasi:</strong> Klik pada *card* titik lokasi untuk membuka halaman detail rekapitulasi sesi dan instansi peserta.</li>
                            <li><strong>Tambah Titik Lokasi Baru:</strong> Jika titik lokasi ujian belum terdaftar, klik tombol biru <strong>"Tambah Titik Lokasi"</strong>.</li>
                            <li><strong>Ubah / Hapus Data:</strong> Gunakan tombol ikon pensil untuk mengubah kapasitas & rentang tanggal, atau ikon tempat sampah untuk menghapus titik lokasi.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="tw-head d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3 mb-4 mt-4 tw-animate-entry" style="--animation-order: 2;" role="toolbar">
            <div class="flex-grow-1 cat-search-wrap">
                <div class="position-relative tws-search-wrap">
                    <input type="search" id="searchdata" class="form-control pe-7 cat-btn-primary-action"
                        placeholder="Cari titik lokasi...">
                    <button type="button" class="btn tws-search-indicator" disabled>
                        <i id="twsSearchIcon" class="bi bi-search fs-5 text-primary"></i>
                    </button>
                    <button type="button" id="twsClearSearch" class="btn tws-search-clear d-none" aria-label="Bersihkan pencarian">
                        <i class="bi bi-x-circle-fill fs-5"></i>
                    </button>
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="d-flex flex-wrap align-items-center gap-2" id="twsQuickFilters">
                    <button type="button" class="btn btn-outline-primary tws-filter-chip is-active cat-btn-filter-chip" data-filter="all">Semua</button>
                    <button type="button" class="btn btn-outline-primary tws-filter-chip cat-btn-filter-chip" data-filter="updated">Sudah Update</button>
                    <button type="button" class="btn btn-outline-primary tws-filter-chip cat-btn-filter-chip" data-filter="pending">Belum Update</button>
                </div>
                
                <select id="twsSort" class="form-select fw-bold cat-filter-select">
                    <option value="updated_desc" selected>Terbaru Update</option>
                    <option value="name_asc">Nama A-Z</option>
                    <option value="pending_first">Belum Update Dulu</option>
                </select>
                
                <button type="button" class="btn btn-primary d-inline-flex align-items-center justify-content-center px-4 cat-btn-primary-action" data-bs-toggle="modal" data-bs-target="#DataModal">
                    <span class="fw-bold">Tambah Titik Lokasi</span> <i class="bi bi-plus-lg ms-2 d-flex align-items-center fs-6"></i>
                </button>
            </div>
        </div>

        <div class="row tw-animate-entry tws-list-mode cat-tilok-row-container" id="loaded">
            <div class="col-12 text-center text-muted py-5">
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Memuat data titik lokasi...
            </div>
        </div>

        <div id="twsPaginationWrap" class="mt-4 mb-5 d-flex justify-content-center tw-animate-entry"></div>
    </div>

    <div class="modal fade" id="DataModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel">Tambah Titik Lokasi</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formAction" autocomplete="off">
                        <input type="hidden" name="action" class="action" value="create">
                        <input type="hidden" name="key" class="key" value="">
                        <input type="hidden" name="jenis_periode_id" value="<?= esc($catPeriode['id'] ?? '') ?>">
                        <input type="hidden" name="seleksi_uid" value="<?= esc($catPeriode['uid'] ?? '') ?>">
                        <input type="hidden" name="jenis_tes_id" value="<?= esc($catPeriode['jenis_tes_id'] ?? ($jenisTes['id'] ?? '')) ?>">
                        <div class="row gy-3">
                            <div class="col-md-6">
                                <label class="form-label mb-1">Tahun / Periode *</label>
                                <input type="text" name="period" class="form-control period" placeholder="Contoh: <?= esc($catPeriode['periode'] ?? date('Y')) ?>" value="<?= esc($catPeriode['periode'] ?? date('Y')) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label mb-1">Kapasitas Sesi</label>
                                <input type="number" name="kapasitas" class="form-control kapasitas" placeholder="Jumlah Peserta Per Sesi">
                            </div>
                            <div class="col-12">
                                <label class="form-label mb-1">Nama Titik Lokasi *</label>
                                <input type="text" name="nama_tilok" class="form-control nama_tilok" placeholder="Contoh: Kantor Regional III BKN Bandung" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label mb-1">Tanggal Mulai</label>
                                <input type="date" name="startdate" class="form-control startdate">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label mb-1">Tanggal Selesai</label>
                                <input type="date" name="enddate" class="form-control enddate">
                            </div>                        
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary sbmt" form="formAction">Simpan Data</button>
                </div>
            </div>
        </div>
    </div>
</main>
<?= $this->endSection(); ?>
<?= $this->section('scripts'); ?>
<script>
    var CAT_PERIODE_UID = "<?= esc($catPeriode['uid'] ?? ($seleksi['uid'] ?? '')) ?>";
    var JENIS_PERIODE_ID = "<?= esc($catPeriode['id'] ?? '') ?>";
    var SELEKSI_UID = CAT_PERIODE_UID;
    var JENIS_TES_ID = "<?= esc($catPeriode['jenis_tes_id'] ?? ($jenisTes['id'] ?? '')) ?>";
    var PERIODE_TAHUN = "<?= esc($catPeriode['periode'] ?? ($periodeTahun ?? '')) ?>";
</script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/service-table-ui.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/cat/tilok.js?v=' . time()) ?>"></script>
<?= $this->endSection(); ?>
