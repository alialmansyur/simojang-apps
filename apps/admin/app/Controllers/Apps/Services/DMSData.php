<?php

namespace App\Controllers\Apps\Services;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\Apps\Services\DMSModel;
use App\Models\Apps\AppsModel;
use PhpOffice\PhpSpreadsheet\IOFactory; 
use App\Libraries\ExcelUploader;
use App\Libraries\DataTablesLib;

class DMSData extends BaseController
{

    public function __construct()
    { 
        $this->dmsmodel = new DMSModel();
        $this->apps = new AppsModel();
        $this->uploader = new ExcelUploader();
        $this->dataTables = new DataTablesLib();
        $sess = session()->get();
    } 

    public function index(){ 
        return $this->renderView('Apps/pages/services/dms/main', [
            'seslog' => session()->get(),
        ]);           
    }

    public function importData(){
        $session = session()->get();
        $file    = $this->request->getFile('file');
        $instansi   = $this->request->getPost('instansi');
        $period     = $this->request->getPost('period');
        $syncdate1  = $this->request->getPost('syncdate1');
        $syncdate2  = $this->request->getPost('syncdate2');

        $this->uploader->validateFile($file);
 
        $localPath  = $file->getTempName();
        $fileSize   = filesize($localPath);
        $mimeType   = $file->getClientMimeType();
        $checksum   = hash_file('sha256', $localPath);
        $ipAddress  = $this->request->getIPAddress();
        $userAgent  = $this->request->getUserAgent()
                        ? $this->request->getUserAgent()->getAgentString()
                        : null;

        $dataLog = [
            'action'     => 'create',
            'period'     => $period,
            'period_date'       => $syncdate1,
            'period_start_date' => $syncdate1,
            'period_end_date'   => $syncdate2,
            'period'     => $period,
            'file_name'  => $file->getClientName(),
            'file_size'  => $fileSize,
            'file_type'  => $mimeType,
            'file_hash'  => $checksum,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'created_by' => $session['username'] ?? null,
        ];

        $insertID = $this->apps->storeData($dataLog, 'txn_dms');

        $rows = $this->uploader->parseExcel($file);
        if (empty($rows)) {
            throw new \Exception('Data kosong atau format salah.');
        }
        $rows   = $this->uploader->parseExcel($localPath);

        if (!is_array($rows) || count($rows) < 1) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data kosong atau format salah.',
            ]);
        }

        $syncDate        = date('Y-m-d H:i:s');
        $mappingFunction = $this->getMapperForJenis('DMS', $insertID, $syncDate);

        $dataBatch = [];
        foreach (array_slice($rows, 2) as $row) {
            if (empty($row[0])) {
                continue; 
            }
            $dataBatch[] = $mappingFunction($row);
        }

        if (!empty($dataBatch)) {
            $this->apps->insertBatchData($dataBatch, 'txn_dms_detail');
            $this->apps->storeData(
                [
                    'layanan_id' => 26,
                    'tanggal'    => date('Y-m-d'),
                    'created_by' => $session['username']
                ],
                'activity_daily_logs'
            );            
        }

        return $this->response->setJSON([
            'status'   => 'success',
            'message'  => 'Upload dan import data berhasil.',
        ]);
    } 

    public function storeData(){
        $sess = session()->get();
        $layananID  = $this->request->getPost('layanan_id');
        $instansi   = $this->request->getPost('instansi');
        $periode    = $this->request->getPost('period');
        $startdate  = $this->request->getPost('startdate');
        $enddate    = $this->request->getPost('enddate');
        $jenis      = $this->request->getPost('jenis');
        $acc        = $this->request->getPost('total');

        list($tahun, $bulan) = explode('-', $periode);
    
        if (!$periode || !$instansi || !$startdate || !$enddate || !$jenis || !$acc) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data tidak lengkap. Semua kolom wajib diisi.'
            ]);
        }

        $scema_group = bin2hex(random_bytes(8));

        $dataBatch = [];
        foreach ($jenis as $i => $n) {
            if (empty($n) || (empty($acc[$i]))) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => "Baris ke-" . ($i + 1) . " tidak lengkap. Kolom 'jenis' tidak boleh kosong dan minimal salah satu dari ACC / BTL / TMS harus diisi."
                ]);
            }

            $dataBatch[] = [
                'scema_group'   => $scema_group,
                'instansi_id'   => $instansi,
                'period'        => $periode,
                'period_date'   => $startdate,
                'period_start_date'    => $startdate,
                'period_end_date'      => $enddate,
                'jenis'         => $n,
                'total'         => $acc[$i], 
                'created_by'    => $sess['username'],
            ];
        }

        if ($dataBatch) {
            $this->apps->insertBatchData($dataBatch, 'txn_dms_entry');
            $this->apps->storeData(
                [
                    'layanan_id' => 26,
                    'tanggal'    => date('Y-m-d'),
                    'created_by' => $sess['username']
                ],
                'activity_daily_logs'
            );            
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Data berhasil disimpan.'
        ]);
    }

    private function getMapperForJenis($jenis, $insertID, $syncdate){
        switch ($jenis) {
            case 'DMS':
                return function ($row) use ($insertID, $syncdate) {
                    $instansiID = $this->apps->getInstansiID($row[13]);
                    return [
                        'dms_log_id'    => $insertID,
                        'nip'           => $row[0],
                        'instansi_id'   => $instansiID,
                        'd2nip'         => ExcelUploader::boolFromExcel($row[1]),
                        'ijazah'        => ExcelUploader::boolFromExcel($row[2]),
                        'drh'           => ExcelUploader::boolFromExcel($row[3]),
                        'cpns'          => ExcelUploader::boolFromExcel($row[4]),
                        'pns'           => ExcelUploader::boolFromExcel($row[5]),
                        'kp'            => ExcelUploader::boolFromExcel($row[6]),
                        'jabatan'       => ExcelUploader::boolFromExcel($row[7]),
                        'perubahan'     => ExcelUploader::boolFromExcel($row[8]),
                        'berhenti'      => ExcelUploader::boolFromExcel($row[9]),
                        'pensiun'       => ExcelUploader::boolFromExcel($row[10]),
                        'total_doc'     => $row[12],
                        'tanggal_proses'=> ExcelUploader::excelDate($row[11]),
                        'pic'           => $row[14],
                    ];
                };

            default:
                throw new \Exception('Jenis tidak dikenali.');
        }
    }    

    public function removeData(){
        $key  = trim((string) $this->request->getPost('key'));
        if ($key === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => false,
                'message' => 'Kunci data tidak valid',
            ]);
        }

        $removed = $this->apps->removeData($key, 'txn_dms_entry');
        if (!$removed) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => false,
                'message' => 'Data DMS tidak ditemukan atau gagal dihapus',
            ]);
        }

        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Data Berhasil di hapus',
        ]);
    }       
    
    public function getData(){
        $sess = session()->get();
        $bulan = $this->request->getPost('bulan');

        if (!is_array($bulan)) {
            $bulan = [];
        }

        if (count($bulan) > 2) {
            return $this->response->setJSON([
                'error' => 'Maksimal 2 bulan diperbolehkan'
            ])->setStatusCode(400);
        }

        $bulan = array_values(array_filter(array_map('intval', $bulan), static function ($item) {
            return $item >= 1 && $item <= 12;
        }));

        $builder = $this->dmsmodel->getBuilder('recap', [
            'bulan'     => $bulan
        ]);

        $columns    = $this->dmsmodel->getColumns('recap');
        $result     = $this->dataTables->render($builder, $columns);
        return $this->response->setJSON($result);
    }  

    public function getDataDetail(){
        $key        = (int) $this->request->getPost('key');
        if ($key <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'error' => 'Kunci detail tidak valid'
            ]);
        }

        $builder    = $this->dmsmodel->getBuilder('detail', $key);
        $columns    = $this->dmsmodel->getColumns('detail', $key);
        $result     = $this->dataTables->render($builder, $columns);
        return $this->response->setJSON($result); 
    }

    public function getSummary()
    {
        $bulan = $this->request->getPost('bulan');
        if (!is_array($bulan)) {
            $bulan = [];
        }

        if (count($bulan) > 2) {
            return $this->response->setJSON([
                'error' => 'Maksimal 2 bulan diperbolehkan'
            ])->setStatusCode(400);
        }

        $bulan = array_values(array_filter(array_map('intval', $bulan), static function ($item) {
            return $item >= 1 && $item <= 12;
        }));

        return $this->response->setJSON([
            'status' => true,
            'summary' => $this->dmsmodel->getSummary($bulan),
        ]);
    } 

    // public function getData(){
    //     $sess = session()->get();
    //     $builder = $this->dmsmodel->getDataRecap($sess['username']);
    //     $columns = ['uid','nip','nama', 'kode_instansi', 'nama_instansi','d2nip','ijazah','akta','drh','cpns','pns','perubahan','kp','jabatan','berhenti','pensiun','tanggal_proses','created_by','created_at'];
    //     $result = $this->dataTables->render($builder, $columns);
    //     return $this->response->setJSON($result);
    // }

    // public function getDataV2(){
    //     $sess = session()->get();
    //     $builder = $this->dmsmodel->getDataRecapV2($sess['username']);
    //     $columns = ['id', 'uid', 'upload_id','nama_instansi','logo', 'nip', 'kode_instansi', 'd2nip', 'ijazah', 'drh', 'cpns', 'pns', 'kp', 'jabatan', 'perubahan', 'berhenti', 'pensiun', 'total_doc', 'tanggal_proses', 'created_by', 'created_at', 'pic','update_at'];
    //     $result = $this->dataTables->render($builder, $columns);
    //     return $this->response->setJSON($result);
    // }

    // public function getDataUpload(){
    //     $sess = session()->get();
    //     $builder = $this->dmsmodel->getDataRecapUpload($sess['username']);
    //     $columns = ['uid','nip','nama', 'kode_instansi', 'nama_instansi','d2nip','ijazah','akta','drh','cpns','pns','perubahan','kp','jabatan','berhenti','pensiun','tanggal_proses','created_by','created_at'];
    //     $result = $this->dataTables->render($builder, $columns);
    //     return $this->response->setJSON($result);
    // }    

    // public function getResume() {
    //     $sess   = session()->get();
    //     $resume = $this->dmsmodel->getResumeData($sess['username']);
    //     $daily  = $this->dmsmodel->getDailyData($sess['username']);
    //     return $this->response->setJSON([
    //         'resume' => $resume,
    //         'daily'  => $daily,
    //     ]);        
    // }

}


