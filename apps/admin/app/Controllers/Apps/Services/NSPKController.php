<?php

namespace App\Controllers\Apps\Services;
use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Libraries\ExcelUploader;
use App\Libraries\DataTablesLib;

use App\Models\Apps\AppsModel;
use App\Models\Apps\Services\NSPKModel;

class NSPKController extends BaseController
{
    protected $appsModel;
    protected $nspkModel;
    protected $uploader;
    protected $dataTables;

    public function __construct(){
        $this->appsModel    = new AppsModel();
        $this->nspkModel      = new NSPKModel();
        $this->uploader     = new ExcelUploader();
        $this->dataTables   = new DataTablesLib();        
        $sess = session()->get();
    }


    public function index(){
        return $this->renderView('Apps/pages/services/nspk/main', [
            'seslog' => session()->get(),
        ]);
    }    

    public function storeData(){
        $sess       = session()->get();
        $key        = trim((string) $this->request->getPost('key'));
        $instansi   = trim((string) $this->request->getPost('instansi'));
        $period     = trim((string) $this->request->getPost('period'));
        $syncdate1  = trim((string) $this->request->getPost('syncdate1'));
        $syncdate2  = trim((string) $this->request->getPost('syncdate2'));
        $level      = trim((string) $this->request->getPost('level'));

        $rules = [
            'instansi'      => 'required',
            'syncdate1'     => 'required',
            'syncdate2'     => 'required',
            'level'         => 'required',
            'period'        => 'required'
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
            'level'             => $level,
            'created_by'        => $sess['username'],
            'created_at'        => date('Y-m-d H:i:s')
        ];

        if (!empty($key)) {
            $this->nspkModel->saveData($dataInsert, $key);
        } else {
            $cek = $this->nspkModel->isDuplicateNSPK($instansi, $period);
            if ($cek) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Data untuk instansi dan tahun ini sudah ada.'
                ]);
            }

            $this->nspkModel->saveData($dataInsert);
            $this->nspkModel->logActivity([
                'layanan_id' => 19,
                'tanggal'    => date('Y-m-d'),
                'created_by' => $sess['username']
            ]);
        }

        return $this->response->setStatusCode(200)->setJSON([
            'status'    => 'success',
            'message'   => 'Data NSPK berhasil disimpan.'
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

        $this->nspkModel->deleteData($key);
        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Data Berhasil di hapus',
        ]);
    }

    public function getData(){
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

        $builder = $this->nspkModel->getBuilder('recap', [
            'bulan'     => $bulan
        ]);

        $columns    = $this->nspkModel->getColumns('recap');
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
            'summary' => $this->nspkModel->getSummary($bulan),
        ]);
    }

}
