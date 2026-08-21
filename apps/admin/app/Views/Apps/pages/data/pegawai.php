<?= $this->extend('Apps/layouts/main_layout'); ?>
<?= $this->section('content'); ?>
<div class="page-content py-4">
    <div class="container-fluid text-start mx-auto tw-wrap" style="max-width: 1160px; padding: 0 .85rem 1.05rem;">
        <div class="page-heading">
            <div class="row align-items-center d-flex justify-content-between">
                <div class="col-md-6 text-start">
                    <h3 class="mt-3"><b><?= $title; ?></b></h3>
                    <p class="text-subtitle text-muted">Simojang | Kantor Regional III Badan Kepegawaian Negara</p>
                </div>
                <div class="col-md-6 text-end">
                    <a href="/taskme" class="btn btn-outline-primary me-2"><i class="bi bi-chevron-left"></i></a>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#DataModal"><i
                                        class="bi bi-plus"></i> Tambah Data Baru</button>
                </div>
            </div>
        </div>
        <section class="row justify-content-center">
            <div class="col-md-12">
                <div class="card border border-primary">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-9">
                                <h4>Data Pegawai</h4>
                            </div>
                        </div>
                        <div class="table-responsive mt-4">
                            <table id="dataTable" class="table table-hover table-bordered nowrap"
                                style="width:100%">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Status</th>
                                        <th>NIP</th>
                                        <th>Nama</th>
                                        <th>Pangkat</th>
                                        <th>Gol</th>
                                        <th>Pendidikan</th>
                                        <th>Tanggal Lahir</th>
                                        <th>Jenis Kelamin</th>
                                        <th>Pernikahan</th>
                                        <th>Phone</th>
                                        <th>Email</th>
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
<?= $this->endSection(); ?>
<?= $this->section('scripts'); ?>
<script src="<?= asset_url('apps/assets/js/custom/data/pegawai.js?v=99.1') ?>"></script>
<?= $this->endSection(); ?>
