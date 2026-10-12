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
    #asset-total-data,
    #asset-last-update {
        color: #0f172a !important;
        font-weight: 800 !important;
    }
    #asset-total-qty {
        color: #1040c1 !important;
        font-weight: 800 !important;
    }
    #asset-total-nilai {
        color: #15803d !important;
        font-weight: 800 !important;
    }
    .asset-thumb-preview {
        width: 42px;
        height: 42px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .asset-thumb-preview:hover {
        transform: scale(1.12);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .dropzone-asset-area {
        border: 2px dashed #1040c1;
        border-radius: 12px;
        background-color: #f8fafc;
        padding: 24px 16px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .dropzone-asset-area:hover,
    .dropzone-asset-area.dragover {
        background-color: #eff6ff;
        border-color: #2563eb;
    }
    .info-box small {
        letter-spacing: 0.03em;
    }
    .interactive-box {
        transition: all 0.3s ease;
        cursor: default;
    }
    .interactive-box:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.02);
        border-color: #cbd5e1 !important;
    }
    .table-responsive {
        overflow-y: hidden !important;
    }
    .service-ui-table-wrap table.dataTable td, 
    .service-ui-table-wrap table.dataTable th {
        padding-top: 0.85rem !important;
        padding-bottom: 0.85rem !important;
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
    .btn-edit-hover {
        transition: all 0.2s;
        color: #0284c7 !important;
    }
    .btn-edit-hover:hover {
        background-color: #e0f2fe !important;
        transform: scale(1.1);
        border-radius: 4px;
    }
</style>
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>
<div class="page-content d-flex align-items-center justify-content-center">
    <div class="container-fluid text-start mx-auto px-4 w-100">
        <input type="hidden" id="category_uid" value="<?= esc($category['uid']) ?>">

        <div class="page-heading mb-3 w-100">
            <div class="row align-items-center d-flex justify-content-between">
                <div class="col-md-8 text-start">
                    <h1 class="tw-title lh-1 mt-3" style="color: #1a202c; font-size: 2.2rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.5rem;"><?= esc($category['name']) ?></h1>
                    <p class="text-subtitle fw-bold mb-0" style="color: #475569; font-size: 1rem;">Manajemen Asset | Kategori: <span class="badge bg-secondary"><?= esc($category['name']) ?></span></p>
                </div>
                <div class="col-md-4 text-end mt-3 mt-md-0">
                    <div class="service-page-inline-actions">
                        <button type="button" class="btn btn-outline-secondary fw-bold px-3 py-2 me-2 js-service-reload" style="color: #1a202c; border-color: #cbd5e1;">
                            <i class="bi bi-arrow-clockwise me-1"></i> Muat Ulang
                        </button>
                        <a href="<?= base_url('apps-manage-assets') ?>" class="btn btn-primary fw-bold px-3 py-2 shadow-sm">
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
                            <h6 class="fw-bold mb-1" style="color: #92400e; font-size: 1rem; line-height: 1.2;">Panduan: Pengelolaan Rincian Asset Kategori <?= esc($category['name']) ?></h6>
                            <div style="font-size: 0.85rem; color: #b45309; line-height: 1.2;">Pastikan kode barang, subcode, uraian spesifikasi, satuan, dan kuantitas tercatat dengan teliti sesuai bukti fisik inventarisasi aset.</div>
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
                            <li><strong>Tambah Asset Baru:</strong> Klik tombol <strong>"Tambah Asset"</strong> untuk menambahkan item baru ke kategori ini dengan mengisi kode, subcode, uraian, satuan, dan jumlah kuantitas.</li>
                            <li><strong>Ubah Data Asset:</strong> Klik tombol edit (ikon pensil) pada baris tabel untuk mengubah spesifikasi, kode, satuan, atau kuantitas barang.</li>
                            <li><strong>Hapus Data Asset:</strong> Klik tombol hapus (ikon tempat sampah) jika item aset salah diinput atau sudah tidak aktif.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="row mb-3">
            <div class="col-md-12">
                <div class="card shadow-sm mb-0" style="border: 1px solid #e2e8f0; border-radius: 12px; background: #ffffff; margin-bottom: 0 !important;">
                    <div class="card-body p-3 p-md-4">
                        <div class="row align-items-center">
                            <div class="col-xl-5 col-lg-5 mb-3 mb-lg-0 pe-lg-3">
                                <h6 class="fw-bold mb-2" style="color: #1a202c; font-size: 1rem;">Rincian Kategori</h6>
                                <p class="mb-3" style="color: #334155; font-size: 0.95rem; line-height: 1.5;">Kategori aset ini menampung inventarisasi barang milik instansi beserta spesifikasi kode, foto fisik, kondisi, kuantitas, dan nilai perolehan terdaftar.</p>
                                <div class="d-flex gap-4 flex-wrap">
                                    <div>
                                        <small class="d-block fw-bold mb-1" style="color: #64748b !important; font-size: 0.75rem;">KODE KATEGORI</small>
                                        <span class="fw-bold fs-6" style="color: #1a202c !important;"><?= esc($category['id']) ?></span>
                                    </div>
                                    <div>
                                        <small class="d-block fw-bold mb-1" style="color: #64748b !important; font-size: 0.75rem;">TANGGAL TERDAFTAR</small>
                                        <span class="fw-bold fs-6" style="color: #1a202c !important;"><?= !empty($category['created_at']) ? date('d/m/Y', strtotime($category['created_at'])) : '-' ?></span>
                                    </div>
                                    <div>
                                        <small class="d-block fw-bold mb-1" style="color: #64748b !important; font-size: 0.75rem;">STATUS</small>
                                        <span class="badge bg-success fw-bold" style="padding: 0.4rem 0.6rem; border-radius: 6px; font-size: 0.8rem;">Aktif</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-7 col-lg-7 border-start-lg ps-lg-4">
                                <div class="row g-2">
                                    <div class="col-6 col-sm-3">
                                        <div class="info-box interactive-box text-center p-2" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                                            <small class="d-block fw-bold mb-1" style="color: #475569 !important; font-size: 0.72rem;">TOTAL ITEM</small>
                                            <span class="fw-bold" style="font-size: 1.15rem; color: #0f172a !important; font-weight: 800 !important;" id="asset-total-data">0</span>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <div class="info-box interactive-box text-center p-2" style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px;">
                                            <small class="d-block fw-bold mb-1" style="color: #1e40af !important; font-size: 0.72rem;">TOTAL KUANTITAS</small>
                                            <span class="fw-bold" style="font-size: 1.15rem; color: #1040c1 !important; font-weight: 800 !important;" id="asset-total-qty">0</span>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <div class="info-box interactive-box text-center p-2" style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px;">
                                            <small class="d-block fw-bold mb-1" style="color: #166534 !important; font-size: 0.72rem;">TOTAL VALUASI</small>
                                            <span class="fw-bold text-truncate d-block" style="font-size: 0.95rem; color: #15803d !important; font-weight: 800 !important;" id="asset-total-nilai" title="Rp 0">Rp 0</span>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <div class="info-box interactive-box text-center p-2" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                                            <small class="d-block fw-bold mb-1" style="color: #475569 !important; font-size: 0.72rem;">UPDATE TERAKHIR</small>
                                            <span class="fw-bold text-truncate d-block" style="font-size: 0.85rem; color: #0f172a !important; font-weight: 800 !important;" id="asset-last-update">-</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="row mb-5">
            <div class="col-12">
                <div class="card shadow-sm w-100" style="border: 1px solid #e2e8f0; border-radius: 12px; background: #ffffff;">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
                            <div>
                                <h5 class="fw-bold mb-0" style="color: #1a202c; font-size: 1.25rem;">Daftar Rincian Asset</h5>
                                <p class="text-muted small mb-0">Kelola dan pantau seluruh item inventaris yang tercatat di bawah kategori ini.</p>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-primary d-inline-flex align-items-center justify-content-center px-4 py-2" data-bs-toggle="modal" data-bs-target="#DataModal" id="btnTambahData" style="border-radius: 8px; box-shadow: 0 4px 12px rgba(var(--bs-primary-rgb), 0.2);">
                                    <span class="fw-bold" style="font-size: 0.95rem;">Tambah Asset</span> <i class="bi bi-plus-lg ms-2 d-flex align-items-center" style="font-size: 1.1rem;"></i>
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="dataTable" class="table table-bordered table-hover nowrap w-100" style="width:100%">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 45px;"><strong>No</strong></th>
                                        <th class="text-center" style="width: 55px;"><strong>Foto</strong></th>
                                        <th><strong>Kode &amp; Subcode</strong></th>
                                        <th><strong>Uraian, Area &amp; Lokasi</strong></th>
                                        <th class="text-center"><strong>Satuan</strong></th>
                                        <th class="text-end"><strong>Qty</strong></th>
                                        <th class="text-end"><strong>Nilai Perolehan</strong></th>
                                        <th class="text-center"><strong>Kondisi</strong></th>
                                        <th class="text-center" style="width: 85px;"><strong>Aksi</strong></th>
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

<!-- Modal Tambah / Edit Data Asset -->
<div class="modal fade" id="DataModal" tabindex="-1" role="dialog" aria-labelledby="DataModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content" style="border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); overflow: hidden;">
            <div class="modal-header align-items-center" style="border-bottom: 1px solid #f1f5f9; padding: 1.5rem 1.75rem 1.25rem; background-color: #ffffff;">
                <h5 class="modal-title fw-bold mb-0" id="DataModalLabel" style="font-size: 1.25rem; color: #1a202c !important;">Tambah Data Asset</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.8rem;"></button>
            </div>
            <form id="formAssetDetail" action="#" method="post" enctype="multipart/form-data" autocomplete="off">
                <div class="modal-body" style="padding: 1.75rem; background-color: #fcfdfd;">
                    <input type="hidden" name="id" id="assetId">
                    <input type="hidden" name="category_uid" value="<?= esc($category['uid']) ?>">
                    <input type="hidden" name="remove_foto" id="removeFoto" value="0">
                    
                    <div class="row g-3">
                        <!-- Kolom Kiri: Spesifikasi Aset -->
                        <div class="col-lg-7 border-end-lg pe-lg-3">
                            <h6 class="fw-bold mb-3" style="color: #1e293b; font-size: 0.95rem;">
                                <i class="bi bi-info-circle me-1 text-primary"></i> Data Pokok &amp; Spesifikasi
                            </h6>
                            <div class="row gy-3">
                                <div class="col-md-6">
                                    <label class="form-label d-block fw-bold" style="font-size: 0.875rem; color: #1a202c; margin-bottom: 0.4rem;">Kode Asset <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="kode" id="kode" required placeholder="Contoh: 2010101002" style="background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.65rem 0.9rem; font-size: 0.9rem; color: #1e293b;">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label d-block fw-bold" style="font-size: 0.875rem; color: #1a202c; margin-bottom: 0.4rem;">Subcode / NUP</label>
                                    <input type="text" class="form-control" name="subcode" id="subcode" placeholder="Contoh: 131111" style="background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.65rem 0.9rem; font-size: 0.9rem; color: #1e293b;">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label d-block fw-bold" style="font-size: 0.875rem; color: #1a202c; margin-bottom: 0.4rem;">Uraian / Nama Asset <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="uraian" id="uraian" required placeholder="Contoh: Laptop Lenovo ThinkPad T14 Gen 3..." style="background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.65rem 0.9rem; font-size: 0.9rem; color: #1e293b;">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label d-block fw-bold" style="font-size: 0.875rem; color: #1a202c; margin-bottom: 0.4rem;">Satuan <span class="text-danger">*</span></label>
                                    <select class="form-select" name="satuan" id="satuan" required style="background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.65rem 0.9rem; font-size: 0.9rem; color: #1e293b;">
                                        <option value="" disabled selected>-- Pilih Satuan --</option>
                                        <option value="Unit">Unit</option>
                                        <option value="Buah">Buah</option>
                                        <option value="Set">Set</option>
                                        <option value="Paket">Paket</option>
                                        <option value="M2">M2 (Meter Persegi)</option>
                                        <option value="M">M (Meter)</option>
                                        <option value="Pcs">Pcs</option>
                                        <option value="Lembar">Lembar</option>
                                        <option value="Dus">Dus / Box</option>
                                        <option value="Botol">Botol</option>
                                        <option value="Roll">Roll</option>
                                        <option value="Eksemplar">Eksemplar</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label d-block fw-bold" style="font-size: 0.875rem; color: #1a202c; margin-bottom: 0.4rem;">Kuantitas (Qty) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="qty" id="qty" required placeholder="0" style="background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.65rem 0.9rem; font-size: 0.9rem; color: #1e293b;">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label d-block fw-bold" style="font-size: 0.875rem; color: #1a202c; margin-bottom: 0.4rem;">Nilai Perolehan Satuan (Rp)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted fw-bold" style="border: 1px solid #cbd5e1; font-size: 0.85rem;">Rp</span>
                                        <input type="text" class="form-control" name="nilai_perolehan" id="nilai_perolehan" placeholder="0" style="background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 0 8px 8px 0; padding: 0.65rem 0.9rem; font-size: 0.9rem; color: #1e293b;">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label d-block fw-bold" style="font-size: 0.875rem; color: #1a202c; margin-bottom: 0.4rem;">Kondisi Fisik <span class="text-danger">*</span></label>
                                    <select class="form-select" name="kondisi" id="kondisi" required style="background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.65rem 0.9rem; font-size: 0.9rem; color: #1e293b;">
                                        <option value="Baik" selected>Baik (Siap Pakai)</option>
                                        <option value="Rusak Ringan">Rusak Ringan (Perlu Servis)</option>
                                        <option value="Rusak Berat">Rusak Berat (Tidak Berfungsi)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label d-block fw-bold" style="font-size: 0.875rem; color: #1a202c; margin-bottom: 0.4rem;">Area Satker <span class="text-danger">*</span></label>
                                    <select class="form-select" name="area" id="area" required style="background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.65rem 0.9rem; font-size: 0.9rem; color: #1e293b;">
                                        <option value="Kantor Regional III BKN" selected>Kantor Regional III BKN</option>
                                        <option value="UPT Bandung">UPT Bandung</option>
                                        <option value="UPT Serang">UPT Serang</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label d-block fw-bold" style="font-size: 0.875rem; color: #1a202c; margin-bottom: 0.4rem;">Lokasi Penempatan</label>
                                    <input type="text" class="form-control" name="lokasi" id="lokasi" placeholder="Contoh: Gedung A Lt. 2, Ruang Server" style="background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.65rem 0.9rem; font-size: 0.9rem; color: #1e293b;">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label d-block fw-bold" style="font-size: 0.875rem; color: #1a202c; margin-bottom: 0.4rem;">Tahun Perolehan</label>
                                    <input type="number" class="form-control" name="tahun_perolehan" id="tahun_perolehan" min="1970" max="2099" placeholder="Contoh: <?= date('Y') ?>" style="background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.65rem 0.9rem; font-size: 0.9rem; color: #1e293b;">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label d-block fw-bold" style="font-size: 0.875rem; color: #1a202c; margin-bottom: 0.4rem;">Keterangan / Catatan</label>
                                    <textarea class="form-control" name="keterangan" id="keterangan" rows="2" placeholder="Catatan spesifikasi, garansi, atau asal usul perolehan aset..." style="background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.65rem 0.9rem; font-size: 0.9rem; color: #1e293b;"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Kolom Kanan: Dropzone Foto Aset (Sesuai Pattern /apps-ikpa) -->
                        <div class="col-lg-5 ps-lg-3">
                            <h6 class="fw-bold mb-3" style="color: #1e293b; font-size: 0.95rem;">
                                <i class="bi bi-camera me-1 text-primary"></i> Foto Fisik Asset
                            </h6>
                            <div class="upload-card">
                                <div class="upload-card-body text-center dropzone-asset-area" id="assetDropzoneArea">
                                    <div id="dropzonePrompt">
                                        <i class="bi bi-cloud-arrow-up text-primary" style="font-size: 2.8rem; line-height: 1;"></i>
                                        <h6 class="fw-bold mt-2 mb-1" style="font-size: 0.95rem; color: #0f172a;">Drag &amp; Drop Foto di sini</h6>
                                        <p class="text-muted mb-2" style="font-size: 0.85rem;">atau</p>
                                        <button type="button" class="btn btn-outline-primary btn-sm px-3 fw-bold" id="btnBrowsePhoto" style="border-radius: 6px;">Pilih Foto</button>
                                        <p class="text-muted small mt-2 mb-0" style="font-size: 0.75rem;">Mendukung JPG, PNG, WEBP (Maks. 3 MB)</p>
                                    </div>
                                    
                                    <!-- Container Preview Foto -->
                                    <div id="photoPreviewContainer" class="d-none mt-2 text-center position-relative">
                                        <div class="p-2 border rounded-3 bg-white shadow-sm mb-2 d-inline-block position-relative" style="max-width: 100%;">
                                            <img id="photoPreviewImgModal" src="" alt="Preview Foto" class="img-fluid rounded-2" style="max-height: 180px; object-fit: contain;">
                                        </div>
                                        <div class="d-flex align-items-center justify-content-center gap-2">
                                            <span id="photoPreviewName" class="badge bg-light text-dark border text-truncate" style="max-width: 180px; font-size: 0.78rem;"></span>
                                            <button type="button" class="btn btn-sm btn-outline-danger py-1 px-2 fw-semibold" id="btnRemovePhoto" title="Hapus Foto">
                                                <i class="bi bi-trash3 me-1"></i> Hapus Foto
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <input type="file" class="d-none" name="foto" id="fotoUpload" accept="image/jpeg,image/png,image/webp,image/jpg">
                                </div>
                            </div>
                            <div class="mt-3 p-3 rounded-3" style="background-color: #f1f5f9; font-size: 0.8rem; color: #475569; line-height: 1.5;">
                                <i class="bi bi-lightbulb-fill text-warning me-1"></i>
                                <strong>Tips Inventaris:</strong> Foto fisik aset memudahkan verifikasi lapangan dan pencocokan label/barcode saat pemeriksaan fisik berkala.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between align-items-center" style="border-top: 1px solid #f1f5f9; padding: 1.25rem 1.75rem; background-color: #ffffff;">
                    <button type="button" class="btn btn-light px-4 py-2 fw-bold" data-bs-dismiss="modal" style="color: #64748b; border-radius: 8px; background: #f1f5f9; border: none;">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 py-2 fw-bold" id="btnSubmitForm" style="border-radius: 8px; box-shadow: 0 4px 12px rgba(var(--bs-primary-rgb), 0.2);">
                        Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Zoom Preview Foto Asset -->
<div class="modal fade" id="PhotoPreviewModal" tabindex="-1" aria-labelledby="PhotoPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border: none; border-radius: 14px; overflow: hidden; background: #0f172a;">
            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <h6 class="modal-title text-white fw-bold" id="photoPreviewTitle">Foto Asset</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-3">
                <img id="photoPreviewFullImg" src="" alt="Preview Foto" class="img-fluid rounded" style="max-height: 75vh; object-fit: contain;">
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
<?= $this->section('scripts'); ?>
<script>
    window.ASSET_UPLOAD_URL = "<?= base_url('apps/assets/uploads/assets/') ?>/";
</script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/service-table-ui.js') ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/manage-assets/detail.js?v=' . time()) ?>"></script>
<?= $this->endSection(); ?>
