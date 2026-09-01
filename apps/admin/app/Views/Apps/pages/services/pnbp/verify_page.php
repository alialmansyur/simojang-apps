<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Keaslian Dokumen - SIMOJANG PNBP</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        .verify-card { max-width: 600px; margin: 2rem auto; border-radius: 16px; }
    </style>
</head>
<body class="py-4 px-2">
    <div class="container">
        <div class="card verify-card border-0 shadow-sm overflow-hidden">
            <div class="card-header bg-success text-white text-center py-4">
                <i class="bi bi-patch-check-fill" style="font-size: 3.5rem;"></i>
                <h4 class="fw-bold mt-2 mb-0">Dokumen Terverifikasi Sah</h4>
                <small class="text-white-50">Sistem Informasi Manajemen Dokumen PNBP CAT &bull; BKN</small>
            </div>
            
            <div class="card-body p-4">
                <h6 class="fw-bold text-secondary mb-3">INFORMASI DOKUMEN</h6>
                
                <table class="table table-bordered table-sm mb-4">
                    <tr>
                        <td class="bg-light fw-bold" style="width: 35%;">Jenis Dokumen</td>
                        <td><?= esc($docTypeLabels[$sig['doc_type']] ?? $sig['doc_type']) ?></td>
                    </tr>
                    <tr>
                        <td class="bg-light fw-bold">Nomor Dokumen</td>
                        <td><?= esc($sig['doc_number'] ?: '-') ?></td>
                    </tr>
                    <tr>
                        <td class="bg-light fw-bold">Perihal / Judul</td>
                        <td><?= esc($sig['document_title']) ?></td>
                    </tr>
                    <tr>
                        <td class="bg-light fw-bold">Tanggal Dokumen</td>
                        <td><?= \App\Services\PNBP\PNBPHelper::formatTanggalIndo($sig['doc_date']) ?></td>
                    </tr>
                    <tr>
                        <td class="bg-light fw-bold">Event & Titik Lokasi</td>
                        <td><?= esc($sig['nama_seleksi'] ?: '-') ?> &bull; <?= esc($sig['nama_tilok'] ?: '-') ?></td>
                    </tr>
                </table>

                <h6 class="fw-bold text-secondary mb-3">INFORMASI PENANDATANGAN</h6>
                <div class="p-3 bg-light rounded-3 border mb-3">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <div class="fw-bold text-dark fs-6"><?= esc($sig['nama']) ?></div>
                            <small class="text-muted">NIP. <?= esc($sig['nip'] ?: '-') ?></small><br>
                            <small class="text-primary fw-semibold"><?= esc($sig['jabatan']) ?></small>
                            <div class="text-secondary mt-2" style="font-size: 0.8rem;">
                                Ditandatangani pada: <strong><?= \App\Services\PNBP\PNBPHelper::formatTanggalIndo($sig['signed_at']) ?></strong>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <?php if ($sig['sign_status'] === 'signed' && !empty($sig['signature_image_path'])): ?>
                                <img src="<?= base_url('writable/' . $sig['signature_image_path']) ?>" alt="TTD" style="max-height: 65px;" class="img-fluid bg-white p-1 rounded border">
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Belum TTD</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php if (!empty($sig['verification_hash'])): ?>
                <div class="p-2 bg-white rounded border">
                    <small class="text-muted d-block" style="font-size: 0.75rem;">Digital Signature Hash:</small>
                    <code style="font-size: 0.78rem; word-break: break-all;"><?= esc($sig['verification_hash']) ?></code>
                </div>
                <?php endif; ?>
            </div>

            <div class="card-footer bg-light text-center py-3">
                <small class="text-muted">&copy; <?= date('Y') ?> Kantor Regional III Badan Kepegawaian Negara</small>
            </div>
        </div>
    </div>
</body>
</html>
