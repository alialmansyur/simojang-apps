<?php

namespace App\Controllers\Apps\Services;
use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Libraries\ExcelUploader;
use App\Libraries\DataTablesLib;

use App\Models\Apps\AppsModel;
use App\Models\Apps\Services\MeritModel;

class MeritController extends BaseController
{
    protected $appsModel;
    protected $meritModel;
    protected $uploader;
    protected $dataTables;

    public function __construct(){
        $this->appsModel    = new AppsModel();
        $this->meritModel   = new MeritModel();
        $this->uploader     = new ExcelUploader();
        $this->dataTables   = new DataTablesLib();        
        $sess = session()->get();
    }

    public function index(){
        return $this->renderView('Apps/pages/services/merit/main', [
            'seslog' => session()->get(),
        ]);
    }

    public function storeData(){
        $sess              = session()->get();
        $key               = trim((string) $this->request->getPost('key'));
        $usulMasuk         = $this->request->getPost('usul_masuk');
        $ms                = $this->request->getPost('ms');
        $tms               = $this->request->getPost('tms');
        $totalRealisasi    = $this->request->getPost('total_realisasi');
        $slaSesuai         = $this->request->getPost('sla_sesuai');
        $slaTidakSesuai    = $this->request->getPost('sla_tidak_sesuai');
        $persentaseSla     = $this->request->getPost('persentase_sla');
        $period            = trim((string) $this->request->getPost('period'));
        $syncdate1         = trim((string) $this->request->getPost('syncdate1'));
        $syncdate2         = trim((string) $this->request->getPost('syncdate2'));

        $rules = [
            'usul_masuk'        => 'required|numeric',
            'ms'                => 'required|numeric',
            'tms'               => 'required|numeric',
            'total_realisasi'   => 'required|numeric',
            'sla_sesuai'        => 'required|numeric',
            'sla_tidak_sesuai'  => 'required|numeric',
            'persentase_sla'    => 'required',
            'period'            => 'required',
            'syncdate1'         => 'required',
            'syncdate2'         => 'required',
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

        $persenClean = str_replace(['%', ','], ['', '.'], $persentaseSla);
        $dataInsert = [
            'period'                => $period,
            'period_date'           => $syncdate1,
            'period_start_date'     => $syncdate1,
            'period_end_date'       => $syncdate2,
            'usul_masuk'            => $usulMasuk,
            'ms'                    => $ms,
            'tms'                   => $tms,
            'total_realisasi'       => $totalRealisasi,
            'sla_sesuai'            => $slaSesuai,
            'sla_tidak_sesuai'      => $slaTidakSesuai,
            'persentase_sla'        => floatval($persenClean),
            'created_by'            => $sess['username']
        ];

        if (!empty($key)) {
            $this->meritModel->saveData($dataInsert, $key);
        } else {
            $this->meritModel->saveData($dataInsert);
            $this->meritModel->logActivity([
                'layanan_id' => 18,
                'tanggal'    => date('Y-m-d'),
                'created_by' => $sess['username']
            ]);
        }

        return $this->response->setStatusCode(200)->setJSON([
            'status'  => 'success',
            'message' => 'Data berhasil disimpan.',
            'key'     => $key
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

        $this->meritModel->deleteData($key);
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

        $builder = $this->meritModel->getBuilder('recap', [
            'bulan'     => $bulan
        ]);

        $columns    = $this->meritModel->getColumns('recap');
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
            'summary' => $this->meritModel->getSummary($bulan),
        ]);
    }

}
