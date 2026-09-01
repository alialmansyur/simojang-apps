<?php

namespace App\Controllers\Apps\Services;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;
use App\Models\Apps\Services\PNBPDocumentModel;
use App\Models\Apps\Services\PNBPPersonelModel;
use App\Models\Apps\Services\PNBPItemModel;
use App\Models\Apps\Services\PNBPSignatureModel;
use App\Models\Apps\Services\PNBPSignerModel;
use App\Services\PNBP\PNBPPdfService;
use App\Services\PNBP\PNBPHelper;
use App\Libraries\DataTablesLib;

class PNBPDocumentController extends BaseController
{
    protected PNBPDocumentModel $pnbpModel;
    protected PNBPPersonelModel $personelModel;
    protected PNBPItemModel $itemModel;
    protected PNBPSignatureModel $signatureModel;
    protected PNBPSignerModel $signerModel;
    protected PNBPPdfService $pdfService;
    protected DataTablesLib $dataTables;

    public function __construct()
    {
        $this->pnbpModel      = new PNBPDocumentModel();
        $this->personelModel  = new PNBPPersonelModel();
        $this->itemModel      = new PNBPItemModel();
        $this->signatureModel = new PNBPSignatureModel();
        $this->signerModel    = new PNBPSignerModel();
        $this->pdfService     = new PNBPPdfService();
        $this->dataTables     = new DataTablesLib();
    }

    /**
     * Halaman 1: Katalog & List 9 Jenis Dokumen PNBP (/apps-pnbp)
     */
    public function index()
    {
        $docTypeDetails  = PNBPDocumentModel::$docTypeDetails;
        $docTypeLabels   = PNBPDocumentModel::$docTypeLabels;
        $docTypeBadges   = PNBPDocumentModel::$docTypeBadges;
        $stats           = $this->pnbpModel->getDocTypeStats();
        $seleksiOptions  = $this->pnbpModel->getSeleksiOptions();
        $instansiOptions = $this->pnbpModel->getInstansiOptions('', 200);

        return $this->renderView('Apps/pages/services/pnbp/catalog', [
            'seslog'          => session()->get(),
            'docTypeDetails'  => $docTypeDetails,
            'docTypeLabels'   => $docTypeLabels,
            'docTypeBadges'   => $docTypeBadges,
            'stats'           => $stats,
            'seleksiOptions'  => $seleksiOptions,
            'instansiOptions' => $instansiOptions,
        ]);
    }

    /**
     * Halaman 2: Daftar Dokumen per Jenis Dokumen (/apps-pnbp/doc/{doc_type})
     */
    public function docTypeView(string $docType)
    {
        if (!isset(PNBPDocumentModel::$docTypeLabels[$docType])) {
            throw PageNotFoundException::forPageNotFound('Jenis dokumen PNBP tidak dikenali.');
        }

        $docDetail       = PNBPDocumentModel::$docTypeDetails[$docType] ?? [];
        $seleksiOptions  = $this->pnbpModel->getSeleksiOptions();
        $instansiOptions = $this->pnbpModel->getInstansiOptions('', 200);

        return $this->renderView('Apps/pages/services/pnbp/type_list', [
            'seslog'          => session()->get(),
            'currentDocType'  => $docType,
            'currentDocDetail'=> $docDetail,
            'docTypeLabels'   => PNBPDocumentModel::$docTypeLabels,
            'docTypeBadges'   => PNBPDocumentModel::$docTypeBadges,
            'seleksiOptions'  => $seleksiOptions,
            'instansiOptions' => $instansiOptions,
        ]);
    }

    /**
     * Halaman 3: Detail / Editor Dokumen PNBP (/apps-pnbp/detail/{uid})
     */
    public function detail(string $uid)
    {
        $doc = $this->pnbpModel->getFullDocumentDetail($uid);
        if (!$doc) {
            throw PageNotFoundException::forPageNotFound('Dokumen PNBP tidak ditemukan.');
        }

        $seleksiOptions  = $this->pnbpModel->getSeleksiOptions();
        $instansiOptions = $this->pnbpModel->getInstansiOptions('', 200);
        $tilokOptions    = !empty($doc['seleksi_id']) ? $this->pnbpModel->getTilokOptionsBySeleksi((int) $doc['seleksi_id']) : [];

        return $this->renderView('Apps/pages/services/pnbp/detail', [
            'seslog'          => session()->get(),
            'doc'             => $doc,
            'seleksiOptions'  => $seleksiOptions,
            'instansiOptions' => $instansiOptions,
            'tilokOptions'    => $tilokOptions,
            'docTypeLabels'   => PNBPDocumentModel::$docTypeLabels,
            'docTypeBadges'   => PNBPDocumentModel::$docTypeBadges,
        ]);
    }

    /**
     * Endpoint AJAX: Data List Dokumen (Card / DataTable)
     */
    public function getData()
    {
        $params = [
            'keyword'     => $this->request->getPost('keyword') ?? $this->request->getPost('search[value]'),
            'doc_type'    => $this->request->getPost('doc_type'),
            'status'      => $this->request->getPost('status'),
            'seleksi_id'  => $this->request->getPost('seleksi_id'),
            'instansi_id' => $this->request->getPost('instansi_id'),
            'tilok_id'    => $this->request->getPost('tilok_id'),
            'tahun'       => $this->request->getPost('tahun'),
        ];

        $builder = $this->pnbpModel->getListBuilder($params);

        $columns = [
            ['data' => 'id', 'search' => false, 'order' => 'a.id'],
            ['data' => 'uid', 'search' => false, 'order' => false],
            ['data' => 'doc_type', 'search' => 'a.doc_type', 'order' => 'a.doc_type'],
            ['data' => 'doc_number', 'search' => 'a.doc_number', 'order' => 'a.doc_number'],
            ['data' => 'doc_date', 'search' => 'a.doc_date', 'order' => 'a.doc_date'],
            ['data' => 'title', 'search' => 'a.title', 'order' => 'a.title'],
            ['data' => 'status', 'search' => 'a.status', 'order' => 'a.status'],
            ['data' => 'nama_seleksi', 'search' => 's.nama_seleksi', 'order' => 's.nama_seleksi'],
            ['data' => 'nama_tilok', 'search' => 't.nama_tilok', 'order' => 't.nama_tilok'],
            ['data' => 'instansi_nama', 'search' => 'i.nama', 'order' => 'i.nama'],
            ['data' => 'created_at', 'search' => 'a.created_at', 'order' => 'a.created_at'],
            ['data' => 'updated_at', 'search' => 'a.updated_at', 'order' => 'a.updated_at'],
            ['data' => 'total_personel', 'search' => false, 'order' => false],
            ['data' => 'total_items', 'search' => false, 'order' => false],
            ['data' => 'total_signatures', 'search' => false, 'order' => false],
            ['data' => 'total_signed', 'search' => false, 'order' => false],
        ];

        $result = $this->dataTables->render($builder, $columns);
        return $this->response->setJSON($result);
    }

    /**
     * Endpoint AJAX: Summary Metrik Status
     */
    public function getSummary()
    {
        $seleksiId = $this->request->getPost('seleksi_id');
        $tilokId   = $this->request->getPost('tilok_id');

        $summary = $this->pnbpModel->getSummaryMetrics([
            'seleksi_id' => $seleksiId,
            'tilok_id'   => $tilokId,
        ]);

        return $this->response->setJSON([
            'status'  => true,
            'summary' => $summary,
        ]);
    }

    /**
     * Endpoint AJAX: Lookup Titik Lokasi berdasarkan Seleksi
     */
    public function getTilokOptions()
    {
        $seleksiId = (int) $this->request->getPost('seleksi_id');
        if ($seleksiId <= 0) {
            return $this->response->setJSON(['status' => false, 'data' => []]);
        }

        $rows = $this->pnbpModel->getTilokOptionsBySeleksi($seleksiId);
        return $this->response->setJSON(['status' => true, 'data' => $rows]);
    }

    /**
     * Endpoint AJAX: Lookup Master Instansi
     */
    public function getInstansiOptions()
    {
        $q = (string) ($this->request->getGet('q') ?? $this->request->getPost('q') ?? '');
        $rows = $this->pnbpModel->getInstansiOptions($q, 50);

        return $this->response->setJSON([
            'status' => true,
            'data'   => $rows,
        ]);
    }

    /**
     * Endpoint AJAX: Search Master Pegawai untuk Select2
     */
    public function getPegawaiOptions()
    {
        $q = (string) ($this->request->getGet('q') ?? $this->request->getPost('q') ?? '');
        $rows = $this->pnbpModel->searchPegawai($q, 25);

        return $this->response->setJSON([
            'status' => true,
            'items'  => $rows,
        ]);
    }

    /**
     * Endpoint AJAX: Simpan / Update Header Dokumen
     */
    public function storeDocument()
    {
        $sess = session()->get();
        $key        = trim((string) $this->request->getPost('key'));
        $docType    = trim((string) $this->request->getPost('doc_type'));
        $docNumber  = trim((string) $this->request->getPost('doc_number'));
        $docDate    = trim((string) $this->request->getPost('doc_date'));
        $seleksiId  = (int) $this->request->getPost('seleksi_id');
        $instansiId = trim((string) $this->request->getPost('instansi_id'));
        $tilokId    = (int) $this->request->getPost('tilok_id');
        $title      = trim((string) $this->request->getPost('title'));
        $notes      = trim((string) $this->request->getPost('notes'));
        $mak        = trim((string) $this->request->getPost('mak'));
        $vendorName = trim((string) $this->request->getPost('vendor_name'));
        $vendorNpwp = trim((string) $this->request->getPost('vendor_npwp'));

        if ($docType === '' || $title === '') {
            return $this->response->setStatusCode(422)->setJSON([
                'status'  => 'error',
                'message' => 'Tipe dokumen dan judul/perihal wajib diisi.',
            ]);
        }

        $metaData = [
            'notes'       => $notes,
            'mak'         => $mak,
            'vendor_name' => $vendorName,
            'vendor_npwp' => $vendorNpwp,
        ];

        $dataPayload = [
            'doc_type'    => $docType,
            'doc_number'  => $docNumber ?: null,
            'doc_date'    => $docDate ?: date('Y-m-d'),
            'seleksi_id'  => $seleksiId > 0 ? $seleksiId : null,
            'instansi_id' => $instansiId !== '' ? $instansiId : null,
            'tilok_id'    => $tilokId > 0 ? $tilokId : null,
            'title'       => $title,
            'meta_data'   => json_encode($metaData),
        ];

        if ($key !== '') {
            // Update Dokumen
            $existing = $this->pnbpModel->where('uid', $key)->first();
            if (!$existing) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status'  => 'error',
                    'message' => 'Dokumen yang akan diupdate tidak ditemukan.',
                ]);
            }

            $this->pnbpModel->update($existing['id'], $dataPayload);
            $docUid = $key;
        } else {
            // Create Dokumen Baru
            $docUid = bin2hex(random_bytes(16));
            $dataPayload['uid']        = $docUid;
            $dataPayload['status']     = 'draft';
            $dataPayload['created_by'] = $sess['username'] ?? 'system';

            $insertId = $this->pnbpModel->insert($dataPayload);
            if (!$insertId) {
                return $this->response->setStatusCode(500)->setJSON([
                    'status'  => 'error',
                    'message' => 'Gagal menyimpan dokumen baru.',
                ]);
            }

            // Inisialisasi Tanda Tangan Default
            $this->signatureModel->initDefaultSignatures((int) $insertId, $docType);
        }

        return $this->response->setJSON([
            'status'   => 'success',
            'message'  => 'Data dokumen berhasil disimpan.',
            'uid'      => $docUid,
            'doc_type' => $docType,
        ]);
    }

    /**
     * Endpoint AJAX: Tambah / Update Personel pada Dokumen
     */
    public function storePersonel()
    {
        $docUid     = trim((string) $this->request->getPost('document_uid'));
        $personelId = (int) $this->request->getPost('personel_id');
        $nip        = trim((string) $this->request->getPost('nip'));
        $nama       = trim((string) $this->request->getPost('nama'));
        $pangkatGol = trim((string) $this->request->getPost('pangkat_gol'));
        $jabatan    = trim((string) $this->request->getPost('jabatan'));
        $peran      = trim((string) $this->request->getPost('peran'));
        $jumlahHari = (int) $this->request->getPost('jumlah_hari') ?: 1;
        $uangHarian = (float) $this->request->getPost('uang_harian');
        $transport  = (float) $this->request->getPost('transport');
        $noRekening = trim((string) $this->request->getPost('no_rekening'));

        $doc = $this->pnbpModel->where('uid', $docUid)->first();
        if (!$doc) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => 'Dokumen induk tidak ditemukan.',
            ]);
        }

        if ($nama === '') {
            return $this->response->setStatusCode(422)->setJSON([
                'status'  => 'error',
                'message' => 'Nama personel wajib diisi.',
            ]);
        }

        $totalBiaya = ($uangHarian * $jumlahHari) + $transport;

        $payload = [
            'document_id' => (int) $doc['id'],
            'nip'         => $nip ?: null,
            'nama'        => $nama,
            'pangkat_gol' => $pangkatGol ?: null,
            'jabatan'     => $jabatan ?: null,
            'peran'       => $peran ?: null,
            'jumlah_hari' => $jumlahHari,
            'uang_harian' => $uangHarian,
            'transport'   => $transport,
            'total_biaya' => $totalBiaya,
            'no_rekening' => $noRekening ?: null,
        ];

        if ($personelId > 0) {
            $this->personelModel->update($personelId, $payload);
        } else {
            $this->personelModel->insert($payload);
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Data personel berhasil disimpan.',
        ]);
    }

    /**
     * Endpoint AJAX: Hapus Personel
     */
    public function removePersonel()
    {
        $id = (int) $this->request->getPost('id');
        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'ID personel tidak valid.',
            ]);
        }

        $this->personelModel->delete($id);
        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Personel berhasil dihapus.',
        ]);
    }

    /**
     * Endpoint AJAX: Tambah / Update Item Jamuan
     */
    public function storeItems()
    {
        $docUid      = trim((string) $this->request->getPost('document_uid'));
        $itemId      = (int) $this->request->getPost('item_id');
        $itemName    = trim((string) $this->request->getPost('item_name'));
        $spesifikasi = trim((string) $this->request->getPost('spesifikasi'));
        $quantity    = (int) $this->request->getPost('quantity') ?: 1;
        $satuan      = trim((string) $this->request->getPost('satuan')) ?: 'Box';
        $hargaSatuan = (float) $this->request->getPost('harga_satuan');

        $doc = $this->pnbpModel->where('uid', $docUid)->first();
        if (!$doc) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => 'Dokumen induk tidak ditemukan.',
            ]);
        }

        if ($itemName === '') {
            return $this->response->setStatusCode(422)->setJSON([
                'status'  => 'error',
                'message' => 'Nama item jamuan wajib diisi.',
            ]);
        }

        $totalHarga = $quantity * $hargaSatuan;

        $payload = [
            'document_id'  => (int) $doc['id'],
            'item_name'    => $itemName,
            'spesifikasi'  => $spesifikasi ?: null,
            'quantity'     => $quantity,
            'satuan'       => $satuan,
            'harga_satuan' => $hargaSatuan,
            'total_harga'  => $totalHarga,
        ];

        if ($itemId > 0) {
            $this->itemModel->update($itemId, $payload);
        } else {
            $this->itemModel->insert($payload);
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Item jamuan berhasil disimpan.',
        ]);
    }

    /**
     * Endpoint AJAX: Hapus Item Jamuan
     */
    public function removeItem()
    {
        $id = (int) $this->request->getPost('id');
        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'ID item tidak valid.',
            ]);
        }

        $this->itemModel->delete($id);
        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Item jamuan berhasil dihapus.',
        ]);
    }

    /**
     * Endpoint AJAX: Simpan Parameter Tanda Tangan
     */
    public function storeSignatureParam()
    {
        $sigId     = (int) $this->request->getPost('signature_id');
        $nama      = trim((string) $this->request->getPost('nama'));
        $nip       = trim((string) $this->request->getPost('nip'));
        $pangkatGol= trim((string) $this->request->getPost('pangkat_gol'));
        $jabatan   = trim((string) $this->request->getPost('jabatan'));
        $signTitle = trim((string) $this->request->getPost('sign_title'));

        if ($sigId <= 0 || $nama === '') {
            return $this->response->setStatusCode(422)->setJSON([
                'status'  => 'error',
                'message' => 'Nama pejabat penandatangan wajib diisi.',
            ]);
        }

        $this->signatureModel->update($sigId, [
            'nama'        => $nama,
            'nip'         => $nip ?: null,
            'pangkat_gol' => $pangkatGol ?: null,
            'jabatan'     => $jabatan ?: null,
            'sign_title'  => $signTitle ?: null,
        ]);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Parameter penandatangan berhasil diperbarui.',
        ]);
    }

    /**
     * Endpoint AJAX: Hapus Dokumen
     */
    public function removeDocument()
    {
        $key = trim((string) $this->request->getPost('key'));
        if ($key === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => false,
                'message' => 'Kunci dokumen tidak valid.',
            ]);
        }

        $deleted = $this->pnbpModel->deleteDocumentWithChildren($key);
        if (!$deleted) {
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => false,
                'message' => 'Gagal menghapus dokumen.',
            ]);
        }

        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Dokumen dan seluruh data terkait berhasil dihapus.',
        ]);
    }

    /**
     * Endpoint AJAX: Generate Dokumen PDF
     */
    public function generatePdf()
    {
        $uid = trim((string) $this->request->getPost('uid'));
        if ($uid === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'UID dokumen tidak valid.',
            ]);
        }

        try {
            ini_set('memory_limit', '256M');
            set_time_limit(30);

            $result = $this->pdfService->generatePdfBinary($uid, true);

            return $this->response->setJSON([
                'status'   => 'success',
                'message'  => 'PDF berhasil dibuat.',
                'preview'  => base_url('apps-pnbp/preview-pdf/' . $uid),
                'download' => base_url('apps-pnbp/download-pdf/' . $uid),
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[PNBP PDF Error] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => 'Gagal generate PDF: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Endpoint Streaming PDF Preview (Iframe / Browser Inline)
     */
    public function previewPdf(string $uid)
    {
        try {
            ini_set('memory_limit', '256M');
            set_time_limit(30);

            $result = $this->pdfService->generatePdfBinary($uid, false);

            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'inline; filename="' . $result['filename'] . '"')
                ->setBody($result['binary']);
        } catch (\Throwable $e) {
            log_message('error', '[PNBP PDF Preview Error] ' . $e->getMessage());
            throw PageNotFoundException::forPageNotFound($e->getMessage());
        }
    }

    /**
     * Endpoint Download PDF File
     */
    public function downloadPdf(string $uid)
    {
        try {
            ini_set('memory_limit', '256M');
            set_time_limit(30);

            $result = $this->pdfService->generatePdfBinary($uid, true);

            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $result['filename'] . '"')
                ->setBody($result['binary']);
        } catch (\Throwable $e) {
            log_message('error', '[PNBP PDF Download Error] ' . $e->getMessage());
            throw PageNotFoundException::forPageNotFound($e->getMessage());
        }
    }

    /**
     * Endpoint Diagnostic: Test server environment and mPDF health
     */
    public function diagnose()
    {
        $start = microtime(true);
        $tests = [];

        $tests['php_version'] = PHP_VERSION;
        $tests['ext_gd'] = extension_loaded('gd') ? 'OK' : 'MISSING';
        $tests['ext_mbstring'] = extension_loaded('mbstring') ? 'OK' : 'MISSING';
        $tests['ext_xml'] = extension_loaded('xml') ? 'OK' : 'MISSING';

        $cacheDir = WRITEPATH . 'cache/mpdf';
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0777, true);
        }
        $tests['cache_mpdf_writable'] = is_writable($cacheDir) ? 'YES' : 'NO (' . $cacheDir . ')';

        $uploadDir = WRITEPATH . 'uploads/pnbp';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }
        $tests['uploads_pnbp_writable'] = is_writable($uploadDir) ? 'YES' : 'NO (' . $uploadDir . ')';

        try {
            $mpdfStart = microtime(true);
            $mpdf = $this->pdfService->createMpdfInstance('sp');
            $mpdf->WriteHTML('<div style="font-family:dejavusans; font-size:14pt; color:#1e293b;"><b>SIMOJANG PNBP mPDF Diagnostic Test</b><p>mPDF is working properly on this server!</p></div>');
            $pdf = $mpdf->Output('', 'S');
            $mpdfElapsed = round((microtime(true) - $mpdfStart), 3);

            $tests['mpdf_test'] = 'SUCCESS';
            $tests['mpdf_output_size'] = strlen($pdf) . ' bytes';
            $tests['mpdf_render_time'] = $mpdfElapsed . ' seconds';
        } catch (\Throwable $e) {
            $tests['mpdf_test'] = 'FAILED: ' . $e->getMessage();
        }

        $totalTime = round((microtime(true) - $start), 3);
        $tests['total_diagnostic_time'] = $totalTime . ' seconds';

        return $this->response->setJSON([
            'status' => ($tests['mpdf_test'] === 'SUCCESS' ? 'success' : 'error'),
            'diagnostics' => $tests,
        ]);
    }
}
