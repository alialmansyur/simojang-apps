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
    
    /* Spacing & Uniform Card Heights */
    #jenisTesList, #seleksiList {
        margin-top: 0.25rem !important;
    }
    .jenis-item, .seleksi-item {
        display: flex;
        flex-direction: column;
    }
    .jenis-item.d-none, .seleksi-item.d-none {
        display: none !important;
    }
    .jenis-item .card, .seleksi-item .card {
        height: 100% !important;
        width: 100% !important;
        margin: 0 !important;
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
                <h1 class="tw-title lh-1 cat-page-title" id="catPageTitle">
                    Fasilitasi CAT
                </h1>
                <p class="tw-subtitle text-secondary mb-0 cat-page-subtitle">
                    Kelola daftar Event dan Nama Seleksi Computer Assisted Test (CAT).
                </p>
            </div>
            <div class="col-12 col-md-4 text-md-end mt-2 mt-md-0 d-flex align-items-center justify-content-md-end gap-2">
                <a href="<?= base_url('apps-pnbp') ?>" class="btn btn-outline-primary fw-semibold">
                    <i class="bi bi-file-earmark-text-fill me-1"></i> Dokumen PNBP
                </a>
                <a href="<?= base_url('timkerja-layanan/' . esc($timkerjaUid ?? 'a13e4110-7ccb-11f0-be4c-5f752d8309a4')) ?>" class="btn btn-primary">
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
                            <h6 class="fw-bold mb-1 cat-alert-title">Himbauan: Kelola Jenis Tes CAT Per Tahun</h6>
                            <div class="cat-alert-desc">Pastikan Jenis Tes yang akan dilaksanakan sudah terdaftar pada tahun berjalan sebelum menambahkan atau mengelola titik lokasi.</div>
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
                            <li><strong>Filter & Cari Jenis Tes:</strong> Gunakan dropdown filter tahun dan kolom pencarian untuk memeriksa Jenis Tes (misal: *CACT, PROASN, SKD CPNS, SELKOM PPPK*) yang terdaftar.</li>
                            <li><strong>Buka Titik Lokasi:</strong> Klik pada *card* jenis tes yang aktif untuk membuka dan mengelola daftar Titik Lokasi di dalamnya.</li>
                            <li><strong>Tambah Jenis Tes Tahunan:</strong> Jika jenis tes yang dilaksanakan belum ada pada tahun terkait, klik tombol biru <strong>"Tambah Jenis Tes CAT"</strong> lalu pilih jenis tes dan tentukan tahun pelaksanaannya.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <?php
            $currentYear = date('Y');
            $uniqueYears = [];
            if (!empty($catJenisPeriodeList)) {
                foreach ($catJenisPeriodeList as $row) {
                    if (!empty($row['periode'])) {
                        $uniqueYears[] = $row['periode'];
                    }
                }
                $uniqueYears = array_unique($uniqueYears);
                rsort($uniqueYears);
            }
            if (!in_array($currentYear, $uniqueYears)) {
                array_unshift($uniqueYears, $currentYear);
            }
            $defaultYear = in_array($currentYear, $uniqueYears) ? $currentYear : (!empty($uniqueYears) ? $uniqueYears[0] : '');
        ?>
        <div class="tw-head d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3" role="toolbar">
            <div class="flex-grow-1 cat-search-wrap">
                <div class="position-relative">
                    <i class="bi bi-search position-absolute text-muted cat-search-icon"></i>
                    <input type="text" id="searchInput" class="form-control tw-search-input cat-search-input" placeholder="Cari berdasarkan nama atau kode jenis tes...">
                </div>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <select id="filterTahun" class="form-select fw-bold cat-filter-select">
                    <option value="">Semua Tahun</option>
                    <?php foreach ($uniqueYears as $yr): ?>
                        <option value="<?= esc($yr) ?>" <?= ($yr == $defaultYear) ? 'selected' : '' ?>><?= esc($yr) ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="catSort" class="form-select fw-bold cat-filter-select">
                    <option value="default">Urutan Standar</option>
                    <option value="name_asc">Nama A-Z</option>
                    <option value="updated_desc">Data Terupdate</option>
                    <option value="pending_first">Data Belum Update</option>
                </select>
                <button type="button" class="btn btn-primary d-inline-flex align-items-center justify-content-center px-4 cat-btn-primary-action" data-bs-toggle="modal" data-bs-target="#SeleksiModal">
                    <span class="fw-bold">Tambah Jenis Tes CAT</span> <i class="bi bi-plus-lg ms-2 d-flex align-items-center fs-6"></i>
                </button>
            </div>
        </div>

        <div class="row g-3" id="seleksiList">

            <?php if (!empty($catJenisPeriodeList)): ?>
                <?php 
                    $today = date('Y-m-d');
                ?>
                <?php foreach ($catJenisPeriodeList as $index => $sel): ?>
                    <?php 
                        $namaTampil = !empty($sel['jenis_tes_nama']) ? $sel['jenis_tes_nama'] : (!empty($sel['jenis_tes_kode']) ? $sel['jenis_tes_kode'] : 'Jenis Tes CAT');
                        $kodeTampil = !empty($sel['jenis_tes_kode']) ? $sel['jenis_tes_kode'] : 'CAT';
                        
                        $hash = abs(crc32($kodeTampil));
                        $hue = $hash % 360;
                        
                        $bg = "hsl({$hue}, 85%, 94%)";
                        $text = "hsl({$hue}, 90%, 35%)";
                        $border = "hsl({$hue}, 85%, 85%)";
                        $hoverBg = "hsl({$hue}, 85%, 97%)";
                        
                        $inlineStyles = "--twx-bg: {$bg}; --twx-text: {$text}; --twx-border: {$border}; --twx-hover-bg: {$hoverBg};";

                        $iconSvg = '<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>';

                        // Logika Sedang Berlangsung: diambil dari SQL is_ongoing
                        $isOngoing = !empty($sel['is_ongoing']) && $sel['is_ongoing'] == 1;
                        $isMatchDefaultYear = ((string)$sel['periode'] === (string)$defaultYear);

                        $totalTilok = (int) ($sel['total_tilok'] ?? 0);
                        $totalPeserta = (int) ($sel['total_peserta'] ?? 0);
                        $lastRekap = !empty($sel['last_rekap_date']) ? $sel['last_rekap_date'] : null;
                        $updatedTimestamp = !empty($lastRekap) ? strtotime($lastRekap) : 0;
                        $hasRekap = !empty($lastRekap) ? 1 : 0;
                    ?>
                    <div class="col-12 col-md-6 col-lg-4 seleksi-item <?= $isMatchDefaultYear ? '' : 'd-none' ?>" 
                         data-name="<?= strtolower(esc($namaTampil)) ?>" 
                         data-kode="<?= strtolower(esc($kodeTampil)) ?>" 
                         data-periode="<?= esc($sel['periode']) ?>" 
                         data-ongoing="<?= $isOngoing ? 1 : 0 ?>" 
                         data-updated="<?= $updatedTimestamp ?>" 
                         data-has-rekap="<?= $hasRekap ?>" 
                         data-tilok="<?= $totalTilok ?>"
                         style="<?= $inlineStyles ?>">
                        <div class="card shadow-sm position-relative twx-anim-card overflow-hidden twx-card-container">
                            <!-- Background Icon -->
                            <div class="position-absolute twx-bg-icon twx-bg-icon-wrapper">
                                <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="twx-bg-icon-svg"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                            </div>
                            
                            <div class="card-body p-3 d-flex flex-column position-relative" style="z-index: 1;">
                                <div class="d-flex justify-content-between align-items-start w-100 mb-2">
                                    <div class="d-flex align-items-center gap-1 flex-wrap">
                                        <span class="badge twx-card-badge"><?= esc($kodeTampil) ?></span>
                                        <?php if ($isOngoing): ?>
                                            <span class="badge cat-badge-ongoing" title="Event sedang berlangsung">
                                                <span class="cat-pulse-dot"></span>
                                                Berlangsung
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-muted text-nowrap twx-period-text"><i class="bi bi-calendar3 me-1"></i> <?= esc($sel['periode']) ?></span>
                                        <div class="d-flex align-items-center gap-2 ms-1 position-relative" style="z-index: 2;">
                                            <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none twx-edit-btn cat-card-action-btn" data-uid="<?= esc($sel['uid']) ?>" data-nama="<?= esc($namaTampil) ?>" data-jenis="<?= esc($sel['jenis_tes_id']) ?>" data-periode="<?= esc($sel['periode']) ?>" title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none twx-delete-btn cat-card-action-btn cat-card-delete-btn" data-uid="<?= esc($sel['uid']) ?>" data-name="<?= esc($namaTampil) ?>" title="Hapus">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-start gap-3 w-100 mt-auto pt-2 position-relative" style="z-index: 1;">
                                    <div class="flex-shrink-0 d-flex align-items-center justify-content-center twx-main-icon twx-main-icon-container">
                                        <span class="twx-main-icon-svg-wrapper"><?= $iconSvg ?></span>
                                    </div>
                                    <div class="d-flex flex-column text-start overflow-hidden flex-grow-1">
                                        <a href="<?= base_url('apps-cat-tilok/' . $sel['uid']) ?>" class="stretched-link text-decoration-none text-reset">
                                            <h6 class="fw-bolder mb-1 lh-sm twx-card-title" title="<?= esc($namaTampil) ?>"><?= esc($namaTampil) ?></h6>
                                        </a>
                                        <?php 
                                            $totalTilok = (int) ($sel['total_tilok'] ?? 0);
                                            $totalPeserta = (int) ($sel['total_peserta'] ?? 0);
                                            $lastRekap = !empty($sel['last_rekap_date']) ? $sel['last_rekap_date'] : null;
                                        ?>
                                        <div class="d-flex flex-wrap gap-1 align-items-center mt-2 cat-card-meta-row">
                                            <span class="cat-card-meta-badge cat-meta-tilok" title="Total Titik Lokasi">
                                                <i class="bi bi-geo-alt-fill"></i> <?= number_format($totalTilok, 0, ',', '.') ?> Tilok
                                            </span>
                                            <span class="cat-card-meta-badge cat-meta-peserta" title="Total Peserta Terealisasi">
                                                <i class="bi bi-people-fill"></i> <?= number_format($totalPeserta, 0, ',', '.') ?> Peserta
                                            </span>
                                            <?php if ($lastRekap): ?>
                                                <span class="cat-card-meta-badge cat-meta-rekap" title="Tanggal Rekap Terakhir">
                                                    <i class="bi bi-clock-history"></i> <?= date('d M Y', strtotime($lastRekap)) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12" id="noDataInfo">
                    <div class="d-flex flex-column align-items-center justify-content-center text-center mt-5 mb-5 pb-4">
                        <img src="<?= asset_url('apps/assets/images/empty-content-profile.png') ?>" alt="Belum Ada Jenis Tes" class="cat-empty-img">
                        <h5 class="fw-bold cat-empty-title">Belum Ada Jenis Tes Terdaftar</h5>
                        <p class="text-muted mb-0 cat-empty-desc">
                            Belum ada jenis tes yang terdaftar pada tahun ini. Silakan klik tombol "Tambah Jenis Tes CAT".
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    </div>
</main>

<div class="modal fade" id="SeleksiModal" tabindex="-1" aria-labelledby="SeleksiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content cat-modal-content">
            <div class="modal-header align-items-center cat-modal-header">
                <h5 class="modal-title fw-bold mb-0 cat-modal-title" id="SeleksiModalLabel">Tambah Jenis Tes CAT</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formSeleksi" autocomplete="off">
                <input type="hidden" name="key" id="seleksi_key">
                <div class="modal-body cat-modal-body">
                    <div class="row gy-4">
                        <div class="col-md-8">
                            <label class="form-label d-block fw-bold mb-2 cat-modal-label">Nama Jenis Tes <span class="text-danger">*</span></label>
                            <select name="jenis" id="jenisEventPicker" class="form-select select-event cat-modal-control" required>
                                <option value="">Pilih Jenis Tes...</option>
                                <?php if (!empty($jenisRows) && is_array($jenisRows)): ?>
                                    <?php foreach ($jenisRows as $row): ?>
                                        <option value="<?= esc((string) $row['id']) ?>"><?= esc($row['kode']) ?> - <?= esc($row['nama']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label d-block fw-bold mb-2 cat-modal-label">Tahun Pelaksanaan <span class="text-danger">*</span></label>
                            <input type="text" name="periode" id="periodeSeleksi" class="form-control cat-modal-control" required value="<?= date('Y') ?>" placeholder="Contoh: <?= date('Y') ?>">
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between align-items-center cat-modal-footer">
                    <button type="button" class="btn btn-light px-4 py-2 fw-bold cat-modal-btn-cancel" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 py-2 fw-bold cat-modal-btn-save" id="btnSaveSeleksi">
                        Simpan Jenis Tes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    var base_url = "<?= base_url() ?>";
</script>

<?= $this->endSection(); ?>
<?= $this->section('scripts'); ?>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/service-table-ui.js?v=' . time()) ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/cat/main.js?v=' . time()) ?>"></script>
<?= $this->endSection(); ?>
