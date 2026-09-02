<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= esc($doc['title']) ?></title>
    <style>
        body { font-family: 'dejavusanscondensed', 'dejavusans', sans-serif; font-size: 9.5pt; line-height: 1.3; color: #000; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .table-data { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 15px; }
        .table-data th, .table-data td { border: 1px solid #000; padding: 6px 8px; font-size: 9pt; vertical-align: middle; }
        .table-data th { background-color: #f1f5f9; text-align: center; }
        .signature-table { width: 100%; border-collapse: collapse; margin-top: 20px; page-break-inside: avoid; }
        .signature-table td { vertical-align: top; }
    </style>
</head>
<body>

    <?= $this->include('Apps/pages/services/pnbp/templates/_kop_surat') ?>
    <?= $this->include('Apps/pages/services/pnbp/templates/_footer_page') ?>

    <div class="text-center" style="margin-bottom: 15px;">
        <div style="font-size: 11.5pt; font-weight: bold; text-decoration: underline; text-transform: uppercase;">
            DAFTAR HADIR PETUGAS PELAKSANA
        </div>
        <div style="font-size: 9.5pt; margin-top: 3px;">
            Kegiatan: <strong><?= esc($doc['title']) ?></strong> &bull; Titik Lokasi: <strong><?= esc($doc['nama_tilok'] ?: '-') ?></strong>
        </div>
        <div style="font-size: 8.5pt; color: #475569;">
            Waktu Pelaksanaan: <?= \App\Services\PNBP\PNBPHelper::formatPeriode($doc['period_start_date'] ?? null, $doc['period_end_date'] ?? null) ?>
        </div>
    </div>

    <!-- Tabel Daftar Hadir -->
    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th>Nama Pegawai / NIP</th>
                <th>Pangkat / Golongan</th>
                <th>Jabatan / Instansi</th>
                <th>Peran Penugasan</th>
                <th style="width: 140px;">Tanda Tangan</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($personel)): ?>
                <?php foreach ($personel as $i => $p): ?>
                <tr>
                    <td class="text-center fw-bold"><?= $i + 1 ?></td>
                    <td>
                        <strong><?= esc($p['nama']) ?></strong><br>
                        <small>NIP. <?= esc($p['nip'] ?: '-') ?></small>
                    </td>
                    <td><?= esc($p['pangkat_gol'] ?: '-') ?></td>
                    <td><?= esc($p['jabatan'] ?: 'Kanreg III BKN') ?></td>
                    <td class="fw-bold"><?= esc($p['peran'] ?: 'Petugas') ?></td>
                    <td style="padding: 10px; font-size: 8pt; color: #64748b;">
                        <?= $i + 1 ?>. .........................
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center py-3">Belum ada daftar hadir personel.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Blok Tanda Tangan: Koordinator Tilok -->
    <table class="signature-table">
        <tr>
            <td style="width: 55%;"></td>
            <td style="width: 45%; text-align: center;">
                <?= esc($doc['nama_tilok'] ?? 'Bandung') ?>, <?= \App\Services\PNBP\PNBPHelper::formatTanggalIndo($doc['doc_date']) ?><br>
                <?= esc($signRight['sign_title'] ?? 'Mengetahui / Penanggung Jawab Tilok,') ?><br>
                <strong><?= esc($signRight['jabatan'] ?? 'Koordinator Titik Lokasi') ?></strong>
                
                <div style="height: 70px; margin: 4px 0;">
                    <?php if (!empty($signRight) && $signRight['sign_status'] === 'signed' && !empty($signRight['signature_base64'])): ?>
                        <img src="<?= $signRight['signature_base64'] ?>" style="height: 65px; max-width: 150px;">
                    <?php elseif (!empty($signRight)): ?>
                        <barcode code="<?= base_url('pnbp-sign/' . $signRight['sign_token']) ?>" type="QR" class="barcode" size="0.8" error="M" disableborder="1" />
                        <br><span style="font-size: 7pt; color: #64748b;">Scan QR untuk TTD</span>
                    <?php endif; ?>
                </div>

                <strong><u><?= esc($signRight['nama'] ?? 'Koordinator Tilok') ?></u></strong><br>
                NIP. <?= esc($signRight['nip'] ?? '-') ?>
            </td>
        </tr>
    </table>

</body>
</html>
