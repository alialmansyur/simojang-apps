<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= esc($doc['title'] ?? 'Daftar Nominatif') ?></title>
    <style>
        @page {
            margin-top: 12mm;
            margin-bottom: 15mm;
            margin-left: 15mm;
            margin-right: 15mm;
        }
        body { 
            font-family: 'dejavusanscondensed', 'dejavusans', Arial, sans-serif; 
            font-size: 8pt; 
            line-height: 1.25; 
            color: #000; 
        }
        .text-center { text-align: center; }
        .text-start { text-align: left; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .text-uppercase { text-transform: uppercase; }

        .doc-title {
            font-size: 11pt;
            font-weight: bold;
            text-align: center;
            letter-spacing: 0.05em;
            margin-bottom: 12px;
        }
        .doc-header-text {
            font-size: 8.5pt;
            line-height: 1.4;
            text-align: justify;
            margin-bottom: 14px;
        }

        table.table-nominatif { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 5px; 
            margin-bottom: 10px; 
        }
        table.table-nominatif thead {
            display: table-header-group;
        }
        table.table-nominatif th { 
            border: 1px solid #000; 
            padding: 5px 3px; 
            font-size: 7.5pt; 
            font-weight: bold;
            text-align: center; 
            vertical-align: middle; 
            background-color: #fff;
            line-height: 1.2;
        }
        table.table-nominatif td { 
            border: 1px solid #000; 
            padding: 4px 4px; 
            font-size: 7.5pt; 
            vertical-align: middle; 
            line-height: 1.25;
        }
        table.table-nominatif tr {
            page-break-inside: avoid;
        }

        .signature-section { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 25px; 
            page-break-inside: avoid; 
        }
        .signature-section td { 
            vertical-align: top; 
            font-size: 8pt;
            line-height: 1.35;
        }
    </style>
</head>
<body>

    <?php
    $instansiNames = !empty($meta['instansi_names']) ? (is_array($meta['instansi_names']) ? implode(', ', $meta['instansi_names']) : $meta['instansi_names']) : ($doc['instansi_nama'] ?? 'Pemerintah Daerah');
    $tanggalKgt = !empty($meta['tanggal_kegiatan']) ? $meta['tanggal_kegiatan'] : (!empty($doc['period_start_date']) ? \App\Services\PNBP\PNBPHelper::formatPeriode($doc['period_start_date'], $doc['period_end_date']) : \App\Services\PNBP\PNBPHelper::formatTanggalIndo($doc['doc_date']));
    
    $defaultKeterangan = "Honorarium Tim Panitia dalam rangka Fasilitasi Seleksi Pengembangan Karier dengan metode CAT BKN di Lingkungan Instansi " . $instansiNames . " di Kanreg III BKN, pada tanggal " . $tanggalKgt . ".";
    $headerText = !empty($meta['header_keterangan']) ? $meta['header_keterangan'] : $defaultKeterangan;
    ?>

    <!-- Judul Dokumen -->
    <div class="doc-title">
        DAFTAR NOMINATIF
    </div>

    <!-- Paragraf Keterangan Header -->
    <div class="doc-header-text">
        <?= esc($headerText) ?>
    </div>

    <!-- Tabel Daftar Nominatif Sesuai Acuan Lampiran -->
    <table class="table-nominatif">
        <thead>
            <tr>
                <th style="width: 25px;">NO</th>
                <th style="width: 175px;">NAMA/NIP</th>
                <th style="width: 35px;">GOL</th>
                <th style="width: 110px;">NIK</th>
                <th style="width: 105px;">BANK & NO REK</th>
                <th style="width: 85px;">JABATAN</th>
                <th style="width: 70px;">JUMLAH</th>
                <th style="width: 65px;">PAJAK<br>PPh psl 21</th>
                <th style="width: 70px;">JUMLAH<br>DITERIMA</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                $totalJumlah   = 0;
                $totalPajak    = 0;
                $totalDiterima = 0;
            ?>
            <?php if (!empty($personel)): ?>
                <?php foreach ($personel as $i => $p): 
                    $jumlahVal   = (float) ($p['jumlah'] > 0 ? $p['jumlah'] : ($p['total_biaya'] ?? 0));
                    $pajakNomVal = (float) ($p['pajak_nominal'] ?? 0);
                    if ($pajakNomVal == 0 && !empty($p['pajak_persen']) && $p['pajak_persen'] > 0) {
                        $pajakNomVal = round($jumlahVal * ($p['pajak_persen'] / 100), 2);
                    }
                    $diterimaVal = (float) ($p['jumlah_diterima'] > 0 ? $p['jumlah_diterima'] : ($jumlahVal - $pajakNomVal));

                    $totalJumlah   += $jumlahVal;
                    $totalPajak    += $pajakNomVal;
                    $totalDiterima += $diterimaVal;

                    // Parse Golongan
                    $golText = trim((string) ($p['pangkat_gol'] ?? ''));
                    if (preg_match('/\((.*?)\)/', $golText, $matches)) {
                        $golDisplay = $matches[1];
                    } else {
                        $golDisplay = $golText ?: '-';
                    }
                    // Format romawi sederhana jika memungkinkan (misal III/a -> III)
                    $golClean = explode('/', $golDisplay)[0];
                    if (in_array(strtoupper($golClean), ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII', 'XIII', 'XIV', 'XV', 'XVI', 'XVII'])) {
                        $golFormatted = strtoupper($golClean);
                    } else {
                        $golFormatted = $golDisplay;
                    }

                    $bankName = trim((string) ($p['bank_nama'] ?? 'BRI'));
                    if (empty($bankName) && !empty($p['no_rekening']) && preg_match('/^(Bank\s+\w+|\w+)/i', $p['no_rekening'], $bMatches)) {
                        $bankName = $bMatches[1];
                    }
                    $noRek = trim((string) ($p['no_rekening'] ?? '-'));
                    // Bersihkan nama bank dari no_rekening jika ada
                    $cleanRek = preg_replace('/^[a-zA-Z\s]+/', '', $noRek);
                    $cleanRek = trim($cleanRek, " ()-\t\n\r\0\x0B");
                    if (empty($cleanRek)) {
                        $cleanRek = $noRek;
                    }
                ?>
                <tr>
                    <td class="text-center"><?= $i + 1 ?></td>
                    <td>
                        <div class="fw-bold text-uppercase"><?= esc($p['nama']) ?></div>
                        <div><?= esc($p['nip'] ?: '-') ?></div>
                    </td>
                    <td class="text-center"><?= esc($golFormatted) ?></td>
                    <td class="text-center"><?= esc($p['nik'] ?: '-') ?></td>
                    <td class="text-center">
                        <div><i><?= esc($bankName ?: 'BRI') ?></i></div>
                        <div>(<?= esc($cleanRek ?: '-') ?>)</div>
                    </td>
                    <td class="text-center"><?= esc($p['jabatan'] ?: ($p['peran'] ?: 'Anggota')) ?></td>
                    <td class="text-end"><?= number_format($jumlahVal, 0, ',', '.') ?></td>
                    <td class="text-end"><?= $pajakNomVal > 0 ? number_format($pajakNomVal, 0, ',', '.') : '-' ?></td>
                    <td class="text-end"><?= number_format($diterimaVal, 0, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="text-center py-3">Belum ada rincian data nominatif pegawai.</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr style="font-weight: bold;">
                <td colspan="6" class="text-center fw-bold" style="letter-spacing: 0.05em;">JUMLAH SELURUHNYA</td>
                <td class="text-end fw-bold"><?= number_format($totalJumlah, 0, ',', '.') ?></td>
                <td class="text-end fw-bold"><?= $totalPajak > 0 ? number_format($totalPajak, 0, ',', '.') : '-' ?></td>
                <td class="text-end fw-bold"><?= number_format($totalDiterima, 0, ',', '.') ?></td>
            </tr>
        </tfoot>
    </table>

    <!-- Blok Tanda Tangan Sesuai Acuan Lampiran -->
    <table class="signature-section">
        <tr>
            <!-- Kolom Kiri: PPK / Mengetahui -->
            <td style="width: 50%; text-align: center; padding-right: 25px;">
                <?php
                    $leftTitle = !empty($signLeft['sign_title']) ? $signLeft['sign_title'] : "Mengetahui\nAnalis Pengelolaan Keuangan APBN Ahli Madya\nsebagai Pejabat Pembuat Komitmen\nPusat Pengembangan Sistem Rekrutmen (PNBP)";
                    $leftNama  = !empty($signLeft['nama']) ? $signLeft['nama'] : 'LESTARI PRASETIJANI, SE, MM';
                    $leftNip   = !empty($signLeft['nip']) ? $signLeft['nip'] : '197104241992032001';
                ?>
                <div><?= nl2br(esc($leftTitle)) ?></div>
                
                <!-- Space lapang untuk tanda tangan basah/digital di atas nama pejabat -->
                <div style="height: 75px; margin: 8px 0; min-height: 70px;">
                    <?php if (!empty($signLeft) && $signLeft['sign_status'] === 'signed' && !empty($signLeft['signature_base64'])): ?>
                        <img src="<?= $signLeft['signature_base64'] ?>" style="height: 65px; max-width: 150px;">
                    <?php else: ?>
                        <div style="height: 70px;"></div>
                    <?php endif; ?>
                </div>

                <div class="fw-bold" style="text-decoration: underline;"><?= esc($leftNama) ?></div>
                <div>NIP. <?= esc($leftNip) ?></div>
            </td>

            <!-- Kolom Kanan: Bendahara Pengeluaran -->
            <td style="width: 50%; text-align: center; padding-left: 25px;">
                <?php
                    $rightTitle = !empty($signRight['sign_title']) ? $signRight['sign_title'] : "Jakarta, ..................................................\nDiajukan ke Kuasa Pengguna Anggaran BKN\nPada tanggal...............................\nBendahara Pengeluaran";
                    $rightNama  = !empty($signRight['nama']) ? $signRight['nama'] : 'FITRIANI PANJAITAN, S.Kom.';
                    $rightNip   = !empty($signRight['nip']) ? $signRight['nip'] : '199009062014022001';
                ?>
                <div><?= nl2br(esc($rightTitle)) ?></div>

                <!-- Space lapang untuk tanda tangan basah/digital di atas nama pejabat -->
                <div style="height: 75px; margin: 8px 0; min-height: 70px;">
                    <?php if (!empty($signRight) && $signRight['sign_status'] === 'signed' && !empty($signRight['signature_base64'])): ?>
                        <img src="<?= $signRight['signature_base64'] ?>" style="height: 65px; max-width: 150px;">
                    <?php else: ?>
                        <div style="height: 70px;"></div>
                    <?php endif; ?>
                </div>

                <div class="fw-bold" style="text-decoration: underline;"><?= esc($rightNama) ?></div>
                <div>NIP. <?= esc($rightNip) ?></div>
            </td>
        </tr>
    </table>

</body>
</html>

