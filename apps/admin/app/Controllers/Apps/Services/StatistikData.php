<?php

namespace App\Controllers\Apps\Services;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Libraries\ExcelUploader;
use App\Libraries\DataTablesLib;
use App\Models\Apps\AppsModel;
use App\Models\Apps\Services\STKModel;

class StatistikData extends BaseController
{
    private const JENIS_LIST = [
        'Jumlah ASN',
        'Golongan ASN',
        'Jenis Kelamin ASN',
        'Pendidikan ASN',
        'Usia ASN',
        'Generasi ASN',
        'Kelompok Jabatan ASN',
        'Masa Kerja ASN',
    ];
  
    public function __construct()
    {
        $this->stkmodel = new STKModel();
        $this->apps = new AppsModel();
        $this->uploader = new ExcelUploader();
        $this->dataTables = new DataTablesLib();
        $sess = session()->get();
    }

    public function index(){
        return $this->renderView('Apps/pages/services/statistikdata/main', [
            'seslog'    => session()->get(),
        ]);
    }      

    public function storeData(){
        $sess     = session()->get();
        $file     = $this->request->getFile('file');
        $period   = $this->request->getPost('period');
        $syncdate1 = $this->request->getPost('syncdate1');
        $syncdate2 = $this->request->getPost('syncdate2');
        $jenis    = $this->request->getPost('doc_category');

        $rules = [
            'doc_category'    => 'required',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => false,
                'message' => $this->validator->getErrors()
            ]);
        }

        $this->uploader->validateFile($file);

        $localPath  = $file->getTempName();
        $fileSize   = filesize($localPath);
        $mimeType   = $file->getClientMimeType();
        $checksum   = hash_file('sha256', $localPath);
        $ipAddress  = $this->request->getIPAddress();
        $userAgent  = $this->request->getUserAgent()
                        ? $this->request->getUserAgent()->getAgentString()
                        : null;

        $dataLog = [
            'action'     => 'create',
            'jenis'      => $jenis,
            'date'       => $syncdate1,
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
            'created_by' => $sess['username'] ?? null,
        ];

        $insertID = $this->apps->storeData($dataLog, 'txn_asn');

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

        $syncDate        = date('Y-m-d H:i:s');
        $mappingFunction = $this->getMapperForJenis($jenis, $insertID, $syncDate);
        $table = $this->getTableForJenis($jenis);

        $dataBatch = [];
        foreach (array_slice($rows, 2) as $row) {
            if (empty($row[0])) continue;
            $dataBatch[] = $mappingFunction($row);
        }

        if ($dataBatch) {
            $this->apps->insertBatchData($dataBatch, $table);
            $this->apps->storeData(
                [
                    'layanan_id' => 21,
                    'tanggal'    => date('Y-m-d'),
                    'created_by' => $sess['username']
                ],
                'activity_daily_logs'
            );
        }

        return $this->response->setJSON([
            'status'   => 'success',
            'message'  => 'Upload dan import data berhasil.',
            'datalist' => $dataBatch
        ]);
    }

    private function getMapperForJenis($jenis, $insertID, $syncdate)
    {
        switch ($jenis) {
            case 'Jumlah ASN':
                return function ($row) use ($insertID, $syncdate) {
                    return [
                        'asn_log_id'    => $insertID,
                        'instansi_id'   => $row[0],
                        'pns'           => ExcelUploader::cleanNumber($row[2]),
                        'pppk'          => ExcelUploader::cleanNumber($row[3]),
                        'pppk_pw'        => ExcelUploader::cleanNumber($row[4]),
                        'jumlah'        => ExcelUploader::cleanNumber($row[5]),
                        'tanggal'       => $syncdate
                    ];
                };

            case 'Golongan ASN':
                return function ($row) use ($insertID, $syncdate) {
                    return [
                        'asn_log_id'    => $insertID,
                        'instansi_id'   => $row[0],
                        'pns_gol_i'     => ExcelUploader::cleanNumber($row[2]),
                        'pns_gol_ii'    => ExcelUploader::cleanNumber($row[3]),
                        'pns_gol_iii'   => ExcelUploader::cleanNumber($row[4]),
                        'pns_gol_iv'    => ExcelUploader::cleanNumber($row[5]),
                        'pppk_gol_i'    => ExcelUploader::cleanNumber($row[6]),
                        'pppk_gol_ii'   => ExcelUploader::cleanNumber($row[7]),
                        'pppk_gol_iii'  => ExcelUploader::cleanNumber($row[8]),
                        'pppk_gol_iv'   => ExcelUploader::cleanNumber($row[9]),
                        'pppk_gol_v'    => ExcelUploader::cleanNumber($row[10]),
                        'pppk_gol_vi'   => ExcelUploader::cleanNumber($row[11]),
                        'pppk_gol_vii'  => ExcelUploader::cleanNumber($row[12]),
                        'pppk_gol_viii' => ExcelUploader::cleanNumber($row[13]),
                        'pppk_gol_ix'   => ExcelUploader::cleanNumber($row[14]),
                        'pppk_gol_x'    => ExcelUploader::cleanNumber($row[15]),
                        'pppk_gol_xi'   => ExcelUploader::cleanNumber($row[16]),
                        'pppk_gol_xii'  => ExcelUploader::cleanNumber($row[17]),
                        'pppk_gol_xiii' => ExcelUploader::cleanNumber($row[18]),
                        'pppk_gol_xiv'  => ExcelUploader::cleanNumber($row[19]),
                        'pppk_gol_xv'   => ExcelUploader::cleanNumber($row[20]),
                        'pppk_gol_xvi'  => ExcelUploader::cleanNumber($row[21]),
                        'pppk_gol_xvii' => ExcelUploader::cleanNumber($row[22]),
                        'pppk_pw_gol_v_x' => ExcelUploader::cleanNumber($row[23]),
                        'jumlah'        => ExcelUploader::cleanNumber($row[24]),
                        'tanggal'       => $syncdate
                    ];
                };

            case 'Jenis Kelamin ASN':
                return function ($row) use ($insertID, $syncdate) {
                    return [
                        'asn_log_id'    => $insertID,
                        'instansi_id'   => $row[0],
                        'pns_pria'      => ExcelUploader::cleanNumber($row[2]),
                        'pns_wanita'    => ExcelUploader::cleanNumber($row[3]),
                        'pppk_pria'     => ExcelUploader::cleanNumber($row[4]),
                        'pppk_wanita'   => ExcelUploader::cleanNumber($row[5]),
                        'pppk_pw_pria'  => ExcelUploader::cleanNumber($row[6]),
                        'pppk_pw_wanita'=> ExcelUploader::cleanNumber($row[7]),
                        'jumlah'        => ExcelUploader::cleanNumber($row[8]),
                        'tanggal'       => $syncdate
                    ];
                };
            case 'Pendidikan ASN':
                return function ($row) use ($insertID, $syncdate) {
                    return [
                        'asn_log_id'    => $insertID,
                        'instansi_id'   => $row[0],
                        'pns_sd'        => ExcelUploader::cleanNumber($row[2]),
                        'pns_smp'       => ExcelUploader::cleanNumber($row[3]),
                        'pns_sma'       => ExcelUploader::cleanNumber($row[4]),
                        'pns_d1'        => ExcelUploader::cleanNumber($row[5]),
                        'pns_d2'        => ExcelUploader::cleanNumber($row[6]),
                        'pns_d3'        => ExcelUploader::cleanNumber($row[7]),
                        'pns_s1'        => ExcelUploader::cleanNumber($row[8]),
                        'pns_s2'        => ExcelUploader::cleanNumber($row[9]),
                        'pns_s3'        => ExcelUploader::cleanNumber($row[10]),
                        'pppk_sd'       => ExcelUploader::cleanNumber($row[11]),
                        'pppk_smp'      => ExcelUploader::cleanNumber($row[12]),
                        'pppk_sma'      => ExcelUploader::cleanNumber($row[13]),
                        'pppk_d1'       => ExcelUploader::cleanNumber($row[14]),
                        'pppk_d2'       => ExcelUploader::cleanNumber($row[15]),
                        'pppk_d3'       => ExcelUploader::cleanNumber($row[16]),
                        'pppk_s1'       => ExcelUploader::cleanNumber($row[17]),
                        'pppk_s2'       => ExcelUploader::cleanNumber($row[18]),
                        'pppk_s3'       => ExcelUploader::cleanNumber($row[19]),
                        'pppk_pw_sd'    => ExcelUploader::cleanNumber($row[20]),
                        'pppk_pw_smp'   => ExcelUploader::cleanNumber($row[21]),
                        'pppk_pw_sma'   => ExcelUploader::cleanNumber($row[22]),
                        'pppk_pw_d1'    => ExcelUploader::cleanNumber($row[23]),
                        'pppk_pw_d2'    => ExcelUploader::cleanNumber($row[24]),
                        'pppk_pw_d3'    => ExcelUploader::cleanNumber($row[25]),
                        'pppk_pw_s1'    => ExcelUploader::cleanNumber($row[26]),
                        'pppk_pw_s2'    => ExcelUploader::cleanNumber($row[28]),
                        'pppk_pw_s3'    => ExcelUploader::cleanNumber($row[29]),
                        'jumlah'        => ExcelUploader::cleanNumber($row[30]),                        
                        'tanggal'       => $syncdate
                    ];
                };
            case 'Usia ASN':                
                return function ($row) use ($insertID, $syncdate) {
                    return [
                        'asn_log_id'    => $insertID,
                        'instansi_id'   => $row[0],
                        'pns_kurang_sama_31'    => ExcelUploader::cleanNumber($row[2]),
                        'pns_31_40'             => ExcelUploader::cleanNumber($row[3]),
                        'pns_41_50'             => ExcelUploader::cleanNumber($row[4]),
                        'pns_lebih_sama_51'     => ExcelUploader::cleanNumber($row[5]),
                        'pppk_kurang_sama_31'   => ExcelUploader::cleanNumber($row[6]),
                        'pppk_31_40'            => ExcelUploader::cleanNumber($row[7]),
                        'pppk_41_50'            => ExcelUploader::cleanNumber($row[8]),
                        'pppk_lebih_sama_51'    => ExcelUploader::cleanNumber($row[9]),
                        'pppk_pw_kurang_sama_31'=> ExcelUploader::cleanNumber($row[10]),
                        'pppk_pw_31_40'         => ExcelUploader::cleanNumber($row[11]),
                        'pppk_pw_41_50'         => ExcelUploader::cleanNumber($row[12]),
                        'pppk_pw_lebih_sama_51' => ExcelUploader::cleanNumber($row[13]),
                        'jumlah'                => ExcelUploader::cleanNumber($row[14]),                        
                        'tanggal'       => $syncdate
                    ];
                };
            case 'Generasi ASN':                
                return function ($row) use ($insertID, $syncdate) {
                    return [
                        'asn_log_id'            => $insertID,
                        'instansi_id'           => $row[0],
                        'pns_pppk_baby_boomer'  => ExcelUploader::cleanNumber($row[2]),
                        'pns_pppk_gen_x'        => ExcelUploader::cleanNumber($row[3]),
                        'pns_pppk_gen_y'        => ExcelUploader::cleanNumber($row[4]),
                        'pns_pppk_gen_z'        => ExcelUploader::cleanNumber($row[5]),
                        'pppk_pw_baby_boomer'   => ExcelUploader::cleanNumber($row[6]),
                        'pppk_pw_gen_x'         => ExcelUploader::cleanNumber($row[7]),
                        'pppk_pw_gen_y'         => ExcelUploader::cleanNumber($row[8]),
                        'pppk_pw_gen_z'         => ExcelUploader::cleanNumber($row[9]),
                        'jumlah'                => ExcelUploader::cleanNumber($row[10]),                        
                        'tanggal'               => $syncdate
                    ];
                };     
            case 'Kelompok Jabatan ASN':                
                return function ($row) use ($insertID, $syncdate) {
                    return [
                        'asn_log_id'            => $insertID,
                        'instansi_id'           => $row[0],
                        // PNS
                        'pns_jpt_madya'            => ExcelUploader::cleanNumber($row[2]),
                        'pns_jpt_pratama'          => ExcelUploader::cleanNumber($row[3]),
                        'pns_administrator'        => ExcelUploader::cleanNumber($row[4]),
                        'pns_pengawas'             => ExcelUploader::cleanNumber($row[5]),
                        'pns_eselon_v'             => ExcelUploader::cleanNumber($row[6]),
                        'pns_struktural_kosong'    => ExcelUploader::cleanNumber($row[7]),
                        'pns_jf_guru'              => ExcelUploader::cleanNumber($row[8]),
                        'pns_jf_nakes'             => ExcelUploader::cleanNumber($row[9]),
                        'pns_jf_teknis'            => ExcelUploader::cleanNumber($row[10]),
                        'pns_jf_kosong'            => ExcelUploader::cleanNumber($row[11]),
                        'pns_pelaksana'            => ExcelUploader::cleanNumber($row[12]),
                        // PPPK
                        'pppk_jpt_madya'           => ExcelUploader::cleanNumber($row[13]),
                        'pppk_jpt_pratama'         => ExcelUploader::cleanNumber($row[14]),
                        'pppk_administrator'       => ExcelUploader::cleanNumber($row[15]),
                        'pppk_pengawas'            => ExcelUploader::cleanNumber($row[16]),
                        'pppk_eselon_v'            => ExcelUploader::cleanNumber($row[17]),
                        'pppk_struktural_kosong'   => ExcelUploader::cleanNumber($row[18]),
                        'pppk_jf_guru'             => ExcelUploader::cleanNumber($row[19]),
                        'pppk_jf_nakes'            => ExcelUploader::cleanNumber($row[20]),
                        'pppk_jf_teknis'           => ExcelUploader::cleanNumber($row[21]),
                        'pppk_jf_kosong'           => ExcelUploader::cleanNumber($row[22]),
                        'pppk_pelaksana'           => ExcelUploader::cleanNumber($row[23]),
                        // PPPK PW
                        'pppk_pw_jf'               => ExcelUploader::cleanNumber($row[24]),
                        'pppk_pw_pelaksana'        => ExcelUploader::cleanNumber($row[25]),
                        'jumlah'                => ExcelUploader::cleanNumber($row[26]),                        
                        'tanggal'               => $syncdate
                    ];
                };
            case 'Masa Kerja ASN':
                 return function ($row) use ($insertID, $syncdate) {
                    return [
                        'asn_log_id'            => $insertID,
                        'instansi_id'           => $row[0],
                        'masa_kerja_0_10'      => ExcelUploader::cleanNumber($row[2]),
                        'masa_kerja_11_20'     => ExcelUploader::cleanNumber($row[3]),
                        'masa_kerja_21_30'     => ExcelUploader::cleanNumber($row[4]),
                        'masa_kerja_lebih_30'  => ExcelUploader::cleanNumber($row[5]),
                        'masa_kerja_pw'        => ExcelUploader::cleanNumber($row[6]),
                        'jumlah'               => ExcelUploader::cleanNumber($row[7]),                    
                        'tanggal'               => $syncdate                        
                    ];
                };

            default:
                throw new \Exception('Jenis tidak dikenali.');
        }
    }

    private function getTableForJenis($jenis)
    {
        switch ($jenis) {
            case 'Jumlah ASN':
                return 'txn_asn_jumlahs';
            case 'Golongan ASN':
                return 'txn_asn_golongans';
            case 'Jenis Kelamin ASN':
                return 'txn_asn_jenis_kelamins';
            case 'Pendidikan ASN':
                return 'txn_asn_pendidikans';
            case 'Usia ASN':
                return 'txn_asn_usias';  
            case 'Generasi ASN':
                return 'txn_asn_generasis';   
            case 'Kelompok Jabatan ASN':
                return 'txn_asn_kelompok_jabatans';     
            case 'Masa Kerja ASN':
                return 'txn_asn_masa_kerjas';                    
            default:
                throw new \Exception('Jenis tidak dikenali.');
        }
    }

    public function removeData(){
        $key   = trim((string) $this->request->getPost('key'));
        $jenis = trim((string) $this->request->getPost('jenis'));
        if ($key === '' || $jenis === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => false,
                'message' => 'Parameter penghapusan tidak valid',
            ]);
        }

        $table = $this->getTableForJenis($jenis);
        $this->apps->removeData($key,'txn_asn');
        $this->apps->removeDataLogStatistik($key,$table);
        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Data Berhasil di hapus',
        ]);
    }

    public function getData(){
        $jenis = trim((string) $this->request->getPost('jenis'));
        if (!in_array($jenis, self::JENIS_LIST, true)) {
            return $this->response->setJSON([
                'draw' => (int) ($this->request->getPost('draw') ?? 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }

        $builder    = $this->stkmodel->getBuilder('recap', $jenis);
        $columns    = $this->stkmodel->getColumns('recap', $jenis);
        $result     = $this->dataTables->render($builder, $columns);
        return $this->response->setJSON($result);       
    }    

    public function getDataDetail(){
        $key        = (int) $this->request->getPost('key');
        if ($key <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'error' => 'Kunci detail tidak valid'
            ]);
        }

        $builder    = $this->stkmodel->getBuilder('detail', $key);
        $columns    = $this->stkmodel->getColumns('detail', $key);
        $result     = $this->dataTables->render($builder, $columns);
        return $this->response->setJSON($result); 
    }

    public function getSummary()
    {
        $jenis = trim((string) $this->request->getPost('jenis'));
        if (!in_array($jenis, self::JENIS_LIST, true)) {
            return $this->response->setJSON([
                'status' => true,
                'summary' => [
                    'total_upload' => 0,
                    'total_data' => 0,
                    'total_instansi' => 0,
                    'total_periode' => 0,
                    'last_update' => null,
                ],
            ]);
        }

        return $this->response->setJSON([
            'status' => true,
            'summary' => $this->stkmodel->getSummary($jenis),
        ]);
    }


}


