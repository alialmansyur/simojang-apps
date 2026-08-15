<?php

namespace App\Controllers\Apps\Services;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\Apps\Services\IKPAModel;
use App\Models\Apps\AppsModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Libraries\ExcelUploader;
use App\Libraries\DataTablesLib;

class IKPAData extends BaseController
{

    public function __construct()
    { 
        $this->ikpamodel = new IKPAModel();
        $this->apps = new AppsModel();
        $this->uploader = new ExcelUploader();
        $this->dataTables = new DataTablesLib();
        $sess = session()->get();
    }

    public function index(){ 
        return $this->renderView('Apps/pages/services/ikpa/main', [
            'seslog' => session()->get(),
        ]);
    }    

    public function importData(){
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

        $rows   = $this->uploader->parseExcel($localPath);

        if (!is_array($rows) || count($rows) < 1) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data kosong atau format salah.',
            ]);
        }      
        
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

        $logID = $this->apps->storeData($dataLog, 'txn_ikpa');        

        $row = $rows[4]; // BARIS KE-5 (index 4)

        $header = [
            'ikpa_log_id'       => $logID,
            'kode_kppn'         => isset($row[1]) ? str_replace("'", "", trim($row[1])) : null,
            'kode_ba'           => isset($row[2]) ? str_replace("'", "", trim($row[2])) : null,
            'kode_satker'       => isset($row[3]) ? str_replace("'", "", trim($row[3])) : null,
            'uraian_satker'     => isset($row[4]) ? str_replace("'", "", trim($row[4])) : null,
            'nilai_total'       => (float) ($row[13] ?? 0),
            'konversi_bobot'    => (float) ($row[14] ?? 100),
            'dispensasi_spm'    => (float) ($row[15] ?? 0),
            'nilai_akhir'       => (float) ($row[16] ?? 0),
            'created_by'        => $session['username'] ?? null,
        ];

        $HeaderID = $this->apps->storeData($header, 'txn_ikpa_header');   

        $rowNilai      = $rows[4]; // baris "Nilai"
        $rowBobot      = $rows[5]; // baris "Bobot"
        $rowNilaiAkhir = $rows[6]; // baris "Nilai Akhir"

        $indikatorMap = [
            1 => 6,  // Revisi DIPA
            2 => 7,  // Deviasi Halaman III DIPA
            3 => 8,  // Penyerapan Anggaran
            4 => 9,  // Belanja Kontraktual
            5 => 10,  // Penyelesaian Tagihan
            6 => 11, // Pengelolaan UP dan TUP
            7 => 12, // Capaian Output
        ];

        $detailData = [];
        foreach ($indikatorMap as $indikatorId => $colIndex) {

            $detailData[] = [
                'ikpa_log_id'       => $logID,
                'ikpa_header_id'   => $HeaderID,
                'ikpa_indikator_id' => $indikatorId,
                'nilai'        => (float) ($rowNilai[$colIndex] ?? 0),
                'bobot'        => (float) ($rowBobot[$colIndex] ?? 0),
                'nilai_akhir'  => (float) ($rowNilaiAkhir[$colIndex] ?? 0),
            ];
        }

        if (empty($detailData)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data detail tidak ditemukan'
            ]);
        }

        if (!empty($detailData)) {
            $this->apps->insertBatchData($detailData, 'txn_ikpa_detail');
            $this->apps->storeData(
                [
                    'layanan_id' => 34,
                    'tanggal'    => date('Y-m-d'),
                    'created_by' => $session['username']
                ],
                'activity_daily_logs'
            );            
        }

        return $this->response->setJSON([
            'header'    => $header,
            'detail'    => $detailData,
            'status'    => 'success',
            'total'     => count($detailData),
            'message'   => 'Upload dan import data berhasil'
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
        $this->apps->removeData($key,'txn_ikpa'); 
        $this->apps->removeDataLogIKPA($key,'txn_ikpa_header');        
        $this->apps->removeDataLogIKPA($key,'txn_ikpa_detail');        
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

        $builder = $this->ikpamodel->getBuilder('recap', [
            'bulan'     => $bulan
        ]);

        $columns    = $this->ikpamodel->getColumns('recap');
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
            'summary' => $this->ikpamodel->getSummary($bulan),
        ]);
    }

    public function getDataDetail(){
        $key        = trim((string) $this->request->getPost('key'));
        if ($key === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'error' => 'Kunci detail tidak valid',
            ]);
        }
        $builder    = $this->ikpamodel->getBuilder('detail', $key);
        $columns    = $this->ikpamodel->getColumns('detail', $key);
        $result     = $this->dataTables->render($builder, $columns);
        return $this->response->setJSON($result); 
    } 

    // public function getData(){
    //     $sess = session()->get();
    //     $builder = $this->dmsmodel->getDataRecap($sess['username']);
    //     $columns = ['uid','nip','nama', 'kode_instansi', 'nama_instansi','d2nip','ijazah','akta','drh','cpns','pns','perubahan','kp','jabatan','berhenti','pensiun','tanggal_proses','created_by','created_at'];
    //     $result = $this->dataTables->render($builder, $columns);
    //     return $this->response->setJSON($result);
    // }

    // public function getDataV2(){
    //     $sess = session()->get();
    //     $builder = $this->dmsmodel->getDataRecapV2($sess['username']);
    //     $columns = ['id', 'uid', 'upload_id','nama_instansi','logo', 'nip', 'kode_instansi', 'd2nip', 'ijazah', 'drh', 'cpns', 'pns', 'kp', 'jabatan', 'perubahan', 'berhenti', 'pensiun', 'total_doc', 'tanggal_proses', 'created_by', 'created_at', 'pic','update_at'];
    //     $result = $this->dataTables->render($builder, $columns);
    //     return $this->response->setJSON($result);
    // }

    // public function getDataUpload(){
    //     $sess = session()->get();
    //     $builder = $this->dmsmodel->getDataRecapUpload($sess['username']);
    //     $columns = ['uid','nip','nama', 'kode_instansi', 'nama_instansi','d2nip','ijazah','akta','drh','cpns','pns','perubahan','kp','jabatan','berhenti','pensiun','tanggal_proses','created_by','created_at'];
    //     $result = $this->dataTables->render($builder, $columns);
    //     return $this->response->setJSON($result);
    // }    

    // public function getResume() {
    //     $sess   = session()->get();
    //     $resume = $this->dmsmodel->getResumeData($sess['username']);
    //     $daily  = $this->dmsmodel->getDailyData($sess['username']);
    //     return $this->response->setJSON([
    //         'resume' => $resume,
    //         'daily'  => $daily,
    //     ]);        
    // }

}


