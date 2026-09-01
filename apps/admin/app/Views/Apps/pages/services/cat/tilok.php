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
                <h1 class="tw-title lh-1" id="twsHeading" style="color: #1a202c; font-size: 2.2rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.5rem;">
                    Titik Lokasi
                </h1>
                <p class="tw-subtitle text-secondary mb-0" style="font-size: 1.05rem; font-weight: 500;">
                    <?= esc($seleksi['nama_seleksi']) ?> (<?= esc($seleksi['periode']) ?>)
                </p>
            </div>
            <div class="col-12 col-md-4 text-md-end mt-2 mt-md-0">
                <a href="<?= base_url('apps-cat') ?>" class="btn btn-primary">
                    <i class="bi bi-chevron-left fs-6"></i> Kembali
                </a>
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
                            <h6 class="fw-bold mb-1" style="color: #92400e; font-size: 1rem; line-height: 1.2;">Himbauan: Gunakan Titik Lokasi yang Sudah Ada</h6>
                            <div style="font-size: 0.85rem; color: #b45309; line-height: 1.2;">Untuk mencegah duplikasi data, mohon periksa dan gunakan Titik Lokasi yang sudah terdaftar terlebih dahulu.</div>
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
                            <li><strong>Cari Titik Lokasi:</strong> Gunakan form pencarian pada *toolbar* di bawah untuk memastikan Titik Lokasi belum terdaftar.</li>
                            <li><strong>Pilih Titik Lokasi:</strong> Jika titik lokasi sudah ada, klik *card* yang sesuai untuk langsung mengelola/merekap data instansi.</li>
                            <li><strong>Tambah Baru:</strong> Jika titik lokasi benar-benar belum terdaftar, klik tombol biru <strong>"Tambah Titik Lokasi"</strong>.</li>
                            <li><strong>Kelola Data:</strong> Lengkapi form detail lokasi, lalu simpan.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="tw-head d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3 mb-4 mt-4 tw-animate-entry" style="--animation-order: 2;" role="toolbar">
            <div class="flex-grow-1" style="max-width: 450px;">
                <div class="position-relative tws-search-wrap">
                    <input type="search" id="searchdata" class="form-control pe-7" style="height: 42px; border-radius: 8px;"
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
                    <button type="button" class="btn btn-outline-primary tws-filter-chip is-active" style="height: 42px; border-radius: 8px;" data-filter="all">Semua</button>
                    <button type="button" class="btn btn-outline-primary tws-filter-chip" style="height: 42px; border-radius: 8px;" data-filter="updated">Sudah Update</button>
                    <button type="button" class="btn btn-outline-primary tws-filter-chip" style="height: 42px; border-radius: 8px;" data-filter="pending">Belum Update</button>
                </div>
                
                <select id="twsSort" class="form-select fw-bold" style="width: auto !important; height: 42px; color: #1a202c !important; border: 1px solid #cbd5e1; border-radius: 8px;">
                    <option value="name_asc">Nama A-Z</option>
                    <option value="updated_desc">Terbaru Update</option>
                    <option value="pending_first">Belum Update Dulu</option>
                </select>
                
                <button type="button" class="btn btn-primary d-inline-flex align-items-center justify-content-center px-4" data-bs-toggle="modal" data-bs-target="#DataModal" style="height: 42px; border-radius: 8px;">
                    <span class="fw-bold" style="font-size: 0.95rem;">Tambah Data</span> <i class="bi bi-plus-lg ms-2 d-flex align-items-center" style="font-size: 1.1rem;"></i>
                </button>
            </div>
        </div>

        <div class="row tw-animate-entry tws-list-mode" id="loaded" style="--animation-order: 3; row-gap: 0;">
            <div class="col-12 text-center text-muted py-5">
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Memuat data titik lokasi...
            </div>
        </div>

        <div id="twsPaginationWrap" class="mt-4 mb-5 d-flex justify-content-center tw-animate-entry" style="--animation-order: 4;"></div>
    </div>

    <div class="modal fade" id="DataModal" data-bs-backdrop="static" data-bs-keyboard="false"
        tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-4" id="DataModalLabel">Tambah Data</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="form-usulan" autocomplete="off">
                        <input type="hidden" name="key">
                        <input type="hidden" name="action" value="create">
                        <input type="hidden" name="seleksi_uid" id="seleksi_uid" value="<?= esc($seleksi['uid']) ?>">
                        <div class="row ps-2 pe-2">
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="form-label mb-1">Nama Tilok</label>
                                    <input type="text" class="form-control rounded" name="tilok" placeholder="Nama Titik Lokasi"
                                        required>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label class="form-label mb-1">Tanggal Mulai Periode</label>
                                    <input type="date" class="form-control rounded" name="startdate" placeholder="Tanggal Mulai"
                                        required>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label class="form-label mb-1">Tanggal Selesai Periode</label>
                                    <input type="date" class="form-control rounded" name="enddate" placeholder="Tanggal Selesai"
                                        required>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="form-label mb-1">Kapasitas PC</label>
                                    <input type="number" class="form-control rounded" name="capacity" placeholder="Kapasitas PC"
                                        required>
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

    <div class="modal fade" id="catEventModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5">Kelola Event CAT</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="catEventForm" autocomplete="off">
                        <input type="hidden" id="cat_event_id" name="id">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label mb-1">Kode</label>
                                <input type="text" class="form-control text-uppercase" id="cat_event_kode" name="kode" maxlength="50" placeholder="Contoh: SKD CPNS" required>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label mb-1">Nama Event</label>
                                <input type="text" class="form-control" id="cat_event_nama" name="nama" maxlength="50" placeholder="Contoh: Seleksi Kompetensi Dasar (SKD) CPNS" required>
                            </div>
                            <div class="col-12 d-flex justify-content-end mt-2">
                                <button type="submit" class="btn btn-primary" id="catEventSubmitBtn">Simpan Event</button>
                            </div>
                        </div>
                    </form>

                    <hr class="my-3">
                    <div class="event-table-wrap table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="cat-event-col-code">Kode</th>
                                    <th>Nama Event</th>
                                    <th class="text-center cat-event-col-action">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="catEventTableBody">
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                        Memuat data event...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="catEventResetBtn">Reset</button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</main>
<?= $this->endSection(); ?>
<?= $this->section('scripts'); ?>
<script>
    var SELEKSI_UID = "<?= esc($seleksi['uid']) ?>";
</script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/service-table-ui.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/cat/tilok.js?v=' . time()) ?>"></script>
<?= $this->endSection(); ?>
