<?php

namespace App\Controllers\Apps\Services;
use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Libraries\ExcelUploader;
use App\Libraries\DataTablesLib;

use App\Models\Apps\AppsModel;
use App\Models\Apps\Services\WasdalModel;

class WasdalController extends BaseController
{
    protected $appsModel;
    protected $wasdalModel;
    protected $uploader;
    protected $dataTables;

    public function __construct(){
        $this->appsModel    = new AppsModel();
        $this->wasdalModel   = new WasdalModel();
        $this->uploader     = new ExcelUploader();
        $this->dataTables   = new DataTablesLib();        
        $sess = session()->get();
    }

    public function index(){
        return $this->renderView('Apps/pages/services/wasdal/main', [
            'seslog' => session()->get(),
        ]);
    }

    public function storeData(){
        $sess           = session()->get();
        $key            = trim((string) $this->request->getPost('key'));
        $instansi       = trim((string) $this->request->getPost('instansi'));
        $period         = trim((string) $this->request->getPost('period'));
        $syncdate1      = trim((string) $this->request->getPost('syncdate1'));
        $syncdate2      = trim((string) $this->request->getPost('syncdate2'));
        $permasalahan   = trim((string) $this->request->getPost('permasalahan'));
        $total          = (int) $this->request->getPost('total');

        $rules = [
            'instansi'          => 'required',            
            'period'            => 'required',
            'syncdate1'         => 'required',
            'syncdate2'         => 'required',
            'permasalahan'      => 'required',
            'total'             => 'required|numeric',
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

        if ($syncdate2 < $syncdate1) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Tanggal selesai tidak boleh lebih kecil dari tanggal mulai.',
            ]);
        }

        $dataInsert = [
            'action'            => 'create',
            'period'            => $period,
            'period_date'       => $syncdate1,
            'period_start_date' => $syncdate1,
            'period_end_date'   => $syncdate2,
            'instansi_id'       => $instansi,
            'permasalahan'      => $permasalahan,
            'total'             => $total,
            'created_by'        => $sess['username']
        ];

        if (!empty($key)) {
            $this->wasdalModel->saveData($dataInsert, $key);
        } else {
            $this->wasdalModel->saveData($dataInsert);
            $this->wasdalModel->logActivity([
                'layanan_id' => 20,
                'tanggal'    => date('Y-m-d'),
                'created_by' => $sess['username']
            ]);
        }
        
        return $this->response->setStatusCode(200)->setJSON([
            'status'  => 'success',
            'message' => 'Data berhasil disimpan.',
        ]);
    }

    public function removeData(){
        $key = trim((string) $this->request->getPost('key'));
        if ($key === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => false,
                'message' => 'Kunci data tidak valid',
            ]);
        }

        $this->wasdalModel->deleteData($key);
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

        $builder = $this->wasdalModel->getBuilder('recap', [
            'bulan'     => $bulan
        ]);

        $columns    = $this->wasdalModel->getColumns('recap');
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
            'status' => 'success',
            'summary' => $this->wasdalModel->getSummary($bulan),
        ]);
    }

}
