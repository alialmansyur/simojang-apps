<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= esc($doc['title']) ?></title>
    <style>
        body { font-family: 'dejavusanscondensed', 'dejavusans', sans-serif; font-size: 10pt; line-height: 1.4; color: #000; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .table-data { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 15px; }
        .table-data th, .table-data td { border: 1px solid #000; padding: 6px 8px; font-size: 9pt; }
        .table-data th { background-color: #f1f5f9; text-align: center; }
        .signature-table { width: 100%; border-collapse: collapse; margin-top: 25px; page-break-inside: avoid; }
        .signature-table td { vertical-align: top; }
    </style>
</head>
<body>

    <!-- Header Rekanan Katering -->
    <table style="width: 100%; border-bottom: 2px solid #000; padding-bottom: 6px; margin-bottom: 15px;">
        <tr>
            <td style="width: 65%;">
                <div style="font-size: 14pt; font-weight: bold; text-transform: uppercase; color: #1e293b;">
                    <?= esc($meta['vendor_name'] ?: 'REKANAN PENYEDIA KATERING') ?>
                </div>
                <div style="font-size: 8.5pt; color: #475569;">
                    Layanan Pengadaan Konsumsi, Snack Box & Prasmanan &bull; NPWP: <?= esc($meta['vendor_npwp'] ?: '-') ?>
                </div>
            </td>
            <td style="width: 35%; text-align: right; vertical-align: middle;">
                <div style="font-size: 14pt; font-weight: bold; text-decoration: underline;">SURAT JALAN</div>
                <div style="font-size: 9pt;">No: <strong><?= esc($doc['doc_number'] ?: '.../SJ-JMN/' . date('Y')) ?></strong></div>
            </td>
        </tr>
    </table>

    <?= $this->include('Apps/pages/services/pnbp/templates/_footer_page') ?>

    <table style="width: 100%; border: none; font-size: 10pt; margin-bottom: 12px;">
        <tr>
            <td style="width: 60%; vertical-align: top;">
                <strong>Tujuan Pengiriman / Kepada Yth:</strong><br>
                Panitia Pelaksana <?= esc($doc['title']) ?><br>
                Lokasi: <strong><?= esc($doc['nama_tilok'] ?: 'Titik Lokasi CAT') ?></strong><br>
                Kantor Regional III Badan Kepegawaian Negara
            </td>
            <td style="width: 40%; vertical-align: top;">
                Tanggal Pengiriman : <strong><?= \App\Services\PNBP\PNBPHelper::formatTanggalIndo($doc['doc_date']) ?></strong><br>
                Kendaraan / Driver : <strong>Kurir Katering</strong><br>
                Status : <strong>Pengiriman Pesanan Konsumsi</strong>
            </td>
        </tr>
    </table>

    <p style="font-size: 9.5pt; margin-bottom: 6px;">
        Bersama ini kami kirimkan rincian pesanan jamuan/konsumsi dengan rincian sebagai berikut:
    </p>

    <!-- Tabel Rincian Barang -->
    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th>Nama Barang / Item Konsumsi</th>
                <th>Rincian Menu / Spesifikasi</th>
                <th style="width: 70px;">Jumlah</th>
                <th style="width: 70px;">Satuan</th>
                <th style="width: 90px;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($items)): ?>
                <?php foreach ($items as $i => $it): ?>
                <tr>
                    <td class="text-center fw-bold"><?= $i + 1 ?></td>
                    <td class="fw-bold"><?= esc($it['item_name']) ?></td>
                    <td><?= esc($it['spesifikasi'] ?: '-') ?></td>
                    <td class="text-center fw-bold"><?= (int) $it['quantity'] ?></td>
                    <td class="text-center"><?= esc($it['satuan']) ?></td>
                    <td class="text-center"><small class="badge">Kondisi Baik</small></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center py-3">Belum ada rincian item pengiriman.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <p style="font-size: 9pt; margin-top: 8px;">
        Barang-barang tersebut di atas telah diterima dalam keadaan baik, lengkap, dan cukup sesuai dengan pesanan.
    </p>

    <!-- 2 Kolom TTD: Pengirim & Penerima di Tilok -->
    <table class="signature-table">
        <tr>
            <td style="width: 50%; text-align: center;">
                <?= esc($signLeft['sign_title'] ?? 'Yang Menyerahkan / Pengirim,') ?><br>
                <strong><?= esc($meta['vendor_name'] ?: 'Penyedia Katering') ?></strong>
                
                <div style="height: 70px; margin: 4px 0;">
                    <?php if (!empty($signLeft) && $signLeft['sign_status'] === 'signed' && !empty($signLeft['signature_base64'])): ?>
                        <img src="<?= $signLeft['signature_base64'] ?>" style="height: 65px; max-width: 150px;">
                    <?php elseif (!empty($signLeft)): ?>
                        <barcode code="<?= base_url('pnbp-sign/' . $signLeft['sign_token']) ?>" type="QR" class="barcode" size="0.8" error="M" disableborder="1" />
                        <br><span style="font-size: 7pt; color: #64748b;">Scan QR untuk TTD</span>
                    <?php endif; ?>
                </div>

                <strong><u><?= esc($signLeft['nama'] ?? 'Petugas Pengirim') ?></u></strong><br>
                <small>Kurir / Pengantar</small>
            </td>

            <td style="width: 50%; text-align: center;">
                Diterima di Tilok, <?= \App\Services\PNBP\PNBPHelper::formatTanggalIndo($doc['doc_date']) ?><br>
                <?= esc($signRight['sign_title'] ?? 'Yang Menerima di Lokasi,') ?><br>
                <strong><?= esc($signRight['jabatan'] ?? 'Koordinator Tilok CAT') ?></strong>
                
                <div style="height: 70px; margin: 4px 0;">
                    <?php if (!empty($signRight) && $signRight['sign_status'] === 'signed' && !empty($signRight['signature_base64'])): ?>
                        <img src="<?= $signRight['signature_base64'] ?>" style="height: 65px; max-width: 150px;">
                    <?php elseif (!empty($signRight)): ?>
                        <barcode code="<?= base_url('pnbp-sign/' . $signRight['sign_token']) ?>" type="QR" class="barcode" size="0.8" error="M" disableborder="1" />
                        <br><span style="font-size: 7pt; color: #64748b;">Scan QR untuk TTD</span>
                    <?php endif; ?>
                </div>

                <strong><u><?= esc($signRight['nama'] ?? 'Petugas Penerima') ?></u></strong><br>
                <small>NIP. <?= esc($signRight['nip'] ?? '-') ?></small>
            </td>
        </tr>
    </table>

</body>
</html>
