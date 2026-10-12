<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>
<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork-common.css') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork.css?v=99') ?>">
<style>
    .tw-card {
        min-height: 88px;
    }
    .tw-category-title {
        height: 2.6em;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.3;
        font-size: 1.05rem !important;
        font-weight: 800 !important;
        color: #111827 !important;
        margin-bottom: 0.25rem !important;
    }
    .tw-access-corner {
        position: absolute;
        top: 10px;
        right: 12px;
        display: inline-flex;
        align-items: center;
        z-index: 2;
    }
    .btn-trash-hover {
        transition: all 0.2s;
    }
    .btn-trash-hover:hover {
        background-color: #fee2e2 !important;
        transform: scale(1.1);
        border-radius: 6px;
    }
    .btn-edit-hover {
        transition: all 0.2s;
    }
    .btn-edit-hover:hover {
        background-color: #e0f2fe !important;
        transform: scale(1.1);
        border-radius: 6px;
    }
</style>
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>
<main class="page-content" aria-labelledby="assetsPageTitle">
    <div class="text-start tw-wrap">
        <div class="row align-items-center mt-4 mb-3" role="banner">
            <div class="col-12 col-md-8 text-start">
                <h1 class="tw-title lh-1" id="assetsPageTitle" style="color: #1a202c; font-size: 2.2rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.5rem;">
                    Manajemen Asset
                </h1>
                <p class="tw-subtitle text-secondary mb-0" style="font-size: 1rem; font-weight: 400;">
                    Kelola dan pantau seluruh asset Anda. Pilih kategori di bawah ini untuk melihat rincian asset yang terdaftar.
                </p>
            </div>
            <div class="col-12 col-md-4 text-md-end mt-2 mt-md-0">
                <a href="javascript:history.back()" class="btn btn-primary">
                    <i class="bi bi-chevron-left fs-6"></i> Kembali
                </a>
            </div>
        </div>

        <div class="row mb-3 mt-2">
            <div class="col-12">
                <div class="alert shadow-sm border-0 rounded-3 p-3 position-relative mb-0" style="background-color: #fffbe4; border-left: 6px solid #f59e0b !important;" role="alert">
                    <div class="row align-items-center g-0 pe-5">
                        <div class="col-auto pe-3">
                            <i class="bi bi-exclamation-triangle-fill" style="color: #d97706; font-size: 2.2rem; line-height: 1;"></i>
                        </div>
                        <div class="col">
                            <h6 class="fw-bold mb-1" style="color: #92400e; font-size: 1rem; line-height: 1.2;">Himbauan: Pengelolaan &amp; Pemantauan Asset Milik Negara</h6>
                            <div style="font-size: 0.85rem; color: #b45309; line-height: 1.2;">Lakukan inventarisasi, pencatatan kuantitas, dan pemeliharaan kategori aset secara berkala untuk menjamin transparansi serta akuntabilitas aset instansi.</div>
                        </div>
                    </div>
                    
                    <div class="position-absolute top-0 end-0 p-3">
                        <button class="btn btn-sm text-nowrap fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#tataCaraCollapse" aria-expanded="false" aria-controls="tataCaraCollapse" style="background-color: #fef3c7; color: #92400e; border: 1px solid #fcd34d;">
                            <i class="bi bi-info-circle me-1"></i> Tata Cara
                        </button>
                    </div>
                    
                    <div class="collapse" id="tataCaraCollapse">
                        <hr style="border-color: #f59e0b; opacity: 0.2; margin-top: 1rem; margin-bottom: 1rem;">
                        <ol class="mb-0 ps-3" style="font-size: 0.85rem; line-height: 1.7; color: #78350f;">
                            <li><strong>Cari &amp; Pilih Kategori Asset:</strong> Gunakan kotak pencarian untuk menemukan kategori aset, lalu klik kartu kategori untuk membuka rincian barang, kode, satuan, dan kuantitas aset yang terdaftar.</li>
                            <li><strong>Tambah Kategori Baru:</strong> Jika ingin mendaftarkan klasifikasi aset baru, klik tombol <strong>"Tambah Kategori"</strong> dan masukkan nama kategori aset.</li>
                            <li><strong>Kelola &amp; Perbarui Data:</strong> Gunakan tombol edit untuk memperbarui nama kategori atau tombol hapus jika kategori belum memiliki rincian aset terdaftar.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="tw-head d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4" role="toolbar">
            <div class="flex-grow-1" style="max-width: 450px;">
                <div class="position-relative">
                    <i class="bi bi-search position-absolute text-muted" style="left: 1.2rem; top: 50%; transform: translateY(-50%); margin-top: -1px; line-height: 1; pointer-events: none;"></i>
                    <input type="text" id="searchInput" class="form-control tw-search-input" placeholder="Cari berdasarkan nama kategori asset..." style="padding-left: 2.8rem; padding-top: 0.65rem; padding-bottom: 0.65rem;">
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center justify-content-center px-4 py-2" onclick="window.location.reload();">
                    <span class="fw-bold" style="font-size: 0.95rem; color: #1a202c !important;">Muat Ulang</span> <i class="bi bi-arrow-clockwise ms-2 d-flex align-items-center" style="font-size: 1.1rem; color: #1a202c !important;"></i>
                </button>
                <button type="button" class="btn btn-primary d-inline-flex align-items-center justify-content-center px-4 py-2" data-bs-toggle="modal" data-bs-target="#CategoryModal">
                    <span class="fw-bold" style="font-size: 0.95rem;">Tambah Kategori</span> <i class="bi bi-plus-lg ms-2 d-flex align-items-center" style="font-size: 1.1rem;"></i>
                </button>
            </div>
        </div>

        <div class="row g-2 mt-2" id="categoryList">
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $index => $cat): ?>
                    <?php 
                        $toneClass = 'tw-tone-' . (($index % 4) + 1); 
                        $iconSvg = '<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>';
                        $totalAssets = (int) $cat['total_assets'];
                        $qty = (float) $cat['total_qty'];
                        $qtyFormatted = (floor($qty) == $qty) ? number_format($qty, 0, ',', '.') : number_format($qty, 2, ',', '.');
                    ?>
                    <div class="col-12 col-md-6 col-xl-3 category-item" data-name="<?= strtolower(esc($cat['name'])) ?>">
                        <a href="<?= base_url('apps-manage-assets-detail/' . $cat['uid']) ?>" class="tw-link text-decoration-none h-100 d-block">
                            <div class="card tw-card tw-animate-entry h-100 <?= $toneClass ?>" style="--animation-order: <?= $index ?>; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: var(--twx-card-radius); transition: all 0.2s ease;">
                                <div class="card-body position-relative overflow-hidden d-flex align-items-center p-3 gap-3">
                                    <div class="tw-icon-box flex-shrink-0 d-flex align-items-center justify-content-center z-1">
                                        <span class="tw-icon"><?= $iconSvg ?></span>
                                    </div>
                                    <div class="tw-text-box d-flex flex-column text-start overflow-hidden z-1 pe-4 flex-grow-1">
                                        <h6 class="fw-bold tw-team-name tw-category-title mb-1 lh-sm text-dark" title="<?= esc($cat['name']) ?>"><?= esc($cat['name']) ?></h6>
                                        <span class="text-muted" style="font-size: 0.8rem; line-height: 1.2;">
                                            <?= number_format($totalAssets, 0, ',', '.') ?> Item &bull; Total Qty: <?= $qtyFormatted ?>
                                        </span>
                                    </div>
                                    <div class="tw-access-corner z-2 gap-1 d-flex">
                                        <button type="button" class="btn btn-sm p-1 lh-1 btn-edit-hover" title="Edit Kategori" style="background: rgba(2, 132, 199, 0.08); border: 1px solid rgba(2, 132, 199, 0.25); border-radius: 6px; color: #0284c7; width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s;" onclick="event.preventDefault(); event.stopPropagation(); editCategory('<?= esc($cat['uid']) ?>', '<?= esc(addslashes($cat['name'])) ?>');">
                                            <i class="bi bi-pencil" style="font-size: 0.8rem;"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm p-1 lh-1 btn-trash-hover" title="Hapus Kategori" style="background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.25); border-radius: 6px; color: #ef4444; width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s;" onclick="event.preventDefault(); event.stopPropagation(); deleteCategory('<?= esc($cat['uid']) ?>', '<?= esc(addslashes($cat['name'])) ?>', <?= $totalAssets ?>);">
                                            <i class="bi bi-trash3" style="font-size: 0.8rem;"></i>
                                        </button>
                                    </div>
                                    <div class="tw-card-bg-decoration pe-none" style="opacity: 0.03; right: -10px; bottom: -10px;">
                                        <?= $iconSvg ?>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12" id="noDataInfo">
                    <div class="d-flex flex-column align-items-center justify-content-center text-center mt-5 mb-5 pb-4">
                        <img src="<?= asset_url('apps/assets/images/empty-content-profile.png') ?>" alt="Belum Ada Kategori Asset" style="max-width: 320px; margin-bottom: 2rem;">
                        <h5 class="fw-bold" style="color: #1a202c; font-size: 1.35rem;">Belum Ada Kategori Asset</h5>
                        <p class="text-muted mb-0" style="font-size: 1.05rem; max-width: 450px; margin: 0 auto; line-height: 1.6;">
                            Anda belum memiliki data kategori asset. Buat kategori pertama Anda sekarang.
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    </div>
</main>

<!-- Modal Tambah Kategori -->
<div class="modal fade" id="CategoryModal" tabindex="-1" aria-labelledby="CategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content" style="border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); overflow: hidden;">
            <div class="modal-header align-items-center" style="border-bottom: 1px solid #f1f5f9; padding: 1.5rem 1.75rem 1.25rem; background-color: #ffffff;">
                <h5 class="modal-title fw-bold mb-0" id="CategoryModalLabel" style="font-size: 1.25rem; color: #1a202c !important;">Tambah Kategori Asset Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.8rem;"></button>
            </div>
            <form id="formCategory" method="post" autocomplete="off">
                <div class="modal-body" style="padding: 1.75rem; background-color: #fcfdfd;">
                    <div class="row gy-3">
                        <div class="col-12">
                            <label class="form-label d-block fw-bold" style="font-size: 0.9rem; color: #1a202c; margin-bottom: 0.5rem;">Nama Kategori Asset <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="Contoh: Peralatan dan Mesin, Kendaraan Dinas, dll" style="background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.75rem 1rem; font-size: 0.95rem; color: #1e293b; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between align-items-center" style="border-top: 1px solid #f1f5f9; padding: 1.25rem 1.75rem; background-color: #ffffff;">
                    <button type="button" class="btn btn-light px-4 py-2 fw-bold" data-bs-dismiss="modal" style="color: #64748b; border-radius: 8px; background: #f1f5f9; border: none;">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 py-2 fw-bold" id="btnSaveCategory" style="border-radius: 8px; box-shadow: 0 4px 12px rgba(var(--bs-primary-rgb), 0.2);">
                        Simpan Kategori
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Kategori -->
<div class="modal fade" id="EditCategoryModal" tabindex="-1" aria-labelledby="EditCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content" style="border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); overflow: hidden;">
            <div class="modal-header align-items-center" style="border-bottom: 1px solid #f1f5f9; padding: 1.5rem 1.75rem 1.25rem; background-color: #ffffff;">
                <h5 class="modal-title fw-bold mb-0" id="EditCategoryModalLabel" style="font-size: 1.25rem; color: #1a202c !important;">Edit Kategori Asset</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.8rem;"></button>
            </div>
            <form id="formEditCategory" method="post" autocomplete="off">
                <input type="hidden" name="uid" id="editCategoryUid">
                <div class="modal-body" style="padding: 1.75rem; background-color: #fcfdfd;">
                    <div class="row gy-3">
                        <div class="col-12">
                            <label class="form-label d-block fw-bold" style="font-size: 0.9rem; color: #1a202c; margin-bottom: 0.5rem;">Nama Kategori Asset <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="editCategoryName" class="form-control" required placeholder="Masukkan nama kategori asset" style="background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.75rem 1rem; font-size: 0.95rem; color: #1e293b; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between align-items-center" style="border-top: 1px solid #f1f5f9; padding: 1.25rem 1.75rem; background-color: #ffffff;">
                    <button type="button" class="btn btn-light px-4 py-2 fw-bold" data-bs-dismiss="modal" style="color: #64748b; border-radius: 8px; background: #f1f5f9; border: none;">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 py-2 fw-bold" id="btnUpdateCategory" style="border-radius: 8px; box-shadow: 0 4px 12px rgba(var(--bs-primary-rgb), 0.2);">
                        Perbarui Kategori
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
<?= $this->section('scripts'); ?>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/manage-assets/index.js?v=99') ?>"></script>
<?= $this->endSection(); ?>
