<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= esc($doc['title']) ?></title>
    <style>
        body { font-family: 'dejavusanscondensed', 'dejavusans', sans-serif; font-size: 9pt; line-height: 1.3; color: #000; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .table-data { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 12px; }
        .table-data th, .table-data td { border: 1px solid #000; padding: 4px 6px; font-size: 8.5pt; }
        .table-data th { background-color: #f1f5f9; text-align: center; vertical-align: middle; }
        .signature-table { width: 100%; border-collapse: collapse; margin-top: 15px; page-break-inside: avoid; }
        .signature-table td { vertical-align: top; }
    </style>
</head>
<body>

    <?= $this->include('Apps/pages/services/pnbp/templates/_kop_surat') ?>
    <?= $this->include('Apps/pages/services/pnbp/templates/_footer_page') ?>

    <div class="text-center" style="margin-bottom: 12px;">
        <div style="font-size: 11pt; font-weight: bold; text-decoration: underline; text-transform: uppercase;">
            DAFTAR NOMINATIF PERJALANAN DINAS / PENUGASAN PETUGAS
        </div>
        <div style="font-size: 9pt; margin-top: 2px;">
            Kegiatan: <strong><?= esc($doc['title']) ?></strong> &bull; Lokasi: <strong><?= esc($doc['nama_tilok'] ?: '-') ?></strong>
        </div>
        <div style="font-size: 8.5pt; color: #475569;">
            Periode: <?= \App\Services\PNBP\PNBPHelper::formatPeriode($doc['period_start_date'] ?? null, $doc['period_end_date'] ?? null) ?> &bull; MAK: <?= esc($meta['mak'] ?? '-') ?>
        </div>
    </div>

    <!-- Tabel Daftar Nominatif -->
    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th>Nama Pegawai / NIP</th>
                <th style="width: 90px;">Gol. / Pangkat</th>
                <th>Jabatan / Peran</th>
                <th style="width: 45px;">Hari</th>
                <th style="width: 85px;">Uang Harian</th>
                <th style="width: 85px;">Transport</th>
                <th style="width: 95px;">Jumlah (Rp)</th>
                <th style="width: 100px;">No. Rekening</th>
                <th style="width: 80px;">Tanda Tangan</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                $totalUH = 0;
                $totalTR = 0;
                $totalGrand = 0;
            ?>
            <?php if (!empty($personel)): ?>
                <?php foreach ($personel as $i => $p): 
                    $subUH = (float) $p['uang_harian'] * (int) $p['jumlah_hari'];
                    $subTR = (float) $p['transport'];
                    $subTotal = (float) $p['total_biaya'];
                    $totalUH += $subUH;
                    $totalTR += $subTR;
                    $totalGrand += $subTotal;
                ?>
                <tr>
                    <td class="text-center"><?= $i + 1 ?></td>
                    <td>
                        <strong><?= esc($p['nama']) ?></strong><br>
                        <small>NIP. <?= esc($p['nip'] ?: '-') ?></small>
                    </td>
                    <td class="text-center"><?= esc($p['pangkat_gol'] ?: '-') ?></td>
                    <td>
                        <?= esc($p['jabatan'] ?: '-') ?><br>
                        <small class="fw-bold text-primary">(<?= esc($p['peran'] ?: 'Petugas') ?>)</small>
                    </td>
                    <td class="text-center"><?= (int) $p['jumlah_hari'] ?></td>
                    <td class="text-end"><?= \App\Services\PNBP\PNBPHelper::formatRupiah($p['uang_harian'], false) ?></td>
                    <td class="text-end"><?= \App\Services\PNBP\PNBPHelper::formatRupiah($p['transport'], false) ?></td>
                    <td class="text-end fw-bold"><?= \App\Services\PNBP\PNBPHelper::formatRupiah($p['total_biaya'], false) ?></td>
                    <td><small><?= esc($p['no_rekening'] ?: '-') ?></small></td>
                    <td class="text-center" style="font-size: 7pt; color: #94a3b8;">
                        <?= $i + 1 ?>. ..........
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="10" class="text-center py-3">Belum ada rincian data nominatif personel.</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr style="background-color: #f8fafc; font-weight: bold;">
                <td colspan="4" class="text-center">JUMLAH TOTAL</td>
                <td class="text-center">-</td>
                <td class="text-end"><?= \App\Services\PNBP\PNBPHelper::formatRupiah($totalUH, false) ?></td>
                <td class="text-end"><?= \App\Services\PNBP\PNBPHelper::formatRupiah($totalTR, false) ?></td>
                <td class="text-end"><?= \App\Services\PNBP\PNBPHelper::formatRupiah($totalGrand, false) ?></td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    <div style="font-size: 8.5pt; margin-bottom: 15px;">
        Terbilang: <strong><i><?= \App\Services\PNBP\PNBPHelper::terbilang($totalGrand) ?></i></strong>
    </div>

    <!-- Blok 2 TTD: PPK (Kiri) & Bendahara (Kanan) -->
    <table class="signature-table">
        <tr>
            <td style="width: 50%; text-align: center;">
                <?= esc($signLeft['sign_title'] ?? 'Mengetahui / Menyetujui,') ?><br>
                <strong><?= esc($signLeft['jabatan'] ?? 'Pejabat Pembuat Komitmen') ?></strong>
                
                <div style="height: 65px; margin: 3px 0;">
                    <?php if (!empty($signLeft) && $signLeft['sign_status'] === 'signed' && !empty($signLeft['signature_base64'])): ?>
                        <img src="<?= $signLeft['signature_base64'] ?>" style="height: 65px; max-width: 150px;">
                    <?php elseif (!empty($signLeft)): ?>
                        <barcode code="<?= base_url('pnbp-sign/' . $signLeft['sign_token']) ?>" type="QR" class="barcode" size="0.8" error="M" disableborder="1" />
                        <br><span style="font-size: 7pt; color: #64748b;">Scan QR untuk TTD Digital</span>
                    <?php endif; ?>
                </div>
                <strong><u><?= esc($signLeft['nama'] ?? '-') ?></u></strong><br>
                NIP. <?= esc($signLeft['nip'] ?? '-') ?>
            </td>

            <td style="width: 50%; text-align: center;">
                Mengetahui,<br>
                <strong><?= esc($signRight['jabatan'] ?? 'Kepala Kantor Regional III BKN') ?></strong>
                <div style="height: 65px; margin: 3px 0;">
                    <?php if (!empty($signRight) && $signRight['sign_status'] === 'signed' && !empty($signRight['signature_base64'])): ?>
                        <img src="<?= $signRight['signature_base64'] ?>" style="height: 65px; max-width: 150px;">
                    <?php elseif (!empty($signRight)): ?>
                        <barcode code="<?= base_url('pnbp-sign/' . $signRight['sign_token']) ?>" type="QR" class="barcode" size="0.8" error="M" disableborder="1" />
                        <br><span style="font-size: 7pt; color: #64748b;">Scan QR untuk TTD Digital</span>
                    <?php endif; ?>
                </div>

                <strong><u><?= esc($signRight['nama'] ?? 'Siti Rahmawati, S.E., Ak.') ?></u></strong><br>
                NIP. <?= esc($signRight['nip'] ?? '-') ?>
            </td>
        </tr>
    </table>

</body>
</html>
