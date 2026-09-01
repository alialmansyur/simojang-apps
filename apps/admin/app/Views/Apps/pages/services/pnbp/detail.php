<?= $this->extend('Apps/layouts/main_layout_with_navbar_v2'); ?>

<?= $this->section('style'); ?>
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork-common.css') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/teamwork-service.css') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/cat/main.css') ?>">
<link rel="stylesheet" href="<?= asset_url('apps/assets/css/pages/services/pnbp/pnbp-main.css?v=' . time()) ?>">
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>
<?php
    $isJamuan = in_array($doc['doc_type'], ['kwitansi_jamuan', 'surat_jalan', 'faktur', 'hadir_jamuan']);
    $isPersonel = in_array($doc['doc_type'], ['sp', 'st', 'nominatif', 'kwitansi', 'hadir']);
    $statusBadge = $doc['status'] === 'generated' ? 'bg-success' : 'bg-warning text-dark';
?>
<main class="page-content py-4">
    <div class="container-fluid text-start mx-auto pnbp-detail-container">
        
        <!-- Header & Action Buttons -->
        <div class="row align-items-center mb-3 tw-animate-entry" style="--animation-order: 1;">
            <div class="col-12 col-xl-7">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge <?= $statusBadge ?> px-3 py-1 fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.05em;">
                        <?= esc($doc['status']) ?>
                    </span>
                    <span class="badge bg-primary-subtle text-primary px-3 py-1 fw-bold" style="font-size: 0.75rem;">
                        <?= esc($docTypeLabels[$doc['doc_type']] ?? $doc['doc_type']) ?>
                    </span>
                </div>
                <h1 class="tw-title lh-1 text-dark fw-bold mb-1" style="font-size: 1.85rem;">
                    <?= esc($doc['title']) ?>
                </h1>
                <div class="text-secondary" style="font-size: 0.95rem;">
                    <i class="bi bi-hash"></i> No: <strong><?= esc($doc['doc_number'] ?: 'Belum Ada Nomor') ?></strong> &bull; 
                    <i class="bi bi-calendar-event"></i> Tanggal: <strong><?= \App\Services\PNBP\PNBPHelper::formatTanggalIndo($doc['doc_date']) ?></strong>
                </div>
            </div>

            <div class="col-12 col-xl-5 text-xl-end mt-3 mt-xl-0">
                <div class="d-inline-flex flex-wrap align-items-center gap-2">
                    <button type="button" class="btn btn-primary d-inline-flex align-items-center justify-content-center px-3 fw-bold" id="btnGeneratePdfDetail" data-uid="<?= esc($doc['uid']) ?>" style="height: 42px; border-radius: 8px;">
                        <i class="bi bi-file-earmark-pdf-fill me-1 fs-6"></i> Generate PDF
                    </button>
                    <button type="button" class="btn btn-outline-primary d-inline-flex align-items-center justify-content-center px-3 fw-semibold" id="btnPreviewPdfDetail" data-uid="<?= esc($doc['uid']) ?>" style="height: 42px; border-radius: 8px;">
                        <i class="bi bi-eye me-1 fs-6"></i> Pratinjau
                    </button>
                    <a href="<?= base_url('apps-pnbp/download-pdf/' . esc($doc['uid'])) ?>" class="btn btn-outline-success d-inline-flex align-items-center justify-content-center px-3 fw-semibold" style="height: 42px; border-radius: 8px;">
                        <i class="bi bi-download me-1 fs-6"></i> Unduh
                    </a>
                    <a href="<?= base_url('apps-pnbp/doc/' . esc($doc['doc_type'])) ?>" class="btn btn-light border px-3 fw-semibold" style="height: 42px; border-radius: 8px;">
                        <i class="bi bi-chevron-left me-1"></i> Kembali ke Daftar
                    </a>
                </div>
            </div>
        </div>

        <!-- Document Metadata Card -->
        <div class="card border-0 shadow-sm rounded-3 mb-4 tw-animate-entry" style="--animation-order: 2;">
            <div class="card-body p-3 p-md-4">
                <div class="row g-3 align-items-center">
                    <div class="col-12 col-md-3">
                        <small class="text-secondary fw-semibold d-block mb-1">Event Seleksi CAT</small>
                        <div class="fw-bold text-dark fs-6"><?= esc($doc['nama_seleksi'] ?? 'Tidak Terikat Event') ?></div>
                        <small class="text-muted"><?= esc($doc['seleksi_periode'] ?? '-') ?></small>
                    </div>
                    <div class="col-12 col-md-3">
                        <small class="text-secondary fw-semibold d-block mb-1">Instansi Terkait</small>
                        <div class="fw-bold text-dark fs-6"><?= esc($doc['instansi_nama'] ?? 'Non-Instansi') ?></div>
                        <small class="text-muted">Kode: <?= esc($doc['instansi_id'] ?? '-') ?></small>
                    </div>
                    <div class="col-12 col-md-3">
                        <small class="text-secondary fw-semibold d-block mb-1">Titik Lokasi (Tilok)</small>
                        <div class="fw-bold text-dark fs-6"><?= esc($doc['nama_tilok'] ?? 'Tidak Terikat Tilok') ?></div>
                        <small class="text-muted">
                            <?= \App\Services\PNBP\PNBPHelper::formatPeriode($doc['period_start_date'] ?? null, $doc['period_end_date'] ?? null) ?>
                        </small>
                    </div>
                    <div class="col-12 col-md-3 text-md-end">
                        <div class="mb-2 text-start text-md-end">
                            <small class="text-secondary fw-semibold d-block mb-1">Mata Anggaran (MAK)</small>
                            <div class="fw-bold text-dark" style="font-size: 0.85rem;"><?= esc($meta['mak'] ?? '-') ?></div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary fw-semibold" data-bs-toggle="modal" data-bs-target="#pnbpDocModal" id="btnEditDocHeader">
                            <i class="bi bi-pencil-square me-1"></i> Edit Header Dokumen
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($isPersonel): ?>
        <!-- ========================================================================= -->
        <!-- SECTION 1: DAFTAR PERSONEL TIM (SP / ST / NOMINATIF / KWITANSI / HADIR)   -->
        <!-- ========================================================================= -->
        <div class="card border-0 shadow-sm rounded-3 mb-4 tw-animate-entry" style="--animation-order: 3;">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="bi bi-people-fill text-primary me-2"></i> Daftar Personel Tim Pelaksana
                    </h5>
                    <small class="text-secondary">Daftar pegawai yang ditugaskan beserta rincian biaya penugasan.</small>
                </div>
                <button type="button" class="btn btn-sm btn-primary fw-semibold px-3" id="btnAddPersonel">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Personel
                </button>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 50px;">No</th>
                                <th>Nama & NIP</th>
                                <th>Pangkat / Golongan</th>
                                <th>Jabatan & Peran</th>
                                <th class="text-center">Hari</th>
                                <th class="text-end">Uang Harian</th>
                                <th class="text-end">Transport</th>
                                <th class="text-end">Total Biaya</th>
                                <th class="text-center" style="width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="personelTableBody">
                            <?php if (!empty($doc['personel'])): ?>
                                <?php 
                                    $totalBiayaAll = 0;
                                    foreach ($doc['personel'] as $idx => $p): 
                                        $totalBiayaAll += (float) ($p['total_biaya'] ?? 0);
                                ?>
                                <tr>
                                    <td class="text-center fw-bold"><?= $idx + 1 ?></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= esc($p['nama']) ?></div>
                                        <small class="text-muted">NIP. <?= esc($p['nip'] ?: '-') ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?= esc($p['pangkat_gol'] ?: '-') ?></span>
                                    </td>
                                    <td>
                                        <div><?= esc($p['jabatan'] ?: '-') ?></div>
                                        <small class="text-primary fw-semibold"><?= esc($p['peran'] ?: 'Pelaksana') ?></small>
                                    </td>
                                    <td class="text-center fw-semibold"><?= (int) $p['jumlah_hari'] ?> Hari</td>
                                    <td class="text-end"><?= \App\Services\PNBP\PNBPHelper::formatRupiah($p['uang_harian']) ?></td>
                                    <td class="text-end"><?= \App\Services\PNBP\PNBPHelper::formatRupiah($p['transport']) ?></td>
                                    <td class="text-end fw-bold text-success"><?= \App\Services\PNBP\PNBPHelper::formatRupiah($p['total_biaya']) ?></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm p-1 text-secondary btn-edit-personel" data-json="<?= esc(json_encode($p)) ?>" title="Edit">
                                            <i class="bi bi-pencil-square fs-6"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm p-1 text-danger btn-delete-personel" data-id="<?= esc($p['id']) ?>" title="Hapus">
                                            <i class="bi bi-trash fs-6"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        Belum ada personel yang ditambahkan. Klik tombol <strong>"Tambah Personel"</strong> di atas.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <?php if (!empty($doc['personel'])): ?>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="7" class="text-end fw-bold">TOTAL BIAYA:</th>
                                <th class="text-end fw-bold text-success fs-6"><?= \App\Services\PNBP\PNBPHelper::formatRupiah($totalBiayaAll ?? 0) ?></th>
                                <th></th>
                            </tr>
                        </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($isJamuan): ?>
        <!-- ========================================================================= -->
        <!-- SECTION 2: RINCIAN MENU / BELANJA JAMUAN                                  -->
        <!-- ========================================================================= -->
        <div class="card border-0 shadow-sm rounded-3 mb-4 tw-animate-entry" style="--animation-order: 3;">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="bi bi-cup-hot-fill text-warning me-2"></i> Rincian Menu / Belanja Jamuan (Katering)
                    </h5>
                    <small class="text-secondary">Daftar item konsumsi, jumlah pesanan, dan rincian harga satuan.</small>
                </div>
                <button type="button" class="btn btn-sm btn-warning text-dark fw-bold px-3" id="btnAddItem">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Item Jamuan
                </button>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 50px;">No</th>
                                <th>Nama Menu / Jamuan</th>
                                <th>Spesifikasi / Menu</th>
                                <th class="text-center">Jumlah</th>
                                <th class="text-center">Satuan</th>
                                <th class="text-end">Harga Satuan</th>
                                <th class="text-end">Total Harga</th>
                                <th class="text-center" style="width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="itemsTableBody">
                            <?php if (!empty($doc['items'])): ?>
                                <?php 
                                    $grandTotalItems = 0;
                                    foreach ($doc['items'] as $idx => $it): 
                                        $grandTotalItems += (float) ($it['total_harga'] ?? 0);
                                ?>
                                <tr>
                                    <td class="text-center fw-bold"><?= $idx + 1 ?></td>
                                    <td class="fw-bold text-dark"><?= esc($it['item_name']) ?></td>
                                    <td class="text-muted"><?= esc($it['spesifikasi'] ?: '-') ?></td>
                                    <td class="text-center fw-semibold"><?= (int) $it['quantity'] ?></td>
                                    <td class="text-center"><span class="badge bg-light text-dark border"><?= esc($it['satuan']) ?></span></td>
                                    <td class="text-end"><?= \App\Services\PNBP\PNBPHelper::formatRupiah($it['harga_satuan']) ?></td>
                                    <td class="text-end fw-bold text-success"><?= \App\Services\PNBP\PNBPHelper::formatRupiah($it['total_harga']) ?></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm p-1 text-secondary btn-edit-item" data-json="<?= esc(json_encode($it)) ?>" title="Edit">
                                            <i class="bi bi-pencil-square fs-6"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm p-1 text-danger btn-delete-item" data-id="<?= esc($it['id']) ?>" title="Hapus">
                                            <i class="bi bi-trash fs-6"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        Belum ada item jamuan yang ditambahkan. Klik tombol <strong>"Tambah Item Jamuan"</strong> di atas.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <?php if (!empty($doc['items'])): ?>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="6" class="text-end fw-bold">TOTAL BELANJA JAMUAN:</th>
                                <th class="text-end fw-bold text-success fs-6"><?= \App\Services\PNBP\PNBPHelper::formatRupiah($grandTotalItems ?? 0) ?></th>
                                <th></th>
                            </tr>
                        </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- ========================================================================= -->
        <!-- SECTION 3: PANEL PEJABAT TANDA TANGAN & QR CODE E-SIGN                    -->
        <!-- ========================================================================= -->
        <div class="card border-0 shadow-sm rounded-3 mb-4 tw-animate-entry" style="--animation-order: 4;">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="bi bi-qr-code-scan text-info me-2"></i> Parameter Tanda Tangan & Digital Signature
                    </h5>
                    <small class="text-secondary">Scan QR code atau buka link tanda tangan untuk membubuhkan goresan digital via smartphone.</small>
                </div>
            </div>
            
            <div class="card-body p-4">
                <div class="row g-4">
                    <?php if (!empty($doc['signatures'])): ?>
                        <?php foreach ($doc['signatures'] as $sig): ?>
                            <?php 
                                $isSigned = $sig['sign_status'] === 'signed';
                                $signUrl = base_url('pnbp-sign/' . $sig['sign_token']);
                            ?>
                            <div class="col-12 col-md-6 col-xl-4">
                                <div class="p-3 rounded-3 border h-100 position-relative <?= $isSigned ? 'border-success bg-success-subtle' : 'bg-light' ?>">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="badge bg-dark px-2 py-1 text-uppercase" style="font-size: 0.7rem;">
                                            Posisi: <?= esc($sig['sign_position']) ?> (<?= esc($sig['sign_role']) ?>)
                                        </span>
                                        <?php if ($isSigned): ?>
                                            <span class="badge bg-success fw-bold">
                                                <i class="bi bi-check-circle-fill me-1"></i> Terverifikasi
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark fw-bold">
                                                <i class="bi bi-hourglass-split me-1"></i> Menunggu TTD
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="mb-3">
                                        <small class="text-muted d-block"><?= esc($sig['sign_title'] ?: 'Penandatangan:') ?></small>
                                        <h6 class="fw-bold text-dark mb-0"><?= esc($sig['nama']) ?></h6>
                                        <small class="text-secondary">NIP. <?= esc($sig['nip'] ?: '-') ?></small><br>
                                        <small class="text-muted"><?= esc($sig['jabatan']) ?></small>
                                    </div>

                                    <div class="text-center py-2 bg-white rounded-2 border mb-3">
                                        <?php if ($isSigned && !empty($sig['signature_image_path'])): ?>
                                            <!-- Preview Tanda Tangan Digital -->
                                            <img src="<?= base_url('writable/' . $sig['signature_image_path']) ?>" alt="Signature" style="max-height: 70px; max-width: 140px;" class="img-fluid">
                                            <div class="text-success mt-1 fw-semibold" style="font-size: 0.72rem;">
                                                Ditandatangani pada <?= \App\Services\PNBP\PNBPHelper::formatTanggalIndo($sig['signed_at']) ?>
                                            </div>
                                        <?php else: ?>
                                            <!-- QR Code Link -->
                                            <div class="p-1">
                                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?= urlencode($signUrl) ?>" alt="QR Code" style="width: 80px; height: 80px;">
                                            </div>
                                            <small class="text-muted d-block" style="font-size: 0.72rem;">Scan QR di atas untuk TTD di HP</small>
                                        <?php endif; ?>
                                    </div>

                                    <div class="d-flex align-items-center gap-1">
                                        <a href="<?= $signUrl ?>" target="_blank" class="btn btn-sm btn-outline-primary flex-grow-1 fw-semibold">
                                            <i class="bi bi-box-arrow-up-right me-1"></i> Buka TTD
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-copy-link" data-url="<?= $signUrl ?>" title="Salin Tautan">
                                            <i class="bi bi-link-45deg fs-6"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-light border btn-edit-signer-param" data-json="<?= esc(json_encode($sig)) ?>" title="Ubah Pejabat">
                                            <i class="bi bi-pencil fs-6"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals for Personel, Items, Signer Param, and PDF Preview -->
    
    <!-- Modal Personel -->
    <div class="modal fade" id="pnbpPersonelModal" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom py-3 px-4 bg-light">
                    <h5 class="modal-title fw-bold text-dark" id="personelModalTitle">Tambah Personel Tim</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="pnbpPersonelForm">
                        <input type="hidden" name="document_uid" value="<?= esc($doc['uid']) ?>">
                        <input type="hidden" name="personel_id" id="personel_id">
                        
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold mb-1">Cari dari Master Pegawai (Lookup)</label>
                                <select class="form-select" id="pegawaiLookupSelect">
                                    <option value="">Ketik nama atau NIP pegawai...</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold mb-1">Nama Lengkap & Gelar <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nama" id="personel_nama" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold mb-1">NIP</label>
                                <input type="text" class="form-control" name="nip" id="personel_nip">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold mb-1">Pangkat / Golongan</label>
                                <input type="text" class="form-control" name="pangkat_gol" id="personel_pangkat_gol" placeholder="Contoh: Penata Tk. I (III/d)">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold mb-1">Jabatan</label>
                                <input type="text" class="form-control" name="jabatan" id="personel_jabatan" placeholder="Contoh: Pranata Komputer Ahli Muda">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold mb-1">Peran dalam Tim</label>
                                <input type="text" class="form-control" name="peran" id="personel_peran" placeholder="Contoh: Koordinator / Tim IT / Pengawas">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold mb-1">Jumlah Hari Tugas</label>
                                <input type="number" class="form-control" name="jumlah_hari" id="personel_jumlah_hari" value="1" min="1">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold mb-1">Uang Harian (Rp)</label>
                                <input type="number" class="form-control" name="uang_harian" id="personel_uang_harian" value="0">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold mb-1">Biaya Transport (Rp)</label>
                                <input type="number" class="form-control" name="transport" id="personel_transport" value="0">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold mb-1">Nomor Rekening Bank</label>
                                <input type="text" class="form-control" name="no_rekening" id="personel_no_rekening" placeholder="Contoh: Bank Mandiri - 123456789">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary fw-bold" id="btnSavePersonel">Simpan Personel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Item Jamuan -->
    <div class="modal fade" id="pnbpItemModal" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom py-3 px-4 bg-light">
                    <h5 class="modal-title fw-bold text-dark" id="itemModalTitle">Tambah Item Jamuan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="pnbpItemForm">
                        <input type="hidden" name="document_uid" value="<?= esc($doc['uid']) ?>">
                        <input type="hidden" name="item_id" id="item_id">
                        
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold mb-1">Nama Jamuan / Konsumsi <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="item_name" id="item_name" placeholder="Contoh: Snack Box Pagi / Makan Siang Prasmanan" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold mb-1">Spesifikasi / Menu</label>
                                <textarea class="form-control" name="spesifikasi" id="item_spesifikasi" rows="2" placeholder="Contoh: Nasi, Ayam Bakar, Sayur Asem, Buah, Air Mineral"></textarea>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold mb-1">Jumlah (Qty)</label>
                                <input type="number" class="form-control" name="quantity" id="item_quantity" value="1" min="1">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold mb-1">Satuan</label>
                                <input type="text" class="form-control" name="satuan" id="item_satuan" value="Box" placeholder="Box / Pax / Kali">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold mb-1">Harga Satuan (Rp)</label>
                                <input type="number" class="form-control" name="harga_satuan" id="item_harga_satuan" value="0">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-warning text-dark fw-bold" id="btnSaveItem">Simpan Item</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Signer Param -->
    <div class="modal fade" id="pnbpSignerModal" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom py-3 px-4 bg-light">
                    <h5 class="modal-title fw-bold text-dark">Ubah Pejabat Penandatangan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="pnbpSignerForm">
                        <input type="hidden" name="signature_id" id="sig_id">
                        
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold mb-1">Label Teks (Atas)</label>
                                <input type="text" class="form-control" name="sign_title" id="sig_sign_title" placeholder="Contoh: Yang Memerintahkan, / Mengetahui,">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold mb-1">Nama Pejabat <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nama" id="sig_nama" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold mb-1">NIP</label>
                                <input type="text" class="form-control" name="nip" id="sig_nip">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold mb-1">Pangkat / Golongan</label>
                                <input type="text" class="form-control" name="pangkat_gol" id="sig_pangkat_gol">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold mb-1">Jabatan Resmi</label>
                                <input type="text" class="form-control" name="jabatan" id="sig_jabatan">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary fw-bold" id="btnSaveSignerParam">Simpan Perubahan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals Shared -->
    <?= $this->include('Apps/pages/services/pnbp/modal_form'); ?>
    <?= $this->include('Apps/pages/services/pnbp/modal_preview'); ?>
</main>
<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script>
    var DOC_UID = "<?= esc($doc['uid']) ?>";
    var DOC_DATA = <?= json_encode($doc) ?>;
</script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/pnbp/pnbp-generator.js?v=' . time()) ?>"></script>
<script src="<?= asset_url('apps/assets/js/custom/pages/services/pnbp/pnbp-detail.js?v=' . time()) ?>"></script>
<?= $this->endSection(); ?>
