<?php

namespace App\Controllers\Apps\Services;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Libraries\ExcelUploader;
use App\Libraries\DataTablesLib;

use App\Models\Apps\AppsModel;
use App\Models\Apps\Services\DSPModel;

class DisparitasData extends BaseController
{
    protected $dspModel;
    protected $apps;
    protected $uploader;
    protected $dataTables;

    public function __construct()
    {
        $this->dspModel     = new DSPModel();
        $this->apps         = new AppsModel();
        $this->uploader     = new ExcelUploader();
        $this->dataTables   = new DataTablesLib();
        $sess = session()->get();
    }

    public function index(){
        return $this->renderView('Apps/pages/services/disparitasdata/main', [
            'seslog' => session()->get(),
        ]);
    }

    public function storeData(){
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

        $insertID = $this->apps->storeData($dataLog, 'txn_disparitas');

        $rows = $this->uploader->parseExcel($file);
        if (empty($rows)) {
            throw new \Exception('Data kosong atau format salah.');
        }

        $syncDate        = date('Y-m-d H:i:s');
        $mappingFunction = $this->getMapperForJenis('Disparitas', $insertID, $syncDate);

        $dataBatch = [];
        foreach (array_slice($rows, 2) as $row) {
            if (empty($row[0])) {
                continue; 
            }
            $dataBatch[] = $mappingFunction($row);
        }

        if (!empty($dataBatch)) {
            $this->apps->insertBatchData($dataBatch, 'txn_disparitas_detail');
            $this->apps->storeData(
                [
                    'layanan_id' => 23,
                    'tanggal'    => date('Y-m-d'),
                    'created_by' => $session['username']
                ],
                'activity_daily_logs'
            );            
        }

        return $this->response->setJSON([
            'status'   => 'success',
            'message'  => 'Upload dan import data berhasil.',
            'datalist' => $dataBatch
        ]);
    }

    private function getMapperForJenis($jenis, $insertID, $syncdate)
    {
        switch ($jenis) {
            case 'Disparitas':
                return function ($row) use ($insertID, $syncdate) {
                    $instansiID = $this->apps->getInstansiID($row[1]);
                    return [
                        'disparitas_log_id' => $insertID,
                        'instansi_id'       => $instansiID,                    
                        'jenis_anomali'     => $row[2],
                        'jumlah'            => $row[3],
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

        $this->apps->removeData($key,'txn_disparitas');
        $this->apps->removeDataLogDisparitas($key,'txn_disparitas_detail');
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

        $builder = $this->dspModel->getBuilder('recap', [
            'bulan'     => $bulan
        ]);

        $columns    = $this->dspModel->getColumns('recap');
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

        $builder    = $this->dspModel->getBuilder('detail', $key);
        $columns    = $this->dspModel->getColumns('detail', $key);
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
            'summary' => $this->dspModel->getSummary($bulan),
        ]);
    }       

}


