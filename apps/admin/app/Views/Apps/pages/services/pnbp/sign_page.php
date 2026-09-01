<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Tanda Tangan Digital - SIMOJANG PNBP</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        body {
            background-color: #f1f5f9;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        .sign-card {
            max-width: 540px;
            margin: 0 auto;
            border-radius: 16px;
        }
        .canvas-container {
            position: relative;
            background-color: #ffffff;
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            touch-action: none;
        }
        #signatureCanvas {
            width: 100%;
            height: 220px;
            display: block;
            border-radius: 12px;
            cursor: crosshair;
        }
        .canvas-hint {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #94a3b8;
            font-size: 0.9rem;
            pointer-events: none;
            user-select: none;
        }
    </style>
</head>
<body class="py-4 px-2">

    <div class="container">
        <div class="card sign-card border-0 shadow-sm overflow-hidden">
            <!-- Header Banner -->
            <div class="card-header bg-primary text-white text-center py-4 px-3">
                <i class="bi bi-shield-check fs-1 mb-2 d-block"></i>
                <h5 class="fw-bold mb-1">Tanda Tangan Digital</h5>
                <small class="text-white-50">Sistem Informasi Manajemen Dokumen PNBP CAT</small>
            </div>

            <div class="card-body p-4" id="signFormSection">
                <!-- Document Summary Info -->
                <div class="p-3 bg-light rounded-3 mb-4 border">
                    <span class="badge bg-primary px-2 py-1 mb-2">
                        <?= esc($docTypeLabels[$sig['doc_type']] ?? $sig['doc_type']) ?>
                    </span>
                    <h6 class="fw-bold text-dark mb-1"><?= esc($sig['document_title']) ?></h6>
                    <div class="text-secondary" style="font-size: 0.85rem;">
                        <div><i class="bi bi-hash"></i> No: <strong><?= esc($sig['doc_number'] ?: '-') ?></strong></div>
                        <div><i class="bi bi-geo-alt"></i> Tilok: <strong><?= esc($sig['nama_tilok'] ?: '-') ?></strong></div>
                        <div><i class="bi bi-calendar"></i> Tanggal: <strong><?= \App\Services\PNBP\PNBPHelper::formatTanggalIndo($sig['doc_date']) ?></strong></div>
                    </div>
                </div>

                <!-- Signer Identity -->
                <div class="mb-4">
                    <label class="form-label text-secondary fw-semibold mb-1" style="font-size: 0.85rem;">
                        Identitas Penandatangan (<?= esc($sig['sign_role']) ?>)
                    </label>
                    <div class="p-3 border rounded-3 bg-white">
                        <div class="fw-bold text-dark fs-6"><?= esc($sig['nama']) ?></div>
                        <div class="text-muted" style="font-size: 0.85rem;">NIP. <?= esc($sig['nip'] ?: '-') ?></div>
                        <small class="text-primary fw-semibold"><?= esc($sig['jabatan']) ?></small>
                    </div>
                </div>

                <?php if ($sig['sign_status'] === 'signed'): ?>
                    <!-- Already Signed State -->
                    <div class="alert alert-success text-center py-4 rounded-3 border-0">
                        <i class="bi bi-check-circle-fill text-success fs-1 mb-2 d-block"></i>
                        <h6 class="fw-bold mb-1">Dokumen Sudah Ditandatangani</h6>
                        <small class="text-muted d-block mb-3">
                            Pada <?= \App\Services\PNBP\PNBPHelper::formatTanggalIndo($sig['signed_at']) ?>
                        </small>
                        
                        <?php if (!empty($sig['signature_image_path'])): ?>
                            <div class="p-3 bg-white rounded-3 border d-inline-block">
                                <img src="<?= base_url('writable/' . $sig['signature_image_path']) ?>" alt="Signature" style="max-height: 90px;" class="img-fluid">
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <!-- Signature Pad Canvas Form -->
                    <form id="signatureForm">
                        <input type="hidden" name="token" id="token" value="<?= esc($sig['sign_token']) ?>">
                        <input type="hidden" name="nama" value="<?= esc($sig['nama']) ?>">
                        <input type="hidden" name="nip" value="<?= esc($sig['nip']) ?>">

                        <div class="mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label class="form-label text-secondary fw-semibold mb-0" style="font-size: 0.85rem;">
                                    Goreskan Tanda Tangan <span class="text-danger">*</span>
                                </label>
                                <button type="button" class="btn btn-sm btn-link text-danger p-0 text-decoration-none fw-semibold" id="btnClearCanvas">
                                    <i class="bi bi-arrow-counterclockwise"></i> Bersihkan Canvas
                                </button>
                            </div>

                            <div class="canvas-container" id="canvasContainer">
                                <canvas id="signatureCanvas"></canvas>
                                <div class="canvas-hint" id="canvasHint">
                                    <i class="bi bi-pencil me-1"></i> Tanda tangan di sini dengan jari/stylus
                                </div>
                            </div>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="checkConsent" required>
                            <label class="form-check-label text-secondary" for="checkConsent" style="font-size: 0.8rem; line-height: 1.4;">
                                Saya menyatakan bahwa tanda tangan digital ini dibubuhkan secara sadar dan sah untuk keperluan dokumen kegiatan.
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-3 fw-bold shadow-sm" id="btnSubmitSign" style="border-radius: 10px;" disabled>
                            <i class="bi bi-pen-fill me-1"></i> Bubuhkan Tanda Tangan
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <!-- Success Section (Hidden by default) -->
            <div class="card-body p-4 text-center d-none" id="signSuccessSection">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                <h4 class="fw-bold text-dark mt-2 mb-1">Berhasil Ditandatangani!</h4>
                <p class="text-secondary mb-3" style="font-size: 0.9rem;">
                    Tanda tangan digital Anda telah berhasil direkam dan otomatis terpasang pada dokumen PDF.
                </p>
                <div class="p-3 bg-light rounded-3 text-start mb-4 border">
                    <small class="text-muted d-block">Kode Verifikasi:</small>
                    <code class="fw-bold text-primary fs-6" id="successHash">-</code>
                </div>
                <button type="button" class="btn btn-outline-primary w-100 py-2 fw-semibold" onclick="window.location.reload();">
                    Selesai / Muat Ulang
                </button>
            </div>

            <div class="card-footer bg-light text-center py-3 border-top">
                <small class="text-muted">&copy; <?= date('Y') ?> SIMOJANG &bull; Kantor Regional III BKN Bandung</small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="<?= asset_url('apps/assets/js/custom/pages/services/pnbp/pnbp-sign.js?v=' . time()) ?>"></script>
</body>
</html>
