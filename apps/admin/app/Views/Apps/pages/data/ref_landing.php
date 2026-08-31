<?= $this->extend('Apps/layouts/main_layout'); ?>
<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork-common.css') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/settings/ref-landing.css') ?>">
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>
<div class="page-content py-4">
    <div class="container-fluid text-start mx-auto tw-wrap" style="max-width: 1160px; padding: 0 .85rem 1.05rem;">
        <div class="ref-welcome text-center" role="banner">
            <h2 class="ref-title" id="refPageTitle">Referensi Data Support</h2>
            <p class="ref-subtitle">Pilih menu referensi untuk kelola master data yang Anda miliki akses.</p>
        </div>
        <section class="row mt-2">
            <div class="col-md-12">
                <div class="card border mb-0">
                    <div class="card-body js-setting-load-card">
                        <div class="ref-head row align-items-center" role="toolbar" aria-label="Aksi referensi">
                            <div class="col-12 d-flex align-items-center justify-content-between">
                                <h5 class="mb-0 fw-bold text-primary">Daftar Menu Referensi</h5>
                                <button type="button" class="btn btn-sm btn-outline-primary js-setting-reload">
                                    <i class="bi bi-arrow-clockwise me-1"></i> Muat Ulang
                                </button>
                            </div>
                        </div>
                        <div id="refLandingGrid" class="row ref-grid g-2" aria-live="polite" aria-busy="true">
                            <div class="col-12 text-center py-4 text-muted">Memuat tabel referensi...</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
<?= $this->endSection(); ?>
<?= $this->section('scripts'); ?>
<script src="<?= asset_url('apps/assets/js/custom/pages/settings/page-loader.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/settings/ref-landing.js') ?>"></script>
<?= $this->endSection(); ?>
