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
    /* Flat Upload & Dropzone styling matching activity-gallery */
    .flat-upload {
        background-color: #f8fafc;
        border: 2px dashed #cbd5e1 !important;
        border-radius: 8px;
        transition: border-color 0.2s, background-color 0.2s;
    }
    .flat-upload:hover, .flat-upload.drag-over {
        border-color: var(--bs-primary) !important;
        background-color: #f0f7ff;
    }
    .flat-border {
        border: 1px solid #e2e8f0;
    }
    .img-doc-thumb {
        width: 44px;
        height: 44px;
        object-fit: cover;
        border-radius: 6px;
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .img-doc-thumb:hover {
        transform: scale(1.08);
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
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

            <div class="row mb-3 mt-2">
                <div class="col-12">
                    <div class="alert shadow-sm border-0 rounded-3 p-3 position-relative mb-0" style="background-color: #fffbe4; border-left: 6px solid #f59e0b !important;" role="alert">
                        <div class="row align-items-center g-0 pe-5">
                            <div class="col-auto pe-3">
                                <i class="bi bi-exclamation-triangle-fill" style="color: #d97706; font-size: 2.2rem; line-height: 1;"></i>
                            </div>
                            <div class="col">
                                <h6 class="fw-bold mb-1" style="color: #92400e; font-size: 1rem; line-height: 1.2;">Panduan: Pengelolaan Rincian &amp; Tahapan Proyek</h6>
                                <div style="font-size: 0.85rem; color: #b45309; line-height: 1.2;">Perbarui milestone, realisasi anggaran, dan penugasan tim kerja secara berkelanjutan untuk memastikan akurasi pelaporan progres proyek.</div>
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
                                <li><strong>Kelola Milestone &amp; Tugas:</strong> Buka tab <em>Milestone</em> untuk menambah tahapan kerja dan menandai sub-tugas yang telah selesai guna mengkalkulasi progres fisik.</li>
                                <li><strong>Catat Realisasi Anggaran:</strong> Gunakan tab <em>Anggaran</em> untuk mencatat pengeluaran riil dan membandingkan serapan terhadap plafon yang dialokasikan.</li>
                                <li><strong>Atur Tim &amp; Dokumen:</strong> Tetapkan anggota pelaksana pada tab <em>Tim Kerja</em> serta unggah berkas pendukung pada tab <em>Dokumen</em>.</li>
                                <li><strong>Perbarui Status Proyek:</strong> Klik tombol <strong>"Edit Proyek"</strong> jika terdapat penyesuaian target tanggal selesai atau status penyelesaian proyek.</li>
                            </ol>
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
            /* Horizontal Nav Tabs styling */
            .custom-horizontal-nav {
                border-bottom: none !important;
                gap: 0.5rem;
                flex-wrap: nowrap;
                overflow-x: auto;
                overflow-y: hidden;
                scrollbar-width: thin;
            }
            .custom-horizontal-nav .nav-link {
                color: #64748b;
                background: transparent !important;
                border: none !important;
                border-bottom: 3px solid transparent !important;
                border-radius: 0 !important;
                padding: 0.85rem 1.25rem !important;
                transition: all 0.2s ease;
                white-space: nowrap;
                font-size: 0.95rem;
            }
            .custom-horizontal-nav .nav-link:hover {
                color: #1040c1;
                border-bottom-color: #cbd5e1 !important;
            }
            .custom-horizontal-nav .nav-link.active {
                color: #1040c1 !important;
                background: transparent !important;
                border-bottom: 3px solid #1040c1 !important;
                font-weight: 800 !important;
            }

            /* Child row and expand (+) styling */
            .btn-dt-expand {
                background: #f1f5f9;
                border: 1px solid #cbd5e1;
                border-radius: 4px;
                padding: 1px 7px;
                cursor: pointer;
                line-height: 1.2;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-weight: 700;
                font-size: 0.85rem;
                color: #334155;
                transition: all 0.15s ease;
            }
            .btn-dt-expand:hover {
                background: #e2e8f0;
                color: #0f172a;
            }
            .child-detail-card {
                background-color: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 10px;
                padding: 1.25rem;
                box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
            }
            .child-detail-label {
                font-size: 0.75rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                color: #475569 !important;
                font-weight: 700;
                margin-bottom: 0.25rem;
            }
            .child-detail-value {
                font-size: 0.925rem;
                color: #0f172a !important;
                font-weight: 600;
            }
            .table-compact td, .table-compact th {
                padding: 0.65rem 0.6rem !important;
                vertical-align: middle;
            }
            .text-wrap-normal {
                white-space: normal !important;
                word-break: normal !important;
                overflow-wrap: break-word !important;
            }

            /* Table Layout and Dark Font Styles */
            #progressTable, #budgetTable {
                width: 100% !important;
                table-layout: fixed !important;
                border-collapse: collapse !important;
            }

            #progressTable th, #budgetTable th,
            #progressTable td, #budgetTable td {
                color: #111827 !important;
                vertical-align: middle !important;
            }

            #progressTable thead th, #budgetTable thead th,
            #progressTable tfoot th, #budgetTable tfoot th {
                color: #0f172a !important;
                font-weight: 700 !important;
                background-color: #f1f5f9 !important;
                border-color: #cbd5e1 !important;
                white-space: normal !important;
                word-break: normal !important;
            }

            #progressTable tbody td, #budgetTable tbody td {
                color: #111827 !important;
                border-color: #e2e8f0 !important;
                word-break: break-word !important;
            }

            #progressTable tbody td span, #budgetTable tbody td span,
            #progressTable tbody td small, #budgetTable tbody td small,
            #progressTable tbody td div, #budgetTable tbody td div {
                color: #111827;
            }

            #progressTable .text-primary, #budgetTable .text-primary {
                color: #1d4ed8 !important;
            }

            #progressTable .text-success, #budgetTable .text-success {
                color: #15803d !important;
            }

            #progressTable .text-danger, #budgetTable .text-danger {
                color: #b91c1c !important;
            }

            #progressTable .text-muted, #budgetTable .text-muted {
                color: #64748b !important;
            }

            #progressTable .btn-primary, #budgetTable .btn-primary,
            #progressTable .btn-primary *, #budgetTable .btn-primary * {
                color: #ffffff !important;
            }

            #progressTable .btn-danger, #budgetTable .btn-danger,
            #progressTable .btn-danger *, #budgetTable .btn-danger * {
                color: #ffffff !important;
            }

            .dataTables_wrapper .dataTables_info,
            .dataTables_wrapper .dataTables_length,
            .dataTables_wrapper .dataTables_length label,
            .dataTables_wrapper .dataTables_filter,
            .dataTables_wrapper .dataTables_filter label,
            .dataTables_wrapper .dataTables_paginate {
                color: #111827 !important;
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
        </style>
        
        <!-- Horizontal Nav & Content Card Layout -->
        <div class="row mb-5" style="margin-top: -1rem;">
            <div class="col-12">
                <div class="card shadow-sm w-100" style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: #ffffff;">
                    
                    <!-- Horizontal Nav Header (No Icons) -->
                    <div class="card-header border-bottom bg-white p-0">
                        <ul class="nav nav-tabs custom-horizontal-nav px-3 pt-2" id="projectTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active fw-bold px-4 py-3" id="progress-tab" data-bs-toggle="pill" data-bs-target="#progress" type="button" role="tab" aria-controls="progress" aria-selected="true">
                                    Riwayat Progres
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-bold px-4 py-3" id="budget-tab" data-bs-toggle="pill" data-bs-target="#budget" type="button" role="tab" aria-controls="budget" aria-selected="false">
                                    Realisasi Anggaran
                                </button>
                            </li>
                        </ul>
                    </div>

                    <!-- Content Body -->
                    <div class="card-body p-3 p-md-4">
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
                                    <table id="progressTable" class="table table-bordered table-hover w-100 table-compact">
                                        <colgroup>
                                            <col style="width: 4%;">
                                            <col style="width: 5%;">
                                            <col style="width: 12%;">
                                            <col style="width: 18%;">
                                            <col style="width: 9%;">
                                            <col style="width: 10%;">
                                            <col style="width: 9%;">
                                            <col style="width: 23%;">
                                            <col style="width: 10%;">
                                        </colgroup>
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center"></th>
                                                <th class="text-center"><strong>No</strong></th>
                                                <th><strong>Tanggal Log</strong></th>
                                                <th><strong>Periode</strong></th>
                                                <th class="text-end"><strong>Target (%)</strong></th>
                                                <th class="text-end"><strong>Realisasi (%)</strong></th>
                                                <th class="text-center"><strong>Dokumentasi</strong></th>
                                                <th><strong>Catatan</strong></th>
                                                <th class="text-center"><strong>Aksi</strong></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th class="text-center"></th>
                                                <th class="text-center"><strong>No</strong></th>
                                                <th><strong>Tanggal Log</strong></th>
                                                <th><strong>Periode</strong></th>
                                                <th class="text-end"><strong>Target (%)</strong></th>
                                                <th class="text-end"><strong>Realisasi (%)</strong></th>
                                                <th class="text-center"><strong>Dokumentasi</strong></th>
                                                <th><strong>Catatan</strong></th>
                                                <th class="text-center"><strong>Aksi</strong></th>
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
                                    <table id="budgetTable" class="table table-bordered table-hover w-100 table-compact">
                                        <colgroup>
                                            <col style="width: 4%;">
                                            <col style="width: 5%;">
                                            <col style="width: 16%;">
                                            <col style="width: 18%;">
                                            <col style="width: 47%;">
                                            <col style="width: 10%;">
                                        </colgroup>
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center"></th>
                                                <th class="text-center"><strong>No</strong></th>
                                                <th><strong>Tanggal Realisasi</strong></th>
                                                <th class="text-end"><strong>Jumlah (Rp)</strong></th>
                                                <th><strong>Keterangan / Deskripsi</strong></th>
                                                <th class="text-center"><strong>Aksi</strong></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th class="text-center"></th>
                                                <th class="text-center"><strong>No</strong></th>
                                                <th><strong>Tanggal Realisasi</strong></th>
                                                <th class="text-end"><strong>Jumlah (Rp)</strong></th>
                                                <th><strong>Keterangan / Deskripsi</strong></th>
                                                <th class="text-center"><strong>Aksi</strong></th>
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

<!-- Modal Tambah / Update Progres -->
<div class="modal fade" id="ProgressModal" tabindex="-1" aria-labelledby="ProgressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); overflow: hidden;">
            <div class="modal-header align-items-center" style="border-bottom: 1px solid #f1f5f9; padding: 1.25rem 1.75rem; background-color: #ffffff;">
                <h5 class="modal-title fw-bold mb-0" id="ProgressModalLabel" style="font-size: 1.2rem; color: #1a202c !important;">Update Progres Pekerjaan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.8rem;"></button>
            </div>
            <form id="formProgress" method="post" enctype="multipart/form-data" autocomplete="off">
                <input type="hidden" name="project_uid" value="<?= esc($project['uid']) ?>">
                <input type="hidden" name="id" id="progress_id" value="">
                <div class="modal-body" style="padding: 1.5rem 1.75rem; background-color: #fcfdfd;">
                    <div class="row gy-3">
                        <div class="col-12">
                            <label class="form-label d-block fw-bold" style="font-size: 0.9rem; color: #1a202c; margin-bottom: 0.4rem;">Judul / Nama Progres Kegiatan</label>
                            <input type="text" name="title" id="progress_title" class="form-control flat-input" placeholder="Contoh: Pemasangan Jaringan Tahap 1 / Rapat Evaluasi Proyek">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label d-block fw-bold" style="font-size: 0.9rem; color: #1a202c; margin-bottom: 0.4rem;">Tanggal Update <span class="text-danger">*</span></label>
                            <input type="date" name="log_date" class="form-control flat-input" required value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label d-block fw-bold" style="font-size: 0.9rem; color: #1a202c; margin-bottom: 0.4rem;">Periode Awal</label>
                            <input type="date" name="start_date" id="progress_start_date" class="form-control flat-input">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label d-block fw-bold" style="font-size: 0.9rem; color: #1a202c; margin-bottom: 0.4rem;">Periode Akhir</label>
                            <input type="date" name="end_date" id="progress_end_date" class="form-control flat-input">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label d-block fw-bold" style="font-size: 0.9rem; color: #1a202c; margin-bottom: 0.4rem;">Target (%)</label>
                            <input type="number" step="0.01" min="0" max="100" name="target_percentage" class="form-control flat-input" placeholder="0.00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label d-block fw-bold" style="font-size: 0.9rem; color: #1a202c; margin-bottom: 0.4rem;">Realisasi Aktual (%) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" max="100" name="actual_percentage" class="form-control flat-input" required placeholder="0.00">
                            <small class="text-muted d-block mt-1" style="font-size: 0.78rem;">Akumulasi progres fisik saat ini</small>
                        </div>

                        <div class="col-12">
                            <label class="form-label d-block fw-bold" style="font-size: 0.9rem; color: #1a202c; margin-bottom: 0.4rem;">Catatan Kendala / Uraian Kegiatan</label>
                            <textarea name="notes" class="form-control flat-input" rows="2" placeholder="Tuliskan catatan kendala atau progres rincian jika ada..."></textarea>
                        </div>

                        <div class="col-12 mt-2">
                            <div class="d-flex justify-content-between align-items-end mb-1">
                                <label class="form-label d-block fw-bold mb-0" style="font-size: 0.9rem; color: #1a202c;">Dokumentasi / Foto Kegiatan</label>
                                <span class="text-muted" style="font-size: 0.75rem;">Maksimal 2 MB (.jpg, .png, .webp)</span>
                            </div>
                            <div class="upload-area text-center p-3 position-relative flat-upload d-flex flex-column align-items-center justify-content-center" id="progressUploadArea" style="min-height: 160px;">
                                <input type="file" id="inputProgressFoto" name="foto" class="position-absolute w-100 h-100 top-0 start-0 opacity-0" style="cursor: pointer; z-index: 5;" accept="image/png, image/jpeg, image/jpg, image/webp">
                                <div id="progressUploadPlaceholder" class="d-flex flex-column align-items-center justify-content-center w-100 py-2">
                                    <i class="bi bi-cloud-arrow-up text-secondary mb-2 d-block" style="font-size: 2.25rem; line-height: 1;"></i>
                                    <span class="d-block fw-bold text-dark" style="font-size: 0.9rem;">Klik atau seret foto ke sini</span>
                                    <span class="d-block text-muted small mt-1">Maksimal 2MB (JPG, PNG, WebP)</span>
                                </div>
                                <div id="progressUploadPreview" class="d-none position-relative" style="z-index: 6;">
                                    <img src="" alt="Preview" class="img-fluid rounded flat-border" style="max-height: 180px; object-fit: contain;">
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-light fw-bold px-3" style="border: 1px solid #e2e8f0; position: relative; z-index: 10;" id="btnRemoveProgressPhoto">Ganti Foto</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between align-items-center" style="border-top: 1px solid #f1f5f9; padding: 1.15rem 1.75rem; background-color: #ffffff;">
                    <button type="button" class="btn btn-light px-4 py-2 fw-bold flat-btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 py-2 fw-bold" id="btnSaveProgress" style="border-radius: 8px; box-shadow: 0 4px 12px rgba(var(--bs-primary-rgb), 0.2);">
                        Simpan Progres
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal View Progress Photo (Lightbox) -->
<div class="modal fade" id="modalViewProgressPhoto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content flat-modal p-0 overflow-hidden" style="border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
            <div class="modal-header border-0 pb-2 px-4 pt-3 d-flex justify-content-between align-items-center bg-white">
                <h5 class="modal-title fw-bold text-dark m-0" id="viewProgressTitle" style="font-size: 1.15rem;">Dokumentasi Progres</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 d-flex flex-column">
                <div class="w-100 bg-light d-flex align-items-center justify-content-center" style="min-height: 35vh; max-height: 65vh; overflow: hidden;">
                    <img src="" id="viewProgressImg" class="img-fluid w-100 h-100" style="object-fit: contain;" alt="Dokumentasi Progres">
                </div>
                <div class="p-3 bg-white border-top">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <span class="badge bg-primary px-2 py-1" id="viewProgressBadge">0%</span>
                        <span class="badge bg-light text-dark border px-2 py-1" id="viewProgressPeriod"><i class="bi bi-calendar-range me-1"></i> Periode</span>
                        <span class="text-muted small fw-medium ms-auto" id="viewProgressDate"><i class="bi bi-calendar3 me-1"></i> Tanggal</span>
                    </div>
                    <p class="text-secondary mb-0 mt-1" id="viewProgressNotes" style="line-height: 1.5; font-size: 0.9rem;">-</p>
                </div>
            </div>
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
