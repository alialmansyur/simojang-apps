<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= esc($doc['title']) ?></title>
    <style>
        body { font-family: 'Arial', sans-serif; font-size: 9pt; line-height: 1.3; color: #000; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .table-data { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 15px; }
        .table-data th, .table-data td { border: 1px solid #000; padding: 5px 7px; font-size: 8.5pt; vertical-align: middle; }
        .table-data th { background-color: #f1f5f9; text-align: center; }
        .signature-table { width: 100%; border-collapse: collapse; margin-top: 20px; page-break-inside: avoid; }
        .signature-table td { vertical-align: top; }
    </style>
</head>
<body>

    <?= $this->include('Apps/pages/services/pnbp/templates/_kop_surat') ?>
    <?= $this->include('Apps/pages/services/pnbp/templates/_footer_page') ?>

    <div class="text-center" style="margin-bottom: 12px;">
        <div style="font-size: 11pt; font-weight: bold; text-decoration: underline; text-transform: uppercase;">
            DAFTAR HADIR / PENERIMAAN JAMUAN KONSUMSI
        </div>
        <div style="font-size: 9pt; margin-top: 2px;">
            Kegiatan: <strong><?= esc($doc['title']) ?></strong> &bull; Titik Lokasi: <strong><?= esc($doc['nama_tilok'] ?: '-') ?></strong>
        </div>
        <div style="font-size: 8.5pt; color: #475569;">
            Waktu Pelaksanaan: <?= \App\Services\PNBP\PNBPHelper::formatTanggalIndo($doc['doc_date']) ?>
        </div>
    </div>

    <!-- Tabel Daftar Penerima Konsumsi -->
    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th>Nama Penerima / Pegawai</th>
                <th>NIP / Identitas</th>
                <th>Instansi / Unit Kerja</th>
                <th>Jenis Konsumsi</th>
                <th style="width: 130px;">Tanda Tangan</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                $listData = !empty($personel) ? $personel : (!empty($attendees) ? $attendees : []);
            ?>
            <?php if (!empty($listData)): ?>
                <?php foreach ($listData as $i => $row): ?>
                <tr>
                    <td class="text-center fw-bold"><?= $i + 1 ?></td>
                    <td><strong><?= esc($row['nama']) ?></strong></td>
                    <td><?= esc($row['nip'] ?: '-') ?></td>
                    <td><?= esc($row['instansi'] ?? $row['jabatan'] ?? 'Panitia CAT') ?></td>
                    <td class="text-center">Snack & Makan Siang</td>
                    <td style="padding: 8px; font-size: 7.5pt; color: #64748b;">
                        <?= $i + 1 ?>. .........................
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center py-3">Belum ada daftar penerima jamuan.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- 2 Kolom TTD: Pengelola Konsumsi & Koordinator Tilok -->
    <table class="signature-table">
        <tr>
            <td style="width: 50%; text-align: center;">
                <?= esc($signLeft['sign_title'] ?? 'Petugas Konsumsi / Rekanan,') ?><br>
                <strong><?= esc($meta['vendor_name'] ?: 'Penyedia Katering') ?></strong>
                
                <div style="height: 65px; margin: 4px 0;">
                    <?php if (!empty($signLeft) && $signLeft['sign_status'] === 'signed' && !empty($signLeft['signature_base64'])): ?>
                        <img src="<?= $signLeft['signature_base64'] ?>" style="height: 60px; max-width: 140px;">
                    <?php elseif (!empty($signLeft)): ?>
                        <barcode code="<?= base_url('pnbp-sign/' . $signLeft['sign_token']) ?>" type="QR" class="barcode" size="0.75" error="M" disableborder="1" />
                        <br><span style="font-size: 6.5pt; color: #64748b;">Scan QR TTD</span>
                    <?php endif; ?>
                </div>

                <strong><u><?= esc($signLeft['nama'] ?? 'Petugas Konsumsi') ?></u></strong>
            </td>

            <td style="width: 50%; text-align: center;">
                <?= esc($doc['nama_tilok'] ?? 'Bandung') ?>, <?= \App\Services\PNBP\PNBPHelper::formatTanggalIndo($doc['doc_date']) ?><br>
                <?= esc($signRight['sign_title'] ?? 'Mengetahui / Penanggung Jawab Tilok,') ?><br>
                <strong><?= esc($signRight['jabatan'] ?? 'Koordinator Titik Lokasi') ?></strong>
                
                <div style="height: 65px; margin: 4px 0;">
                    <?php if (!empty($signRight) && $signRight['sign_status'] === 'signed' && !empty($signRight['signature_base64'])): ?>
                        <img src="<?= $signRight['signature_base64'] ?>" style="height: 60px; max-width: 140px;">
                    <?php elseif (!empty($signRight)): ?>
                        <barcode code="<?= base_url('pnbp-sign/' . $signRight['sign_token']) ?>" type="QR" class="barcode" size="0.75" error="M" disableborder="1" />
                        <br><span style="font-size: 6.5pt; color: #64748b;">Scan QR TTD</span>
                    <?php endif; ?>
                </div>

                <strong><u><?= esc($signRight['nama'] ?? 'Koordinator Tilok') ?></u></strong><br>
                <small>NIP. <?= esc($signRight['nip'] ?? '-') ?></small>
            </td>
        </tr>
    </table>

</body>
</html>
