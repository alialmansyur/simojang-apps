<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>
<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork-common.css') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork.css?v=99') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/cat/main.css?v=' . time()) ?>">
<style>
    .btn-trash-hover {
        transition: all 0.2s;
        color: #ef4444 !important;
    }
    .btn-trash-hover:hover {
        background-color: #fee2e2 !important;
        transform: scale(1.1);
        border-radius: 4px;
    }
    .tw-subtitle {
        font-size: .95rem !important;
    }
    #seleksiList {
        row-gap: 1rem !important;
        margin-top: 0.5rem !important;
        padding-top: 0 !important;
        align-content: flex-start !important;
        height: max-content !important;
    }
    #seleksiList .seleksi-item {
        margin-top: 0 !important;
        margin-bottom: 0 !important;
        padding-top: 0 !important;
        padding-bottom: 0 !important;
        align-self: flex-start !important;
        height: auto !important;
    }
    #seleksiList .card {
        margin: 0 !important;
        height: auto !important;
    }
    #seleksiList a {
        margin: 0 !important;
        padding: 0 !important;
    }
    
    /* Hover Animations & Clean Hover States */
    .twx-anim-card {
        transition: transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275), border-color 0.2s ease !important;
    }
    .twx-anim-card:hover {
        transform: translateY(-4px) !important;
        border-color: var(--bs-primary) !important;
    }
    .twx-anim-card:hover .twx-main-icon svg { 
        transform: scale(1.15) rotate(-10deg); 
    }
    .twx-main-icon svg { 
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
    }
    .twx-anim-card:hover .twx-bg-icon { 
        transform: scale(1.1) rotate(5deg) !important; 
        opacity: 0.08 !important; 
    }
    .twx-bg-icon { 
        transition: transform 0.4s ease, opacity 0.4s ease; 
    }
</style>
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>
<main class="page-content" aria-labelledby="catPageTitle">
    <div class="text-start tw-wrap container-fluid">
        <div class="row align-items-center mt-4 mb-3" role="banner">
            <div class="col-12 col-md-8 text-start">
                <h1 class="tw-title lh-1" id="catPageTitle" style="color: #1a202c; font-size: 2.2rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.5rem;">
                    Fasilitasi CAT
                </h1>
                <p class="tw-subtitle text-secondary mb-0" style="font-size: 1rem; font-weight: 400;">
                    Kelola daftar Event dan Nama Seleksi Computer Assisted Test (CAT).
                </p>
            </div>
            <div class="col-12 col-md-4 text-md-end mt-2 mt-md-0">
                <a href="<?= base_url('timkerja-layanan') ?>" class="btn btn-primary">
                    <i class="bi bi-chevron-left fs-6"></i> Kembali
                </a>
            </div>
        </div>

        <div class="row mb-3 mt-4">
            <div class="col-12">
                <div class="alert shadow-sm border-0 rounded-3 p-3 position-relative" style="background-color: #fffbe4; border-left: 6px solid #f59e0b !important;" role="alert">
                    <div class="row align-items-center g-0 pe-5">
                        <div class="col-auto pe-3">
                            <i class="bi bi-exclamation-triangle-fill" style="color: #d97706; font-size: 2.2rem; line-height: 1;"></i>
                        </div>
                        <div class="col">
                            <h6 class="fw-bold mb-1" style="color: #92400e; font-size: 1rem; line-height: 1.2;">Himbauan: Gunakan Event/Seleksi yang Sudah Ada</h6>
                            <div style="font-size: 0.85rem; color: #b45309; line-height: 1.2;">Untuk mencegah duplikasi, mohon cari dan kelola data pada Event yang sudah terdaftar terlebih dahulu.</div>
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
                            <li><strong>Cari Event:</strong> Gunakan kotak pencarian untuk mengecek apakah Event (misal: CACT) sudah pernah dibuat sebelumnya.</li>
                            <li><strong>Kelola Event:</strong> Jika sudah ada, langsung klik *card* event tersebut untuk mengelola Titik Lokasi di dalamnya.</li>
                            <li><strong>Tambah Baru:</strong> Jika Event/Seleksi benar-benar baru dan belum terdaftar, barulah klik tombol biru <strong>"Tambah Event/Seleksi"</strong>.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <?php
            $uniqueYears = [];
            $uniqueEvents = [];
            if (!empty($seleksiList)) {
                foreach ($seleksiList as $sel) {
                    $yr = substr($sel['periode'], 0, 4);
                    if (!in_array($yr, $uniqueYears)) {
                        $uniqueYears[] = $yr;
                    }
                    $evt = $sel['jenis_tes_nama'];
                    if (!in_array($evt, $uniqueEvents)) {
                        $uniqueEvents[] = $evt;
                    }
                }
                rsort($uniqueYears);
                sort($uniqueEvents);
            }
        ?>
        <div class="tw-head d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3" role="toolbar">
            <div class="flex-grow-1" style="max-width: 450px;">
                <div class="position-relative">
                    <i class="bi bi-search position-absolute text-muted" style="left: 1.2rem; top: 50%; transform: translateY(-50%); margin-top: -1px; line-height: 1; pointer-events: none;"></i>
                    <input type="text" id="searchInput" class="form-control tw-search-input" placeholder="Cari berdasarkan event atau nama seleksi..." style="padding-left: 2.8rem; padding-top: 0.65rem; padding-bottom: 0.65rem;">
                </div>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <select id="filterEvent" class="form-select fw-bold" style="width: auto !important; height: 42px; color: #1a202c !important; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                    <option value="">Semua Event</option>
                    <?php foreach ($uniqueEvents as $evt): ?>
                        <option value="<?= esc(strtolower($evt)) ?>"><?= esc($evt) ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="filterTahun" class="form-select fw-bold" style="width: auto !important; height: 42px; color: #1a202c !important; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                    <option value="">Semua Tahun</option>
                    <?php foreach ($uniqueYears as $yr): ?>
                        <option value="<?= esc($yr) ?>"><?= esc($yr) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="button" class="btn btn-primary d-inline-flex align-items-center justify-content-center px-4" data-bs-toggle="modal" data-bs-target="#SeleksiModal" style="height: 42px; border-radius: 8px;">
                    <span class="fw-bold" style="font-size: 0.95rem;">Tambah Data</span> <i class="bi bi-plus-lg ms-2 d-flex align-items-center" style="font-size: 1.1rem;"></i>
                </button>
            </div>
        </div>

        <div class="row g-3" id="seleksiList">

            <?php if (!empty($seleksiList)): ?>
                <?php foreach ($seleksiList as $index => $sel): ?>
                    <?php 
                        $hash = abs(crc32($sel['jenis_tes_kode']));
                        // Generate a unique Hue (0-359) for each event type
                        $hue = $hash % 360;
                        
                        // Define dynamic HSL colors based on the hue
                        $bg = "hsl({$hue}, 85%, 94%)";
                        $text = "hsl({$hue}, 90%, 35%)";
                        $border = "hsl({$hue}, 85%, 85%)";
                        $hoverBg = "hsl({$hue}, 85%, 97%)";
                        
                        $inlineStyles = "--twx-bg: {$bg}; --twx-text: {$text}; --twx-border: {$border}; --twx-hover-bg: {$hoverBg};";

                        $iconSvg = '<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>';
                    ?>
                    <div class="col-12 col-md-6 col-lg-4 seleksi-item" data-name="<?= strtolower(esc($sel['nama_seleksi'])) ?>" data-event="<?= strtolower(esc($sel['jenis_tes_nama'])) ?>" data-periode="<?= esc($sel['periode']) ?>" style="display: none; <?= $inlineStyles ?>">
                        <div class="card shadow-sm position-relative twx-anim-card overflow-hidden twx-card-container">
                            <!-- Background Icon -->
                            <div class="position-absolute twx-bg-icon twx-bg-icon-wrapper">
                                <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="twx-bg-icon-svg"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                            </div>
                            
                            <div class="card-body p-3 d-flex flex-column position-relative" style="z-index: 1;">
                                <div class="d-flex justify-content-between align-items-start w-100 mb-2">
                                    <span class="badge twx-card-badge"><?= esc($sel['jenis_tes_kode']) ?></span>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-muted text-nowrap twx-period-text"><i class="bi bi-calendar3 me-1"></i> <?= esc($sel['periode']) ?></span>
                                        <div class="d-flex align-items-center gap-2 ms-1 position-relative" style="z-index: 2;">
                                            <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none twx-edit-btn" data-uid="<?= esc($sel['uid']) ?>" data-name="<?= esc($sel['nama_seleksi']) ?>" data-jenis="<?= esc($sel['jenis_tes_id']) ?>" data-periode="<?= esc($sel['periode']) ?>" title="Edit" style="color: #94a3b8; line-height: 1; transition: color 0.2s ease;">
                                                <i class="bi bi-pencil-square" style="font-size: 1.05rem;"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none twx-delete-btn" data-uid="<?= esc($sel['uid']) ?>" data-name="<?= esc($sel['nama_seleksi']) ?>" title="Hapus" style="color: #94a3b8; line-height: 1; transition: color 0.2s ease;">
                                                <i class="bi bi-trash3" style="font-size: 1.05rem;"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-3 w-100 mt-auto pt-2 position-relative" style="z-index: 1;">
                                    <div class="flex-shrink-0 d-flex align-items-center justify-content-center twx-main-icon twx-main-icon-container">
                                        <span class="twx-main-icon-svg-wrapper"><?= $iconSvg ?></span>
                                    </div>
                                    <div class="d-flex flex-column text-start overflow-hidden flex-grow-1">
                                        <a href="<?= base_url('apps-cat-tilok/' . $sel['uid']) ?>" class="stretched-link text-decoration-none" style="color: inherit;">
                                            <h6 class="fw-bold mb-0 lh-sm twx-card-title" title="<?= esc($sel['nama_seleksi']) ?>"><?= esc($sel['nama_seleksi']) ?></h6>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12" id="noDataInfo">
                    <div class="d-flex flex-column align-items-center justify-content-center text-center mt-5 mb-5 pb-4">
                        <img src="<?= asset_url('apps/assets/images/empty-content-profile.png') ?>" alt="Belum Ada Seleksi" style="max-width: 320px; margin-bottom: 2rem;">
                        <h5 class="fw-bold" style="color: #1a202c; font-size: 1.35rem;">Belum Ada Nama Seleksi</h5>
                        <p class="text-muted mb-0" style="font-size: 1.05rem; max-width: 450px; margin: 0 auto; line-height: 1.6;">
                            Anda belum memiliki data Seleksi CAT. Silakan tambah data baru.
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    </div>
</main>

<div class="modal fade" id="SeleksiModal" tabindex="-1" aria-labelledby="SeleksiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); overflow: hidden;">
            <div class="modal-header align-items-center" style="border-bottom: 1px solid #f1f5f9; padding: 1.5rem 1.75rem 1.25rem; background-color: #ffffff;">
                <h5 class="modal-title fw-bold mb-0" id="SeleksiModalLabel" style="font-size: 1.25rem; color: #1a202c !important;">Tambah Nama Seleksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.8rem;"></button>
            </div>
            <form id="formSeleksi" autocomplete="off">
                <input type="hidden" name="key" id="seleksi_key">
                <div class="modal-body" style="padding: 1.75rem; background-color: #fcfdfd;">
                    <div class="row gy-4">
                        <div class="col-md-12">
                            <label class="form-label d-block fw-bold" style="font-size: 0.9rem; color: #1a202c; margin-bottom: 0.5rem;">Event <span class="text-danger">*</span></label>
                            <select name="jenis" id="jenisEventPicker" class="form-select select-event" required style="background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.75rem 1rem; font-size: 0.95rem; color: #1e293b; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                                <option value="">Pilih Event...</option>
                                <?php if (!empty($jenisOptions) && is_array($jenisOptions)): ?>
                                    <?php foreach ($jenisOptions as $id => $label): ?>
                                        <option value="<?= esc((string) $id) ?>"><?= esc((string) $label) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label d-block fw-bold" style="font-size: 0.9rem; color: #1a202c; margin-bottom: 0.5rem;">Nama Seleksi <span class="text-danger">*</span></label>
                            <input type="text" name="nama" id="namaSeleksi" class="form-control" required placeholder="Contoh: SKD CPNS" style="background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.75rem 1rem; font-size: 0.95rem; color: #1e293b; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label d-block fw-bold" style="font-size: 0.9rem; color: #1a202c; margin-bottom: 0.5rem;">Tahun/Periode <span class="text-danger">*</span></label>
                            <input type="text" name="periode" id="periodeSeleksi" class="form-control" required placeholder="Contoh: 2026" style="background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.75rem 1rem; font-size: 0.95rem; color: #1e293b; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between align-items-center" style="border-top: 1px solid #f1f5f9; padding: 1.25rem 1.75rem; background-color: #ffffff;">
                    <button type="button" class="btn btn-light px-4 py-2 fw-bold" data-bs-dismiss="modal" style="color: #64748b; border-radius: 8px; background: #f1f5f9; border: none;">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 py-2 fw-bold" id="btnSaveSeleksi" style="border-radius: 8px; box-shadow: 0 4px 12px rgba(var(--bs-primary-rgb), 0.2);">
                        Simpan Seleksi
                    </button>
                </div>
            </form>
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

<script>
    var base_url = "<?= base_url() ?>";
</script>

<?= $this->endSection(); ?>
<?= $this->section('scripts'); ?>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/service-table-ui.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/cat/main.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/cat/tables.js') ?>"></script>
<?= $this->endSection(); ?>
