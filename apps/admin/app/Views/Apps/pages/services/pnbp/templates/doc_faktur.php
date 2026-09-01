<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= esc($doc['title']) ?></title>
    <style>
        body { font-family: 'Arial', sans-serif; font-size: 9.5pt; line-height: 1.35; color: #000; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .table-data { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 12px; }
        .table-data th, .table-data td { border: 1px solid #000; padding: 5px 7px; font-size: 9pt; }
        .table-data th { background-color: #f1f5f9; text-align: center; }
        .signature-table { width: 100%; border-collapse: collapse; margin-top: 20px; page-break-inside: avoid; }
        .signature-table td { vertical-align: top; }
    </style>
</head>
<body>

    <!-- Header Rekanan Katering -->
    <table style="width: 100%; border-bottom: 2px solid #000; padding-bottom: 6px; margin-bottom: 12px;">
        <tr>
            <td style="width: 65%;">
                <div style="font-size: 14pt; font-weight: bold; text-transform: uppercase; color: #1e293b;">
                    <?= esc($meta['vendor_name'] ?: 'REKANAN PENYEDIA KATERING') ?>
                </div>
                <div style="font-size: 8.5pt; color: #475569;">
                    Layanan Pengadaan Jamuan, Snack Box & Prasmanan &bull; NPWP: <?= esc($meta['vendor_npwp'] ?: '-') ?>
                </div>
            </td>
            <td style="width: 35%; text-align: right; vertical-align: middle;">
                <div style="font-size: 14pt; font-weight: bold; text-decoration: underline;">FAKTUR / INVOICE</div>
                <div style="font-size: 9pt;">No: <strong><?= esc($doc['doc_number'] ?: '.../FAK-JMN/' . date('Y')) ?></strong></div>
            </td>
        </tr>
    </table>

    <?= $this->include('Apps/pages/services/pnbp/templates/_footer_page') ?>

    <table style="width: 100%; border: none; font-size: 9.5pt; margin-bottom: 10px;">
        <tr>
            <td style="width: 60%; vertical-align: top;">
                <strong>Ditagihkan Kepada (Pembeli):</strong><br>
                Pejabat Pembuat Komitmen (PPK)<br>
                Kantor Regional III Badan Kepegawaian Negara<br>
                Jalan Surapati No. 10 Bandung
            </td>
            <td style="width: 40%; vertical-align: top;">
                Tanggal Faktur : <strong><?= \App\Services\PNBP\PNBPHelper::formatTanggalIndo($doc['doc_date']) ?></strong><br>
                Kegiatan : <strong><?= esc($doc['title']) ?></strong><br>
                Titik Lokasi : <strong><?= esc($doc['nama_tilok'] ?: '-') ?></strong>
            </td>
        </tr>
    </table>

    <!-- Tabel Rincian Menu & Harga -->
    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th>Nama Pesanan Jamuan</th>
                <th>Rincian Menu</th>
                <th style="width: 60px;">Qty</th>
                <th style="width: 60px;">Satuan</th>
                <th style="width: 95px;">Harga Satuan</th>
                <th style="width: 110px;">Total (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                $grandTotal = 0;
            ?>
            <?php if (!empty($items)): ?>
                <?php foreach ($items as $i => $it): 
                    $sub = (float) $it['total_harga'];
                    $grandTotal += $sub;
                ?>
                <tr>
                    <td class="text-center fw-bold"><?= $i + 1 ?></td>
                    <td class="fw-bold"><?= esc($it['item_name']) ?></td>
                    <td><?= esc($it['spesifikasi'] ?: '-') ?></td>
                    <td class="text-center"><?= (int) $it['quantity'] ?></td>
                    <td class="text-center"><?= esc($it['satuan']) ?></td>
                    <td class="text-end"><?= \App\Services\PNBP\PNBPHelper::formatRupiah($it['harga_satuan'], false) ?></td>
                    <td class="text-end fw-bold"><?= \App\Services\PNBP\PNBPHelper::formatRupiah($it['total_harga'], false) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center py-3">Belum ada rincian item tagihan.</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr style="background-color: #f8fafc; font-weight: bold;">
                <td colspan="6" class="text-end">TOTAL TAGIHAN :</td>
                <td class="text-end fw-bold text-success fs-6"><?= \App\Services\PNBP\PNBPHelper::formatRupiah($grandTotal, false) ?></td>
            </tr>
        </tfoot>
    </table>

    <div style="font-size: 9pt; margin-bottom: 12px;">
        Terbilang: <strong><i><?= \App\Services\PNBP\PNBPHelper::terbilang($grandTotal) ?></i></strong>
    </div>

    <!-- 2 Kolom TTD: Penyedia & PPK -->
    <table class="signature-table">
        <tr>
            <td style="width: 50%; text-align: center;">
                <?= esc($signLeft['sign_title'] ?? 'Setuju Dibayar / PPK,') ?><br>
                <strong><?= esc($signLeft['jabatan'] ?? 'Pejabat Pembuat Komitmen') ?></strong>
                
                <div style="height: 65px; margin: 4px 0;">
                    <?php if (!empty($signLeft) && $signLeft['sign_status'] === 'signed' && !empty($signLeft['signature_base64'])): ?>
                        <img src="<?= $signLeft['signature_base64'] ?>" style="height: 60px; max-width: 140px;">
                    <?php elseif (!empty($signLeft)): ?>
                        <barcode code="<?= base_url('pnbp-sign/' . $signLeft['sign_token']) ?>" type="QR" class="barcode" size="0.75" error="M" disableborder="1" />
                        <br><span style="font-size: 6.5pt; color: #64748b;">Scan QR TTD</span>
                    <?php endif; ?>
                </div>

                <strong><u><?= esc($signLeft['nama'] ?? 'Ahmad Fauzi, S.Kom., M.T.I.') ?></u></strong><br>
                <small>NIP. <?= esc($signLeft['nip'] ?? '-') ?></small>
            </td>

            <td style="width: 50%; text-align: center;">
                Bandung, <?= \App\Services\PNBP\PNBPHelper::formatTanggalIndo($doc['doc_date']) ?><br>
                <?= esc($signRight['sign_title'] ?? 'Hormat Kami / Rekanan,') ?><br>
                <strong><?= esc($meta['vendor_name'] ?: 'Penyedia Katering') ?></strong>
                
                <div style="height: 65px; margin: 4px 0;">
                    <?php if (!empty($signRight) && $signRight['sign_status'] === 'signed' && !empty($signRight['signature_base64'])): ?>
                        <img src="<?= $signRight['signature_base64'] ?>" style="height: 60px; max-width: 140px;">
                    <?php elseif (!empty($signRight)): ?>
                        <barcode code="<?= base_url('pnbp-sign/' . $signRight['sign_token']) ?>" type="QR" class="barcode" size="0.75" error="M" disableborder="1" />
                        <br><span style="font-size: 6.5pt; color: #64748b;">Scan QR TTD</span>
                    <?php endif; ?>
                </div>

                <strong><u><?= esc($meta['vendor_name'] ?? $signRight['nama'] ?? 'Pimpinan Usaha') ?></u></strong><br>
                <small>Penyedia Konsumsi</small>
            </td>
        </tr>
    </table>

</body>
</html>
