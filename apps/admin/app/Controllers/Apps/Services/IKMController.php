<?php

namespace App\Controllers\Apps\Services;
use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Libraries\ExcelUploader;
use App\Libraries\DataTablesLib;

use App\Models\Apps\AppsModel;
use App\Models\Apps\Services\IKMModel;

class IKMController extends BaseController
{
    protected $apps;
    protected $ikm;
    protected $uploader;
    protected $dataTables;

    public function __construct(){
        $this->apps    = new AppsModel();
        $this->ikm   = new IKMModel();
        $this->uploader     = new ExcelUploader();
        $this->dataTables   = new DataTablesLib();        
        $sess = session()->get();
    }

    public function index(){
        return $this->renderView('Apps/pages/services/ikm/main', [
            'seslog' => session()->get(),
        ]);
    }     

    public function storeData(){
        $sess       = session()->get();
        $key        = $this->request->getPost('key');
        $period     = $this->request->getPost('period');
        $syncdate1  = $this->request->getPost('syncdate1');
        $syncdate2  = $this->request->getPost('syncdate2');
        $jenis      = $this->request->getPost('jenis');     
        $responder  = $this->request->getPost('responder');
        $nilai      = $this->request->getPost('nilai');
        $nilaiCon   = str_replace(',', '.', $nilai);
        $rules = [
            // 'jenis'     => 'required',
            'responder' => 'required',
            'nilai'     => [
                'label' => 'Nilai',
                'rules' => 'regex_match[/^(100|[0-9]{1,2})([.,][0-9]+)?$/]'
            ],
            'period'    => 'required',
            'syncdate1' => 'required',
            'syncdate2' => 'required',
        ];

        if (!$this->validate($rules)) {
            $errorsArray = $this->validator->getErrors();
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => implode(', ', $errorsArray),
                'data'    => $this->request->getPost()
            ]);
        }

        $dataInsert = [
            'period'            => $period,
            'period_date'       => $syncdate1,
            'period_start_date' => $syncdate1,
            'period_end_date'   => $syncdate2,
            // 'jenis'             => $jenis,
            'nilai'             => $nilaiCon,
            'jumlah_responden'  => $responder,
            'created_by'        => $sess['username']
        ];

        if (!empty($key)) {
            $this->apps->updateData($dataInsert,$key,'txn_survey_ikm');
        } else {
            $this->apps->storeData($dataInsert, 'txn_survey_ikm');
            $this->apps->storeData(
                [
                    'layanan_id' => 30,
                    'tanggal'    => date('Y-m-d'),
                    'created_by' => $sess['username']
                ],
                'activity_daily_logs'
            );            
        }

        return $this->response->setStatusCode(200)->setJSON([
            'status'  => 'success',
            'message' => 'Data berhasil disimpan.',
            'key'     => $key
        ]);
    }

    public function removeData(){
        $sess = session()->get();
        $key  = trim((string) $this->request->getPost('key'));
        if ($key === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => false,
                'message' => 'Kunci data tidak valid',
            ]);
        }
        $this->apps->removeData($key,'txn_survey_ikm');
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

        $builder = $this->ikm->getBuilder('recap', [
            'bulan'     => $bulan
        ]);

        $columns    = $this->ikm->getColumns('recap');
        $result     = $this->dataTables->render($builder, $columns);
        return $this->response->setJSON($result);
    }

    public function getSummary()
    {
        $bulan = $this->request->getPost('bulan');
        if (!is_array($bulan)) {
            $bulan = [];
        }

        return $this->response->setJSON([
            'status' => 'success',
            'summary' => $this->ikm->getSummary($bulan),
        ]);
    }

}


