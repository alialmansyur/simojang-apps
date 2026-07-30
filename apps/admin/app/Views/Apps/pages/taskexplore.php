<?= $this->extend('Apps/layouts/main_layout'); ?>
<?= $this->section('content'); ?>
<div class="page-content py-4">
    <div class="container-fluid text-start mx-auto tw-wrap" style="max-width: 1160px; padding: 0 .85rem 1.05rem;">
        <div class="page-heading">
            <div id="layanan-container"></div>
            <div class="row align-items-center d-flex justify-content-between">
                <div class="col-md-6 text-start">
                    <h3 class="mt-3"><b><?= $title; ?></b></h3>
                    <p class="text-subtitle text-muted">Simojang | Kantor Regional III Badan Kepegawaian Negara</p>
                </div>
            </div>
        </div>
        <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" id="home-tab" data-bs-toggle="tab" href="#home" role="tab"
                    aria-controls="home" aria-selected="true"><strong>Semua Data</strong></a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="profile-tab" data-bs-toggle="tab" href="#profile" role="tab"
                    aria-controls="profile" aria-selected="false"><strong>Untuk Saya</strong></a>
            </li>
        </ul>
        <h5 class="text-bold mb-4" style="font-weight: 400;"><b>Pilih layanan untuk di enroll ke dalam task kamu</b></h5>
        <div class="position-relative">
            <input type="search" id="searchdata" class="form-control rounded form-control-lg pe-5"
                placeholder="Cari tugas disini" style="border-radius:0.95em !important">
            <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y me-2 p-0"
                disabled>
                <i class="bi bi-search fs-4 text-primary"></i>
            </button>
        </div>
        <input type="hidden" id="unit" value=0>
        <div class="row mt-4">
            <div class="col-3">
                <div class="nav flex-column nav-pills" role="tablist" aria-orientation="vertical">
                    <a class="nav-link bidang rounded active" data-key="0" data-bs-toggle="pill" href="#started"
                        role="tab">Semua Bidang</a>
                    <a class="nav-link bidang rounded" data-key="2" data-bs-toggle="pill" href="#" role="tab">Bidang
                        TU</a>
                    <a class="nav-link bidang rounded" data-key="4" data-bs-toggle="pill" href="#" role="tab">Bidang
                        INKA</a>
                    <a class="nav-link bidang rounded" data-key="6" data-bs-toggle="pill" href="#" role="tab">Bidang
                        PENSIUN</a>
                    <a class="nav-link bidang rounded" data-key="3" data-bs-toggle="pill" href="#" role="tab">Bidang
                        MUTASI</a>
                    <a class="nav-link bidang rounded" data-key="5" data-bs-toggle="pill" href="#" role="tab">Bidang
                        PDSK</a>
                </div>
            </div>
            <div class="col-9">
                <div class="tab-content" id="v-pills-tabContent">
                    <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel" aria-labelledby="started">
                        <div class="row d-flex align-items-stretch g-3" id="loaded">
                            <div class="col-md-12" id="spinLoadData">
                                <div class="d-flex justify-content-center align-items-center" style="height:500px;">
                                    <div class="text-center text-primary">
                                        <span class="spinner-border spinner-border-sm me-2 text-primary" role="status"
                                            aria-hidden="true"></span> Sedang memproses data ...
                                    </div>
                                </div>
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
<script src="<?= asset_url('apps/assets/js/custom/pages/taskExplore.js?v=99.1') ?>"></script>
<?= $this->endSection(); ?>
