<!-- Modal Tambah / Edit Dokumen PNBP -->
<div class="modal fade" id="pnbpDocModal" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="pnbpDocModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; background-color: #ffffff !important; color: #0f172a !important;">
            <div class="modal-header border-bottom py-3 px-4 d-flex align-items-center justify-content-between" style="background-color: #f8fafc;">
                <h5 class="modal-title fw-bold mb-0" id="pnbpDocModalLabel" style="color: #0f172a !important;">
                    <i class="bi bi-file-earmark-plus-fill text-primary me-2"></i> Buat Dokumen Baru
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary fw-semibold" id="btnQuickSampleDoc" title="Isi form otomatis dengan data contoh untuk pengujian cepat">
                        Isi Contoh Cepat
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-4" style="background-color: #ffffff !important;">
                <form id="pnbpDocForm" autocomplete="off">
                    <input type="hidden" name="key" id="doc_key">
                    
                    <div class="row g-3">
                        <!-- Jenis Dokumen -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold mb-1" style="color: #0f172a !important;">
                                Jenis Format Dokumen <span class="text-danger">*</span>
                            </label>
                            <select class="form-select select2-pnbp" name="doc_type" id="doc_type" required>
                                <option value="">-- Pilih Format Dokumen --</option>
                                <?php 
                                $activeList = !empty($activeDocTypes) ? $activeDocTypes : (!empty($docTypeDetails) ? $docTypeDetails : []);
                                $curType = $currentDocType ?? 'nominatif';
                                if (!empty($activeList)):
                                    foreach ($activeList as $optKey => $optDoc): 
                                        $val = is_array($optDoc) ? ($optDoc['doc_type'] ?? $optKey) : $optKey;
                                        $num = is_array($optDoc) ? ($optDoc['number'] ?? '') : '';
                                        $lbl = is_array($optDoc) ? ($optDoc['title'] ?? $optDoc) : $optDoc;
                                        $prefix = $num ? "{$num}. " : '';
                                        $isOptActive = is_array($optDoc) ? (!empty($optDoc['is_status']) && (int)$optDoc['is_status'] === 1) : ($val === 'nominatif');
                                        $disabledAttr = !$isOptActive ? 'disabled' : '';
                                        $suffix = $isOptActive ? ' (Aktif - Siap Digunakan)' : ' (Belum Aktif)';
                                        $selectedAttr = ($val === $curType && $isOptActive) ? 'selected' : '';
                                ?>
                                    <option value="<?= esc($val) ?>" <?= $disabledAttr ?> <?= $selectedAttr ?>><?= esc($prefix . $lbl . $suffix) ?></option>
                                <?php 
                                    endforeach;
                                endif; 
                                ?>
                            </select>
                        </div>

                        <!-- Tanggal Dokumen -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold mb-1" style="color: #0f172a !important;">
                                Tanggal Dokumen <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control fw-medium" name="doc_date" id="doc_date" value="<?= date('Y-m-d') ?>" required style="height: 44px; border: 1px solid #cbd5e1; border-radius: 8px; color: #0f172a !important; background-color: #ffffff !important;">
                        </div>

                        <!-- Instansi (Existing data_instansi) -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold mb-1" style="color: #0f172a !important;">
                                Instansi Terkait
                            </label>
                            <select class="form-select select2-pnbp" name="instansi_id" id="doc_instansi_id">
                                <option value="">-- Pilih Instansi Terkait --</option>
                                <?php if (!empty($instansiOptions)): ?>
                                    <?php foreach ($instansiOptions as $ins): ?>
                                        <option value="<?= esc($ins['kodeins']) ?>" data-nama="<?= esc($ins['nama']) ?>">
                                             <?= esc($ins['nama']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Event Seleksi CAT (Existing - Opsional) -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold mb-1" style="color: #0f172a !important;">
                                Event / Seleksi CAT <small class="text-muted fw-normal">(Opsional)</small>
                            </label>
                            <select class="form-select select2-pnbp" name="seleksi_id" id="doc_seleksi_id">
                                <option value="">-- Tanpa Event / Mandiri --</option>
                                <?php if (!empty($seleksiOptions)): ?>
                                    <?php foreach ($seleksiOptions as $sel): ?>
                                        <option value="<?= esc($sel['id']) ?>" data-nama="<?= esc($sel['nama_seleksi']) ?>">
                                            <?= esc($sel['nama_seleksi']) ?> (<?= esc($sel['periode']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Titik Lokasi CAT (Opsional) -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold mb-1" style="color: #0f172a !important;">
                                Titik Lokasi (Tilok) <small class="text-muted fw-normal">(Opsional)</small>
                            </label>
                            <select class="form-select select2-pnbp" name="tilok_id" id="doc_tilok_id">
                                <option value="">-- Pilih Event Dulu / Opsional --</option>
                            </select>
                        </div>

                        <!-- Nomor Dokumen -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold mb-1" style="color: #0f172a !important;">
                                Nomor Dokumen / Surat
                            </label>
                            <input type="text" class="form-control" name="doc_number" id="doc_number" placeholder="Contoh: 142/NOM/BKN/KANREG.III/<?= date('Y') ?>" style="height: 44px; border: 1px solid #cbd5e1; border-radius: 8px; color: #0f172a !important; background-color: #ffffff !important;">
                        </div>

                        <!-- Mata Anggaran Kegiatan (MAK) / DIPA -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold mb-1" style="color: #0f172a !important;">
                                Mata Anggaran (Akun MAK / DIPA)
                            </label>
                            <input type="text" class="form-control" name="mak" id="doc_mak" value="030.01.WA.6253.EAA.001.051.A.524111" placeholder="Contoh: 030.01.WA.6253.EAA.001.051.A.524111" style="height: 44px; border: 1px solid #cbd5e1; border-radius: 8px; color: #0f172a !important; background-color: #ffffff !important;">
                        </div>

                        <!-- Judul / Perihal Dokumen -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold mb-1" style="color: #0f172a !important;">
                                Perihal / Judul Kegiatan <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control fw-medium" name="title" id="doc_title" placeholder="Contoh: Fasilitasi Seleksi Pengembangan Karier dengan metode CAT BKN di Lingkungan Instansi..." required style="height: 44px; border: 1px solid #cbd5e1; border-radius: 8px; color: #0f172a !important; background-color: #ffffff !important;">
                        </div>

                        <!-- Form Tambahan untuk Jamuan (Katering) -->
                        <div class="col-12 jamuan-fields d-none">
                            <div class="p-3 bg-light rounded-3 border" style="border-color: #cbd5e1 !important;">
                                <h6 class="fw-bold text-primary mb-2" style="font-size: 0.9rem;">
                                    <i class="bi bi-shop me-1"></i> Data Rekanan Penyedia Jamuan (Katering)
                                </h6>
                                <div class="row g-2">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-bold mb-1" style="font-size: 0.82rem; color: #0f172a !important;">Nama Rekanan / Rumah Makan</label>
                                        <input type="text" class="form-control form-control-sm" name="vendor_name" id="doc_vendor_name" placeholder="Contoh: CV. Berkah Katering Nusantara" style="color: #0f172a !important;">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-bold mb-1" style="font-size: 0.82rem; color: #0f172a !important;">NPWP Rekanan</label>
                                        <input type="text" class="form-control form-control-sm" name="vendor_npwp" id="doc_vendor_npwp" placeholder="Contoh: 01.234.567.8-012.000" style="color: #0f172a !important;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Catatan Tambahan -->
                        <div class="col-12">
                            <label class="form-label fw-bold mb-1" style="color: #0f172a !important;">
                                Catatan / Keterangan Tambahan
                            </label>
                            <textarea class="form-control" name="notes" id="doc_notes" rows="2" placeholder="Catatan opsional untuk lampiran atau pengesahan..." style="border: 1px solid #cbd5e1; border-radius: 8px; color: #0f172a !important; background-color: #ffffff !important;"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top py-3 px-4 d-flex align-items-center justify-content-between" style="background-color: #f8fafc;">
                <small class="d-none d-sm-inline" style="color: #64748b !important;">
                    <i class="bi bi-info-circle me-1"></i> Format aktif saat ini: <strong style="color: #0f172a !important;">Daftar Nominatif</strong>
                </small>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center justify-content-center px-4 fw-semibold" data-bs-dismiss="modal" style="height: 42px; border-radius: 8px;">
                        Batal
                    </button>
                    <button type="button" class="btn btn-primary d-inline-flex align-items-center justify-content-center px-4 fw-bold" id="btnSaveDocument" style="height: 42px; border-radius: 8px;">
                        Simpan Dokumen
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
