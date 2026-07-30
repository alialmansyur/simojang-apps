<?php

namespace App\Controllers\Apps\Services;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\Apps\Services\TKHModel;
use App\Models\Apps\AppsModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Libraries\ExcelUploader;
use App\Libraries\DataTablesLib;

class TakahData extends BaseController
{

    public function __construct()
    {
        $this->takahmodel = new TKHModel();
        $this->apps = new AppsModel();
        $this->uploader = new ExcelUploader();
        $this->dataTables = new DataTablesLib();
        $sess = session()->get();
    }

    public function index(){ 
        return $this->renderView('Apps/pages/services/takah/main', [
            'seslog' => session()->get(),
        ]);           
    }    

    public function importData(){
        $session = session()->get();
        $file    = $this->request->getFile('file');
        $period   = $this->request->getPost('period');
        $syncdate1 = $this->request->getPost('syncdate1');
        $syncdate2 = $this->request->getPost('syncdate2');

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
            'file_name'  => $file->getClientName(),
            'file_size'  => $fileSize,
            'file_type'  => $mimeType,
            'file_hash'  => $checksum,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'created_by' => $session['username'] ?? null,
        ];

        $insertID = $this->apps->storeData($dataLog, 'txn_takah');

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
        $mappingFunction = $this->getMapperForJenis('Takah', $insertID, $syncDate);

        $dataBatch = [];
        foreach (array_slice($rows, 2) as $row) {
            if (empty($row[0])) {
                continue; 
            }
            $dataBatch[] = $mappingFunction($row);
        }

        if (!empty($dataBatch)) {
            $this->apps->insertBatchData($dataBatch, 'txn_takah_detail');
            $this->apps->storeData(
                [
                    'layanan_id' => 25,
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

    private function getMapperForJenis($jenis, $insertID, $syncdate){
        switch ($jenis) {
            case 'Takah':
                return function ($row) use ($insertID, $syncdate) {
                    $instansiID = $this->apps->getInstansiID($row[2]);
                    return [
                        'takah_log_id'  => $insertID,                        
                        'instansi_id'   => $instansiID,
                        'nip'           => $row[0],
                        'nama'          => $row[1],
                        'd2nip'         => $row[3] ? 1 : 0,
                        'ijazah'        => $row[4] ? 1 : 0,
                        'akta'          => $row[5] ? 1 : 0,
                        'drh'           => $row[6] ? 1 : 0,
                        'cpns'          => $row[7] ? 1 : 0,
                        'pns'           => $row[8] ? 1 : 0,
                        'perubahan'     => $row[9] ? 1 : 0,
                        'kp'            => $row[10] ? 1 : 0,
                        'jabatan'       => $row[11] ? 1 : 0,
                        'berhenti'      => $row[12] ? 1 : 0,
                        'pensiun'       => $row[13] ? 1 : 0,
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

        $this->apps->removeData($key,'txn_takah');
        $this->apps->removeDataLogTakah($key,'txn_takah_detail');            
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

        $builder = $this->takahmodel->getBuilder('recap', [
            'bulan'     => $bulan
        ]);

        $columns    = $this->takahmodel->getColumns('recap');
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

        $builder    = $this->takahmodel->getBuilder('detail', $key);
        $columns    = $this->takahmodel->getColumns('detail', $key);
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
            'summary' => $this->takahmodel->getSummary($bulan),
        ]);
    }

    public function getRefLastInstansi($param){
        $data = $this->ktlgmodel->getRefInstansi($param);
        return $data->last;
    }

}


