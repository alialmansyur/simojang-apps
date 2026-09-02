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
<main class="page-content" aria-labelledby="pnbpDetailHeading">
    <div class="text-start tws-wrap container-fluid">
        
        <!-- Page Header -->
        <div class="row align-items-start mt-3 mb-3 tw-animate-entry" style="--animation-order: 1;">
            <div class="col-12 col-xl-8">
                <!-- Badges Status & Tipe Dokumen -->
                <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                    <span class="badge <?= $statusBadge ?> px-3 py-1 fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.05em;">
                        <?= esc($doc['status']) ?>
                    </span>
                    <span class="badge bg-primary px-3 py-1 fw-bold text-white" style="font-size: 0.75rem;">
                        <?= esc($docTypeLabels[$doc['doc_type']] ?? $doc['doc_type']) ?>
                    </span>
                </div>

                <!-- Judul Dokumen (Besar & Bold Tegas) -->
                <h1 class="tw-title mb-2 pnbp-text-dark" id="pnbpDetailHeading" style="color: #0f172a !important; font-size: 1.55rem; font-weight: 800; line-height: 1.35; letter-spacing: -0.015em;">
                    <?= esc($doc['title']) ?>
                </h1>

                <!-- Ringkasan Metadata Header (Tanpa Info Instansi, Rapi & Gelap Terbaca Jelas) -->
                <div class="d-flex flex-wrap align-items-center gap-x-3 gap-y-1 pnbp-text-secondary" style="font-size: 0.88rem; color: #334155 !important; row-gap: 6px; column-gap: 16px;">
                    <div class="d-inline-flex align-items-center gap-1">
                        <i class="bi bi-hash pnbp-text-muted" style="color: #64748b !important;"></i> <span>No:</span> <span class="fw-bold pnbp-text-dark" style="color: #0f172a !important;"><?= esc($doc['doc_number'] ?: 'Belum Ada Nomor') ?></span>
                    </div>
                    <span class="d-none d-sm-inline" style="color: #94a3b8 !important;">&bull;</span>
                    <div class="d-inline-flex align-items-center gap-1">
                        <i class="bi bi-calendar-event pnbp-text-muted" style="color: #64748b !important;"></i> <span>Tanggal:</span> <span class="fw-bold pnbp-text-dark" style="color: #0f172a !important;"><?= \App\Services\PNBP\PNBPHelper::formatTanggalIndo($doc['doc_date']) ?></span>
                    </div>
                    <?php if (!empty($doc['nama_seleksi'])): ?>
                        <span class="d-none d-sm-inline" style="color: #94a3b8 !important;">&bull;</span>
                        <div class="d-inline-flex align-items-center gap-1">
                            <i class="bi bi-award pnbp-text-muted" style="color: #64748b !important;"></i> <span>Event:</span> <span class="fw-bold pnbp-text-dark" style="color: #0f172a !important;"><?= esc($doc['nama_seleksi']) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tombol Aksi Header (1 Baris Ringkas: Generate PDF & Kembali) -->
            <div class="col-12 col-xl-4 text-xl-end mt-3 mt-xl-0">
                <div class="d-flex align-items-center justify-content-start justify-content-xl-end gap-2 flex-nowrap">
                    <button type="button" class="btn btn-primary d-inline-flex align-items-center justify-content-center px-3 fw-bold flex-shrink-0" id="btnGeneratePdfDetail" data-uid="<?= esc($doc['uid']) ?>" style="height: 42px; border-radius: 8px;">
                        <i class="bi bi-file-earmark-pdf-fill fs-6"></i> <span>Generate PDF</span>
                    </button>
                    <a href="<?= base_url('apps-pnbp/doc/' . esc($doc['doc_type'])) ?>" class="btn btn-outline-primary d-inline-flex align-items-center justify-content-center px-3 fw-semibold flex-shrink-0" style="height: 42px; border-radius: 8px;">
                        <i class="bi bi-chevron-left fs-6"></i> <span>Kembali</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Document Metadata Card -->
        <div class="card border-0 shadow-sm rounded-3 mb-4 tw-animate-entry" style="--animation-order: 2;">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center">
                    <h5 class="fw-bolder mb-0 pnbp-text-dark d-inline-flex align-items-center gap-3" style="color: #0f172a !important; font-size: 1.12rem; font-weight: 800; letter-spacing: -0.01em; line-height: 1.2;">
                        <i class="bi bi-info-circle text-primary fs-5 d-inline-flex align-items-center justify-content-center" style="line-height: 1; vertical-align: 0;"></i>
                        <span class="d-inline-flex align-items-center">Rincian Informasi & Titik Lokasi</span>
                    </h5>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center justify-content-center px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#pnbpDocModal" id="btnEditDocHeader" style="height: 36px; border-radius: 8px; font-size: 0.85rem;">
                        <i class="bi bi-pencil-square"></i> <span>Edit Header Dokumen</span>
                    </button>
                    <button class="btn btn-sm btn-light border d-inline-flex align-items-center justify-content-center" type="button" data-bs-toggle="collapse" data-bs-target="#metadataCollapse" aria-expanded="true" aria-controls="metadataCollapse" style="height: 36px; width: 36px; border-radius: 8px;" title="Tampilkan / Sembunyikan Metadata">
                        <i class="bi bi-chevron-down"></i>
                    </button>
                </div>
            </div>
            
            <div class="collapse show" id="metadataCollapse">
                <div class="card-body p-3 p-md-4">
                    <div class="row g-3 align-items-stretch">
                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="pnbp-meta-box">
                                <div class="pnbp-meta-label"><i class="bi bi-award me-1"></i> Event Seleksi CAT</div>
                                <div class="pnbp-meta-val"><?= esc($doc['nama_seleksi'] ?? 'Tidak Terikat Event') ?></div>
                                <div class="pnbp-meta-sub"><?= esc($doc['seleksi_periode'] ?? 'Periode Mandiri') ?></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="pnbp-meta-box">
                                <div class="pnbp-meta-label"><i class="bi bi-building me-1"></i> Instansi Terkait</div>
                                <div class="pnbp-meta-val"><?= esc($doc['instansi_nama'] ?? 'Non-Instansi') ?></div>
                                <div class="pnbp-meta-sub">Kode Instansi: <?= esc($doc['instansi_id'] ?? '-') ?></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="pnbp-meta-box">
                                <div class="pnbp-meta-label"><i class="bi bi-geo-alt me-1"></i> Titik Lokasi (Tilok)</div>
                                <div class="pnbp-meta-val"><?= esc($doc['nama_tilok'] ?? 'Tidak Terikat Tilok') ?></div>
                                <div class="pnbp-meta-sub">
                                    <?= \App\Services\PNBP\PNBPHelper::formatPeriode($doc['period_start_date'] ?? null, $doc['period_end_date'] ?? null) ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="pnbp-meta-box">
                                <div class="pnbp-meta-label"><i class="bi bi-cash-stack me-1"></i> Mata Anggaran (MAK)</div>
                                <div class="pnbp-meta-val text-primary"><?= esc($meta['mak'] ?? '-') ?></div>
                                <div class="pnbp-meta-sub">Akun DIPA / MAK Kegiatan</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php 
            $isNominatif = ($doc['doc_type'] === 'nominatif');
            $instansiList = !empty($instansiOptions) ? $instansiOptions : [];
            $selectedInstansiIds = !empty($meta['instansi_ids']) ? (array) $meta['instansi_ids'] : (!empty($doc['instansi_id']) ? [$doc['instansi_id']] : []);
            
            $defaultTanggal = !empty($meta['tanggal_kegiatan']) ? $meta['tanggal_kegiatan'] : (!empty($doc['period_start_date']) ? \App\Services\PNBP\PNBPHelper::formatPeriode($doc['period_start_date'], $doc['period_end_date']) : \App\Services\PNBP\PNBPHelper::formatTanggalIndo($doc['doc_date']));
            
            $instansiNamesInitial = !empty($meta['instansi_names']) ? (is_array($meta['instansi_names']) ? implode(', ', $meta['instansi_names']) : $meta['instansi_names']) : ($doc['instansi_nama'] ?? 'Pemerintah Daerah');
            $defaultKeteranganInitial = "Honorarium Tim Panitia dalam rangka Fasilitasi Seleksi Pengembangan Karier dengan metode CAT BKN di Lingkungan Instansi " . $instansiNamesInitial . " di Kanreg III BKN, pada tanggal " . $defaultTanggal . ".";
            $currentHeaderKeterangan = !empty($meta['header_keterangan']) ? $meta['header_keterangan'] : $defaultKeteranganInitial;
        ?>

        <?php if ($isNominatif): ?>
        <!-- ========================================================================= -->
        <!-- SECTION 0: HEADER / KETERANGAN REDAKSI & MULTIPLE INSTANSI (NOMINATIF)     -->
        <!-- ========================================================================= -->
        <div class="card mb-4 tw-animate-entry" style="--animation-order: 2.5;">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold text-dark mb-0" style="font-size: 1.05rem;">
                        <i class="bi bi-file-text-fill text-primary me-2"></i> Redaksi Header / Keterangan Dokumen Nominatif
                    </h5>
                    <div class="text-secondary small mt-1">Pilih instansi terkait untuk otomatis menyusun kalimat baku, atau ubah redaksi secara bebas.</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary fw-semibold text-dark" id="btnResetKalimatBaku">
                        <i class="bi bi-arrow-repeat me-1"></i> Kalimat Baku
                    </button>
                    <button type="button" class="btn btn-sm btn-primary fw-bold px-3 shadow-sm" id="btnSaveHeader">
                        <i class="bi bi-check-lg me-1"></i> Simpan Keterangan
                    </button>
                </div>
            </div>
            
            <div class="card-body p-4">
                <form id="pnbpHeaderForm">
                    <input type="hidden" name="document_uid" value="<?= esc($doc['uid']) ?>">
                    
                    <div class="row g-3">
                        <div class="col-12 col-lg-7">
                            <label class="form-label mb-1">
                                Instansi Terkait (Bisa Pilih Lebih dari 1) <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="headerInstansiSelect" name="instansi_ids[]" multiple="multiple" style="width: 100%;">
                                <?php foreach ($instansiList as $ins): 
                                    $isSel = in_array($ins['kodeins'], $selectedInstansiIds) ? 'selected' : '';
                                ?>
                                    <option value="<?= esc($ins['kodeins']) ?>" data-nama="<?= esc($ins['nama']) ?>" <?= $isSel ?>>
                                        <?= esc($ins['nama']) ?> (<?= esc($ins['kodeins']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="text-muted small mt-1">Pilih satu atau beberapa instansi pemerintah yang difasilitasi.</div>
                        </div>

                        <div class="col-12 col-lg-5">
                            <label class="form-label mb-1">
                                Tanggal / Periode Kegiatan
                            </label>
                            <input type="text" class="form-control" name="tanggal_kegiatan" id="headerTanggalKegiatan" value="<?= esc($defaultTanggal) ?>" placeholder="Contoh: 27 Agustus 2026 atau 27 s/d 29 Agustus 2026">
                            <div class="text-muted small mt-1">Digunakan pada kalimat pembuka keterangan nominatif.</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label mb-1">
                                Teks Keterangan Pembuka (Dapat Diedit Manual)
                            </label>
                            <textarea class="form-control" name="header_keterangan" id="headerKeteranganText" rows="3"><?= esc($currentHeaderKeterangan) ?></textarea>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- SECTION 1 (NOMINATIF): TABLE APPEND ENTRY DATA PEGAWAI                    -->
        <!-- ========================================================================= -->
        <div class="card mb-4 tw-animate-entry" style="--animation-order: 3;">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold text-dark mb-0" style="font-size: 1.05rem;">
                        <i class="bi bi-people-fill text-primary me-2"></i> Daftar Nominatif Penerima Honorarium
                    </h5>
                    <div class="text-secondary small mt-1">Input data pegawai dari master data, tentukan honorarium, potongan PPh 21, dan jumlah diterima.</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary px-3 py-2 fw-bold text-white" id="badgeTotalPegawai" style="font-size: 0.8rem;">
                        Total: <?= count($doc['personel'] ?? []) ?> Pegawai
                    </span>
                </div>
            </div>
            
            <!-- Quick Append Form Panel (Ala /apps-cat-detail/*) -->
            <div class="card-body bg-light border-bottom p-3 p-md-4">
                <form id="appendNominatifForm">
                    <input type="hidden" name="document_uid" value="<?= esc($doc['uid']) ?>">
                    <input type="hidden" name="status_pegawai" id="app_status_pegawai" value="PNS">
                    
                    <div class="row g-2 align-items-end">
                        <div class="col-12 col-xl-3">
                            <label class="form-label mb-1">Cari dari Data Pegawai <span class="text-danger">*</span></label>
                            <select class="form-select" id="selectPegawaiNominatif" style="width: 100%;">
                                <option value="">-- Ketik Nama / NIP --</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-4 col-xl-2">
                            <label class="form-label mb-1">Nama Lengkap</label>
                            <input type="text" class="form-control" name="nama" id="app_nama" placeholder="Nama Pegawai" required style="height: 38px;">
                            <input type="hidden" name="nip" id="app_nip">
                        </div>

                        <div class="col-6 col-md-2 col-xl-1">
                            <label class="form-label mb-1">Gol.</label>
                            <input type="text" class="form-control text-center" name="pangkat_gol" id="app_gol" placeholder="III / VII" style="height: 38px;">
                        </div>

                        <div class="col-6 col-md-3 col-xl-2">
                            <label class="form-label mb-1">NIK Pegawai</label>
                            <input type="text" class="form-control text-center" name="nik" id="app_nik" placeholder="16 digit NIK" style="height: 38px;">
                        </div>

                        <div class="col-12 col-md-3 col-xl-2">
                            <label class="form-label mb-1">Bank & No. Rekening</label>
                            <div class="input-group" style="height: 38px;">
                                <input type="text" class="form-control fw-semibold" name="bank_nama" id="app_bank_nama" placeholder="BRI" style="max-width: 65px;">
                                <input type="text" class="form-control" name="no_rekening" id="app_no_rek" placeholder="No Rekening">
                            </div>
                        </div>

                        <div class="col-6 col-md-3 col-xl-2">
                            <label class="form-label mb-1">Jabatan</label>
                            <input type="text" class="form-control" name="jabatan" id="app_jabatan" value="Anggota" placeholder="Penanggung Jawab / Anggota" style="height: 38px;">
                        </div>

                        <!-- Baris 2 Form Append: Biaya, Pajak, dan Tombol Tambah -->
                        <div class="col-12 col-md-4 col-xl-2 mt-2">
                            <label class="form-label mb-1 text-primary">Jumlah Honor (Rp) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control fw-bold text-end" name="jumlah" id="app_jumlah" value="450000" min="0" step="1000" required style="height: 38px;">
                        </div>

                        <div class="col-6 col-md-2 col-xl-1 mt-2">
                            <label class="form-label mb-1">Pajak</label>
                            <select class="form-select fw-bold text-center" name="pajak_persen" id="app_pajak_persen" style="height: 38px;">
                                <option value="0">0%</option>
                                <option value="1">1%</option>
                                <option value="2">2%</option>
                                <option value="3">3%</option>
                                <option value="4">4%</option>
                                <option value="5" selected>5%</option>
                            </select>
                        </div>

                        <div class="col-6 col-md-3 col-xl-2 mt-2">
                            <label class="form-label mb-1">Nilai Pajak (Rp)</label>
                            <input type="text" class="form-control text-end bg-white fw-bold text-danger" id="app_pajak_nominal_view" readonly value="22.500" style="height: 38px;">
                        </div>

                        <div class="col-6 col-md-3 col-xl-2 mt-2">
                            <label class="form-label mb-1 text-success">Diterima Bersih (Rp)</label>
                            <input type="text" class="form-control text-end fw-bold bg-white text-success" id="app_jumlah_diterima_view" readonly value="427.500" style="height: 38px;">
                        </div>

                        <div class="col-6 col-md-12 col-xl-5 mt-2 text-end">
                            <button type="button" class="btn btn-primary px-4 fw-bold w-100 d-flex align-items-center justify-content-center shadow-sm" id="btnAppendNominatif" style="height: 38px; border-radius: 6px;">
                                <i class="bi bi-plus-circle-fill me-2 fs-6"></i> Tambah Pegawai ke Daftar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tableNominatifList">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 45px;">NO</th>
                                <th>NAMA / NIP</th>
                                <th class="text-center" style="width: 75px;">GOL</th>
                                <th class="text-center" style="width: 150px;">NIK</th>
                                <th class="text-center" style="width: 170px;">BANK & NO REK</th>
                                <th class="text-center" style="width: 130px;">JABATAN</th>
                                <th class="text-end" style="width: 125px;">JUMLAH (Rp)</th>
                                <th class="text-end" style="width: 115px;">PAJAK PPh 21</th>
                                <th class="text-end" style="width: 135px;">JUMLAH DITERIMA</th>
                                <th class="text-center" style="width: 95px;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody id="nominatifTableBody">
                            <?php 
                                $totalJumlahInit   = 0;
                                $totalPajakInit    = 0;
                                $totalDiterimaInit = 0;
                            ?>
                            <?php if (!empty($doc['personel'])): ?>
                                <?php foreach ($doc['personel'] as $idx => $p): 
                                    $jVal   = (float) ($p['jumlah'] > 0 ? $p['jumlah'] : ($p['total_biaya'] ?? 0));
                                    $pNomVal = (float) ($p['pajak_nominal'] ?? 0);
                                    if ($pNomVal == 0 && !empty($p['pajak_persen']) && $p['pajak_persen'] > 0) {
                                        $pNomVal = round($jVal * ($p['pajak_persen'] / 100), 2);
                                    }
                                    $dVal = (float) ($p['jumlah_diterima'] > 0 ? $p['jumlah_diterima'] : ($jVal - $pNomVal));

                                    $totalJumlahInit   += $jVal;
                                    $totalPajakInit    += $pNomVal;
                                    $totalDiterimaInit += $dVal;

                                    $golTxt = trim((string) ($p['pangkat_gol'] ?? '-'));
                                    $bName = trim((string) ($p['bank_nama'] ?? 'BRI'));
                                    $nRek = trim((string) ($p['no_rekening'] ?? '-'));
                                ?>
                                <tr id="row-personel-<?= $p['id'] ?>">
                                    <td class="text-center fw-bold row-no"><?= $idx + 1 ?></td>
                                    <td>
                                        <div class="fw-bold text-dark text-uppercase"><?= esc($p['nama']) ?></div>
                                        <div class="text-secondary small fw-medium">NIP. <?= esc($p['nip'] ?: '-') ?></div>
                                    </td>
                                    <td class="text-center fw-bold text-dark"><?= esc($golTxt) ?></td>
                                    <td class="text-center fw-medium text-dark"><?= esc($p['nik'] ?: '-') ?></td>
                                    <td class="text-center">
                                        <div class="fw-bold text-primary"><?= esc($bName) ?></div>
                                        <div class="text-secondary small fw-medium"><?= esc($nRek) ?></div>
                                    </td>
                                    <td class="text-center fw-medium text-dark"><?= esc($p['jabatan'] ?: ($p['peran'] ?: 'Anggota')) ?></td>
                                    <td class="text-end fw-bold text-dark"><?= number_format($jVal, 0, ',', '.') ?></td>
                                    <td class="text-end fw-bold text-danger"><?= $pNomVal > 0 ? number_format($pNomVal, 0, ',', '.') : '-' ?></td>
                                    <td class="text-end fw-bold text-success"><?= number_format($dVal, 0, ',', '.') ?></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-secondary p-1 me-1 btn-edit-personel-nom" data-json="<?= esc(json_encode($p)) ?>" title="Edit">
                                            <i class="bi bi-pencil-square fs-6 text-dark"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger p-1 btn-delete-personel" data-id="<?= esc($p['id']) ?>" title="Hapus">
                                            <i class="bi bi-trash fs-6 text-danger"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr id="emptyNominatifRow">
                                    <td colspan="10" class="text-center text-secondary py-5">
                                        <img src="<?= asset_url('apps/assets/images/empty-content-profile.png') ?>" alt="Kosong" style="max-width: 130px; margin-bottom: 0.75rem;"><br>
                                        <div class="fw-bold text-dark fs-6">Belum ada pegawai pada daftar nominatif.</div>
                                        <div class="text-secondary small mt-1">Gunakan form pencarian di atas untuk menambahkan penerima honorarium.</div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" class="text-center fw-bold" style="letter-spacing: 0.05em; font-size: 0.85rem;">JUMLAH SELURUHNYA</td>
                                <td class="text-end fw-bold text-dark" id="footTotalJumlah" style="font-size: 0.92rem;"><?= number_format($totalJumlahInit, 0, ',', '.') ?></td>
                                <td class="text-end fw-bold text-danger" id="footTotalPajak" style="font-size: 0.92rem;"><?= $totalPajakInit > 0 ? number_format($totalPajakInit, 0, ',', '.') : '-' ?></td>
                                <td class="text-end fw-bold text-success" id="footTotalDiterima" style="font-size: 1rem;"><?= number_format($totalDiterimaInit, 0, ',', '.') ?></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <?php else: ?>
        <!-- ========================================================================= -->
        <!-- SECTION 1 (STANDARD): DAFTAR PERSONEL TIM (SP / ST / KWITANSI / HADIR)    -->
        <!-- ========================================================================= -->
        <div class="card mb-4 tw-animate-entry" style="--animation-order: 3;">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold text-dark mb-0" style="font-size: 1.05rem;">
                        <i class="bi bi-people-fill text-primary me-2"></i> Daftar Personel Tim Pelaksana
                    </h5>
                    <div class="text-secondary small mt-1">Daftar pegawai yang ditugaskan beserta rincian biaya penugasan.</div>
                </div>
                <button type="button" class="btn btn-sm btn-primary fw-bold px-3 shadow-sm" id="btnAddPersonel">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Personel
                </button>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
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
                                        <div class="text-secondary small fw-medium">NIP. <?= esc($p['nip'] ?: '-') ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?= esc($p['pangkat_gol'] ?: '-') ?></span>
                                    </td>
                                    <td>
                                        <div class="text-dark fw-medium"><?= esc($p['jabatan'] ?: '-') ?></div>
                                        <div class="text-primary small fw-bold"><?= esc($p['peran'] ?: 'Pelaksana') ?></div>
                                    </td>
                                    <td class="text-center fw-bold text-dark"><?= (int) $p['jumlah_hari'] ?> Hari</td>
                                    <td class="text-end text-dark"><?= \App\Services\PNBP\PNBPHelper::formatRupiah($p['uang_harian']) ?></td>
                                    <td class="text-end text-dark"><?= \App\Services\PNBP\PNBPHelper::formatRupiah($p['transport']) ?></td>
                                    <td class="text-end fw-bold text-success"><?= \App\Services\PNBP\PNBPHelper::formatRupiah($p['total_biaya']) ?></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-secondary p-1 me-1 btn-edit-personel" data-json="<?= esc(json_encode($p)) ?>" title="Edit">
                                            <i class="bi bi-pencil-square fs-6 text-dark"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger p-1 btn-delete-personel" data-id="<?= esc($p['id']) ?>" title="Hapus">
                                            <i class="bi bi-trash fs-6 text-danger"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center text-secondary py-4">
                                        Belum ada personel yang ditambahkan. Klik tombol <strong>"Tambah Personel"</strong> di atas.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <?php if (!empty($doc['personel'])): ?>
                        <tfoot>
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
        <div class="card mb-4 tw-animate-entry" style="--animation-order: 3;">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold text-dark mb-0" style="font-size: 1.05rem;">
                        <i class="bi bi-cup-hot-fill text-warning me-2"></i> Rincian Menu / Belanja Jamuan (Katering)
                    </h5>
                    <div class="text-secondary small mt-1">Daftar item konsumsi, jumlah pesanan, dan rincian harga satuan.</div>
                </div>
                <button type="button" class="btn btn-sm btn-warning text-dark fw-bold px-3 shadow-sm" id="btnAddItem">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Item Jamuan
                </button>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
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
                                    <td class="text-secondary"><?= esc($it['spesifikasi'] ?: '-') ?></td>
                                    <td class="text-center fw-bold text-dark"><?= (int) $it['quantity'] ?></td>
                                    <td class="text-center"><span class="badge bg-light text-dark border"><?= esc($it['satuan']) ?></span></td>
                                    <td class="text-end text-dark"><?= \App\Services\PNBP\PNBPHelper::formatRupiah($it['harga_satuan']) ?></td>
                                    <td class="text-end fw-bold text-success"><?= \App\Services\PNBP\PNBPHelper::formatRupiah($it['total_harga']) ?></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-secondary p-1 me-1 btn-edit-item" data-json="<?= esc(json_encode($it)) ?>" title="Edit">
                                            <i class="bi bi-pencil-square fs-6 text-dark"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger p-1 btn-delete-item" data-id="<?= esc($it['id']) ?>" title="Hapus">
                                            <i class="bi bi-trash fs-6 text-danger"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center text-secondary py-4">
                                        Belum ada item jamuan yang ditambahkan. Klik tombol <strong>"Tambah Item Jamuan"</strong> di atas.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <?php if (!empty($doc['items'])): ?>
                        <tfoot>
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
        <div class="card mb-4 tw-animate-entry" style="--animation-order: 4;">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold text-dark mb-0" style="font-size: 1.05rem;">
                        <i class="bi bi-qr-code-scan text-primary me-2"></i> Parameter Tanda Tangan & Digital Signature
                    </h5>
                    <div class="text-secondary small mt-1">Scan QR code atau buka link tanda tangan untuk membubuhkan goresan digital via smartphone.</div>
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
                                <div class="p-3 rounded-3 border h-100 position-relative <?= $isSigned ? 'border-success bg-success-subtle' : 'bg-light' ?>" style="border-color: #cbd5e1 !important;">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="badge bg-dark px-2 py-1 text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                                            Posisi: <?= esc($sig['sign_position']) ?> (<?= esc($sig['sign_role']) ?>)
                                        </span>
                                        <?php if ($isSigned): ?>
                                            <span class="badge bg-success fw-bold text-white">
                                                <i class="bi bi-check-circle-fill me-1"></i> Terverifikasi
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark fw-bold">
                                                <i class="bi bi-hourglass-split me-1"></i> Menunggu TTD
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="mb-3">
                                        <div class="pnbp-meta-label"><?= esc($sig['sign_title'] ?: 'Penandatangan:') ?></div>
                                        <h6 class="fw-bold text-dark mb-1" style="font-size: 1rem;"><?= esc($sig['nama']) ?></h6>
                                        <div class="text-secondary small fw-medium">NIP. <?= esc($sig['nip'] ?: '-') ?></div>
                                        <div class="text-muted small mt-1"><?= esc($sig['jabatan']) ?></div>
                                    </div>

                                    <div class="text-center py-2 bg-white rounded-2 border mb-3" style="border-color: #cbd5e1 !important;">
                                        <?php if ($isSigned && !empty($sig['signature_image_path'])): ?>
                                            <!-- Preview Tanda Tangan Digital -->
                                            <img src="<?= base_url('writable/' . $sig['signature_image_path']) ?>" alt="Signature" style="max-height: 70px; max-width: 140px;" class="img-fluid">
                                            <div class="text-success mt-1 fw-bold" style="font-size: 0.75rem;">
                                                Ditandatangani pada <?= \App\Services\PNBP\PNBPHelper::formatTanggalIndo($sig['signed_at']) ?>
                                            </div>
                                        <?php else: ?>
                                            <!-- QR Code Link -->
                                            <div class="p-1">
                                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?= urlencode($signUrl) ?>" alt="QR Code" style="width: 80px; height: 80px;">
                                            </div>
                                            <div class="text-secondary small mt-1 fw-medium" style="font-size: 0.75rem;">Scan QR di atas untuk TTD di HP</div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="d-flex align-items-center gap-1">
                                        <a href="<?= $signUrl ?>" target="_blank" class="btn btn-sm btn-outline-primary flex-grow-1 fw-bold" style="height: 34px;">
                                            <i class="bi bi-box-arrow-up-right me-1"></i> Buka TTD
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-copy-link" data-url="<?= $signUrl ?>" title="Salin Tautan" style="height: 34px;">
                                            <i class="bi bi-link-45deg fs-6 text-dark"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-light border btn-edit-signer-param" data-json="<?= esc(json_encode($sig)) ?>" title="Ubah Pejabat" style="height: 34px;">
                                            <i class="bi bi-pencil fs-6 text-dark"></i>
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
            <div class="modal-content border-0 shadow" style="background-color: #ffffff !important; color: #0f172a !important;">
                <div class="modal-header border-bottom py-3 px-4 bg-light">
                    <h5 class="modal-title fw-bold mb-0" id="personelModalTitle" style="color: #0f172a !important;">Tambah Personel Tim</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" style="background-color: #ffffff !important;">
                    <form id="pnbpPersonelForm">
                        <input type="hidden" name="document_uid" value="<?= esc($doc['uid']) ?>">
                        <input type="hidden" name="personel_id" id="personel_id">
                        
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold mb-1" style="color: #0f172a !important;">Cari dari Master Pegawai (Lookup)</label>
                                <select class="form-select" id="pegawaiLookupSelect" style="border: 1px solid #cbd5e1; border-radius: 8px; color: #0f172a !important; background-color: #ffffff !important;">
                                    <option value="">Ketik nama atau NIP pegawai...</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold mb-1" style="color: #0f172a !important;">Nama Lengkap & Gelar <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nama" id="personel_nama" required style="border: 1px solid #cbd5e1; border-radius: 8px; color: #0f172a !important; background-color: #ffffff !important;">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold mb-1" style="color: #0f172a !important;">NIP</label>
                                <input type="text" class="form-control" name="nip" id="personel_nip" style="border: 1px solid #cbd5e1; border-radius: 8px; color: #0f172a !important; background-color: #ffffff !important;">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold mb-1" style="color: #0f172a !important;">Pangkat / Golongan</label>
                                <input type="text" class="form-control" name="pangkat_gol" id="personel_pangkat_gol" placeholder="Contoh: Penata Tk. I (III/d)" style="border: 1px solid #cbd5e1; border-radius: 8px; color: #0f172a !important; background-color: #ffffff !important;">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold mb-1" style="color: #0f172a !important;">Jabatan</label>
                                <input type="text" class="form-control" name="jabatan" id="personel_jabatan" placeholder="Contoh: Pranata Komputer Ahli Muda" style="border: 1px solid #cbd5e1; border-radius: 8px; color: #0f172a !important; background-color: #ffffff !important;">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold mb-1" style="color: #0f172a !important;">Peran dalam Tim</label>
                                <input type="text" class="form-control" name="peran" id="personel_peran" placeholder="Contoh: Koordinator / Tim IT / Pengawas" style="border: 1px solid #cbd5e1; border-radius: 8px; color: #0f172a !important; background-color: #ffffff !important;">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold mb-1" style="color: #0f172a !important;">Jumlah Hari Tugas</label>
                                <input type="number" class="form-control" name="jumlah_hari" id="personel_jumlah_hari" value="1" min="1" style="border: 1px solid #cbd5e1; border-radius: 8px; color: #0f172a !important; background-color: #ffffff !important;">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold mb-1" style="color: #0f172a !important;">Uang Harian (Rp)</label>
                                <input type="number" class="form-control" name="uang_harian" id="personel_uang_harian" value="0" style="border: 1px solid #cbd5e1; border-radius: 8px; color: #0f172a !important; background-color: #ffffff !important;">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold mb-1" style="color: #0f172a !important;">Biaya Transport (Rp)</label>
                                <input type="number" class="form-control" name="transport" id="personel_transport" value="0" style="border: 1px solid #cbd5e1; border-radius: 8px; color: #0f172a !important; background-color: #ffffff !important;">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold mb-1" style="color: #0f172a !important;">Nomor Rekening Bank</label>
                                <input type="text" class="form-control" name="no_rekening" id="personel_no_rekening" placeholder="Contoh: Bank Mandiri - 123456789" style="border: 1px solid #cbd5e1; border-radius: 8px; color: #0f172a !important; background-color: #ffffff !important;">
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
            <div class="modal-content border-0 shadow" style="background-color: #ffffff !important; color: #0f172a !important;">
                <div class="modal-header border-bottom py-3 px-4 bg-light">
                    <h5 class="modal-title fw-bold mb-0" id="itemModalTitle" style="color: #0f172a !important;">Tambah Item Jamuan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" style="background-color: #ffffff !important;">
                    <form id="pnbpItemForm">
                        <input type="hidden" name="document_uid" value="<?= esc($doc['uid']) ?>">
                        <input type="hidden" name="item_id" id="item_id">
                        
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold mb-1" style="color: #0f172a !important;">Nama Jamuan / Konsumsi <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="item_name" id="item_name" placeholder="Contoh: Snack Box Pagi / Makan Siang Prasmanan" required style="border: 1px solid #cbd5e1; border-radius: 8px; color: #0f172a !important; background-color: #ffffff !important;">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold mb-1" style="color: #0f172a !important;">Spesifikasi / Menu</label>
                                <textarea class="form-control" name="spesifikasi" id="item_spesifikasi" rows="2" placeholder="Contoh: Nasi, Ayam Bakar, Sayur Asem, Buah, Air Mineral" style="border: 1px solid #cbd5e1; border-radius: 8px; color: #0f172a !important; background-color: #ffffff !important;"></textarea>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold mb-1" style="color: #0f172a !important;">Jumlah (Qty)</label>
                                <input type="number" class="form-control" name="quantity" id="item_quantity" value="1" min="1" style="border: 1px solid #cbd5e1; border-radius: 8px; color: #0f172a !important; background-color: #ffffff !important;">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold mb-1" style="color: #0f172a !important;">Satuan</label>
                                <input type="text" class="form-control" name="satuan" id="item_satuan" value="Box" placeholder="Box / Pax / Kali" style="border: 1px solid #cbd5e1; border-radius: 8px; color: #0f172a !important; background-color: #ffffff !important;">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold mb-1" style="color: #0f172a !important;">Harga Satuan (Rp)</label>
                                <input type="number" class="form-control" name="harga_satuan" id="item_harga_satuan" value="0" style="border: 1px solid #cbd5e1; border-radius: 8px; color: #0f172a !important; background-color: #ffffff !important;">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary fw-bold" id="btnSaveItem">Simpan Item</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Signer Param -->
    <div class="modal fade" id="pnbpSignerModal" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow" style="background-color: #ffffff !important; color: #0f172a !important;">
                <div class="modal-header border-bottom py-3 px-4 bg-light">
                    <h5 class="modal-title fw-bold mb-0" style="color: #0f172a !important;">Ubah Pejabat Penandatangan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" style="background-color: #ffffff !important;">
                    <form id="pnbpSignerForm">
                        <input type="hidden" name="signature_id" id="sig_id">
                        
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold mb-1" style="color: #0f172a !important;">Label Teks (Atas)</label>
                                <input type="text" class="form-control" name="sign_title" id="sig_sign_title" placeholder="Contoh: Yang Memerintahkan, / Mengetahui," style="border: 1px solid #cbd5e1; border-radius: 8px; color: #0f172a !important; background-color: #ffffff !important;">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold mb-1" style="color: #0f172a !important;">Nama Pejabat <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nama" id="sig_nama" required style="border: 1px solid #cbd5e1; border-radius: 8px; color: #0f172a !important; background-color: #ffffff !important;">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold mb-1" style="color: #0f172a !important;">NIP</label>
                                <input type="text" class="form-control" name="nip" id="sig_nip" style="border: 1px solid #cbd5e1; border-radius: 8px; color: #0f172a !important; background-color: #ffffff !important;">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold mb-1" style="color: #0f172a !important;">Pangkat / Golongan</label>
                                <input type="text" class="form-control" name="pangkat_gol" id="sig_pangkat_gol" style="border: 1px solid #cbd5e1; border-radius: 8px; color: #0f172a !important; background-color: #ffffff !important;">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold mb-1" style="color: #0f172a !important;">Jabatan Resmi</label>
                                <input type="text" class="form-control" name="jabatan" id="sig_jabatan" style="border: 1px solid #cbd5e1; border-radius: 8px; color: #0f172a !important; background-color: #ffffff !important;">
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

    <!-- Modal Edit Personel Nominatif -->
    <div class="modal fade" id="pnbpNominatifEditModal" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow" style="background-color: #ffffff !important; color: #0f172a !important;">
                <div class="modal-header border-bottom py-3 px-4 bg-light">
                    <h5 class="modal-title fw-bold mb-0" style="color: #0f172a !important;">
                        <i class="bi bi-person-gear text-primary me-2"></i> Ubah Data Pegawai Nominatif
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" style="background-color: #ffffff !important;">
                    <form id="pnbpNominatifEditForm">
                        <input type="hidden" name="document_uid" value="<?= esc($doc['uid']) ?>">
                        <input type="hidden" name="personel_id" id="nom_edit_id">
                        <input type="hidden" name="status_pegawai" id="nom_edit_status_pegawai">
                        
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold mb-1" style="color: #0f172a !important;">Nama Lengkap & Gelar <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nama" id="nom_edit_nama" required style="border: 1px solid #cbd5e1; border-radius: 8px; color: #0f172a !important; background-color: #ffffff !important;">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold mb-1" style="color: #0f172a !important;">NIP</label>
                                <input type="text" class="form-control" name="nip" id="nom_edit_nip" style="border: 1px solid #cbd5e1; border-radius: 8px; color: #0f172a !important; background-color: #ffffff !important;">
                            </div>

                            <div class="col-6 col-md-4">
                                <label class="form-label fw-bold mb-1" style="color: #0f172a !important;">Golongan</label>
                                <input type="text" class="form-control text-center" name="pangkat_gol" id="nom_edit_gol" placeholder="Contoh: III atau VII" style="border: 1px solid #cbd5e1; border-radius: 8px; color: #0f172a !important; background-color: #ffffff !important;">
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label fw-bold mb-1" style="color: #0f172a !important;">NIK (16 Digit)</label>
                                <input type="text" class="form-control text-center" name="nik" id="nom_edit_nik" placeholder="3277010110810032" style="border: 1px solid #cbd5e1; border-radius: 8px; color: #0f172a !important; background-color: #ffffff !important;">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold mb-1" style="color: #0f172a !important;">Jabatan dalam Tim</label>
                                <input type="text" class="form-control" name="jabatan" id="nom_edit_jabatan" placeholder="Penanggung Jawab / Anggota" style="border: 1px solid #cbd5e1; border-radius: 8px; color: #0f172a !important; background-color: #ffffff !important;">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold mb-1" style="color: #0f172a !important;">Nama Bank</label>
                                <input type="text" class="form-control" name="bank_nama" id="nom_edit_bank_nama" placeholder="Contoh: BRI / Bank Mandiri / BNI" style="border: 1px solid #cbd5e1; border-radius: 8px; color: #0f172a !important; background-color: #ffffff !important;">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold mb-1" style="color: #0f172a !important;">Nomor Rekening</label>
                                <input type="text" class="form-control" name="no_rekening" id="nom_edit_no_rekening" placeholder="Contoh: 99001037262539" style="border: 1px solid #cbd5e1; border-radius: 8px; color: #0f172a !important; background-color: #ffffff !important;">
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold mb-1 text-primary">Jumlah Honor (Rp) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control fw-bold text-end" name="jumlah" id="nom_edit_jumlah" min="0" step="1000" required style="border: 1px solid #cbd5e1; border-radius: 8px; color: #0f172a !important; background-color: #ffffff !important;">
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label fw-bold mb-1" style="color: #0f172a !important;">Pajak PPh 21 (%)</label>
                                <select class="form-select fw-bold text-center" name="pajak_persen" id="nom_edit_pajak_persen" style="border: 1px solid #cbd5e1; border-radius: 8px; color: #0f172a !important; background-color: #ffffff !important;">
                                    <option value="0">0%</option>
                                    <option value="1">1%</option>
                                    <option value="2">2%</option>
                                    <option value="3">3%</option>
                                    <option value="4">4%</option>
                                    <option value="5">5%</option>
                                </select>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label fw-bold mb-1" style="color: #0f172a !important;">Potongan Pajak (Rp)</label>
                                <input type="text" class="form-control text-end bg-light fw-bold text-danger" id="nom_edit_pajak_nominal_view" readonly style="border: 1px solid #cbd5e1; border-radius: 8px;">
                            </div>

                            <div class="col-12">
                                <label class="form-label mb-1 text-success">Jumlah Diterima Bersih (Rp)</label>
                                <input type="text" class="form-control form-control-lg text-end fw-bold bg-light text-success fs-5" id="nom_edit_jumlah_diterima_view" readonly>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary fw-bold px-4" id="btnSaveNominatifEdit">Simpan Perubahan</button>
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
