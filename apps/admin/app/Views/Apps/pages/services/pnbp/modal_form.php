<!-- Modal Tambah / Edit Dokumen PNBP -->
<div class="modal fade" id="pnbpDocModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="pnbpDocModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header border-bottom py-3 px-4" style="background-color: #f8fafc;">
                <h5 class="modal-title fw-bold text-dark" id="pnbpDocModalLabel">
                    <i class="bi bi-file-earmark-plus-fill text-primary me-2"></i> Buat Dokumen Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="pnbpDocForm" autocomplete="off">
                    <input type="hidden" name="key" id="doc_key">
                    
                    <div class="row g-3">
                        <!-- Jenis Dokumen -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-secondary mb-1">
                                Jenis Format Dokumen <span class="text-danger">*</span>
                            </label>
                            <select class="form-select fw-bold" name="doc_type" id="doc_type" required style="height: 44px; border-radius: 8px;">
                                <option value="">-- Pilih Format Dokumen --</option>
                                <optgroup label="1. Dokumen Kepegawaian & Tim">
                                    <option value="sp">1. Surat Perintah (SP)</option>
                                    <option value="st">2. Surat Tugas (ST)</option>
                                    <option value="nominatif">3. Daftar Nominatif</option>
                                    <option value="kwitansi">4. Kwitansi Perjalanan Dinas</option>
                                    <option value="hadir">5. Daftar Hadir Petugas</option>
                                </optgroup>
                                <optgroup label="2. Dokumen Jamuan & Konsumsi">
                                    <option value="kwitansi_jamuan">6. Kwitansi Jamuan</option>
                                    <option value="surat_jalan">7. Surat Jalan Jamuan</option>
                                    <option value="faktur">8. Faktur Jamuan</option>
                                    <option value="hadir_jamuan">9. Daftar Hadir Jamuan</option>
                                </optgroup>
                            </select>
                        </div>

                        <!-- Tanggal Dokumen -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-secondary mb-1">
                                Tanggal Dokumen <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control" name="doc_date" id="doc_date" value="<?= date('Y-m-d') ?>" required style="height: 44px; border-radius: 8px;">
                        </div>

                        <!-- Event Seleksi CAT (Existing) -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-secondary mb-1">
                                Event / Seleksi CAT (Existing) <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" name="seleksi_id" id="doc_seleksi_id" style="height: 44px; border-radius: 8px;">
                                <option value="">-- Pilih Event Seleksi --</option>
                                <?php if (!empty($seleksiOptions)): ?>
                                    <?php foreach ($seleksiOptions as $sel): ?>
                                        <option value="<?= esc($sel['id']) ?>">
                                            <?= esc($sel['nama_seleksi']) ?> (<?= esc($sel['periode']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Instansi (Existing data_instansi) -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-secondary mb-1">
                                Instansi Terkait
                            </label>
                            <select class="form-select" name="instansi_id" id="doc_instansi_id" style="height: 44px; border-radius: 8px;">
                                <option value="">-- Pilih Instansi --</option>
                                <?php if (!empty($instansiOptions)): ?>
                                    <?php foreach ($instansiOptions as $ins): ?>
                                        <option value="<?= esc($ins['kodeins']) ?>">
                                            <?= esc($ins['nama']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Titik Lokasi CAT (Existing - Filtered by Seleksi) -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-secondary mb-1">
                                Titik Lokasi (Tilok)
                            </label>
                            <select class="form-select" name="tilok_id" id="doc_tilok_id" style="height: 44px; border-radius: 8px;">
                                <option value="">-- Pilih Event Dulu --</option>
                            </select>
                        </div>

                        <!-- Nomor Dokumen -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-secondary mb-1">
                                Nomor Dokumen / Surat
                            </label>
                            <input type="text" class="form-control" name="doc_number" id="doc_number" placeholder="Contoh: 142/SP/BKN/KANREG.VII/2026" style="height: 44px; border-radius: 8px;">
                        </div>

                        <!-- Mata Anggaran Kegiatan (MAK) / DIPA -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-secondary mb-1">
                                Mata Anggaran (Akun MAK / DIPA)
                            </label>
                            <input type="text" class="form-control" name="mak" id="doc_mak" placeholder="Contoh: 030.01.WA.6253.EAA.001.051.A.524111" style="height: 44px; border-radius: 8px;">
                        </div>

                        <!-- Judul / Perihal Dokumen -->
                        <div class="col-12">
                            <label class="form-label fw-semibold text-secondary mb-1">
                                Perihal / Judul Kegiatan <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="title" id="doc_title" placeholder="Contoh: Pelaksanaan Fasilitasi CAT SKD CPNS 2026" required style="height: 44px; border-radius: 8px;">
                        </div>

                        <!-- Form Tambahan untuk Jamuan (Katering) -->
                        <div class="col-12 jamuan-fields d-none">
                            <div class="p-3 bg-light rounded-3 border">
                                <h6 class="fw-bold text-primary mb-2" style="font-size: 0.9rem;">
                                    <i class="bi bi-shop me-1"></i> Data Rekanan Penyedia Jamuan (Katering)
                                </h6>
                                <div class="row g-2">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label mb-1" style="font-size: 0.82rem;">Nama Rekanan / Rumah Makan</label>
                                        <input type="text" class="form-control form-control-sm" name="vendor_name" id="doc_vendor_name" placeholder="Contoh: CV. Berkah Katering Nusantara">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label mb-1" style="font-size: 0.82rem;">NPWP Rekanan</label>
                                        <input type="text" class="form-control form-control-sm" name="vendor_npwp" id="doc_vendor_npwp" placeholder="Contoh: 01.234.567.8-012.000">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Catatan Tambahan -->
                        <div class="col-12">
                            <label class="form-label fw-semibold text-secondary mb-1">
                                Catatan / Keterangan Tambahan
                            </label>
                            <textarea class="form-control" name="notes" id="doc_notes" rows="2" placeholder="Catatan opsional untuk lampiran atau pengesahan..." style="border-radius: 8px;"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top py-3 px-4" style="background-color: #f8fafc;">
                <button type="button" class="btn btn-outline-secondary px-4 fw-semibold" data-bs-dismiss="modal" style="height: 42px; border-radius: 8px;">
                    Batal
                </button>
                <button type="button" class="btn btn-primary px-4 fw-bold" id="btnSaveDocument" style="height: 42px; border-radius: 8px;">
                    <i class="bi bi-save me-1"></i> Simpan Dokumen
                </button>
            </div>
        </div>
    </div>
</div>
