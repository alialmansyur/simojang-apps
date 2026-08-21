<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>
<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/service-table-ui.css?v=99') ?>">
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>
<div class="page-content py-4">
    <div class="container-fluid text-start mx-auto tw-wrap" style="max-width: 1160px; padding: 0 .85rem 1.05rem;">
        <div class="page-heading mb-0">
            <div class="row align-items-center d-flex justify-content-between">
                <div class="col-md-8 text-start">
                    <h3 class="mt-3"><b id="refPageTitle">Referensi</b></h3>
                    <p class="text-subtitle text-muted">Simojang | Kantor Regional III Badan Kepegawaian Negara</p>
                </div>
                <div class="col-md-4 text-end">
                    <button type="button" class="btn btn-outline-primary js-setting-reload me-2">
                        <i class="bi bi-arrow-clockwise me-1"></i>Muat Ulang
                    </button>
                    <a href="<?= base_url('/ref'); ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-grid me-1"></i>Daftar Tabel
                    </a>
                </div>
            </div>
        </div>

        <section class="row mt-2">
            <div class="col-md-12">
                <div class="card border">
                    <div class="card-body js-setting-load-card">
                        <div class="service-ui-topbar service-ui-static-topbar mb-3">
                            <div class="row g-2 align-items-center w-100 service-ui-topbar-row">
                                <div class="col-12 col-md-8">
                                    <input id="refSearch" class="form-control w-100" placeholder="Cari data...">
                                </div>
                                <div class="col-12 col-md-2">
                                    <button id="refBtnSearch" type="button" class="btn btn-primary w-100">
                                        <i class="bi bi-search me-1"></i>Cari
                                    </button>
                                </div>
                                <div class="col-12 col-md-2">
                                    <button id="refBtnAdd" type="button" class="btn btn-success w-100">
                                        <i class="bi bi-plus-circle me-1"></i>Tambah Data
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="refTable">
                                <thead id="refHead"></thead>
                                <tbody id="refBody">
                                    <tr><td class="text-center text-muted py-4">Memuat data...</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div id="refPagingInfo" class="text-muted small"></div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="refPrev">Sebelumnya</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="refNext">Berikutnya</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<div class="modal fade" id="refFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 p-2">
            <div class="modal-header">
                <h5 class="modal-title" id="refModalTitle">Tambah Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="refForm" class="row g-3"></form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="refBtnSave">Simpan</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>
<?= $this->section('scripts'); ?>
<script src="<?= asset_url('apps/assets/js/custom/pages/settings/page-loader.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/settings/ref-detail.js') ?>"></script>
<?= $this->endSection(); ?>
