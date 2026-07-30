<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>
<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/pembinaanmanajemenasn/main.css') ?>">
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>
<div class="page-content d-flex align-items-center justify-content-center py-3">
    <div class="container-sm text-start mx-auto">
        <div class="card border-0 shadow-sm rounded-4 pmasn-card">
            <div class="card-body p-4 p-md-5">
                <span class="badge text-bg-primary mb-3">Tim Kerja Pembinaan Manajemen ASN</span>
                <h3 class="fw-bold mb-2"><?= esc($service_name ?? 'Layanan') ?></h3>
                <p class="text-muted mb-4">
                    Halaman layanan sudah aktif dan siap dipakai sebagai pondasi pengembangan fitur berikutnya.
                </p>
                <div class="d-flex flex-wrap gap-2 pmasn-actions">
                    <a href="<?= base_url('timkerja') ?>" class="btn btn-outline-primary">
                        <i class="bi bi-people me-1"></i> Kembali ke Tim Kerja
                    </a>
                    <a href="javascript:history.back()" class="btn btn-primary">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                    <button type="button" class="btn btn-outline-primary" onclick="window.location.reload()">
                        <i class="bi bi-arrow-clockwise me-1"></i> Muat Ulang
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>
