<?php

namespace App\Controllers\Apps\Services;
use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Libraries\ExcelUploader;
use App\Libraries\DataTablesLib;

use App\Models\Apps\AppsModel;
use App\Models\Apps\Services\SuratModel;

class SuratController extends BaseController
{
    protected $apps;
    protected $srtmodel;
    protected $uploader;
    protected $dataTables;

    public function __construct(){
        $this->apps         = new AppsModel();
        $this->srtmodel     = new SuratModel();
        $this->uploader     = new ExcelUploader();
        $this->dataTables   = new DataTablesLib();        
        $sess = session()->get();
    }

    public function index(){
        return $this->renderView('Apps/pages/services/naskah/main', [
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
        $klasifikasi  = $this->request->getPost('klasifikasi');
        $summary      = $this->request->getPost('summary');

        $rules = [
            'jenis'         => 'required',
            'klasifikasi'   => 'required',
            'summary'       => 'required',
            'period'        => 'required',
            'syncdate1'     => 'required',
            'syncdate2'     => 'required',
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
            'jenis_id'          => $jenis,
            'klasifikasi_id'    => $klasifikasi,
            'total'             => $summary,
            'created_by'        => $sess['username']
        ];

        if (!empty($key)) {
            $this->apps->updateData($dataInsert,$key,'txn_naskah_surat');
        } else {
            $this->apps->storeData($dataInsert, 'txn_naskah_surat');
            $this->apps->storeData(
                [
                    'layanan_id' => 32,
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
        $this->apps->removeData($key,'txn_naskah_surat');
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

        $builder = $this->srtmodel->getBuilder('recap', [
            'bulan'     => $bulan
        ]);

        $columns    = $this->srtmodel->getColumns('recap');
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
            'summary' => $this->srtmodel->getSummary($bulan),
        ]);
    }

}


