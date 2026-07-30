<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>
<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork-common.css') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork.css?v=12') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/activity-gallery.css?v=2') ?>">
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>
<main class="page-content" aria-labelledby="galleryPageTitle">
    <div class="text-start tw-wrap container-fluid">
        
        <!-- Header -->
        <div class="row align-items-center mt-4 mb-3" role="banner">
            <div class="col-12 col-md-8 text-start">
                <h1 class="tw-title lh-1" id="galleryPageTitle" style="color: #1a202c; font-size: 2.2rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.5rem;">
                    Galeri Kegiatan
                </h1>
                <p class="tw-subtitle text-secondary mb-0" style="font-size: 1rem; font-weight: 400;">
                    Kelola dan jelajahi dokumentasi momen dari berbagai kegiatan di lingkungan instansi.
                </p>
            </div>
            <div class="col-12 col-md-4 text-md-end mt-2 mt-md-0">
                <a href="<?= base_url('timkerja-layanan') ?>" class="btn btn-primary">
                    <i class="bi bi-chevron-left fs-6"></i> Kembali
                </a>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="tw-head d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4" role="toolbar">
            <div class="flex-grow-1" style="max-width: 450px;">
                <div class="position-relative">
                    <i class="bi bi-search position-absolute text-muted" style="left: 1.2rem; top: 50%; transform: translateY(-50%); margin-top: -1px; line-height: 1; pointer-events: none;"></i>
                    <input type="text" id="searchGallery" class="form-control tw-search-input" placeholder="Cari berdasarkan judul kegiatan..." style="padding-left: 2.8rem; padding-top: 0.65rem; padding-bottom: 0.65rem;">
                </div>
            </div>
            <div class="d-flex flex-column flex-sm-row flex-wrap align-items-stretch align-items-sm-center gap-2">
                <select class="form-select tw-search-input flex-grow-1 flex-sm-grow-0" id="filterTimKerja" style="padding-top: 0.65rem; padding-bottom: 0.65rem; width: auto; min-width: 180px; max-width: 100%;">
                    <option value="">Semua Tim Kerja</option>
                    <?php foreach($timkerja as $tk): ?>
                        <option value="<?= $tk['id'] ?>"><?= esc($tk['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="month" id="filterBulan" class="form-control fw-bold flex-grow-1 flex-sm-grow-0" style="width: auto; height: 42px; color: #1a202c !important; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                <button type="button" class="btn btn-primary d-inline-flex align-items-center justify-content-center px-4 flex-grow-1 flex-sm-grow-0" data-bs-toggle="modal" data-bs-target="#modalAddGallery" style="height: 42px; border-radius: 8px;">
                    <span class="fw-bold" style="font-size: 0.95rem;">Tambah Data</span> <i class="bi bi-plus-lg ms-2 d-flex align-items-center" style="font-size: 1.1rem;"></i>
                </button>
            </div>
        </div>

        <!-- Hero Carousel -->
        <div id="galleryCarousel" class="carousel slide mb-5 rounded-4 overflow-hidden shadow-sm d-none" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <!-- Indicators will be loaded via AJAX -->
            </div>
            <div class="carousel-inner" style="max-height: 400px;">
                <!-- Slides will be loaded via AJAX -->
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#galleryCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#galleryCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>

        <!-- Gallery Data Area -->
        <div class="position-relative" style="min-height: 250px;">
            <!-- Gallery Grid -->
            <div class="row g-4" id="galleryGrid">
                <!-- Data will be loaded via AJAX -->
            </div>

            <!-- Empty State -->
            <div class="col-12 d-none" id="galleryEmptyState">
                <div class="d-flex flex-column align-items-center justify-content-center text-center mt-5 mb-5 pb-4 tw-animate-entry">
                    <img src="<?= base_url('apps/assets/images/empty-content-profile.png') ?>" alt="Belum Ada Kegiatan" class="empty-state-img" style="max-width: 320px; margin-bottom: 2rem; transition: transform 0.3s ease;">
                    <h5 class="fw-bold" style="color: #1a202c; font-size: 1.35rem;">Belum Ada Galeri Kegiatan</h5>
                    <p class="text-muted mb-4" style="font-size: 1.05rem; max-width: 450px; margin: 0 auto; line-height: 1.6;">
                        Anda belum memiliki data dokumentasi kegiatan. Silakan tambah data baru.
                    </p>
                    <button type="button" class="btn btn-primary d-inline-flex align-items-center justify-content-center px-4 btn-empty-state" data-bs-toggle="modal" data-bs-target="#modalAddGallery" style="height: 42px; border-radius: 8px; box-shadow: 0 4px 12px rgba(var(--bs-primary-rgb), 0.2); transition: all 0.3s ease; gap: 0.5rem;">
                        <span class="fw-bold d-flex align-items-center" style="font-size: 0.95rem; line-height: 1; padding-top: 2px;">Tambah Data Baru</span>
                        <i class="bi bi-plus-lg d-flex align-items-center" style="font-size: 1.1rem; line-height: 0;"></i>
                    </button>
                </div>
            </div>
        </div>

    </div>
</main>

<!-- Modal Add Gallery (Flat Minimalist) -->
<div class="modal fade" id="modalAddGallery" tabindex="-1" aria-labelledby="modalAddGalleryLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content flat-modal">
            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4 align-items-center">
                <h5 class="modal-title fw-bold" id="modalAddGalleryLabel" style="color: #1a202c; font-size: 1.25rem;">Tambah Dokumentasi Kegiatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formAddGallery" autocomplete="off">
                <div class="modal-body px-4 pt-3 pb-4">
                    <div class="row gy-3">
                        <div class="col-md-6">
                            <label for="inputTimKerja" class="form-label d-block fw-bold" style="font-size: 0.9rem; color: #1a202c; margin-bottom: 0.5rem;">Tim Kerja <span class="text-danger">*</span></label>
                            <select id="inputTimKerja" name="timkerja" class="form-select flat-input" required>
                                <option value="" selected disabled>Pilih Tim Kerja...</option>
                                <?php foreach($timkerja as $tk): ?>
                                    <option value="<?= $tk['id'] ?>"><?= esc($tk['nama']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="inputTanggal" class="form-label d-block fw-bold" style="font-size: 0.9rem; color: #1a202c; margin-bottom: 0.5rem;">Tanggal Kegiatan <span class="text-danger">*</span></label>
                            <input type="date" class="form-control flat-input" name="tanggal" id="inputTanggal" required>
                        </div>
                        <div class="col-12">
                            <label for="inputJudul" class="form-label d-block fw-bold" style="font-size: 0.9rem; color: #1a202c; margin-bottom: 0.5rem;">Judul Kegiatan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control flat-input" name="judul" id="inputJudul" placeholder="Contoh: Rapat Koordinasi Tahunan" required>
                        </div>
                        <div class="col-12">
                            <label for="inputDeskripsi" class="form-label d-block fw-bold" style="font-size: 0.9rem; color: #1a202c; margin-bottom: 0.5rem;">Deskripsi Singkat <span class="text-danger">*</span></label>
                            <textarea class="form-control flat-input" name="deskripsi" id="inputDeskripsi" rows="3" placeholder="Jelaskan secara singkat mengenai kegiatan ini..." required></textarea>
                        </div>
                        <div class="col-12 mt-3">
                            <div class="d-flex justify-content-between align-items-end mb-1">
                                <label class="form-label d-block fw-bold mb-0" style="font-size: 0.9rem; color: #1a202c;">Unggah Foto Kegiatan <span class="text-danger">*</span></label>
                                <span class="text-muted" style="font-size: 0.75rem;">Maksimal 1 MB (.jpg, .png)</span>
                            </div>
                            <div class="upload-area text-center p-4 position-relative flat-upload" id="uploadArea">
                                <input type="file" id="inputFoto" name="foto" class="position-absolute w-100 h-100 top-0 start-0 opacity-0" style="cursor: pointer;" accept="image/png, image/jpeg, image/jpg, image/webp">
                                <div id="uploadPlaceholder">
                                    <i class="bi bi-image text-secondary mb-2 d-block fs-3"></i>
                                    <span class="d-block fw-bold text-dark" style="font-size: 0.9rem;">Klik atau seret foto ke sini</span>
                                    <span class="d-block text-muted small mt-1">Maksimal 2MB (JPG, PNG)</span>
                                </div>
                                <div id="uploadPreview" class="d-none position-relative z-2">
                                    <img src="" alt="Preview" class="img-fluid rounded flat-border" style="max-height: 180px; object-fit: contain;">
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-light fw-bold px-3 mt-2" style="border: 1px solid #e2e8f0;" id="btnRemovePhoto">Ganti Foto</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 pb-4 px-4 justify-content-end">
                    <button type="button" class="btn btn-light fw-bold px-4 flat-btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary fw-bold px-4 flat-btn-primary" id="btnSaveGallery">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal View Gallery (Lightbox Alternative - Minimalist) -->
<div class="modal fade" id="modalViewGallery" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content flat-modal p-0 overflow-hidden">
            <div class="modal-header border-0 pb-2 px-4 pt-3 d-flex justify-content-between align-items-center bg-white z-1">
                <h5 class="modal-title fw-bold text-dark m-0" id="viewGalleryTitle" style="font-size: 1.15rem;">Judul Kegiatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 d-flex flex-column">
                <div class="w-100 bg-light d-flex align-items-center justify-content-center" style="min-height: 40vh; max-height: 70vh;">
                    <img src="" id="viewGalleryImg" class="img-fluid w-100 h-100" style="object-fit: contain;" alt="Gallery">
                </div>
                <div class="p-4 bg-white border-top">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <span class="badge px-2 py-1" style="background-color: #f1f5f9; color: #334155; border: 1px solid #e2e8f0; font-weight: 600;" id="viewGalleryTeam">Tim Kerja</span>
                        <span class="text-muted small fw-medium" id="viewGalleryDate"><i class="bi bi-calendar3"></i> Tanggal</span>
                    </div>
                    <p class="text-secondary mb-0 mt-2" id="viewGalleryDesc" style="line-height: 1.6; font-size: 0.95rem;">Deskripsi kegiatan.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<template id="gallerySkeletonTemplate">
    <div class="col-12 col-md-6 col-lg-4 col-xl-3">
        <div class="gallery-card-skel flat-border rounded-3 overflow-hidden bg-white">
            <div class="skeleton" style="height: 180px; width: 100%;"></div>
            <div class="p-3">
                <div class="skeleton mb-2" style="height: 12px; width: 40%; border-radius: 4px;"></div>
                <div class="skeleton mb-3" style="height: 18px; width: 85%; border-radius: 4px;"></div>
                <div class="skeleton" style="height: 12px; width: 60%; border-radius: 4px;"></div>
            </div>
        </div>
    </div>
</template>

<?= $this->endSection(); ?>
<?= $this->section('scripts'); ?>
<script src="<?= asset_url('apps/assets/js/custom/pages/activity-gallery.js?v=3') ?>"></script>
<?= $this->endSection(); ?>
