<?php

namespace App\Controllers\Apps\Services;
use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Libraries\ExcelUploader;
use App\Libraries\DataTablesLib;

use App\Models\Apps\AppsModel;
use App\Models\Apps\Services\KonsulModel;

class KonsulController extends BaseController
{
    protected $apps;
    protected $konsul;
    protected $uploader;
    protected $dataTables;

    public function __construct(){
        $this->apps    = new AppsModel();
        $this->konsul   = new KonsulModel();
        $this->uploader     = new ExcelUploader();
        $this->dataTables   = new DataTablesLib();        
        $sess = session()->get();
    }

    public function index(){
        return $this->renderView('Apps/pages/services/konsul/main', [
            'seslog' => session()->get(),
        ]);
    }     

    public function storeData(){
        $sess       = session()->get();
        $key        = $this->request->getPost('key');
        $period     = $this->request->getPost('period');
        $syncdate1  = $this->request->getPost('syncdate1');
        $syncdate2  = $this->request->getPost('syncdate2');
        $media      = $this->request->getPost('media');     
        $jumlah     = str_replace(',', '', $this->request->getPost('summary'));
        $notes      = $this->request->getPost('notes');     

        $rules = [
            'media'     => 'required',
            'summary'   => 'required|numeric',
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
            'pelayanan'         => $media,
            'jumlah'            => $jumlah,
            'keterangan'        => $notes,
            'created_by'        => $sess['username']
        ];

        if (!empty($key)) {
            $this->apps->updateData($dataInsert,$key,'txn_konsultasi');
        } else {
            $this->apps->storeData($dataInsert, 'txn_konsultasi');
            $this->apps->storeData(
                [
                    'layanan_id' => 33,
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
        $this->apps->removeData($key,'txn_konsultasi');
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

        $builder = $this->konsul->getBuilder('recap', [
            'bulan'     => $bulan
        ]);

        $columns    = $this->konsul->getColumns('recap');
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
            'summary' => $this->konsul->getSummary($bulan),
        ]);
    }

}


