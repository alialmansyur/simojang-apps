<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>
<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork-common.css') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork-service.css') ?>">
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>
<input type="hidden" id="key" value="<?= esc($layanan_key ?? '') ?>">
<main class="page-content" aria-labelledby="twsHeading">
    <div class="text-start tws-wrap">
        <div class="row align-items-center mt-3 mb-2 tw-animate-entry" style="--animation-order: 1;">
            <div class="col-12 col-md-8">
                <h1 class="tw-title lh-1" id="twsHeading" style="color: #1a202c; font-size: 2.2rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.5rem;">
                    Katalog Layanan
                </h1>
                <p class="tw-subtitle text-secondary mb-0" style="font-size: 1.05rem; font-weight: 500;" id="twsScopeName">
                    -
                </p>
            </div>
            <div class="col-12 col-md-4 text-md-end mt-2 mt-md-0">
                <div class="d-inline-flex align-items-center gap-2 tws-header-actions">
                    <button type="button" id="twsReload" class="btn btn-outline-primary d-none" title="Muat ulang data">
                        <i class="bi bi-arrow-clockwise fs-6 me-1"></i> Muat Ulang
                    </button>
                    <a href="javascript:history.back()" class="btn btn-primary">
                        <i class="bi bi-chevron-left fs-6"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="col-12 d-none">
                <p class="mb-1">Progress Pengisian <span class="text-primary fw-bold" id="myProgressLabel">0%</span></p>
                <div class="progress rounded tws-progress">
                <div id="myProgressBar" class="progress-bar bg-primary"
                        role="progressbar" style="width: 0%; transition: width 0.4s ease;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        </div>

        <div class="mb-3 tw-animate-entry" style="--animation-order: 2;">
            <div class="position-relative tws-search-wrap">
                <input type="search" id="searchdata" class="form-control pe-7" style="border-radius: 8px;"
                    placeholder="Cari layanan disini">
                <button type="button" class="btn tws-search-indicator" disabled>
                    <span id="twsSearchLoading" class="spinner-border spinner-border-sm text-primary d-none" role="status" aria-hidden="true"></span>
                    <i id="twsSearchIcon" class="bi bi-search fs-5 text-primary"></i>
                </button>
                <button type="button" id="twsClearSearch" class="btn tws-search-clear d-none" aria-label="Bersihkan pencarian">
                    <i class="bi bi-x-circle-fill fs-5"></i>
                </button>
            </div>
        </div>
        <div id="twsToolbar" class="tws-sticky-toolbar mb-2 tw-animate-entry" style="--animation-order: 3;">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 tws-controls-wrap">
                <div class="d-flex flex-wrap align-items-center gap-2" id="twsQuickFilters">
                    <button type="button" class="btn btn-sm btn-outline-primary tws-filter-chip is-active" data-filter="all">Semua</button>
                    <button type="button" class="btn btn-sm btn-outline-primary tws-filter-chip" data-filter="updated">Sudah Update</button>
                    <button type="button" class="btn btn-sm btn-outline-primary tws-filter-chip" data-filter="pending">Belum Update</button>
                    <button type="button" class="btn btn-sm btn-outline-primary tws-filter-chip" data-filter="accessible">Bisa Diakses</button>
                    <button type="button" class="btn btn-sm btn-outline-primary tws-filter-chip" data-filter="favorite">Favorit</button>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <select id="twsSort" class="form-select form-select-sm">
                        <option value="name_asc">Nama A-Z</option>
                        <option value="updated_desc">Terbaru Update</option>
                        <option value="pending_first">Belum Update Dulu</option>
                        <option value="favorite_first">Favorit Dulu</option>
                    </select>
                    <div class="btn-group btn-group-sm" role="group" aria-label="Mode tampilan">
                        <button type="button" id="twsViewGrid" class="btn btn-outline-primary me-2" title="Mode grid">
                            <i class="bi bi-grid-3x3-gap"></i>
                        </button>
                        <button type="button" id="twsViewList" class="btn btn-outline-primary is-active" title="Mode list">
                            <i class="bi bi-list-ul"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
            <p id="twsSummary" class="mb-0 text-muted small d-none">0 layanan</p>
            <div id="twsAccessHint" class="alert alert-warning py-1 px-2 mb-0 d-none tws-access-hint">
                <i class="bi bi-shield-lock me-1"></i>
                Beberapa layanan terkunci. Ajukan akses untuk membuka layanan.
                <button type="button" id="twsRequestAccess" class="btn btn-link btn-sm p-0 ms-1">Ajukan Akses</button>
            </div>
        </div>

        <div class="row d-flex align-items-stretch g-2" id="loaded"></div>
        <div id="twsEmptyState" class="col-12 d-none">
            <div class="d-flex flex-column align-items-center justify-content-center text-center mt-5 mb-5 pb-4 tw-animate-entry">
                <img src="<?= base_url('apps/assets/images/empty-content-profile.png') ?>" alt="Tidak Ditemukan" style="max-width: 320px; margin-bottom: 2rem;">
                <h5 class="fw-bold" style="color: #1a202c; font-size: 1.35rem;" id="twsEmptyTitle">Layanan tidak ditemukan.</h5>
                <p class="text-muted mb-0" style="font-size: 1.05rem; max-width: 450px; margin: 0 auto; line-height: 1.6;" id="twsEmptyDesc">Coba kata kunci lain atau reset pencarian.</p>
                <div class="mt-4">
                    <button type="button" id="twsResetSearch" class="btn btn-outline-primary px-4 py-2" style="border-radius: 8px;">Reset Pencarian</button>
                </div>
            </div>
        </div>
        <div id="twsErrorState" class="text-center text-danger d-none">
            <p class="mb-2">Terjadi kendala saat memuat data layanan.</p>
            <button type="button" id="twsRetryLoad" class="btn btn-outline-danger btn-sm">Coba Lagi</button>
        </div>
    </div>
</main>

<button
    type="button"
    id="twsBackToTop"
    class="tws-back-to-top"
    aria-label="Kembali ke atas"
    title="Kembali ke atas">
    <i class="bi bi-arrow-up-short" aria-hidden="true"></i>
    <span class="tws-back-to-top-label">Kembali ke atas</span>
</button>
<div class="modal fade" id="twsAccessModal" tabindex="-1" aria-labelledby="twsAccessModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="twsAccessModalLabel">Ajukan Akses Layanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-2">Beberapa layanan masih terkunci untuk akun Anda.</p>
                <p class="text-muted small mb-0">Hubungi admin untuk pengajuan akses sesuai role tim kerja.</p>
            </div>
            <div class="modal-footer border-0 pt-2">
                <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="twsOpenContactAdmin">
                    <i class="bi bi-send me-1"></i> Hubungi Admin
                </button>
            </div>
        </div>
    </div>
</div>

<template id="twsSkeletonTemplateGrid">
    <div class="col-12 col-sm-6 col-md-4 col-xl-2 tws-skel-col">
        <div class="tws-skel-card tws-skel-card-grid">
            <div class="tws-skel-icon skeleton"></div>
            <div class="tws-skel-title skeleton"></div>
            <div class="tws-skel-chip skeleton"></div>
            <div class="tws-skel-chip skeleton"></div>
            <div class="tws-skel-chip skeleton"></div>
        </div>
    </div>
</template>

<template id="twsSkeletonTemplateList">
    <div class="col-12 tws-skel-col tws-skel-col-list">
        <div class="tws-skel-card tws-skel-card-list">
            <div class="tws-skel-list-main">
                <div class="tws-skel-icon tws-skel-icon-list skeleton"></div>
                <div class="tws-skel-copy">
                    <div class="tws-skel-title tws-skel-title-list skeleton"></div>
                    <div class="tws-skel-line tws-skel-line-medium skeleton"></div>
                    <div class="tws-skel-line tws-skel-line-short skeleton"></div>
                </div>
            </div>
            <div class="tws-skel-actions">
                <div class="tws-skel-circle skeleton"></div>
                <div class="tws-skel-circle skeleton"></div>
                <div class="tws-skel-btn skeleton"></div>
            </div>
        </div>
    </div>
</template>
<?= $this->endSection(); ?>
<?= $this->section('scripts'); ?>
<script src="<?= asset_url('apps/assets/js/custom/pages/teamWorkService.js?v=3.0') ?>"></script>
<?= $this->endSection(); ?>


