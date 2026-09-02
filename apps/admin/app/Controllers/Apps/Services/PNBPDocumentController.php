<?php

namespace App\Controllers\Apps\Services;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;
use App\Models\Apps\Services\PNBPDocumentModel;
use App\Models\Apps\Services\PNBPDocTypeModel;
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
    protected PNBPDocTypeModel $docTypeModel;
    protected PNBPPersonelModel $personelModel;
    protected PNBPItemModel $itemModel;
    protected PNBPSignatureModel $signatureModel;
    protected PNBPSignerModel $signerModel;
    protected PNBPPdfService $pdfService;
    protected DataTablesLib $dataTables;

    public function __construct()
    {
        $this->pnbpModel      = new PNBPDocumentModel();
        $this->docTypeModel   = new PNBPDocTypeModel();
        $this->personelModel  = new PNBPPersonelModel();
        $this->itemModel      = new PNBPItemModel();
        $this->signatureModel = new PNBPSignatureModel();
        $this->signerModel    = new PNBPSignerModel();
        $this->pdfService     = new PNBPPdfService();
        $this->dataTables     = new DataTablesLib();
    }

    /**
     * Halaman 1: Katalog & List 9 Jenis Dokumen PNBP (/apps-pnbp)
     * Memuat 9 dokumen dari database; dokumen dengan is_status = 0 ditampilkan dalam keadaan disabled
     */
    public function index()
    {
        $allDocTypes     = $this->docTypeModel->getAllDocTypes();
        $docTypeLabels   = $this->docTypeModel->getDocTypeLabels(false);
        $docTypeBadges   = $this->docTypeModel->getDocTypeBadges(false);
        $stats           = $this->pnbpModel->getDocTypeStats(array_keys($allDocTypes));
        $seleksiOptions  = $this->pnbpModel->getSeleksiOptions();
        $instansiOptions = $this->pnbpModel->getInstansiOptions('', 200);

        // Kategori dinamis berdasarkan data dari database
        $categoryCounts = [
            'all'      => count($allDocTypes),
            'personel' => 0,
            'jamuan'   => 0,
        ];
        foreach ($allDocTypes as $doc) {
            $catKey = $doc['category_key'] ?? 'personel';
            if (isset($categoryCounts[$catKey])) {
                $categoryCounts[$catKey]++;
            } else {
                $categoryCounts[$catKey] = 1;
            }
        }

        return $this->renderView('Apps/pages/services/pnbp/catalog', [
            'seslog'          => session()->get(),
            'docTypeDetails'  => $allDocTypes,
            'docTypeLabels'   => $docTypeLabels,
            'docTypeBadges'   => $docTypeBadges,
            'categoryCounts'  => $categoryCounts,
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
        $docDetail = $this->docTypeModel->getDocTypeByCode($docType);
        if (!$docDetail || empty($docDetail['is_status'])) {
            throw PageNotFoundException::forPageNotFound('Jenis dokumen PNBP tidak ditemukan atau belum aktif.');
        }

        $activeDocTypes  = $this->docTypeModel->getActiveDocTypes();
        $docTypeLabels   = $this->docTypeModel->getDocTypeLabels(false);
        $docTypeBadges   = $this->docTypeModel->getDocTypeBadges(false);
        $seleksiOptions  = $this->pnbpModel->getSeleksiOptions();
        $instansiOptions = $this->pnbpModel->getInstansiOptions('', 200);

        return $this->renderView('Apps/pages/services/pnbp/type_list', [
            'seslog'          => session()->get(),
            'currentDocType'  => $docType,
            'currentDocDetail'=> $docDetail,
            'activeDocTypes'  => $activeDocTypes,
            'docTypeLabels'   => $docTypeLabels,
            'docTypeBadges'   => $docTypeBadges,
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
        $activeDocTypes  = $this->docTypeModel->getActiveDocTypes();
        $docTypeLabels   = $this->docTypeModel->getDocTypeLabels(false);
        $docTypeBadges   = $this->docTypeModel->getDocTypeBadges(false);

        return $this->renderView('Apps/pages/services/pnbp/detail', [
            'seslog'          => session()->get(),
            'doc'             => $doc,
            'activeDocTypes'  => $activeDocTypes,
            'seleksiOptions'  => $seleksiOptions,
            'instansiOptions' => $instansiOptions,
            'tilokOptions'    => $tilokOptions,
            'docTypeLabels'   => $docTypeLabels,
            'docTypeBadges'   => $docTypeBadges,
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
     * Endpoint AJAX: Simpan / Update Keterangan Header Dokumen
     */
    public function storeHeader()
    {
        $docUid           = trim((string) $this->request->getPost('document_uid'));
        $headerKeterangan = trim((string) $this->request->getPost('header_keterangan'));
        $instansiIds      = $this->request->getPost('instansi_ids');
        $instansiNames    = $this->request->getPost('instansi_names');
        $tanggalKegiatan  = trim((string) $this->request->getPost('tanggal_kegiatan'));

        $doc = $this->pnbpModel->where('uid', $docUid)->first();
        if (!$doc) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => 'Dokumen tidak ditemukan.',
            ]);
        }

        $meta = !empty($doc['meta_data']) ? json_decode($doc['meta_data'], true) : [];
        $meta['header_keterangan'] = $headerKeterangan;
        $meta['instansi_ids']      = is_array($instansiIds) ? $instansiIds : (!empty($instansiIds) ? explode(',', $instansiIds) : []);
        $meta['instansi_names']    = is_array($instansiNames) ? $instansiNames : (!empty($instansiNames) ? explode(',', $instansiNames) : []);
        $meta['tanggal_kegiatan']  = $tanggalKegiatan;

        $this->pnbpModel->update((int) $doc['id'], [
            'meta_data'  => json_encode($meta),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Keterangan header berhasil disimpan.',
            'meta'    => $meta,
        ]);
    }

    /**
     * Endpoint AJAX: Tambah / Update Personel pada Dokumen
     */
    public function storePersonel()
    {
        $docUid        = trim((string) $this->request->getPost('document_uid'));
        $personelId    = (int) $this->request->getPost('personel_id');
        $nip           = trim((string) $this->request->getPost('nip'));
        $nama          = trim((string) $this->request->getPost('nama'));
        $pangkatGol    = trim((string) $this->request->getPost('pangkat_gol'));
        $nik           = trim((string) $this->request->getPost('nik'));
        $jabatan       = trim((string) $this->request->getPost('jabatan'));
        $peran         = trim((string) $this->request->getPost('peran'));
        $statusPegawai = trim((string) $this->request->getPost('status_pegawai'));
        $bankNama      = trim((string) $this->request->getPost('bank_nama'));
        $noRekening    = trim((string) $this->request->getPost('no_rekening'));
        
        $jumlahHari    = (int) $this->request->getPost('jumlah_hari') ?: 1;
        $uangHarian    = (float) $this->request->getPost('uang_harian');
        $transport     = (float) $this->request->getPost('transport');
        
        $jumlah        = (float) $this->request->getPost('jumlah');
        $pajakPersen   = (float) $this->request->getPost('pajak_persen');

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

        // Kalkulasi otomatis pajak & jumlah diterima
        $pajakNominal   = round($jumlah * ($pajakPersen / 100), 2);
        $jumlahDiterima = $jumlah - $pajakNominal;

        if ($doc['doc_type'] === 'nominatif') {
            $totalBiaya = $jumlah;
        } else {
            $totalBiaya = ($uangHarian * $jumlahHari) + $transport;
        }

        $payload = [
            'document_id'     => (int) $doc['id'],
            'nip'             => $nip ?: null,
            'nama'            => $nama,
            'pangkat_gol'     => $pangkatGol ?: null,
            'nik'             => $nik ?: null,
            'jabatan'         => $jabatan ?: null,
            'peran'           => $peran ?: null,
            'status_pegawai'  => $statusPegawai ?: null,
            'jumlah'          => $jumlah,
            'pajak_persen'    => $pajakPersen,
            'pajak_nominal'   => $pajakNominal,
            'jumlah_diterima' => $jumlahDiterima,
            'jumlah_hari'     => $jumlahHari,
            'uang_harian'     => $uangHarian,
            'transport'       => $transport,
            'total_biaya'     => $totalBiaya,
            'bank_nama'       => $bankNama ?: null,
            'no_rekening'     => $noRekening ?: null,
        ];

        if ($personelId > 0) {
            $this->personelModel->update($personelId, $payload);
        } else {
            $this->personelModel->insert($payload);
        }

        // Invalidate cache dengan update timestamp dokumen
        $this->pnbpModel->update((int) $doc['id'], ['updated_at' => date('Y-m-d H:i:s')]);

        $totals = $this->personelModel->calculateTotalBudget((int) $doc['id']);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Data pegawai/personel berhasil disimpan.',
            'totals'  => $totals,
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

        $row = $this->personelModel->find($id);
        if ($row && !empty($row['document_id'])) {
            $this->pnbpModel->update((int) $row['document_id'], ['updated_at' => date('Y-m-d H:i:s')]);
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

        // Invalidate cache dengan update timestamp dokumen
        $this->pnbpModel->update((int) $doc['id'], ['updated_at' => date('Y-m-d H:i:s')]);

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

        $row = $this->itemModel->find($id);
        if ($row && !empty($row['document_id'])) {
            $this->pnbpModel->update((int) $row['document_id'], ['updated_at' => date('Y-m-d H:i:s')]);
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

        $sigRow = $this->signatureModel->find($sigId);
        if ($sigRow && !empty($sigRow['document_id'])) {
            $this->pnbpModel->update((int) $sigRow['document_id'], ['updated_at' => date('Y-m-d H:i:s')]);
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
     * Endpoint AJAX: Simpan Seluruh Parameter Tanda Tangan Sekaligus (Left & Right)
     */
    public function storeSignersAll()
    {
        $docUid    = trim((string) $this->request->getPost('document_uid'));
        $signLeft  = $this->request->getPost('sign_left');
        $signRight = $this->request->getPost('sign_right');

        $doc = $this->pnbpModel->where('uid', $docUid)->first();
        if (!$doc) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => 'Dokumen tidak ditemukan.',
            ]);
        }

        $signers = [];
        if (!empty($signLeft) && is_array($signLeft)) {
            $signers[] = $signLeft;
        }
        if (!empty($signRight) && is_array($signRight)) {
            $signers[] = $signRight;
        }

        $this->signatureModel->updateDocumentSigners((int) $doc['id'], $doc['doc_type'], $signers);
        $this->pnbpModel->update((int) $doc['id'], ['updated_at' => date('Y-m-d H:i:s')]);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Parameter penandatangan berhasil disimpan dan diperbarui.',
        ]);
    }

    /**
     * Endpoint AJAX: Ambil data tabel personel dan total kalkulasi real-time
     */
    public function getNominatifTableData()
    {
        $docUid = trim((string) $this->request->getPost('document_uid'));
        $doc = $this->pnbpModel->where('uid', $docUid)->first();
        if (!$doc) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => 'Dokumen tidak ditemukan.',
            ]);
        }

        $personel = $this->personelModel->getPersonelByDocumentId((int) $doc['id']);
        $totals   = $this->personelModel->calculateTotalBudget((int) $doc['id']);

        return $this->response->setJSON([
            'status'   => 'success',
            'personel' => $personel,
            'totals'   => $totals,
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
            ini_set('memory_limit', '512M');
            set_time_limit(120);

            $result = $this->pdfService->getOrGeneratePdf($uid, true);

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
     * Menggunakan Smart Disk Caching untuk streaming instan (0% beban CPU mPDF berulang)
     */
    public function previewPdf(string $uid)
    {
        try {
            ini_set('memory_limit', '512M');
            set_time_limit(120);

            $result = $this->pdfService->getOrGeneratePdf($uid, false);
            $contentLength = strlen($result['binary']);

            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'inline; filename="' . $result['filename'] . '"')
                ->setHeader('Content-Length', (string) $contentLength)
                ->setHeader('Accept-Ranges', 'bytes')
                ->setHeader('Cache-Control', 'private, max-age=0, must-revalidate')
                ->setHeader('Pragma', 'public')
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
            ini_set('memory_limit', '512M');
            set_time_limit(120);

            $result = $this->pdfService->getOrGeneratePdf($uid, false);
            $contentLength = strlen($result['binary']);

            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $result['filename'] . '"')
                ->setHeader('Content-Length', (string) $contentLength)
                ->setHeader('Cache-Control', 'private, max-age=0, must-revalidate')
                ->setHeader('Pragma', 'public')
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
