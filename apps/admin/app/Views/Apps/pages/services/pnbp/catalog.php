<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>

<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork-common.css') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork-service.css') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/cat/main.css') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/pnbp/pnbp-main.css?v=' . time()) ?>">
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>
<main class="page-content" aria-labelledby="pnbpHeading">
    <div class="text-start tws-wrap container-fluid">
        
        <!-- Page Header -->
        <div class="row align-items-center mt-3 mb-2 tw-animate-entry" style="--animation-order: 1;">
            <div class="col-12 col-md-8">
                <h1 class="tw-title lh-1" id="pnbpHeading" style="color: #1a202c; font-size: 2.2rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.5rem;">
                    Dokumen PNBP CAT
                </h1>
                <p class="tw-subtitle text-secondary mb-0" style="font-size: 1.05rem; font-weight: 500;">
                    Pilih jenis format dokumen di bawah ini untuk melihat daftar berkas atau mengelola dokumen pertanggungjawaban kegiatan.
                </p>
            </div>
            <div class="col-12 col-md-4 text-md-end mt-2 mt-md-0 d-flex align-items-center justify-content-md-end gap-2">
                <a href="<?= base_url('apps-cat') ?>" class="btn btn-outline-secondary px-3" style="height: 42px; border-radius: 8px;">
                    <i class="bi bi-chevron-left fs-6 me-1"></i> Kembali ke CAT
                </a>
                <button type="button" class="btn btn-primary d-inline-flex align-items-center px-3" data-bs-toggle="modal" data-bs-target="#pnbpDocModal" id="btnOpenCreateModal" style="height: 42px; border-radius: 8px;">
                    <i class="bi bi-plus-lg me-1 fs-6"></i> <span class="fw-bold">Buat Dokumen</span>
                </button>
            </div>
        </div>

        <!-- Himbauan Alert Banner (Alur Kerja - Warning Style Ala /apps-cat-tilok/*) -->
        <div class="row mb-3 mt-2">
            <div class="col-12">
                <div class="alert shadow-sm border-0 rounded-3 p-3 position-relative mb-0" style="background-color: #fffbe4; border-left: 6px solid #f59e0b !important;" role="alert">
                    <div class="row align-items-center g-0 pe-5">
                        <div class="col-auto pe-3">
                            <i class="bi bi-exclamation-triangle-fill" style="color: #d97706; font-size: 2.2rem; line-height: 1;"></i>
                        </div>
                        <div class="col">
                            <h6 class="fw-bold mb-1" style="color: #92400e; font-size: 1rem; line-height: 1.2;">
                                Panduan Pengelolaan Dokumen PNBP CAT
                            </h6>
                            <div style="font-size: 0.85rem; color: #b45309; line-height: 1.3;">
                                Pilih salah satu format dokumen di bawah untuk melihat daftar berkas yang telah dibuat atau membuat dokumen pertanggungjawaban baru.
                            </div>
                        </div>
                    </div>
                    
                    <div class="position-absolute top-0 end-0 p-3">
                        <button class="btn btn-sm text-nowrap fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#tataCaraCollapse" aria-expanded="false" aria-controls="tataCaraCollapse" style="background-color: #fef3c7; color: #92400e; border: 1px solid #fcd34d;">
                            <i class="bi bi-info-circle me-1"></i> Alur Kerja
                        </button>
                    </div>
                    
                    <div class="collapse" id="tataCaraCollapse">
                        <hr style="border-color: #f59e0b; opacity: 0.2; margin-top: 1rem; margin-bottom: 1rem;">
                        <ol class="mb-0 ps-3" style="font-size: 0.85rem; line-height: 1.7; color: #78350f;">
                            <li><strong>Pilih Format:</strong> Klik salah satu kartu jenis dokumen di bawah untuk membuka halaman daftar berkas.</li>
                            <li><strong>Tambah Data:</strong> Pada halaman dokumen yang dipilih, klik tombol <strong>"+ Tambah [Nama Dokumen]"</strong> untuk mengisi form (memilih Event CAT, Instansi, Titik Lokasi, Nomor Surat, dll).</li>
                            <li><strong>Kelola Detail:</strong> Masuk ke halaman detail dokumen untuk mengisi rincian personel tim pengawas atau rincian menu katering.</li>
                            <li><strong>Tanda Tangan Digital:</strong> Scan QR code pada halaman detail / dokumen menggunakan HP untuk menandatangani secara digital.</li>
                            <li><strong>Generate PDF:</strong> Klik tombol <strong>"Generate PDF"</strong> untuk melihat pratinjau dan mengunduh dokumen resmi.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Toolbar (Ala /apps-cat-tilok/*) -->
        <div class="tw-head d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3 mb-4 mt-4 tw-animate-entry" style="--animation-order: 2;" role="toolbar">
            <div class="flex-grow-1" style="max-width: 450px;">
                <div class="position-relative tws-search-wrap">
                    <input type="search" id="searchdata" class="form-control pe-7" style="height: 42px;"
                        placeholder="Cari format dokumen...">
                    <button type="button" class="btn tws-search-indicator" disabled>
                        <i id="twsSearchIcon" class="bi bi-search fs-5 text-primary"></i>
                    </button>
                    <button type="button" id="twsClearSearch" class="btn tws-search-clear d-none" aria-label="Bersihkan pencarian">
                        <i class="bi bi-x-circle-fill fs-5"></i>
                    </button>
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="d-flex flex-wrap align-items-center gap-2" id="catalogCategoryFilters">
                    <button type="button" class="btn btn-outline-primary tws-filter-chip is-active" style="height: 42px; border-radius: 8px;" data-category="all">Semua Format (<?= (int) ($categoryCounts['all'] ?? count($docTypeDetails)) ?>)</button>
                    <?php if (isset($categoryCounts['personel']) && ($categoryCounts['personel'] > 0 || empty($categoryCounts['jamuan']))): ?>
                    <button type="button" class="btn btn-outline-primary tws-filter-chip" style="height: 42px; border-radius: 8px;" data-category="personel">Kepegawaian & Tim (<?= (int) ($categoryCounts['personel'] ?? 0) ?>)</button>
                    <?php endif; ?>
                    <?php if (isset($categoryCounts['jamuan']) && $categoryCounts['jamuan'] > 0): ?>
                    <button type="button" class="btn btn-outline-primary tws-filter-chip" style="height: 42px; border-radius: 8px;" data-category="jamuan">Konsumsi & Jamuan (<?= (int) ($categoryCounts['jamuan'] ?? 0) ?>)</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Catalog Cards List Dinamis dari Database (is_status = 1) -->
        <?php 
        $docIcons = [
            'sp' => '<svg viewBox="0 0 24 24" aria-hidden="true" style="width: 24px; height: 24px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>',
            'st' => '<svg viewBox="0 0 24 24" aria-hidden="true" style="width: 24px; height: 24px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect><path d="M9 14l2 2 4-4"></path></svg>',
            'nominatif' => '<svg viewBox="0 0 24 24" aria-hidden="true" style="width: 24px; height: 24px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>',
            'kwitansi' => '<svg viewBox="0 0 24 24" aria-hidden="true" style="width: 24px; height: 24px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>',
            'hadir' => '<svg viewBox="0 0 24 24" aria-hidden="true" style="width: 24px; height: 24px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;"><path d="M9 11l3 3L22 4"></path><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>',
            'kwitansi_jamuan' => '<svg viewBox="0 0 24 24" aria-hidden="true" style="width: 24px; height: 24px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;"><path d="M18 8h1a4 4 0 0 1 0 8h-1"></path><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path><line x1="6" y1="1" x2="6" y2="4"></line><line x1="10" y1="1" x2="10" y2="4"></line><line x1="14" y1="1" x2="14" y2="4"></line></svg>',
            'surat_jalan' => '<svg viewBox="0 0 24 24" aria-hidden="true" style="width: 24px; height: 24px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>',
            'faktur' => '<svg viewBox="0 0 24 24" aria-hidden="true" style="width: 24px; height: 24px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="12" y1="18" x2="12" y2="12"></line><line x1="9" y1="15" x2="15" y2="15"></line></svg>',
            'hadir_jamuan' => '<svg viewBox="0 0 24 24" aria-hidden="true" style="width: 24px; height: 24px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;"><path d="M18 8h1a4 4 0 0 1 0 8h-1"></path><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path><line x1="9" y1="12" x2="9" y2="16"></line></svg>'
        ];
        $i = 0;
        ?>
        <div class="row tw-animate-entry tws-list-mode" id="catalogCardsContainer" style="--animation-order: 3; row-gap: 0;">
            <?php foreach ($docTypeDetails as $key => $doc): 
                $docKey = $doc['doc_type'] ?? $key;
                $docTitle = $doc['title'] ?? '';
                $docDesc = $doc['description'] ?? ($doc['desc'] ?? '');
                $docCategoryKey = $doc['category_key'] ?? 'personel';
                $docCategory = $doc['category'] ?? '';
                $docIcon = !empty($doc['icon_svg']) ? $doc['icon_svg'] : ($docIcons[$docKey] ?? $docIcons['sp']);
                $isActive = !empty($doc['is_status']) && (int) $doc['is_status'] === 1;
                $manageUrl = $isActive ? base_url('apps-pnbp/doc/' . $docKey) : '';
                $toneIndex = ($i % 4) + 1;
                $i++;
            ?>
            <div class="col-12 tws-col-list catalog-item tw-animate-entry mb-2" data-category="<?= esc($docCategoryKey) ?>" data-title="<?= esc(strtolower($docTitle . ' ' . $docDesc . ' ' . $docCategory)) ?>" style="--animation-order: <?= $i ?>;">
                <div class="card h-100 p-2 rounded-3 border tws-service-card tws-card-soft tws-anim-card overflow-hidden position-relative tws-tone-<?= $toneIndex ?> <?= !$isActive ? 'is-disabled' : '' ?>" style="<?= $isActive ? 'cursor: pointer;' : 'cursor: not-allowed; opacity: 0.65; background-color: #f8fafc;' ?>" data-url="<?= $manageUrl ?>">
                    <div class="position-absolute tws-bg-icon-wrapper" style="opacity: 0.05;">
                        <div class="tws-bg-icon-svg">
                            <?= $docIcon ?>
                        </div>
                    </div>
                    <div class="card-body p-0 d-flex flex-column flex-md-row align-items-md-center justify-content-between" style="position: relative; z-index: 1;">
                        
                        <div class="d-flex align-items-center gap-3">
                            <div class="flex-shrink-0 d-flex align-items-center justify-content-center <?= $isActive ? 'text-primary' : 'text-secondary' ?>" style="width: 48px; height: 48px; transform: none !important;">
                                <?= $docIcon ?>
                            </div>
                            <div class="text-start py-1">
                                <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                    <h6 class="fw-bold mb-0" style="font-size: 1.05rem; color: <?= $isActive ? '#1e293b' : '#64748b' ?>;"><?= esc($docTitle) ?></h6>
                                    <?php if (!$isActive): ?>
                                        <span class="badge bg-secondary-subtle text-secondary border px-2 py-0 fw-semibold" style="font-size: 0.72rem; line-height: 1.6;">
                                            <i class="bi bi-lock-fill me-1"></i>Belum Aktif
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-muted" style="font-size: 0.86rem; line-height: 1.35;">
                                    <?= esc($docDesc) ?>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3 mt-3 mt-md-0 px-2 px-md-0 h-100">
                            <?php if ($isActive): ?>
                                <a href="<?= $manageUrl ?>" class="btn btn-primary p-0 ms-2 d-flex align-items-center justify-content-center text-white shadow-sm tws-access-btn" title="Buka Daftar <?= esc($docTitle) ?>" style="width: 32px; height: 32px; border-radius: 50% !important; min-width: 32px;">
                                    <i class="bi bi-folder2-open d-flex align-items-center justify-content-center" style="font-size: 1.05rem; line-height: 0;"></i>
                                </a>
                            <?php else: ?>
                                <button type="button" class="btn btn-light border text-muted p-0 ms-2 d-flex align-items-center justify-content-center disabled" title="Format dokumen ini belum aktif" style="width: 32px; height: 32px; border-radius: 50% !important; min-width: 32px; cursor: not-allowed; opacity: 0.7;" disabled>
                                    <i class="bi bi-lock-fill d-flex align-items-center justify-content-center" style="font-size: 0.95rem; line-height: 0;"></i>
                                </button>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div id="noResultsAlert" class="text-center py-5 d-none">
            <img src="<?= asset_url('apps/assets/images/empty-content-profile.png') ?>" alt="Kosong" style="max-width: 220px; margin-bottom: 1rem;">
            <h5 class="fw-bold text-dark">Format Dokumen Tidak Ditemukan</h5>
            <p class="text-muted">Tidak ada jenis dokumen yang cocok dengan kata kunci pencarian Anda.</p>
    </div>

    <!-- Modals -->
    <?= $this->include('Apps/pages/services/pnbp/modal_form'); ?>
</main>
<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script>
    var CURRENT_DOC_TYPE = "nominatif";
</script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/service-table-ui.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/pnbp/pnbp-main.js?v=' . time()) ?>"></script>
<script>
$(document).ready(function() {
    let currentCategory = 'all';
    let currentKeyword = '';

    function filterCatalog() {
        let visibleCount = 0;
        $('.catalog-item').each(function() {
            const item = $(this);
            const category = item.data('category');
            const title = String(item.data('title') || '').toLowerCase();

            const matchCategory = (currentCategory === 'all' || category === currentCategory);
            const matchKeyword = (!currentKeyword || title.includes(currentKeyword));

            if (matchCategory && matchKeyword) {
                item.removeClass('d-none');
                visibleCount++;
            } else {
                item.addClass('d-none');
            }
        });

        if (visibleCount === 0) {
            $('#noResultsAlert').removeClass('d-none');
        } else {
            $('#noResultsAlert').addClass('d-none');
        }
    }

    // Search Catalog
    $('#searchdata').on('input', function() {
        currentKeyword = $(this).val().toLowerCase().trim();
        if (currentKeyword.length > 0) {
            $('#twsClearSearch').removeClass('d-none');
        } else {
            $('#twsClearSearch').addClass('d-none');
        }
        filterCatalog();
    });

    $('#twsClearSearch').on('click', function() {
        $('#searchdata').val('').trigger('input').focus();
    });

    // Category Filter Chips
    $('#catalogCategoryFilters .tws-filter-chip').on('click', function() {
        $('#catalogCategoryFilters .tws-filter-chip').removeClass('is-active');
        $(this).addClass('is-active');
        currentCategory = $(this).data('category');
        filterCatalog();
    });

    // Click Card -> Open Type List (Prevent if disabled) with spinner
    $(document).on('click', '.tws-service-card', function(e) {
        if ($(e.target).closest('button:not(.tws-access-btn), a:not(.tws-access-btn)').length) return;
        if ($(this).hasClass('is-disabled')) {
            e.preventDefault();
            return false;
        }
        const url = $(this).data('url');
        if (url) {
            const btn = $(this).find('.tws-access-btn');
            if (btn.length) {
                btn.addClass('disabled is-loading');
                btn.html('<span class="spinner-border spinner-border-sm text-white" style="width: 1.15rem; height: 1.15rem; border-width: 3px;" role="status" aria-hidden="true"></span>');
            }
            window.location.href = url;
        }
    });

    // Explicit Arrow Button Click on Catalog
    $(document).on('click', '.tws-access-btn', function(e) {
        const card = $(this).closest('.tws-service-card');
        if (card.hasClass('is-disabled') || $(this).hasClass('disabled')) {
            e.preventDefault();
            return false;
        }
        const url = card.data('url') || $(this).attr('href');
        if (url && url !== '#' && !url.startsWith('javascript')) {
            e.preventDefault();
            $(this).addClass('disabled is-loading');
            $(this).html('<span class="spinner-border spinner-border-sm text-white" style="width: 1.15rem; height: 1.15rem; border-width: 3px;" role="status" aria-hidden="true"></span>');
            window.location.href = url;
        }
    });
});
</script>
<?= $this->endSection(); ?>
