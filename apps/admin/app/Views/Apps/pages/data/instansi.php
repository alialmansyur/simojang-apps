<?= $this->extend('Apps/layouts/main_layout'); ?>
<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/service-table-ui.css?v=99') ?>">
<style>
    table.dataTable { border-bottom: 1px solid #dee2e6 !important; }
    table.dataTable.no-footer { border-bottom: 1px solid #dee2e6 !important; }
</style>
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>
<div class="page-content py-4">
    <div class="container-fluid text-start mx-auto tw-wrap" style="max-width: 1160px; padding: 0 .85rem 1.05rem;">
        <div class="page-heading mb-0">
            <div class="row align-items-center d-flex justify-content-between">
                <div class="col-md-6 text-start">
                    <h3 class="mt-3"><b><?= $title; ?></b></h3>
                </div>
                <div class="col-md-6 text-end">
                    <a href="/taskme" class="btn btn-outline-primary me-2"><i class="bi bi-chevron-left"></i></a>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#DataModal"><i
                                        class="bi bi-plus"></i> Tambah Data Baru</button>
                </div>
            </div>
        </div>
        <section class="row mt-3">
            <div class="col-md-12">
                <div class="card border shadow-sm">
                    <div class="card-body p-3">
                        <div class="table-responsive">
                            <table id="dataTable" class="table table-hover table-bordered nowrap"
                                style="width:100%">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Status</th>
                                        <th>Kode</th>
                                        <th>Nama Instansi</th>
                                        <th>Last Updated</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<div class="modal fade" id="DataModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 p-2">
            <div class="modal-header">
                <h5 class="modal-title" id="DataModalTitle">Tambah Data Instansi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="DataForm" class="row g-3">
                    <input type="hidden" name="id" id="instansi_id">
                    <div class="col-md-4">
                        <label class="form-label">Kode Instansi</label>
                        <input type="text" class="form-control" name="kodeins" id="kodeins" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Nama Instansi</label>
                        <input type="text" class="form-control" name="nama" id="nama" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnSaveData">Simpan</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>
<?= $this->section('scripts'); ?>
<script src="<?= asset_url('apps/assets/js/custom/data/instansi.js?v=99.1') ?>"></script>
<?= $this->endSection(); ?>
