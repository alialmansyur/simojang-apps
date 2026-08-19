<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>
<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/service-table-ui.css?v=99') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/components/modern-table.css') ?>">
<style>
    .info-box {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1rem;
        border: 1px solid #e2e8f0;
    }
    .progress-bar-custom {
        height: 12px;
        border-radius: 6px;
        background-color: #e2e8f0;
        overflow: hidden;
        margin-top: 5px;
    }
    .progress-bar-fill {
        height: 100%;
        background-color: #1040c1;
        transition: width 0.5s ease;
    }
</style>
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>
<style>
    /* Prevent vertical scrollbar on table-responsive */
    .table-responsive {
        overflow-y: hidden !important;
    }
    /* Increase padding to make table more spacious */
    .service-ui-table-wrap table.dataTable td, 
    .service-ui-table-wrap table.dataTable th {
        padding-top: 0.85rem !important;
        padding-bottom: 0.85rem !important;
    }
</style>
<div class="page-content d-flex align-items-center justify-content-center">
    <div class="container-fluid text-start mx-auto px-4 w-100">
        <input type="hidden" id="project_uid" value="<?= esc($project['uid']) ?>">

        <!-- Skeleton UI -->
        <div id="projectSkeleton">
            <div class="page-heading mb-3 w-100">
                <div class="row align-items-center d-flex justify-content-between">
                    <div class="col-md-8 text-start">
                        <div class="skeleton col-6" style="height: 2.2rem; margin-bottom: 0.5rem;"></div>
                        <div class="skeleton col-4" style="height: 1rem;"></div>
                    </div>
                    <div class="col-md-4 text-end mt-3 mt-md-0 d-flex gap-2 justify-content-end">
                        <div class="skeleton" style="height: 2.5rem; width: 120px;"></div>
                        <div class="skeleton" style="height: 2.5rem; width: 100px;"></div>
                    </div>
                </div>
            </div>

            <section class="row mb-1">
                <div class="col-md-12">
                    <div class="card shadow-sm" style="border: 1px solid #e2e8f0; border-radius: 12px; background: #ffffff;">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-md-6 mb-2 mb-md-0 pe-md-4">
                                    <div class="skeleton col-4 mb-2" style="height: 1.2rem;"></div>
                                    <div class="skeleton col-12 mb-1" style="height: 0.95rem;"></div>
                                    <div class="skeleton col-10 mb-3" style="height: 0.95rem;"></div>
                                    <div class="d-flex gap-4">
                                        <div>
                                            <div class="skeleton mb-1" style="height: 0.75rem; width: 60px;"></div>
                                            <div class="skeleton" style="height: 1rem; width: 80px;"></div>
                                        </div>
                                        <div>
                                            <div class="skeleton mb-1" style="height: 0.75rem; width: 60px;"></div>
                                            <div class="skeleton" style="height: 1rem; width: 80px;"></div>
                                        </div>
                                        <div>
                                            <div class="skeleton mb-1" style="height: 0.75rem; width: 60px;"></div>
                                            <div class="skeleton" style="height: 1.5rem; width: 70px;"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 border-start ps-md-4">
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <div class="skeleton col-5" style="height: 1rem;"></div>
                                            <div class="skeleton col-2" style="height: 1.2rem;"></div>
                                        </div>
                                        <div class="progress-bar-custom w-100" style="height: 10px; border-radius: 8px; background-color: #e2e8f0;"></div>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-sm-6">
                                            <div class="info-box p-2" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; display: flex; flex-direction: column; align-items: center;">
                                                <div class="skeleton mb-1" style="height: 0.75rem; width: 80%;"></div>
                                                <div class="skeleton" style="height: 1.2rem; width: 60%;"></div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="info-box p-2" style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; display: flex; flex-direction: column; align-items: center;">
                                                <div class="skeleton mb-1" style="height: 0.75rem; width: 80%;"></div>
                                                <div class="skeleton" style="height: 1.2rem; width: 60%;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <div id="projectContent" style="display: none;">
            <div class="page-heading mb-3 w-100">
                <div class="row align-items-center d-flex justify-content-between">
                    <div class="col-md-8 text-start">
                        <h1 class="tw-title lh-1 mt-3" style="color: #1a202c; font-size: 2.2rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.5rem;"><span id="lblProjectName"></span></h1>
                        <p class="text-subtitle fw-bold mb-0" style="color: #475569; font-size: 1rem;">Manajemen Proyek | Kategori: <span class="badge bg-secondary" id="lblProjectCategory"></span></p>
                    </div>
                    <div class="col-md-4 text-end mt-3 mt-md-0">
                        <div class="service-page-inline-actions">
                            <button type="button" class="btn btn-outline-secondary fw-bold px-3 py-2 me-2" data-bs-toggle="modal" data-bs-target="#EditProjectModal" style="color: #1a202c; border-color: #cbd5e1;">
                                <i class="bi bi-pencil-square me-1"></i> Edit Proyek
                            </button>
                            <a href="<?= base_url('apps-manage-project') ?>" class="btn btn-primary fw-bold px-3 py-2 shadow-sm">
                                <i class="bi bi-chevron-left me-1"></i> Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <section class="row mb-0">
                <div class="col-md-12">
                    <div class="card shadow-sm" style="border: 1px solid #e2e8f0; border-radius: 12px; background: #ffffff;">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-md-6 mb-2 mb-md-0 pe-md-4">
                                    <h6 class="fw-bold mb-2" style="color: #1a202c; font-size: 1rem;">Deskripsi Proyek</h6>
                                    <p class="mb-3" style="color: #334155; font-size: 0.95rem; line-height: 1.5;" id="lblProjectDescription"></p>
                                    <div class="d-flex gap-4">
                                        <div>
                                            <small class="d-block fw-bold mb-1" style="color: #64748b; font-size: 0.75rem;">TANGGAL MULAI</small>
                                            <span class="fw-bold fs-6" style="color: #1a202c;" id="lblStartDate"></span>
                                        </div>
                                        <div>
                                            <small class="d-block fw-bold mb-1" style="color: #64748b; font-size: 0.75rem;">TARGET SELESAI</small>
                                            <span class="fw-bold fs-6" style="color: #1a202c;" id="lblTargetEndDate"></span>
                                        </div>
                                        <div>
                                            <small class="d-block fw-bold mb-1" style="color: #64748b; font-size: 0.75rem;">STATUS</small>
                                            <span class="badge bg-success fw-bold" style="padding: 0.4rem 0.6rem; border-radius: 6px; font-size: 0.8rem;" id="lblProjectStatus"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 border-start ps-md-4">
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <h6 class="fw-bold mb-0" style="color: #1a202c; font-size: 1rem;">Progres Pekerjaan Fisik</h6>
                                            <span class="fs-5 fw-bold text-primary" id="progressPercentageLabel">0,00%</span>
                                        </div>
                                        <div class="progress-bar-custom w-100" style="height: 10px; border-radius: 8px;">
                                            <div class="progress-bar-fill" id="progressFill" style="width: 0%; border-radius: 8px; transition: width 1.2s cubic-bezier(0.34, 1.56, 0.64, 1);"></div>
                                        </div>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-sm-6">
                                            <div class="info-box interactive-box text-center p-2" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                                                <small class="d-block fw-bold mb-1" style="color: #64748b; font-size: 0.75rem;">PLAFON ANGGARAN</small>
                                                <span class="fw-bold" style="font-size: 1rem; color: #1a202c;" id="lblBudgetAmount">Rp 0</span>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="info-box interactive-box text-center p-2" style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px;">
                                                <small class="d-block fw-bold mb-1" style="color: #1040c1; font-size: 0.75rem;">TOTAL REALISASI</small>
                                                <span class="fw-bold text-primary" id="realizedBudgetLabel" style="font-size: 1rem;">Rp 0</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        <style>
            /* Nav interactions */
            .custom-sidebar-nav .nav-link {
                color: #64748b;
                border-radius: 8px;
                padding-left: 1.5rem !important;
                background: transparent !important;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                position: relative;
                overflow: hidden;
            }
            .custom-sidebar-nav .nav-link::before {
                content: '';
                position: absolute;
                left: 0;
                top: 50%;
                transform: translateY(-50%);
                width: 4px;
                height: 0%;
                background-color: #1040c1;
                border-radius: 0 4px 4px 0;
                transition: height 0.3s ease;
            }
            .custom-sidebar-nav .nav-link:hover {
                color: #1a202c;
                background: transparent !important;
                padding-left: 1.8rem !important;
            }
            .custom-sidebar-nav .nav-link.active {
                color: #1040c1 !important;
                background: transparent !important;
                font-weight: 800 !important;
            }
            .custom-sidebar-nav .nav-link.active::before {
                height: 60%;
            }

            /* Info Box interactions */
            .interactive-box {
                transition: all 0.3s ease;
                cursor: default;
            }
            .interactive-box:hover {
                transform: translateY(-3px);
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.02);
                border-color: #cbd5e1 !important;
            }

            /* Button interactions */
            .btn {
                transition: all 0.2s ease;
            }
            .btn:active {
                transform: scale(0.95);
            }

            /* Progress Bar Animation */
            @keyframes loadProgress {
                from { width: 0; }
            }
            .progress-bar-fill {
                animation: loadProgress 1.2s cubic-bezier(0.1, 0.9, 0.2, 1) forwards;
            }

            /* Table Row Hover */
            .table-hover tbody tr {
                transition: background-color 0.2s ease;
            }
            .table-hover tbody tr:hover {
                background-color: #f8fafc !important;
            }

            /* Clickable Columns */
            td.inline-editable {
                cursor: pointer;
            }
            td.inline-editable:hover {
                background-color: #e2e8f0 !important;
            }
        </style>
        
        <!-- Sidebar and Content Layout -->
        <div class="row mb-5" style="margin-top: -1rem;">
            <div class="col-md-12">
                <div class="card shadow-sm w-100" style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: #ffffff;">
                    <div class="row g-0" style="min-height: 500px;">
                <!-- Left Sidebar Nav -->
                <div class="col-md-2 border-end" style="border-color: #e2e8f0 !important; padding: 1.5rem 0; background: #ffffff;">
                    <ul class="nav nav-pills flex-column custom-sidebar-nav" id="projectTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active w-100 text-start fw-bold py-3" id="progress-tab" data-bs-toggle="pill" data-bs-target="#progress" type="button" role="tab" aria-controls="progress" aria-selected="true" style="font-size: 0.95rem;">
                                Riwayat Progres
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link w-100 text-start fw-bold py-3" id="budget-tab" data-bs-toggle="pill" data-bs-target="#budget" type="button" role="tab" aria-controls="budget" aria-selected="false" style="font-size: 0.95rem;">
                                Realisasi Anggaran
                            </button>
                        </li>
                    </ul>
                </div>

                <!-- Right Content -->
                <div class="col-md-10 p-3 p-md-4">
                    <div class="tab-content" id="projectTabContent">
                    
                    <!-- Tab Progres -->
                    <div class="tab-pane fade show active" id="progress" role="tabpanel" aria-labelledby="progress-tab">
                        <div class="service-ui-topbar service-ui-static-topbar mb-3">
                            <div class="d-flex align-items-center flex-nowrap gap-2">
                                <h5 class="fw-bold mb-0" style="color: #1a202c;">Log Perkembangan Proyek</h5>
                            </div>
                            <div class="d-flex align-items-center justify-content-end gap-2 flex-nowrap">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ProgressModal">
                                    <i class="bi bi-plus-lg me-1"></i> Tambah Progres
                                </button>
                            </div>
                        </div>
                            <input type="hidden" id="project_uid" value="<?= esc($project['uid']) ?>">
                            <div class="table-responsive">
                                <table id="progressTable" class="table table-bordered table-hover nowrap w-100">
                                <thead class="table-light">
                                    <tr>
                                        <th></th>
                                        <th><strong>No</strong></th>
                                        <th><strong>Tanggal Log</strong></th>
                                        <th><strong>Target (%)</strong></th>
                                        <th><strong>Realisasi Aktual (%)</strong></th>
                                        <th><strong>Catatan</strong></th>
                                        <th><strong>Waktu Entry</strong></th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th></th>
                                        <th><strong>No</strong></th>
                                        <th><strong>Tanggal Log</strong></th>
                                        <th><strong>Target (%)</strong></th>
                                        <th><strong>Realisasi Aktual (%)</strong></th>
                                        <th><strong>Catatan</strong></th>
                                        <th><strong>Waktu Entry</strong></th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                            </div>
                    </div>

                    <!-- Tab Anggaran -->
                    <div class="tab-pane fade" id="budget" role="tabpanel" aria-labelledby="budget-tab">
                        <div class="service-ui-topbar service-ui-static-topbar mb-3">
                            <div class="d-flex align-items-center flex-nowrap gap-2">
                                <h5 class="fw-bold mb-0" style="color: #1a202c;">Riwayat Realisasi Anggaran</h5>
                            </div>
                            <div class="d-flex align-items-center justify-content-end gap-2 flex-nowrap">
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#BudgetModal">
                                    <i class="bi bi-plus-lg me-1"></i> Tambah Realisasi
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="budgetTable" class="table table-bordered table-hover nowrap w-100">
                                <thead class="table-light">
                                    <tr>
                                        <th></th>
                                        <th><strong>No</strong></th>
                                        <th><strong>Tanggal Realisasi</strong></th>
                                        <th><strong>Jumlah (Rp)</strong></th>
                                        <th><strong>Keterangan / Deskripsi</strong></th>
                                        <th><strong>Waktu Entry</strong></th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th></th>
                                        <th><strong>No</strong></th>
                                        <th><strong>Tanggal Realisasi</strong></th>
                                        <th><strong>Jumlah (Rp)</strong></th>
                                        <th><strong>Keterangan / Deskripsi</strong></th>
                                        <th><strong>Waktu Entry</strong></th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
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
</div>

<!-- Modal Edit Proyek -->
<div class="modal fade" id="EditProjectModal" tabindex="-1" aria-labelledby="EditProjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); overflow: hidden;">
            <div class="modal-header align-items-center" style="border-bottom: 1px solid #f1f5f9; padding: 1.5rem 1.75rem 1.25rem; background-color: #ffffff;">
                <h5 class="modal-title fw-bold mb-0" id="EditProjectModalLabel" style="font-size: 1.25rem; color: #1a202c !important;">Edit Proyek</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.8rem;"></button>
            </div>
            <form id="formEditProject" method="post" autocomplete="off">
                <input type="hidden" name="project_uid" value="<?= esc($project['uid']) ?>">
                <div class="modal-body" style="padding: 1.75rem; background-color: #fcfdfd;">
                    <div class="row gy-4">
                        <div class="col-md-12">
                            <label class="form-label d-block fw-bold" style="font-size: 0.9rem; color: #1a202c; margin-bottom: 0.5rem;">Nama Proyek <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="Masukkan nama proyek" value="<?= esc($project['name']) ?>" style="background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.75rem 1rem; font-size: 0.95rem; color: #1e293b; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label d-block fw-bold" style="font-size: 0.9rem; color: #1a202c; margin-bottom: 0.5rem;">Kategori</label>
                            <input type="text" name="category" class="form-control" placeholder="Contoh: Infrastruktur, IT, dll" value="<?= esc($project['category']) ?>" style="background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.75rem 1rem; font-size: 0.95rem; color: #1e293b; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label d-block fw-bold" style="font-size: 0.9rem; color: #1a202c; margin-bottom: 0.5rem;">Plafon Anggaran (Rp) <span class="text-danger">*</span></label>
                            <input type="text" name="budget_amount" class="form-control c-numeric" required placeholder="0" data-type="currency" value="<?= number_format((float)$project['budget_amount'], 0, ',', '.') ?>" style="background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.75rem 1rem; font-size: 0.95rem; color: #1e293b; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label d-block fw-bold" style="font-size: 0.9rem; color: #1a202c; margin-bottom: 0.5rem;">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control" required value="<?= esc($project['start_date']) ?>" style="background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.75rem 1rem; font-size: 0.95rem; color: #1e293b; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label d-block fw-bold" style="font-size: 0.9rem; color: #1a202c; margin-bottom: 0.5rem;">Target Selesai <span class="text-danger">*</span></label>
                            <input type="date" name="target_end_date" class="form-control" required value="<?= esc($project['target_end_date']) ?>" style="background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.75rem 1rem; font-size: 0.95rem; color: #1e293b; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label d-block fw-bold" style="font-size: 0.9rem; color: #1a202c; margin-bottom: 0.5rem;">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Tuliskan deskripsi singkat atau tujuan proyek..." style="background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.75rem 1rem; font-size: 0.95rem; color: #1e293b; box-shadow: 0 1px 2px rgba(0,0,0,0.02); resize: none;"><?= esc($project['description']) ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between align-items-center" style="border-top: 1px solid #f1f5f9; padding: 1.25rem 1.75rem; background-color: #ffffff;">
                    <button type="button" class="btn btn-light px-4 py-2 fw-bold" data-bs-dismiss="modal" style="color: #64748b; border-radius: 8px; background: #f1f5f9; border: none;">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 py-2 fw-bold" id="btnUpdateProject" style="border-radius: 8px; box-shadow: 0 4px 12px rgba(var(--bs-primary-rgb), 0.2);">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tambah Progres -->
<div class="modal fade" id="ProgressModal" tabindex="-1" aria-labelledby="ProgressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="ProgressModalLabel">Update Progres Pekerjaan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formProgress" method="post" autocomplete="off">
                <input type="hidden" name="project_uid" value="<?= esc($project['uid']) ?>">
                <input type="hidden" name="id" id="progress_id" value="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tanggal Update</label>
                        <input type="date" name="log_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold">Target (%)</label>
                            <input type="number" step="0.01" min="0" max="100" name="target_percentage" class="form-control" placeholder="0.00">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold">Realisasi Aktual (%) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" max="100" name="actual_percentage" class="form-control" required placeholder="0.00">
                            <small class="text-muted d-block mt-1">Total progres saat ini</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Catatan Kendala/Kegiatan</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Tulis catatan jika ada..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveProgress">Simpan Progres</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tambah Budget -->
<div class="modal fade" id="BudgetModal" tabindex="-1" aria-labelledby="BudgetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="BudgetModalLabel">Tambah Realisasi Anggaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formBudget" method="post" autocomplete="off">
                <input type="hidden" name="project_uid" value="<?= esc($project['uid']) ?>">
                <input type="hidden" name="id" id="budget_id" value="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tanggal Realisasi <span class="text-danger">*</span></label>
                        <input type="date" name="realization_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Jumlah Realisasi (Rp) <span class="text-danger">*</span></label>
                        <input type="text" name="amount" class="form-control c-numeric" required placeholder="0" data-type="currency">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Keterangan / Uraian</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Uraian realisasi anggaran..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success" id="btnSaveBudget">Simpan Realisasi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
<?= $this->section('scripts'); ?>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/service-table-ui.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/manage-project/detail.js?v=99') ?>"></script>
<?= $this->endSection(); ?>
