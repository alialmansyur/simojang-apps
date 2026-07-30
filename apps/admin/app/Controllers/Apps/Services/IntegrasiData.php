<?php

namespace App\Controllers\Apps\Services;
use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Libraries\ExcelUploader;
use App\Libraries\DataTablesLib;

use App\Models\Apps\AppsModel;
use App\Models\Apps\Services\ITGModel;

class IntegrasiData extends BaseController
{
    protected $itgmodel;
    protected $apps;
    protected $uploader;
    protected $dataTables;

    public function __construct()
    {
        $this->itgmodel = new ITGModel();
        $this->apps     = new AppsModel();
        $this->uploader = new ExcelUploader();
        $this->dataTables = new DataTablesLib();
        $sess = session()->get();
    }

    public function index(){
        return $this->renderView('Apps/pages/services/integrasidata/main', [
            'seslog' => session()->get(),
        ]);
    }
 
    public function storeData(){
        $sess       = session()->get();
        $key        = $this->request->getPost('key');
        $instansi   = $this->request->getPost('instansi');
        $period     = $this->request->getPost('period');
        $startdate  = $this->request->getPost('startdate');
        $riwayat    = (array) $this->request->getPost('riwayat');
        $remarks    = $this->request->getPost('remarks');
        $video_url  = $this->request->getPost('video_url');

        $rules = [
            'instansi'      => 'required',
            'period'        => 'required',
            'startdate'     => 'required|valid_date[Y-m-d]',
            'riwayat'       => 'required',
            'remarks'       => 'required',
            'video_url'     => 'required|valid_url',
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

        $dataBatch = [];

        foreach ($riwayat as $rw) {

            if ($this->itgmodel->isDuplicateIntegrasi($instansi, $rw) > 0) {
                continue;
            }

            $dataBatch[] = [
                'period'            => $period,
                'period_date'       => $startdate,
                'instansi_id'       => $instansi,
                'rw_integrasi_id'   => $rw,
                'remarks'           => $remarks,
                'bukti_dukung'      => $video_url,
                'created_by'        => $sess['username'],
                'created_at'        => date('Y-m-d H:i:s')
            ];
        }

        if (empty($dataBatch)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Semua data sudah terdaftar (duplikat).'
            ]);
        }

        $this->apps->insertBatchData($dataBatch, 'txn_integrasi');
        $this->apps->storeData(
            [
                'layanan_id' => 24,
                'tanggal'    => date('Y-m-d'),
                'created_by' => $sess['username']
            ],
            'activity_daily_logs'
        );        

        // $cek = $this->itgmodel->isDuplicateIntegrasi($instansi, $riwayat);
        // if ($cek > 0) {
        //     return $this->response->setJSON([
        //         'status'  => 'error',
        //         'message' => 'Data untuk instansi dan riwayat ini sudah ada.'
        //     ]);
        // }        

        // $dataInsert = [
        //     'period'            => $period,
        //     'period_date'       => $startdate,
        //     'instansi_id'       => $instansi,
        //     'rw_integrasi_id'   => $riwayat,
        //     'remarks'           => $remarks,
        //     'bukti_dukung'      => $video_url,
        //     'created_by'        => $sess['username']
        // ];
 
        // if (!empty($key)) {
        //     $this->apps->updateData($dataInsert,$key,'txn_integrasi');
        // } else {
        //     $this->apps->storeData($dataInsert, 'txn_integrasi');
        // }

        return $this->response->setStatusCode(200)->setJSON([
            'status'  => 'success',
            'message' => 'Data berhasil disimpan.',
            'key'     => $key
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

        $this->apps->removeData($key,'txn_integrasi');
        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Data Berhasil di hapus',
        ]);
    }

    public function getData(){
        $jenis      = (int) ($this->request->getPost('jenis') ?? 0);
        $builder    = $this->itgmodel->getBuilder('recap', $jenis);
        $columns    = $this->itgmodel->getColumns('recap', $jenis);
        $result     = $this->dataTables->render($builder, $columns);
        return $this->response->setJSON($result);
    }

    public function getSummary()
    {
        $jenis = (int) ($this->request->getPost('jenis') ?? 0);
        return $this->response->setJSON([
            'status' => true,
            'summary' => $this->itgmodel->getSummary($jenis),
        ]);
    }


    // public function upload(){
    //     $sess = session()->get();
    //     $data = array(
    //         'title'     => 'Upload Integrasi',
    //         'seslog'    => session()->get(),
    //         'jenis'     => $this->request->getPost('jenis') ?? '-',
    //         'datalist'  => array()
    //     );
    //     return $this->renderView('Apps/pages/services/integrasidata/upload', $data);
    // }

    // public function entry(){
    //     $sess = session()->get();
    //     $data = array(
    //         'title'     => 'Entry Data Integrasi',
    //         'seslog'    => session()->get(),
    //     );
    //     return $this->renderView('Apps/pages/services/integrasidata/entry', $data);
    // }

    // public function info(){
    //     $sess = session()->get();
    //     $data = array(
    //         'title'     => 'Informasi Pekerjaan',
    //         'seslog'    => session()->get()
    //     );
    //     return $this->renderView('Apps/pages/services/integrasidata/info', $data);
    // }

    // public function detail($param){
    //     $sess = session()->get();
    //     $validate = $this->itgmodel->checkData($param);
    //     if (!$validate) {
    //         return redirect()->to('/home');
    //     }        

    //     $data = array(
    //         'title'     => 'Detail Progress Integrasi',
    //         'seslog'    => session()->get(),
    //         'param'     => $validate->id,
    //         'rawdata'   => $validate, 
    //     );
    //     return $this->renderView('Apps/pages/services/integrasidata/detail', $data);
    // }

    // public function removeData(){
    //     $sess = session()->get();
    //     $key  = $this->request->getPost('key');
    //     $this->apps->removeData($key,'txn_integrasi_log');
    //     $this->apps->removeDataLogIntegrasi($key,'txn_integrasi_progress');
    //     return $this->response->setJSON([
    //         'status'  => true,
    //         'message' => 'Data Berhasil di hapus',
    //     ]);
    // }

    // public function storeData(){
    //     $sess     = session()->get();
    //     $file     = $this->request->getFile('file');
    //     $this->uploader->validateFile($file);
    //     $dataLog = [
    //         'title'      => 'Progress Integrasi',
    //         'action'     => 'create',
    //         'date'       => date('Y-m-d'),
    //         'period'     => date('m-Y'), 
    //         'created_by' => $sess['username'],
    //     ];
    //     $insertID   = $this->apps->storeData($dataLog, 'txn_integrasi_log');
    //     $rows       = $this->uploader->parseExcel($file);
    //     if (count($rows) < 1) {
    //         throw new \Exception('Data kosong atau format salah.');
    //     }

    //     $mappingFunction = $this->getMapperForJenis($jenis = 'Progress Integrasi', $insertID, $syncdate = date('Y-m-d H:i:s'));
    //     $dataBatch = [];
    //     foreach (array_slice($rows, 1) as $row) {
    //         if (empty($row[0])) continue;
    //         $dataBatch[] = $mappingFunction($row);
    //     }

    //     if ($dataBatch) {
    //         $this->apps->insertBatchData($dataBatch, 'txn_integrasi_progress');
    //     }

    //     return $this->response->setJSON([
    //         'status'   => 'success',
    //         'message'  => 'Upload dan import data berhasil.',
    //         'datalist' => $dataBatch
    //     ]);
    // }

    // private function getMapperForJenis($jenis, $insertID, $syncdate){
    //     switch ($jenis) {
    //         case 'Progress Integrasi':
    //             return function ($row) use ($insertID, $syncdate) {
    //                 return [
    //                     'integrasi_log_id'    => $insertID,
    //                     'kode_instansi'       => $row[3],
    //                     'kanreg_id'           => $row[1],
    //                     'sapk_satu_arah'      => ExcelUploader::boolFromExcel($row[7]),
    //                     'sapk_dua_arah'       => ExcelUploader::boolFromExcel($row[8]),
    //                     'subscribe_wso2'      => ExcelUploader::boolFromExcel($row[9]),
    //                     'tgl_subscribe'       => ExcelUploader::boolFromExcel($row[10]),
    //                     'implmen_satu_arah'   => ExcelUploader::boolFromExcel($row[11]),
    //                     'implemen_dua_arah'   => ExcelUploader::boolFromExcel($row[12]),
    //                     'rw_jabatan'          => ExcelUploader::boolFromExcel($row[13]),
    //                     'dok_jabatan'         => ExcelUploader::boolFromExcel($row[14]),
    //                     'rw_diklat'           => ExcelUploader::boolFromExcel($row[15]),
    //                     'dok_diklat'          => ExcelUploader::boolFromExcel($row[16]),
    //                     'rw_kursus'           => ExcelUploader::boolFromExcel($row[17]),
    //                     'dok_kursus'          => ExcelUploader::boolFromExcel($row[18]),
    //                     'rw_skp_2022'         => ExcelUploader::boolFromExcel($row[19]),
    //                     'dok_skp_2022'        => ExcelUploader::boolFromExcel($row[20]),
    //                     'rw_hukdis'           => ExcelUploader::boolFromExcel($row[21]),
    //                     'dok_hukdis'          => ExcelUploader::boolFromExcel($row[22]),
    //                     'rw_angka_kredit'     => ExcelUploader::boolFromExcel($row[23]),
    //                     'dok_angka_kredit'    => ExcelUploader::boolFromExcel($row[24]),
    //                     'rw_kinerja'          => ExcelUploader::boolFromExcel($row[25]),
    //                     'dok_kinerja'         => ExcelUploader::boolFromExcel($row[26]),
    //                     'rw_skp_2021'         => ExcelUploader::boolFromExcel($row[27]),
    //                     'dok_skp_2021'        => ExcelUploader::boolFromExcel($row[28]),
    //                     'rw_penghargaan'      => ExcelUploader::boolFromExcel($row[29]),
    //                     'dok_penghargaan'     => ExcelUploader::boolFromExcel($row[30]),
    //                     'rw_cpns'             => ExcelUploader::boolFromExcel($row[31]),
    //                     'dok_cpns'            => ExcelUploader::boolFromExcel($row[32]),
    //                     'data_pribadi'        => ExcelUploader::boolFromExcel($row[33]),
    //                     'download_get_data'   => ExcelUploader::boolFromExcel($row[34]),
    //                     'keterangan'          => ExcelUploader::boolFromExcel($row[35]),
    //                     'status'              => ExcelUploader::boolFromExcel($row[36]),
    //                     'nip'                 => ExcelUploader::boolFromExcel($row[37]),
    //                     'nama'                => ExcelUploader::boolFromExcel($row[38]),
    //                     'att1'                => ExcelUploader::boolFromExcel($row[39]),
    //                     'att2'                => ExcelUploader::boolFromExcel($row[40]),
    //                     'att3'                => ExcelUploader::boolFromExcel($row[41]),
    //                     'att4'                => ExcelUploader::boolFromExcel($row[42]),
    //                     'att5'                => ExcelUploader::boolFromExcel($row[43]),
    //                     'att6'                => ExcelUploader::boolFromExcel($row[44]),
    //                     'att7'                => ExcelUploader::boolFromExcel($row[45]),
    //                     'upload_dok_riwayat'  => ExcelUploader::boolFromExcel($row[46])
    //                 ];
    //             };

    //         default:
    //             throw new \Exception('Jenis tidak dikenali.');
    //     }
    // }

    // public function getData(){
    //     $builder = $this->itgmodel->getDataRecap();
    //     $columns = ['uid','title', 'period', 'created_at','total_instansi','persentase'];
    //     $result = $this->dataTables->render($builder, $columns);
    //     return $this->response->setJSON($result);
    // }

    // public function getDataDetail(){
    //     $key  = $this->request->getPost('key');
    //     $builder = $this->itgmodel->getDataRecapByID($key);
    //     $columns = ['uid','title', 'period', 'kode_instansi', 'nama_instansi', 'created_at','rw_jabatan','rw_diklat','rw_hukdis','rw_angka_kredit','rw_kinerja','rw_penghargaan','rw_cpns','data_pribadi','persentase'];
    //     $result = $this->dataTables->render($builder, $columns);
    //     return $this->response->setJSON($result);
    // }    
}


