<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>
<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork-common.css') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork.css?v=11') ?>">
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>
<main class="page-content" aria-labelledby="teamworkPageTitle">
    <div class="text-start tw-wrap">
        <div class="tw-welcome text-start mt-3 mb-2" role="banner">
            <h1 class="tw-title lh-1" id="teamworkPageTitle" style="color: #1a202c; font-size: 2.2rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.5rem;">
                Tim Kerja
            </h1>
            <div style="max-width: 580px; margin: 0; line-height: 1.6;">
                <p class="tw-subtitle text-secondary mb-0" style="font-size: 1.05rem; font-weight: 500;">
                    <strong>Pilih unit kerja yang akan Anda kelola hari ini.</strong> Setiap tim memiliki daftar layanan spesifik yang dirancang untuk mempermudah dan mempercepat alur kerja operasional.
                </p>
            </div>
        </div>
        <div class="tw-head row align-items-center mt-4" role="toolbar" aria-label="Aksi tim kerja">
            <div class="col-12 d-flex align-items-center justify-content-between">
                <h5 class="mb-0" style="font-weight: 800; color: #1a202c; font-size: 1.25rem;">Daftar Tim Kerja</h5>
                <button type="button" id="twReload" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center justify-content-center gap-2 px-3" style="padding-top: 0.4rem; padding-bottom: 0.4rem;" aria-label="Muat ulang daftar tim kerja">
                    <i class="bi bi-arrow-clockwise reload-icon" style="font-size: 1.1rem; line-height: 0;"></i> <span class="fw-bold" style="font-size: 0.85rem;">Muat Ulang</span>
                </button>
            </div>
        </div>
        <div class="row g-2" id="loaded" aria-live="polite" aria-busy="true"></div>
        <div id="twEmptyState" class="tw-empty d-none mt-2" role="status" aria-live="polite">
            <div class="tw-empty-illustration mb-2" id="twEmptyLottie"></div>
            <p class="mb-1 fw-semibold">Data tim kerja belum tersedia.</p>
            <p class="mb-0 small">Silakan muat ulang halaman atau hubungi admin.</p>
        </div>
        <div id="twErrorState" class="tw-empty d-none mt-2" role="alert">Gagal memuat data tim kerja. Silakan muat ulang halaman.</div>
        <noscript>
            <div class="alert alert-warning mt-3 mb-0">
                Javascript perlu diaktifkan untuk menampilkan daftar tim kerja.
            </div>
        </noscript>
    </div>
</main>
<template id="twSkeletonTemplate">
    <div class="col-12 col-md-6 col-xl-3">
        <div class="tw-skel-card d-flex align-items-center px-3 py-2 gap-3" style="border: 1px solid var(--twx-card-border); border-radius: var(--twx-card-radius); background: #fff;">
            <div class="tw-skel-icon skeleton" style="width: 56px; height: 56px; border-radius: 8px; flex-shrink: 0;"></div>
            <div class="d-flex flex-column w-100 gap-2">
                <div class="tw-skel-title skeleton" style="width: 40%; height: 12px; border-radius: 4px;"></div>
                <div class="tw-skel-sub skeleton" style="width: 80%; height: 16px; border-radius: 4px;"></div>
            </div>
        </div>
    </div>
</template>
<?= $this->endSection(); ?>
<?= $this->section('scripts'); ?>
<script src="<?= asset_url('apps/assets/js/custom/pages/teamWork.js?v=4') ?>"></script>
<?= $this->endSection(); ?>
