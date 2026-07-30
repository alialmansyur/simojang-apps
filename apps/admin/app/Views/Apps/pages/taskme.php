<?= $this->extend('Apps/layouts/main_layout'); ?>
<?= $this->section('content'); ?>
<style>
    .shct {
        cursor: pointer !important;
    }
</style>

<div class="page-content d-flex align-items-center justify-content-center min-vh-100">

    <div class="container-sm px-4 text-start mx-auto" style="max-width: 900px; padding: 0 2rem;">
            <h3 class="fw-bold mt-3" style="font-size: 2.3rem;"><strong> Halo, <br><?= $seslog['fullname']; ?></strong></h3>
        <h4 class="text-muted" style="font-weight: 400;">Apa yang akan kamu kerjakan hari ini ?</h4>
        <div class="row d-flex align-items-stretch g-3 mt-4" id="loaded">
            <div class="col-md-12" id="spinLoadData">
                <div class="d-flex justify-content-center align-items-center" style="height:250px;">
                    <div class="text-center text-primary">
                        <span class="spinner-border spinner-border-sm me-2 text-soft" role="status"
                            aria-hidden="true"></span> Sedang memproses data ...
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="quickActionModal" tabindex="-1" aria-labelledby="quickActionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body p-4">
                    <h5 class="fw-bold mb-4">Quick Action</h5>
                    <div class="card mb-3 border border-1 shct card-upload card-action">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-shrink-0 me-3">
                                <i class="bi bi-upload fs-2 text-primary"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1"><strong>Upload Data</strong></h6>
                                <p class="text-muted small mb-0">Unggah file atau dokumen penting ke sistem.</p>
                            </div>
                        </div>
                    </div>
                    <div class="card mb-3 border border-1 scht card-entry card-action">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-shrink-0 me-3">
                                <i class="bi bi-input-cursor-text fs-2 text-primary"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1"><strong>Entry Data</strong></h6>
                                <p class="text-muted small mb-0">Masukkan data baru ke dalam sistem secara manual.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="card mb-3 border border-1 shct card-info card-action">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-shrink-0 me-3">
                                <i class="bi bi-info-circle-fill fs-2 text-primary"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1"><strong>Informasi Pekerjaan</strong></h6>
                                <p class="text-muted small mb-0">Lihat detail dan status pekerjaan</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>
<?= $this->section('scripts'); ?>
<script src="<?= asset_url('apps/assets/js/custom/pages/taskMe.js?v=99.1') ?>"></script>
<?= $this->endSection(); ?>
