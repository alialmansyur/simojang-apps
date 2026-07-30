<?php

namespace App\Controllers\Apps\Services;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Libraries\ExcelUploader;
use App\Libraries\DataTablesLib;

use App\Models\Apps\AppsModel;
use App\Models\Apps\Services\PDMModel;

class PeremajaanData extends BaseController
{
    protected $pdmmodel;
    protected $apps;
    protected $uploader;
    protected $dataTables;

    public function __construct()
    {
        $this->pdmmodel     = new PDMModel();
        $this->apps         = new AppsModel();
        $this->uploader     = new ExcelUploader();
        $this->dataTables   = new DataTablesLib();
        $sess = session()->get();
    }

    public function index(){
        return $this->renderView('Apps/pages/services/peremajaandata/main', [
            'seslog' => session()->get(),
        ]);            
    }    

    public function storeData(){
        $sess = session()->get();
        $layananID  = $this->request->getPost('layanan_id');
        $periode    = $this->request->getPost('period');
        $startdate  = $this->request->getPost('startdate');
        $enddate    = $this->request->getPost('enddate');
        $jenis      = $this->request->getPost('jenis');
        $acc        = $this->request->getPost('acc');
        $btl        = $this->request->getPost('btl');
        $tms        = $this->request->getPost('tms');

        list($tahun, $bulan) = explode('-', $periode);
    
        if (!$periode || !$startdate || !$enddate || !$jenis || !$acc || !$btl || !$tms) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data tidak lengkap. Semua kolom wajib diisi.'
            ]);
        }

        $scema_group = bin2hex(random_bytes(8));

        $dataBatch = [];
        foreach ($jenis as $i => $n) {
            if (empty($n) || (empty($acc[$i]) && empty($btl[$i]) && empty($tms[$i]))) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => "Baris ke-" . ($i + 1) . " tidak lengkap. Kolom 'jenis' tidak boleh kosong dan minimal salah satu dari ACC / BTL / TMS harus diisi."
                ]);
            }

            $dataBatch[] = [
                'scema_group'   => $scema_group,
                'period'        => $periode,
                'period_date'   => $startdate,
                'period_start_date'    => $startdate,
                'period_end_date'      => $enddate,
                'jenis'         => $n,
                'total_acc'     => $acc[$i], 
                'total_btl'     => $btl[$i],
                'total_tms'     => $tms[$i],
                'created_by'    => $sess['username'],
            ];
        }

        if ($dataBatch) {
            $this->apps->insertBatchData($dataBatch, 'txn_pdm');
            $this->apps->storeData(
                [
                    'layanan_id' => 22,
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

    public function removeData(){
        $key  = trim((string) $this->request->getPost('key'));
        if ($key === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => false,
                'message' => 'Kunci data tidak valid',
            ]);
        }

        $this->apps->removeData($key,'txn_pdm');
        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Data Berhasil di hapus',
        ]);
    }

    // public function storeData(){
    //     $sess = session()->get();
    //     $nip        = $this->request->getPost('key');
    //     $instansi   = $this->request->getPost('instansi');
    //     $jenis      = $this->request->getPost('jenis');
    //     $tt         = $this->request->getPost('tt');
    //     $status     = $this->request->getPost('status');
    //     $keterangan = $this->request->getPost('key');

    //     if (!$nip || !$instansi || !$jenis || !$tt || !$status) {
    //         return $this->response->setJSON([
    //             'status' => 'error',
    //             'message' => 'Data tidak lengkap. Semua kolom wajib diisi.'
    //         ]);
    //     }

    //     $dataBatch = [];
    //     foreach ($nip as $i => $n) {
    //         if (empty($n) || empty($instansi[$i]) || empty($jenis[$i]) || empty($tt[$i]) || empty($status[$i])) {
    //             return $this->response->setJSON([
    //                 'status' => 'error',
    //                 'message' => "Baris ke-" . ($i + 1) . " tidak lengkap. Semua kolom kecuali keterangan wajib diisi."
    //             ]);
    //         }

    //         $dataBatch[] = [
    //             'nip'           => $n,
    //             'kode_instansi' => $instansi[$i],
    //             'jenis'         => $jenis[$i],
    //             'tindak_lanjut' => $tt[$i],
    //             'status'        => $status[$i],
    //             'keterangan'    => $keterangan[$i] ?? null,
    //             'created_by'    => $sess['username'],
    //         ];
    //     }

    //     if ($dataBatch) {
    //         $this->apps->insertBatchData($dataBatch, 'txn_pdm');
    //     }

    //     return $this->response->setJSON([
    //         'status' => 'success',
    //         'message' => 'Data berhasil disimpan.'
    //     ]);
    // }

    public function getData(){
        return $this->getDataV2();
    }

    public function getDataV2(){
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

        $builder = $this->pdmmodel->getBuilder('recap', [
            'bulan'     => $bulan
        ]);

        $columns    = $this->pdmmodel->getColumns('recap');
        $result     = $this->dataTables->render($builder, $columns);
        return $this->response->setJSON($result);
    }      

    public function getResume() {
        $sess   = session()->get();
        $resume = $this->pdmmodel->getResumeData($sess['username']); 
        $daily  = $this->pdmmodel->getDailyData($sess['username']);
        return $this->response->setJSON([
            'resume' => $resume,
            'daily'  => $daily,
        ]);        
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
            'summary' => $this->pdmmodel->getSummary($bulan),
        ]);
    }

}


