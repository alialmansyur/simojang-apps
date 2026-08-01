<?php

namespace App\Controllers\Apps\Services;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\Apps\Services\KompetensiModel;
use App\Models\Apps\AppsModel;
use PhpOffice\PhpSpreadsheet\IOFactory; 
use App\Libraries\ExcelUploader;
use App\Libraries\DataTablesLib;

class Kompetensi extends BaseController
{
    protected $kompetensimodel;
    protected $apps;
    protected $uploader;
    protected $dataTables;

    public function __construct()
    { 
        $this->kompetensimodel = new KompetensiModel();
        $this->apps = new AppsModel();
        $this->uploader = new ExcelUploader();
        $this->dataTables = new DataTablesLib();
    } 

    public function index(){ 
        return $this->renderView('Apps/pages/services/kompetensi/main', [
            'seslog' => session()->get(),
        ]);           
    }

    public function getData(){
        $bulan = $this->request->getPost('bulan');

        if (!is_array($bulan)) {
            $bulan = [];
        }

        if (count($bulan) > 6) {
            return $this->response->setJSON([
                'error' => 'Maksimal 6 bulan diperbolehkan'
            ])->setStatusCode(400);
        }

        $bulan = array_values(array_filter(array_map('intval', $bulan), static function ($item) {
            return $item >= 1 && $item <= 12;
        }));

        $builder = $this->kompetensimodel->getBuilder('recap-kompetensi', [
            'bulan' => $bulan
        ]);

        $columns = [
            ['data' => 'id', 'search' => false, 'order' => 'a.id'],
            ['data' => 'uid', 'search' => false, 'order' => false],
            ['data' => 'instansi_nama', 'search' => 'd.nama', 'order' => 'd.nama'],
            ['data' => 'instansi_id', 'search' => false, 'order' => false],
            ['data' => 'tanggal', 'search' => 'a.tanggal', 'order' => 'a.tanggal'],
            ['data' => 'metode', 'search' => 'a.metode', 'order' => 'a.metode'],
            ['data' => 'total_peserta', 'search' => false, 'order' => 'a.total_peserta'],
            ['data' => 'created_by', 'search' => 'a.created_by', 'order' => 'a.created_by'],
            ['data' => 'created_at', 'search' => 'a.created_at', 'order' => 'a.created_at'],
            ['data' => 'scema_group', 'search' => false, 'order' => false],
        ];

        $result = $this->dataTables->render($builder, $columns);
        return $this->response->setJSON($result);
    }

    public function getSummary()
    {
        $bulan = $this->request->getPost('bulan');
        if (!is_array($bulan)) {
            $bulan = [];
        }

        if (count($bulan) > 6) {
            return $this->response->setJSON([
                'error' => 'Maksimal 6 bulan diperbolehkan'
            ])->setStatusCode(400);
        }

        $bulan = array_values(array_filter(array_map('intval', $bulan), static function ($item) {
            return $item >= 1 && $item <= 12;
        }));

        return $this->response->setJSON([
            'status' => true,
            'summary' => $this->kompetensimodel->getSummary($bulan),
        ]);
    } 

    public function storeData(){
        $sess = session()->get();
        $instansi   = $this->request->getPost('instansi');
        $tanggal    = $this->request->getPost('tanggal');
        $metode     = $this->request->getPost('metode');
        $total_peserta  = $this->request->getPost('total_peserta');
    
        if (!$tanggal || !$instansi || !$metode) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data tidak lengkap. Semua kolom wajib diisi.'
            ]);
        } 

        $scema_group = bin2hex(random_bytes(8));

        $dataBatch = [];
        foreach ($instansi as $i => $n) {
            if (empty($n) || empty($tanggal[$i]) || empty($metode[$i])) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => "Baris ke-" . ($i + 1) . " tidak lengkap"
                ]);
            }

            $dataBatch[] = [
                'uid'           => bin2hex(random_bytes(16)),
                'scema_group'   => $scema_group,
                'instansi_id'   => $n,
                'tanggal'       => $tanggal[$i],
                'metode'        => $metode[$i],
                'total_peserta' => $total_peserta[$i] ?: 0,
                'created_by'    => $sess['username'] ?? 'system',
            ];
        }
 
        if ($dataBatch) {
            $this->apps->insertBatchData($dataBatch, 'txn_kompetensi');
            $this->apps->storeData(
                [
                    'layanan_id' => 100,
                    'tanggal'    => date('Y-m-d'),
                    'created_by' => $sess['username'] ?? 'system'
                ],
                'activity_daily_logs'
            );            
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Data berhasil disimpan.'
        ]);
    }

    public function removeData(){
        $key  = trim((string) $this->request->getPost('key'));
        if ($key === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => false,
                'message' => 'Kunci data tidak valid',
            ]);
        }

        $this->apps->removeDataByField('uid', $key, 'txn_kompetensi');
        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Data Berhasil di hapus',
        ]);
    }

    public function importData(){
        $session = session()->get();
        $file    = $this->request->getFile('file');

        $this->uploader->validateFile($file);
 
        $localPath  = $file->getTempName();

        $rows = $this->uploader->parseExcel($localPath);

        if (!is_array($rows) || count($rows) < 2) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data kosong atau format salah.',
            ]);
        }

        $scema_group = bin2hex(random_bytes(8));
        $dataBatch = [];
        
        foreach (array_slice($rows, 1) as $index => $row) {
            if (empty($row[0])) {
                continue; 
            }
            
            // Kolom Excel:
            // 0: Instansi Kode
            // 1: Tanggal (YYYY-MM-DD)
            // 2: Metode
            // 3: Total Peserta

            $instansiID = $this->apps->getInstansiID($row[0]);
            
            $dataBatch[] = [
                'uid'           => bin2hex(random_bytes(16)),
                'scema_group'   => $scema_group,
                'instansi_id'   => $instansiID,
                'tanggal'       => ExcelUploader::excelDate($row[1]),
                'metode'        => $row[2],
                'total_peserta' => (int)$row[3],
                'created_by'    => $session['username'] ?? 'system',
            ];
        }

        if (!empty($dataBatch)) {
            $this->apps->insertBatchData($dataBatch, 'txn_kompetensi');
            $this->apps->storeData(
                [
                    'layanan_id' => 100, 
                    'tanggal'    => date('Y-m-d'),
                    'created_by' => $session['username'] ?? 'system'
                ],
                'activity_daily_logs'
            );            
        }

        return $this->response->setJSON([
            'status'   => 'success',
            'message'  => 'Upload dan import data berhasil.',
        ]);
    } 
}
