<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>
<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork-common.css') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork.css?v=11') ?>">
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>
<main class="page-content" aria-labelledby="assetsPageTitle">
    <div class="text-start tw-wrap container-fluid">
        <div class="row align-items-center mt-4 mb-3" role="banner">
            <div class="col-12 col-md-8 text-start">
                <h1 class="tw-title lh-1" id="assetsPageTitle" style="color: #1a202c; font-size: 2.2rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.5rem;">
                    Manajemen Asset
                </h1>
                <p class="tw-subtitle text-secondary mb-0" style="font-size: 1rem; font-weight: 400;">
                    Kelola dan pantau seluruh asset Anda. Pilih kategori di bawah ini untuk melihat rincian asset yang terdaftar.
                </p>
            </div>
            <div class="col-12 col-md-4 text-md-end mt-2 mt-md-0">
                <a href="javascript:history.back()" class="btn btn-primary">
                    <i class="bi bi-chevron-left fs-6"></i> Kembali
                </a>
            </div>
        </div>

        <div class="tw-head row align-items-center mt-4" role="toolbar" aria-label="Aksi asset">
            <div class="col-12 d-flex align-items-center justify-content-between">
                <h5 class="mb-0" style="font-weight: 800; color: #1a202c; font-size: 1.25rem;">Daftar Kategori Asset</h5>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" onclick="window.location.reload();" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center justify-content-center px-3" style="padding-top: 0.4rem; padding-bottom: 0.4rem;">
                        <span class="fw-bold" style="font-size: 0.85rem; color: #1a202c !important; margin-right: 0.4rem;">Muat Ulang</span> <i class="bi bi-arrow-clockwise" style="font-size: 1.1rem; line-height: 0; color: #1a202c !important;"></i> 
                    </button>
                </div>
            </div>
        </div>

        <div class="row g-2 mt-2">
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $index => $cat): ?>
                    <?php 
                        $toneClass = 'tw-tone-' . (($index % 4) + 1); 
                        $iconSvg = '<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>';
                    ?>
                    <div class="col-12 col-md-6 col-xl-3">
                        <a href="<?= base_url('apps-manage-assets-detail/' . $cat['uid']) ?>" class="tw-link text-decoration-none">
                            <div class="card tw-card tw-animate-entry h-100 <?= $toneClass ?>" style="--animation-order: <?= $index ?>;">
                                <div class="card-body position-relative overflow-hidden d-flex align-items-center p-3 gap-3">
                                    <div class="tw-icon-box flex-shrink-0 d-flex align-items-center justify-content-center z-1">
                                        <span class="tw-icon"><?= $iconSvg ?></span>
                                    </div>
                                    <div class="tw-text-box d-flex flex-column text-start overflow-hidden z-1">
                                        <h6 class="fw-bold tw-team-name mb-1 lh-sm" title="<?= esc($cat['name']) ?>"><?= esc($cat['name']) ?></h6>
                                        <?php
                                            $qty = $cat['total_qty'];
                                            $qtyFormatted = (floor($qty) == $qty) ? number_format($qty, 0, ',', '.') : number_format($qty, 2, ',', '.');
                                        ?>
                                        <span class="text-muted" style="font-size: 0.8rem;">
                                            <?= number_format($cat['total_assets'], 0, ',', '.') ?> Item &bull; Total Qty: <?= $qtyFormatted ?>
                                        </span>
                                    </div>
                                    <div class="tw-card-bg-decoration pe-none">
                                        <?= $iconSvg ?>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center mt-3">
                        Belum ada kategori asset yang terdaftar.
                    </div>
                </div>
            <?php endif; ?>
        </div>

    </div>
</main>
<?= $this->endSection(); ?>
<?= $this->section('scripts'); ?>
<?= $this->endSection(); ?>
