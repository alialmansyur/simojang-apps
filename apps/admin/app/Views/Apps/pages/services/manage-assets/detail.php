<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>
<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/service-table-ui.css?v=99') ?>">
<style>
    .service-ui-recap {
        grid-template-columns: repeat(2, 1fr) !important;
    }
</style>
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>
<div class="page-content p-2 p-md-4">
    <div class="container-fluid text-start mx-auto tw-wrap" style="max-width: 1160px;">
        <div class="page-heading mb-0">
            <div class="row align-items-center d-flex justify-content-between">
                <div class="col-md-6 text-start">
                    <h3 class="mt-3"><b>Detail Asset: <?= esc($category['name']) ?></b></h3>
                    <p class="text-subtitle text-muted">Simojang | Kantor Regional III Badan Kepegawaian Negara</p>
                </div>
                <div class="col-md-6 text-end">
                    <div class="service-page-inline-actions">
                        <button type="button" class="btn btn-outline-primary js-service-reload">
                            <i class="bi bi-arrow-clockwise me-1"></i> Muat Ulang
                        </button>
                        <a href="<?= base_url('apps-manage-assets') ?>" class="btn btn-primary">
                            <i class="bi bi-chevron-left fs-6"></i> <strong>Kembali</strong>
                        </a>
                    </div>
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

                        <div id="activeFilterContainer" class="active-filters-container my-3 align-items-center flex-wrap gap-2" style="display: none;">
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted small me-2"><i class="bi bi-funnel me-1"></i>Filter Aktif:</span>
                                <div class="active-filters-list d-flex flex-wrap gap-2"></div>
                            </div>
                        </div>

                          <div class="card border shadow-sm">
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
<script src="<?= asset_url('apps/assets/js/custom/pages/services/manage-assets/detail.js?v=99') ?>"></script>
<?= $this->endSection(); ?>
