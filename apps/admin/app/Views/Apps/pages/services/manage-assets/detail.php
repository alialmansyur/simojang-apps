<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>
<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/service-table-ui.css?v=2') ?>">
<style>
    .service-ui-recap {
        grid-template-columns: repeat(2, 1fr) !important;
    }
</style>
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>
<div class="page-content py-4">
    <div class="container-fluid text-start mx-auto tw-wrap" style="max-width: 1160px; padding: 0 .85rem 1.05rem;">
        <div class="page-heading mb-0">
            <div class="row align-items-center mt-4 mb-3" role="banner">
                <div class="col-12 col-md-8 text-start">
                    <h1 class="tw-title lh-1" style="color: #1a202c; font-size: 2.2rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.5rem;">
                        Detail Asset: <?= esc($category['name']) ?>
                    </h1>
                    <p class="tw-subtitle text-secondary mb-0" style="font-size: 1rem; font-weight: 400;">
                        Simojang | Kantor Regional III Badan Kepegawaian Negara
                    </p>
                </div>
                <div class="col-12 col-md-4 text-md-end mt-2 mt-md-0">
                    <a href="<?= base_url('apps-manage-assets') ?>" class="btn btn-primary">
                        <i class="bi bi-chevron-left fs-6"></i> Kembali
                    </a>
                </div>
            </div>
        </div>

        <section class="row">
            <div class="col-md-12">
                <div class="service-ui-topbar mb-3 service-ui-static-topbar">
                            <div class="dropdown">
                                <!-- Placeholders for filters if needed -->
                            </div>
                            <div class="d-flex align-items-center justify-content-end">
                                <!-- <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#DataModal">
                                    <i class="bi bi-plus me-2"></i>Tambah Data
                                </button> -->
                            </div>
                        </div>
                          <div class="card border">
                              <div class="card-body p-3">
                                  <div class="table-responsive">
                            <input type="hidden" id="category_uid" value="<?= esc($category['uid']) ?>">
                            <table id="dataTable" class="table table-bordered table-hover nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th><strong>No</strong></th>
                                        <th><strong>Kode</strong></th>
                                        <th><strong>Subcode</strong></th>
                                        <th><strong>Uraian</strong></th>
                                        <th><strong>Satuan</strong></th>
                                        <th><strong>Qty</strong></th>
                                        <th><strong>Created Date</strong></th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>
</div>
<?= $this->endSection(); ?>
<?= $this->section('scripts'); ?>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/service-table-ui.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/manage-assets/detail.js?v=1') ?>"></script>
<?= $this->endSection(); ?>
