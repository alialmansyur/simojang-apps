<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= esc($doc['title']) ?></title>
    <style>
        body { font-family: 'Arial', sans-serif; font-size: 10pt; line-height: 1.4; color: #000; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .text-justify { text-align: justify; }
        .fw-bold { font-weight: bold; }
        .table-data { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 15px; }
        .table-data th, .table-data td { border: 1px solid #000; padding: 5px 8px; font-size: 9pt; vertical-align: top; }
        .table-data th { background-color: #f1f5f9; text-align: center; }
        .signature-table { width: 100%; border-collapse: collapse; margin-top: 25px; page-break-inside: avoid; }
        .signature-table td { vertical-align: top; }
    </style>
</head>
<body>

    <?= $this->include('Apps/pages/services/pnbp/templates/_kop_surat') ?>
    <?= $this->include('Apps/pages/services/pnbp/templates/_footer_page') ?>

    <div class="text-center" style="margin-bottom: 15px;">
        <div style="font-size: 12pt; font-weight: bold; text-decoration: underline; text-transform: uppercase;">
            SURAT TUGAS
        </div>
        <div style="font-size: 10pt; margin-top: 2px;">
            NOMOR: <?= esc($doc['doc_number'] ?: '.../ST/BKN/KANREG.VII/' . date('Y')) ?>
        </div>
    </div>

    <p class="text-justify" style="margin-bottom: 12px;">
        Yang bertanda tangan di bawah ini, Kepala Kantor Regional III Badan Kepegawaian Negara, dengan ini memberikan tugas kedinasan kepada:
    </p>

    <!-- Tabel Daftar Pegawai -->
    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th>Nama / NIP</th>
                <th>Pangkat / Gol. Ruang</th>
                <th>Jabatan</th>
                <th>Tugas / Peran</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($personel)): ?>
                <?php foreach ($personel as $i => $p): ?>
                <tr>
                    <td class="text-center"><?= $i + 1 ?></td>
                    <td>
                        <strong><?= esc($p['nama']) ?></strong><br>
                        <small>NIP. <?= esc($p['nip'] ?: '-') ?></small>
                    </td>
                    <td><?= esc($p['pangkat_gol'] ?: '-') ?></td>
                    <td><?= esc($p['jabatan'] ?: '-') ?></td>
                    <td class="fw-bold"><?= esc($p['peran'] ?: 'Pelaksana Tugas') ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center">Belum ada personel yang ditugaskan.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <table style="width: 100%; border: none; margin-top: 8px; font-size: 10pt;">
        <tr>
            <td style="width: 20%; vertical-align: top;" class="fw-bold">Untuk Keperluan</td>
            <td style="width: 3%; vertical-align: top;">:</td>
            <td style="vertical-align: top;" class="text-justify">
                Melaksanakan tugas pengawasan dan fasilitasi teknis dalam rangka <?= esc($doc['title']) ?> pada <?= esc($doc['nama_seleksi'] ?? 'Kegiatan CAT') ?>.
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;" class="fw-bold">Tempat Tujuan</td>
            <td style="vertical-align: top;">:</td>
            <td style="vertical-align: top;">
                <?= esc($doc['nama_tilok'] ?? 'Titik Lokasi Pelaksanaan') ?>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;" class="fw-bold">Waktu Pelaksanaan</td>
            <td style="vertical-align: top;">:</td>
            <td style="vertical-align: top;">
                <?= \App\Services\PNBP\PNBPHelper::formatPeriode($doc['period_start_date'] ?? null, $doc['period_end_date'] ?? null) ?>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;" class="fw-bold">Pembebanan Anggaran</td>
            <td style="vertical-align: top;">:</td>
            <td style="vertical-align: top;">
                DIPA Kantor Regional III BKN Tahun Anggaran <?= date('Y') ?> (Akun: <?= esc($meta['mak'] ?? '-') ?>).
            </td>
        </tr>
    </table>

    <p style="margin-top: 12px;">
        Demikian Surat Tugas ini dibuat untuk dapat dilaksanakan dengan penuh tanggung jawab.
    </p>

    <!-- Blok Tanda Tangan -->
    <table class="signature-table">
        <tr>
            <td style="width: 50%;"></td>
            <td style="width: 50%; text-align: center;">
                Bandung, <?= \App\Services\PNBP\PNBPHelper::formatTanggalIndo($doc['doc_date']) ?><br>
                <strong><?= esc($signRight['jabatan'] ?? 'Kepala Kantor Regional III BKN') ?></strong>
                
                <div style="height: 75px; margin: 5px 0;">
                    <?php if (!empty($signRight) && $signRight['sign_status'] === 'signed' && !empty($signRight['signature_base64'])): ?>
                        <img src="<?= $signRight['signature_base64'] ?>" style="height: 70px; max-width: 160px;">
                    <?php elseif (!empty($signRight)): ?>
                        <barcode code="<?= base_url('pnbp-sign/' . $signRight['sign_token']) ?>" type="QR" class="barcode" size="0.9" error="M" disableborder="1" />
                        <br><span style="font-size: 7.5pt; color: #64748b;">Scan QR untuk TTD Digital</span>
                    <?php endif; ?>
                </div>

                <strong><u><?= esc($signRight['nama'] ?? 'Dr. H. Heri Purwanto, S.E., M.M.') ?></u></strong><br>
                NIP. <?= esc($signRight['nip'] ?? '-') ?>
            </td>
        </tr>
    </table>

</body>
</html>
