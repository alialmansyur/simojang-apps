<?php

namespace App\Controllers\Apps\Services;
use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Libraries\ExcelUploader;
use App\Libraries\DataTablesLib;

use App\Models\Apps\AppsModel;
use App\Models\Apps\Services\MTModel;

class MTController extends BaseController
{
    protected $appsModel;
    protected $mtModel;
    protected $uploader;
    protected $dataTables;

    public function __construct(){
        $this->appsModel    = new AppsModel();
        $this->mtModel      = new MTModel();
        $this->uploader     = new ExcelUploader();
        $this->dataTables   = new DataTablesLib();        
        $sess = session()->get();
    }

    public function index(){
        $sess = session()->get();
        $data = array(
            'title'     => 'Manajemen Talenta',
            'seslog'    => session()->get(),
        );
        return $this->renderView('Apps/pages/services/manajementalenta/main', $data);        
    }
    
    public function storeMTData(){
        $sess = session()->get();

        $instansi      = $this->request->getPost('instansi');
        $period        = $this->request->getPost('period');
        $startdate     = $this->request->getPost('startdate');
        $step_progress = $this->request->getPost('stepProgress');

        $key           = $this->request->getPost('key');
        
        $rules = [
            'instansi'      => 'required',
            'period'        => 'required',
            'startdate'     => 'required',
            'stepProgress'  => 'required',
        ];

        if (!$this->validate($rules)) {
            $errorsArray = $this->validator->getErrors();
            $message = implode(', ', $errorsArray); 
            return $this->response->setJSON([
                'data'    => $this->request->getPost(),
                'status'  => 'error',
                'message' => $message
            ]);
        }

        $dataPayload = [
            'instansi_id'   => $instansi,
            'period'        => $period,
            'period_date'   => $startdate,
            'rw_mt_id'      => $step_progress,
        ];

        if (!empty($key) && is_numeric($key)) {
            $dataPayload['updated_by'] = $sess['username'] ?? null;
            $dataPayload['updated_at'] = date('Y-m-d H:i:s');
            $this->appsModel->updateData($dataPayload, $key, 'txn_mt');
            $insertID = $key;
            $activityDesc = 'Mengupdate data MT pada instansi ID '.$instansi;
        } else {
            $dataPayload['created_by'] = $sess['username'] ?? null;
            $dataPayload['created_at'] = date('Y-m-d H:i:s');
            $insertID  = $this->appsModel->storeData($dataPayload, 'txn_mt');
            $activityDesc = 'Menambahkan data MT pada instansi ID '.$instansi;
        }

        $datalog = [
            'activity_type' => 'create',
            'mt_id'         => $insertID,
            'rw_mt_id'      => $step_progress,
            'description'   => $activityDesc,
            'created_by'    => $sess['username'],
            'created_at'    => date('Y-m-d H:i:s')
        ];
    
        $this->appsModel->storeData($datalog,'txn_mt_detail');
        $this->apps->storeData(
            [
                'layanan_id' => 13,
                'tanggal'    => date('Y-m-d'),
                'created_by' => $sess['username']
            ],
            'activity_daily_logs'
        );        
        
        return $this->response->setStatusCode(200)->setJSON([
            'status'    => 'success',
            'message'   => 'Data MT berhasil disimpan.'
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

        $builder = $this->mtModel->getBuilder('recap', [
            'bulan'     => $bulan
        ]);

        $columns    = $this->mtModel->getColumns('recap');
        $result     = $this->dataTables->render($builder, $columns);
        return $this->response->setJSON($result);
    }  

    public function removeData(){
        $sess = session()->get();
        $key  = $this->request->getPost('key');
        $this->appsModel->removeData($key,'txn_mt');
        $this->appsModel->removeDataLogMT($key,'txn_mt_detail');
        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Data Berhasil di hapus',
        ]);
    }    

}


