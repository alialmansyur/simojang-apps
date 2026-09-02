<?php

namespace App\Services\PNBP;

use Mpdf\Mpdf;
use App\Models\Apps\Services\PNBPDocumentModel;

class PNBPPdfService
{
    protected PNBPDocumentModel $docModel;

    public function __construct()
    {
        $this->docModel = new PNBPDocumentModel();
    }

    /**
     * Tentukan orientasi dan ukuran kertas berdasarkan jenis dokumen
     */
    public function getDocumentPaperConfig(string $docType): array
    {
        switch ($docType) {
            case 'nominatif':
            case 'hadir':
            case 'hadir_jamuan':
                return [
                    'format'        => 'A4-L',
                    'orientation'   => 'L',
                    'margin_left'   => 12,
                    'margin_right'  => 12,
                    'margin_top'    => 10,
                    'margin_bottom' => 12,
                ];

            case 'kwitansi':
            case 'kwitansi_jamuan':
                return [
                    'format'        => 'A4-P',
                    'orientation'   => 'P',
                    'margin_left'   => 15,
                    'margin_right'  => 15,
                    'margin_top'    => 12,
                    'margin_bottom' => 12,
                ];

            case 'sp':
            case 'st':
            case 'surat_jalan':
            case 'faktur':
            default:
                return [
                    'format'        => 'A4-P',
                    'orientation'   => 'P',
                    'margin_left'   => 15,
                    'margin_right'  => 15,
                    'margin_top'    => 12,
                    'margin_bottom' => 15,
                ];
        }
    }

    /**
     * Generate HTML view untuk dokumen
     */
    public function renderDocumentHtml(array $docData): string
    {
        $docType = $docData['doc_type'] ?? 'sp';
        $templateName = 'doc_' . $docType;
        $viewPath = 'Apps/pages/services/pnbp/templates/' . $templateName;

        // Logo BKN Base64 (Mencegah blocking/timeout request pada mPDF di Linux/Hosting)
        $logoBase64 = '';
        $logoCandidates = [
            FCPATH . 'apps/assets/images/instansi/Badan Kepegawaian Negara.png',
            ROOTPATH . 'public/apps/assets/images/instansi/Badan Kepegawaian Negara.png',
            FCPATH . 'apps/assets/images/logo/logo.png',
            FCPATH . 'apps/assets/images/logo/logo-dark.png',
        ];
        foreach ($logoCandidates as $cand) {
            if (!empty($cand) && file_exists($cand)) {
                $raw = @file_get_contents($cand);
                if ($raw !== false && strlen($raw) > 0) {
                    $logoBase64 = 'data:image/png;base64,' . base64_encode($raw);
                    break;
                }
            }
        }

        // Pisahkan signature berdasarkan posisi dan konversi tanda tangan ke Base64
        $signatures = $docData['signatures'] ?? [];
        $signLeft   = null;
        $signCenter = null;
        $signRight  = null;

        foreach ($signatures as &$s) {
            $sigBase64 = '';
            $rawPath = trim((string) ($s['signature_image_path'] ?? ''));

            if ($rawPath !== '') {
                if (str_starts_with($rawPath, 'data:image')) {
                    $sigBase64 = $rawPath;
                } else {
                    $cleanRel = ltrim($rawPath, '/\\');
                    $candidates = [
                        $rawPath,
                        WRITEPATH . $cleanRel,
                        WRITEPATH . 'uploads/' . $cleanRel,
                        FCPATH . $cleanRel,
                        ROOTPATH . 'public/' . $cleanRel,
                    ];
                    foreach ($candidates as $f) {
                        if (!empty($f) && file_exists($f)) {
                            $rawSig = @file_get_contents($f);
                            if ($rawSig !== false && strlen($rawSig) > 0) {
                                $sigBase64 = 'data:image/png;base64,' . base64_encode($rawSig);
                                break;
                            }
                        }
                    }
                }
            }
            $s['signature_base64'] = $sigBase64;

            $pos = strtolower(trim((string) ($s['sign_position'] ?? 'right')));
            if ($pos === 'left') {
                $signLeft = $s;
            } elseif ($pos === 'center') {
                $signCenter = $s;
            } else {
                $signRight = $s;
            }
        }
        unset($s);

        $viewData = [
            'doc'         => $docData,
            'meta'        => $docData['meta_data'] ?? [],
            'personel'    => $docData['personel'] ?? [],
            'items'       => $docData['items'] ?? [],
            'attendees'   => $docData['attendees'] ?? [],
            'signatures'  => $signatures,
            'signLeft'    => $signLeft,
            'signCenter'  => $signCenter,
            'signRight'   => $signRight,
            'logoBase64'  => $logoBase64,
        ];

        return view($viewPath, $viewData);
    }

    /**
     * Inisialisasi dan konfigurasi instance mPDF
     */
    public function createMpdfInstance(string $docType): Mpdf
    {
        $paperConfig = $this->getDocumentPaperConfig($docType);

        $tempDir = WRITEPATH . 'cache/mpdf';
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0777, true);
        }
        if (!is_writable($tempDir)) {
            $sysTemp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'mpdf';
            if (!is_dir($sysTemp)) {
                @mkdir($sysTemp, 0777, true);
            }
            if (is_writable($sysTemp)) {
                $tempDir = $sysTemp;
            } else {
                $tempDir = sys_get_temp_dir();
            }
        }

        $mpdf = new Mpdf([
            'mode'                       => 'utf-8',
            'format'                     => $paperConfig['format'],
            'orientation'                => $paperConfig['orientation'],
            'margin_left'                => $paperConfig['margin_left'],
            'margin_right'               => $paperConfig['margin_right'],
            'margin_top'                 => $paperConfig['margin_top'],
            'margin_bottom'              => $paperConfig['margin_bottom'],
            'margin_header'              => 5,
            'margin_footer'              => 5,
            'tempDir'                    => $tempDir,
            'autoMarginPadding'          => 2,
            'setAutoTopMargin'           => 'stretch',
            'setAutoBottomMargin'        => 'stretch',
            'dpi'                        => 96,
            'img_dpi'                    => 96,
            'default_font'               => 'dejavusanscondensed',
            'default_font_size'          => 10,
            'simpleTables'               => true,
            'packTableData'              => true,
            'useSubstitutions'           => true,
            'curlAllowUnsafeSslRequests' => true,
        ]);

        $mpdf->curlTimeout = 2;
        $mpdf->showImageErrors = false;

        $mpdf->SetAuthor('SIMOJANG - Kanreg III BKN');
        $mpdf->SetCreator('SIMOJANG DMS PNBP Generator');

        return $mpdf;
    }

    /**
     * Helper untuk mendapatkan atau menghasilkan PDF binary dengan Smart Disk Caching
     * Mengeliminasi duplikasi render mPDF saat preview, streaming iframe, dan download.
     */
    public function getOrGeneratePdf(string $uid, bool $forceRegenerate = false): array
    {
        $docData = $this->docModel->getFullDocumentDetail($uid);
        if (!$docData) {
            throw new \Exception('Dokumen tidak ditemukan.');
        }

        $docType = $docData['doc_type'] ?? 'sp';
        $year = date('Y', strtotime($docData['doc_date'] ?? date('Y-m-d')));
        $uploadDir = WRITEPATH . 'uploads/pnbp/' . $year . '/' . $docType;
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }

        $fileName = $docType . '_' . $docData['uid'] . '.pdf';
        $fullPath = $uploadDir . '/' . $fileName;
        $savedFilePath = 'uploads/pnbp/' . $year . '/' . $docType . '/' . $fileName;
        $cleanFilename = strtoupper($docType) . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $docData['title']) . '.pdf';

        // Cek apakah file PDF sudah ada di disk dan masih valid (belum ada update data terbaru)
        if (!$forceRegenerate && file_exists($fullPath) && filesize($fullPath) > 0) {
            $docUpdatedTime = !empty($docData['updated_at']) 
                ? strtotime($docData['updated_at']) 
                : (!empty($docData['created_at']) ? strtotime($docData['created_at']) : 0);

            $fileModTime = filemtime($fullPath);

            // Jika file dibuat setelah atau sama dengan update terakhir dokumen, gunakan binary dari file disk langsung (Instant stream ~2ms)
            if ($fileModTime >= $docUpdatedTime) {
                $cachedBinary = file_get_contents($fullPath);
                if ($cachedBinary !== false && strlen($cachedBinary) > 0) {
                    return [
                        'binary'    => $cachedBinary,
                        'filename'  => $cleanFilename,
                        'file_path' => $savedFilePath,
                        'document'  => $docData,
                        'cached'    => true,
                    ];
                }
            }
        }

        // Render HTML ke mPDF jika file belum ada, expired, atau forceRegenerate = true
        $html = $this->renderDocumentHtml($docData);
        $mpdf = $this->createMpdfInstance($docType);
        $mpdf->SetTitle($docData['title'] . ' (' . ($docData['doc_number'] ?: $docData['uid']) . ')');
        $mpdf->WriteHTML($html);

        $pdfBinary = $mpdf->Output('', 'S');

        // Simpan binary ke disk
        @file_put_contents($fullPath, $pdfBinary);

        // Update status dan path di DB
        $this->docModel->update($docData['id'], [
            'status'        => 'generated',
            'pdf_file_path' => $savedFilePath,
            'generated_at'  => date('Y-m-d H:i:s'),
        ]);

        return [
            'binary'    => $pdfBinary,
            'filename'  => $cleanFilename,
            'file_path' => $savedFilePath,
            'document'  => $docData,
            'cached'    => false,
        ];
    }

    /**
     * Backward-compatible wrapper untuk generate PDF binary
     */
    public function generatePdfBinary(string $uid, bool $saveToServer = true): array
    {
        return $this->getOrGeneratePdf($uid, true);
    }
}
