<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= esc($doc['title']) ?></title>
    <style>
        body { font-family: 'Arial', sans-serif; font-size: 10pt; line-height: 1.4; color: #000; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .kwitansi-box { border: 1.5px solid #000; padding: 15px 20px; margin-top: 10px; }
        .table-kwitansi { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table-kwitansi td { padding: 5px 4px; vertical-align: top; font-size: 10pt; }
        .amount-box { border: 1.5px solid #000; background-color: #f8fafc; font-size: 13pt; font-weight: bold; padding: 8px 15px; display: inline-block; }
        .signature-table { width: 100%; border-collapse: collapse; margin-top: 25px; page-break-inside: avoid; }
        .signature-table td { vertical-align: top; }
    </style>
</head>
<body>

    <?= $this->include('Apps/pages/services/pnbp/templates/_kop_surat') ?>
    <?= $this->include('Apps/pages/services/pnbp/templates/_footer_page') ?>

    <?php 
        $totalNominal = 0;
        if (!empty($items)) {
            foreach ($items as $it) {
                $totalNominal += (float) ($it['total_harga'] ?? 0);
            }
        }
    ?>

    <div class="kwitansi-box">
        <table style="width: 100%; border-bottom: 1px dashed #000; padding-bottom: 8px; margin-bottom: 8px;">
            <tr>
                <td style="width: 50%;">
                    <div style="font-size: 13pt; font-weight: bold; text-decoration: underline;">KWITANSI JAMUAN / KONSUMSI</div>
                </td>
                <td style="width: 50%; text-align: right; font-size: 9pt;">
                    Tahun Anggaran : <strong><?= date('Y') ?></strong><br>
                    Nomor Kwitansi : <strong><?= esc($doc['doc_number'] ?: '.../KWT-JMN/' . date('Y')) ?></strong><br>
                    Mata Anggaran : <strong><?= esc($meta['mak'] ?? '-') ?></strong>
                </td>
            </tr>
        </table>

        <table class="table-kwitansi">
            <tr>
                <td style="width: 25%;" class="fw-bold">Sudah Terima Dari</td>
                <td style="width: 3%;">:</td>
                <td style="width: 72%;">Kuasa Pengguna Anggaran / Bendahara Pengeluaran Kantor Regional III BKN</td>
            </tr>
            <tr>
                <td class="fw-bold">Jumlah Uang</td>
                <td>:</td>
                <td>
                    <div class="amount-box">
                        <?= \App\Services\PNBP\PNBPHelper::formatRupiah($totalNominal) ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="fw-bold">Terbilang</td>
                <td>:</td>
                <td style="background-color: #f1f5f9; padding: 8px; border-radius: 4px; font-style: italic; font-weight: bold;">
                    "<?= \App\Services\PNBP\PNBPHelper::terbilang($totalNominal) ?>"
                </td>
            </tr>
            <tr>
                <td class="fw-bold">Untuk Pembayaran</td>
                <td>:</td>
                <td class="text-justify">
                    Belanja pengadaan konsumsi/jamuan rapat dan pelaksanaan penugasan <?= esc($doc['title']) ?> pada <?= esc($doc['nama_seleksi'] ?? 'Seleksi CAT') ?> bertempat di <?= esc($doc['nama_tilok'] ?? 'Titik Lokasi') ?> (Rincian terlampir dalam Faktur & Surat Jalan Jamuan).
                </td>
            </tr>
            <tr>
                <td class="fw-bold">Penyedia / Rekanan</td>
                <td>:</td>
                <td>
                    <strong><?= esc($meta['vendor_name'] ?? 'Penyedia Katering') ?></strong> 
                    (NPWP: <?= esc($meta['vendor_npwp'] ?? '-') ?>)
                </td>
            </tr>
        </table>
    </div>

    <!-- 3 Kolom TTD: PPK (Kiri), Bendahara (Tengah), Rekanan Katering (Kanan) -->
    <table class="signature-table">
        <tr>
            <!-- PPK -->
            <td style="width: 33%; text-align: center;">
                <?= esc($signLeft['sign_title'] ?? 'Setuju Dibayar,') ?><br>
                <strong><?= esc($signLeft['jabatan'] ?? 'Pejabat Pembuat Komitmen') ?></strong>
                
                <div style="height: 65px; margin: 4px 0;">
                    <?php if (!empty($signLeft) && $signLeft['sign_status'] === 'signed' && !empty($signLeft['signature_base64'])): ?>
                        <img src="<?= $signLeft['signature_base64'] ?>" style="height: 60px; max-width: 130px;">
                    <?php elseif (!empty($signLeft)): ?>
                        <barcode code="<?= base_url('pnbp-sign/' . $signLeft['sign_token']) ?>" type="QR" class="barcode" size="0.75" error="M" disableborder="1" />
                        <br><span style="font-size: 6.5pt; color: #64748b;">Scan QR TTD</span>
                    <?php endif; ?>
                </div>

                <strong><u><?= esc($signLeft['nama'] ?? 'Ahmad Fauzi, S.Kom., M.T.I.') ?></u></strong><br>
                <small>NIP. <?= esc($signLeft['nip'] ?? '-') ?></small>
            </td>

            <!-- Bendahara -->
            <td style="width: 34%; text-align: center;">
                <?= esc($signCenter['sign_title'] ?? 'Lunas Dibayar,') ?><br>
                <strong><?= esc($signCenter['jabatan'] ?? 'Bendahara Pengeluaran') ?></strong>
                
                <div style="height: 65px; margin: 4px 0;">
                    <?php if (!empty($signCenter) && $signCenter['sign_status'] === 'signed' && !empty($signCenter['signature_base64'])): ?>
                        <img src="<?= $signCenter['signature_base64'] ?>" style="height: 60px; max-width: 130px;">
                    <?php elseif (!empty($signCenter)): ?>
                        <barcode code="<?= base_url('pnbp-sign/' . $signCenter['sign_token']) ?>" type="QR" class="barcode" size="0.75" error="M" disableborder="1" />
                        <br><span style="font-size: 6.5pt; color: #64748b;">Scan QR TTD</span>
                    <?php endif; ?>
                </div>

                <strong><u><?= esc($signCenter['nama'] ?? 'Siti Rahmawati, S.E., Ak.') ?></u></strong><br>
                <small>NIP. <?= esc($signCenter['nip'] ?? '-') ?></small>
            </td>

            <!-- Rekanan Katering -->
            <td style="width: 33%; text-align: center;">
                Bandung, <?= \App\Services\PNBP\PNBPHelper::formatTanggalIndo($doc['doc_date']) ?><br>
                <strong><?= esc($signRight['sign_title'] ?? 'Yang Menerima / Rekanan,') ?></strong>
                
                <div style="height: 65px; margin: 4px 0;">
                    <?php if (!empty($signRight) && $signRight['sign_status'] === 'signed' && !empty($signRight['signature_base64'])): ?>
                        <img src="<?= $signRight['signature_base64'] ?>" style="height: 60px; max-width: 130px;">
                    <?php elseif (!empty($signRight)): ?>
                        <barcode code="<?= base_url('pnbp-sign/' . $signRight['sign_token']) ?>" type="QR" class="barcode" size="0.75" error="M" disableborder="1" />
                        <br><span style="font-size: 6.5pt; color: #64748b;">Scan QR TTD</span>
                    <?php endif; ?>
                </div>

                <strong><u><?= esc($meta['vendor_name'] ?? $signRight['nama'] ?? 'Penyedia Katering') ?></u></strong><br>
                <small>Penyedia Konsumsi</small>
            </td>
        </tr>
    </table>

</body>
</html>
