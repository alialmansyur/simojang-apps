<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>
<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork-common.css') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork.css?v=99') ?>">
<style>
    .progress-bar-custom {
        height: 8px;
        border-radius: 4px;
        background-color: #e2e8f0;
        overflow: hidden;
    }
    .progress-bar-fill {
        height: 100%;
        background-color: #1040c1;
        transition: width 0.3s ease;
    }
    .btn-trash-hover {
        transition: all 0.2s;
        color: #ef4444 !important;
    }
    .btn-trash-hover:hover {
        background-color: #fee2e2 !important;
        transform: scale(1.1);
        border-radius: 4px;
    }
</style>
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>
<main class="page-content" aria-labelledby="projectPageTitle">
    <div class="text-start tw-wrap container-fluid">
        <div class="row align-items-center mt-4 mb-3" role="banner">
            <div class="col-12 col-md-8 text-start">
                <h1 class="tw-title lh-1" id="projectPageTitle" style="color: #1a202c; font-size: 2.2rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.5rem;">
                    Manajemen Proyek
                </h1>
                <p class="tw-subtitle text-secondary mb-0" style="font-size: 1rem; font-weight: 400;">
                    Kelola dan pantau seluruh proyek Anda. Lihat progres, anggaran, dan detail lainnya.
                </p>
            </div>
            <div class="col-12 col-md-4 text-md-end mt-2 mt-md-0">
                <a href="javascript:history.back()" class="btn btn-primary">
                    <i class="bi bi-chevron-left fs-6"></i> Kembali
                </a>
            </div>
        </div>

        <div class="tw-head d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4" role="toolbar">
            <div class="flex-grow-1" style="max-width: 450px;">
                <div class="position-relative">
                    <i class="bi bi-search position-absolute text-muted" style="left: 1.2rem; top: 50%; transform: translateY(-50%); margin-top: -1px; line-height: 1; pointer-events: none;"></i>
                    <input type="text" id="searchInput" class="form-control tw-search-input" placeholder="Cari berdasarkan nama atau kategori proyek..." style="padding-left: 2.8rem; padding-top: 0.65rem; padding-bottom: 0.65rem;">
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center justify-content-center px-4 py-2" onclick="window.location.reload();">
                    <span class="fw-bold" style="font-size: 0.95rem; color: #1a202c !important;">Muat Ulang</span> <i class="bi bi-arrow-clockwise ms-2 d-flex align-items-center" style="font-size: 1.1rem; color: #1a202c !important;"></i>
                </button>
                <button type="button" class="btn btn-primary d-inline-flex align-items-center justify-content-center px-4 py-2" data-bs-toggle="modal" data-bs-target="#ProjectModal">
                    <span class="fw-bold" style="font-size: 0.95rem;">Tambah Proyek</span> <i class="bi bi-plus-lg ms-2 d-flex align-items-center" style="font-size: 1.1rem;"></i>
                </button>
            </div>
        </div>

        <div class="row g-3 mt-3" id="projectList">
            <!-- Skeleton Loader (Hidden by default) -->
            <div class="col-12" id="projectSkeleton" style="display: none;">
                <div class="row g-3">
                    <?php for($i=0; $i<6; $i++): ?>
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card tw-card shadow-none project-card-flat h-100" style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: var(--twx-card-radius);">
                            <div class="card-body position-relative overflow-hidden d-flex flex-column p-4 gap-1">
                                <div class="d-flex align-items-start gap-3 z-1 w-100 mb-3">
                                    <div class="tw-icon-box flex-shrink-0 skeleton" style="width: 48px; height: 48px; border-radius: 12px;"></div>
                                    <div class="tw-text-box d-flex flex-column text-start overflow-hidden flex-grow-1 mt-1">
                                        <div class="skeleton mb-2" style="width: 80%; height: 20px; border-radius: 4px;"></div>
                                        <div class="d-flex align-items-center gap-2 mb-0 flex-wrap mt-1">
                                            <div class="skeleton" style="width: 60px; height: 18px; border-radius: 6px;"></div>
                                            <div class="skeleton" style="width: 100px; height: 14px; border-radius: 4px;"></div>
                                        </div>
                                    </div>
                                    <div class="z-2 ms-auto">
                                        <div class="skeleton" style="width: 32px; height: 32px; border-radius: 8px;"></div>
                                    </div>
                                </div>
                                
                                <div class="mt-3 z-1 w-100">
                                    <div class="d-flex justify-content-between mb-2 align-items-end">
                                        <div class="skeleton" style="width: 50px; height: 14px; border-radius: 4px;"></div>
                                        <div class="skeleton" style="width: 40px; height: 16px; border-radius: 4px;"></div>
                                    </div>
                                    <div class="skeleton w-100 mb-3" style="height: 6px; border-radius: 10px;"></div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex flex-column gap-1">
                                            <div class="skeleton" style="width: 40px; height: 10px; border-radius: 3px;"></div>
                                            <div class="skeleton" style="width: 80px; height: 16px; border-radius: 4px;"></div>
                                        </div>
                                        <div class="d-flex flex-column text-end gap-1 align-items-end">
                                            <div class="skeleton" style="width: 30px; height: 10px; border-radius: 3px;"></div>
                                            <div class="skeleton" style="width: 80px; height: 16px; border-radius: 4px;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>

            <?php if (!empty($projects)): ?>
                <?php foreach ($projects as $index => $proj): ?>
                    <?php 
                        $toneClass = 'tw-tone-' . (($index % 4) + 1); 
                        $iconSvg = '<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>';
                        $progress = (float)$proj['progress_percentage'];
                        $budget = (float)$proj['budget_amount'];
                        $realized = (float)$proj['realized_budget_amount'];
                        $sisa = $budget - $realized;
                    ?>
                    <div class="col-12 col-md-6 col-xl-4 project-item" data-name="<?= strtolower(esc($proj['name'])) ?>" data-category="<?= strtolower(esc($proj['category'])) ?>">
                        <a href="<?= base_url('apps-manage-project-detail/' . $proj['uid']) ?>" class="tw-link text-decoration-none">
                            <div class="card tw-card shadow-none tw-animate-entry <?= $toneClass ?> project-card-flat" style="--animation-order: <?= $index ?>; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: var(--twx-card-radius); transition: all 0.2s ease;">
                                <div class="card-body position-relative overflow-hidden d-flex flex-column p-4 gap-1">
                                    <div class="d-flex align-items-start gap-3 z-1 w-100 mb-3">
                                        <div class="tw-icon-box flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; border-radius: 12px; background: rgba(var(--bs-primary-rgb), 0.1); color: var(--bs-primary);">
                                            <span class="tw-icon" style="width: 24px; height: 24px;"><?= $iconSvg ?></span>
                                        </div>
                                        <div class="tw-text-box d-flex flex-column text-start overflow-hidden flex-grow-1 mt-1">
                                            <h6 class="fw-bold tw-team-name mb-1 lh-sm text-dark" title="<?= esc($proj['name']) ?>" style="font-size: 1.1rem;"><?= esc($proj['name']) ?></h6>
                                            <div class="d-flex align-items-center gap-2 mb-0 flex-wrap mt-1">
                                                <span class="badge" style="background: #f1f5f9; color: #475569; font-size: 0.7rem; font-weight: 600; padding: 0.35rem 0.6rem; border-radius: 6px; border: 1px solid #e2e8f0;"><?= esc($proj['category'] ?? 'Tanpa Kategori') ?></span>
                                                <span class="text-muted text-nowrap d-flex align-items-center" style="font-size: 0.75rem; font-weight: 500;"><i class="bi bi-calendar3 me-1"></i> <?= date('d/m/y', strtotime($proj['start_date'])) ?> - <?= date('d/m/y', strtotime($proj['target_end_date'])) ?></span>
                                            </div>
                                        </div>
                                        <div class="z-2 ms-auto">
                                            <button type="button" class="btn btn-sm p-2 lh-1 btn-trash-hover" title="Hapus Proyek" style="background: #fff0f2; border: 1px solid #ffe4e6; color: #f43f5e; border-radius: 8px; transition: all 0.2s;" onclick="event.preventDefault(); deleteProject('<?= esc($proj['uid']) ?>', '<?= esc(addslashes($proj['name'])) ?>');">
                                                <i class="bi bi-trash3" style="font-size: 1rem;"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3 z-1 w-100">
                                        <div class="d-flex justify-content-between mb-2 align-items-end" style="font-size: 0.85rem; font-weight: 700; color: #334155 !important;">
                                            <span>Progres</span>
                                            <span class="text-primary" style="font-size: 1rem;"><?= number_format($progress, 2, ',', '.') ?>%</span>
                                        </div>
                                        <div class="progress w-100 mb-3" style="height: 6px; background-color: #f1f5f9; border-radius: 10px;">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $progress ?>%; border-radius: 10px;" aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex flex-column">
                                                <span style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; color: #94a3b8;">Plafon</span>
                                                <strong style="color: #334155; font-size: 0.95rem;">Rp <?= number_format($budget, 0, ',', '.') ?></strong>
                                            </div>
                                            <div class="d-flex flex-column text-end">
                                                <span style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; color: #94a3b8;">Sisa</span>
                                                <strong style="color: #334155; font-size: 0.95rem;">Rp <?= number_format($sisa, 0, ',', '.') ?></strong>
                                            </div>
                                        </div>
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
                        <img src="<?= asset_url('apps/assets/images/empty-content-profile.png') ?>" alt="Belum Ada Proyek" style="max-width: 320px; margin-bottom: 2rem;">
                        <h5 class="fw-bold" style="color: #1a202c; font-size: 1.35rem;">Belum Ada Proyek</h5>
                        <p class="text-muted mb-0" style="font-size: 1.05rem; max-width: 450px; margin: 0 auto; line-height: 1.6;">
                            Anda belum memiliki data riwayat Proyek. Buat Proyek pertama Anda sekarang.
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    </div>
</main>

<div class="modal fade" id="ProjectModal" tabindex="-1" aria-labelledby="ProjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); overflow: hidden;">
            <div class="modal-header align-items-center" style="border-bottom: 1px solid #f1f5f9; padding: 1.5rem 1.75rem 1.25rem; background-color: #ffffff;">
                <h5 class="modal-title fw-bold mb-0" id="ProjectModalLabel" style="font-size: 1.25rem; color: #1a202c !important;">Tambah Proyek Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.8rem;"></button>
            </div>
            <form id="formProject" method="post" autocomplete="off">
                <div class="modal-body" style="padding: 1.75rem; background-color: #fcfdfd;">
                    <div class="row gy-4">
                        <div class="col-md-12">
                            <label class="form-label d-block fw-bold" style="font-size: 0.9rem; color: #1a202c; margin-bottom: 0.5rem;">Nama Proyek <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="Masukkan nama proyek" style="background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.75rem 1rem; font-size: 0.95rem; color: #1e293b; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label d-block fw-bold" style="font-size: 0.9rem; color: #1a202c; margin-bottom: 0.5rem;">Kategori</label>
                            <input type="text" name="category" class="form-control" placeholder="Contoh: Infrastruktur, IT, dll" style="background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.75rem 1rem; font-size: 0.95rem; color: #1e293b; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label d-block fw-bold" style="font-size: 0.9rem; color: #1a202c; margin-bottom: 0.5rem;">Plafon Anggaran (Rp) <span class="text-danger">*</span></label>
                            <input type="text" name="budget_amount" class="form-control c-numeric" required placeholder="0" data-type="currency" style="background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.75rem 1rem; font-size: 0.95rem; color: #1e293b; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label d-block fw-bold" style="font-size: 0.9rem; color: #1a202c; margin-bottom: 0.5rem;">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control" required style="background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.75rem 1rem; font-size: 0.95rem; color: #1e293b; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label d-block fw-bold" style="font-size: 0.9rem; color: #1a202c; margin-bottom: 0.5rem;">Target Selesai <span class="text-danger">*</span></label>
                            <input type="date" name="target_end_date" class="form-control" required style="background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.75rem 1rem; font-size: 0.95rem; color: #1e293b; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label d-block fw-bold" style="font-size: 0.9rem; color: #1a202c; margin-bottom: 0.5rem;">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Tuliskan deskripsi singkat atau tujuan proyek..." style="background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.75rem 1rem; font-size: 0.95rem; color: #1e293b; box-shadow: 0 1px 2px rgba(0,0,0,0.02); resize: none;"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between align-items-center" style="border-top: 1px solid #f1f5f9; padding: 1.25rem 1.75rem; background-color: #ffffff;">
                    <button type="button" class="btn btn-light px-4 py-2 fw-bold" data-bs-dismiss="modal" style="color: #64748b; border-radius: 8px; background: #f1f5f9; border: none;">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 py-2 fw-bold" id="btnSaveProject" style="border-radius: 8px; box-shadow: 0 4px 12px rgba(var(--bs-primary-rgb), 0.2);">
                        Simpan Proyek
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
<?= $this->section('scripts'); ?>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/manage-project/index.js?v=99') ?>"></script>
<?= $this->endSection(); ?>
