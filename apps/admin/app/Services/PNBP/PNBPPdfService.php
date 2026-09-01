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

        // Pisahkan signature berdasarkan posisi
        $signatures = $docData['signatures'] ?? [];
        $signLeft   = null;
        $signCenter = null;
        $signRight  = null;

        foreach ($signatures as $s) {
            $pos = strtolower(trim((string) ($s['sign_position'] ?? 'right')));
            if ($pos === 'left') {
                $signLeft = $s;
            } elseif ($pos === 'center') {
                $signCenter = $s;
            } else {
                $signRight = $s;
            }
        }

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

        $mpdf = new Mpdf([
            'mode'                 => 'utf-8',
            'format'               => $paperConfig['format'],
            'orientation'          => $paperConfig['orientation'],
            'margin_left'          => $paperConfig['margin_left'],
            'margin_right'         => $paperConfig['margin_right'],
            'margin_top'           => $paperConfig['margin_top'],
            'margin_bottom'        => $paperConfig['margin_bottom'],
            'margin_header'        => 5,
            'margin_footer'        => 5,
            'tempDir'              => $tempDir,
            'autoMarginPadding'    => 2,
            'setAutoTopMargin'     => 'stretch',
            'setAutoBottomMargin'  => 'stretch',
            'dpi'                  => 96,
            'img_dpi'              => 96,
            'default_font'         => 'arial',
            'default_font_size'    => 10,
        ]);

        $mpdf->SetAuthor('SIMOJANG - Kanreg III BKN');
        $mpdf->SetCreator('SIMOJANG DMS PNBP Generator');

        return $mpdf;
    }

    /**
     * Generate PDF String (Binary) untuk Preview Streaming atau Download
     */
    public function generatePdfBinary(string $uid, bool $saveToServer = true): array
    {
        $docData = $this->docModel->getFullDocumentDetail($uid);
        if (!$docData) {
            throw new \Exception('Dokumen tidak ditemukan.');
        }

        $docType = $docData['doc_type'] ?? 'sp';
        $html = $this->renderDocumentHtml($docData);

        $mpdf = $this->createMpdfInstance($docType);
        $mpdf->SetTitle($docData['title'] . ' (' . ($docData['doc_number'] ?: $docData['uid']) . ')');

        // Render HTML ke mPDF
        $mpdf->WriteHTML($html);

        $pdfBinary = $mpdf->Output('', 'S');

        $savedFilePath = null;
        if ($saveToServer) {
            $year = date('Y', strtotime($docData['doc_date'] ?? date('Y-m-d')));
            $uploadDir = WRITEPATH . 'uploads/pnbp/' . $year . '/' . $docType;
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }

            $fileName = $docType . '_' . $docData['uid'] . '.pdf';
            $fullPath = $uploadDir . '/' . $fileName;
            file_put_contents($fullPath, $pdfBinary);
            $savedFilePath = 'uploads/pnbp/' . $year . '/' . $docType . '/' . $fileName;

            // Update status dan path di DB
            $this->docModel->update($docData['id'], [
                'status'        => 'generated',
                'pdf_file_path' => $savedFilePath,
                'generated_at'  => date('Y-m-d H:i:s'),
            ]);
        }

        $cleanFilename = strtoupper($docType) . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $docData['title']) . '.pdf';

        return [
            'binary'    => $pdfBinary,
            'filename'  => $cleanFilename,
            'file_path' => $savedFilePath,
            'document'  => $docData,
        ];
    }
}
