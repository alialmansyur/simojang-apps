<?php

namespace App\Controllers\Apps\Services;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\Apps\Services\EKINModel;
use App\Models\Apps\AppsModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Libraries\ExcelUploader;
use App\Libraries\DataTablesLib;

class EKINData extends BaseController
{

    public function __construct()
    { 
        $this->ekinmodel = new EKINModel();
        $this->apps = new AppsModel();
        $this->uploader = new ExcelUploader();
        $this->dataTables = new DataTablesLib();
        $sess = session()->get();
    }

    public function index(){ 
        return $this->renderView('Apps/pages/services/ekin/main', [
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


        $rows = $this->uploader->parseExcel($file);
        if (empty($rows)) {
            throw new \Exception('Data kosong atau format salah.');
        }

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
            'period'     => $period,
            'file_name'  => $file->getClientName(),
            'file_size'  => $fileSize,
            'file_type'  => $mimeType,
            'file_hash'  => $checksum,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'created_by' => $session['username'] ?? null,
        ];

        $logID = $this->apps->storeData($dataLog, 'txn_ekin');        

        $header = [
            'ekin_log_id'   => $logID,
            'unit'          => $rows[0][0] ?? null,
            'sub_unit'      => $this->getValueAfterColon($rows[1][0] ?? null),
            'periode'       => $this->getValueAfterColon($rows[2][0] ?? null),
            'tanggal_kegiatan'    => $this->tanggalIndoToYmd($this->getValueAfterColon($rows[3][0] ?? null)),
            'created_by'    => $session['username'] ?? null,
        ];

        $HeaderID = $this->apps->storeData($header, 'txn_ekin_header');   

        $lastNip  = null;
        $dataDetail = [];
        $startRead = false;

        foreach ($rows as $row) {
            if (
                isset($row[1], $row[2], $row[3]) &&
                strtolower(trim($row[1])) === 'nama' &&
                strtolower(trim($row[2])) === 'nip'
            ) {
                $startRead = true;
                continue;
            }

            if (!$startRead) {
                continue;
            }

            $nip  = trim($row[2] ?? '');
            if ($nip !== '') {
                if (!$this->isValidNIP($nip)) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => "NIP tidak valid: {$nip}"
                    ]);
                }
                $lastNip = $nip;
            }

            if (!$lastNip) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'NIP tidak terbaca sebelum data kegiatan'
                ]);
            }

            $dataDetail[] = [
                'ekin_log_id'       => $logID,
                'ekin_header_id'    => $HeaderID,
                'nip'           => $lastNip,
                'waktu'         => trim($row[3]),
                'kegiatan'      => trim($row[4]),
                'realisasi'     => $row[5] ?? 0,
                'created_by'    => $session['username'] ?? null,
            ];
        }

        if (empty($dataDetail)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data detail tidak ditemukan'
            ]);
        }

        if (!empty($dataDetail)) {
            $this->apps->insertBatchData($dataDetail, 'txn_ekin_detail');
            $this->apps->storeData(
                [
                    'layanan_id' => 36,
                    'tanggal'    => date('Y-m-d'),
                    'created_by' => $session['username']
                ],
                'activity_daily_logs'
            );            
        }

        return $this->response->setJSON([
            'header'    => $header,
            'status' => 'success',
            'total'  => count($dataDetail),
            'message'=> 'Upload dan import data berhasil'
        ]);        
    } 

    private function getValueAfterColon(?string $text): ?string
    {
        if (!$text) {
            return null;
        }

        if (strpos($text, ':') === false) {
            return null;
        }

        return trim(explode(':', $text, 2)[1]);
    }

    function tanggalIndoToYmd(string $tanggal): ?string
    {
        $bulan = [
            'Januari'   => 'January',
            'Februari'  => 'February',
            'Maret'     => 'March',
            'April'     => 'April',
            'Mei'       => 'May',
            'Juni'      => 'June',
            'Juli'      => 'July',
            'Agustus'   => 'August',
            'September' => 'September',
            'Oktober'   => 'October',
            'November'  => 'November',
            'Desember'  => 'December',
        ];

        // Normalisasi spasi
        $tanggal = trim(preg_replace('/\s+/', ' ', $tanggal));

        // Ganti nama bulan ke EN
        $tanggal = str_replace(
            array_keys($bulan),
            array_values($bulan),
            $tanggal
        );

        // Validasi & konversi
        if (!strtotime($tanggal)) {
            return null;
        }

        return date('Y-m-d', strtotime($tanggal));
    }

    private function isValidNIP(string $nip): bool
    {
        return preg_match('/^\d{18}$/', $nip);
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
        $this->apps->removeData($key,'txn_ekin'); 
        $this->apps->removeDataLogEKIN($key,'txn_ekin_header');        
        $this->apps->removeDataLogEKIN($key,'txn_ekin_detail');        
        return $this->response->setJSON([
            'key'     => $key,
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

        $builder = $this->ekinmodel->getBuilder('recap', [
            'bulan'     => $bulan
        ]);

        $columns    = $this->ekinmodel->getColumns('recap');
        $result     = $this->dataTables->render($builder, $columns);
        return $this->response->setJSON($result);
    }

    public function getDataChild()
    {
        $periodDate = trim((string) $this->request->getPost('period_date'));
        if ($periodDate === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Tanggal parent tidak valid',
                'list' => [],
            ]);
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $periodDate)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Format tanggal parent tidak valid',
                'list' => [],
            ]);
        }

        $list = $this->ekinmodel->getChildByPeriodDate($periodDate);
        return $this->response->setJSON([
            'status' => 'success',
            'list' => $list,
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
            'status' => 'success',
            'summary' => $this->ekinmodel->getSummary($bulan),
        ]);
    }

    public function getDataDetail(){
        $draw       = (int) ($this->request->getPost('draw') ?? 0);
        $key        = (int) ($this->request->getPost('key') ?? 0);
        if ($key <= 0) {
            return $this->response->setJSON([
                'draw' => $draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }
        $builder    = $this->ekinmodel->getBuilder('detail', $key);
        $columns    = $this->ekinmodel->getColumns('detail', $key);
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


