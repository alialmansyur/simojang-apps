<?php

namespace App\Controllers\Apps\Modules;
use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\Apps\AppsModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Libraries\ExcelUploader;
use App\Libraries\DataTablesLib;

class ImpExlsController extends BaseController
{
    public function __construct()
    {
        $this->apps = new AppsModel();
        $this->uploader = new ExcelUploader();
        $this->dataTables = new DataTablesLib();
        $sess = session()->get();
    }

    public function importData(){
        $sess = session()->get();

        $file = $this->request->getFile('file');
        $docType = $this->request->getPost('doc_type') ?? 'Unknown';
        $layananId = $this->request->getPost('layanan_id') ?? '';
        $period = $this->request->getPost('period') ?? '';
        $syncdate1  = $this->request->getPost('syncdate1');
        $syncdate2  = $this->request->getPost('syncdate2');
        $remaks = $this->request->getPost('remarks') ?? '';
        $docCategory = $this->request->getPost('doc_category') ?? '';

        $this->uploader->validateFile($file);
        $uploadDir = ROOTPATH . 'public/uploads/excel/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $originalName = $file->getClientName();
        $ext = $file->getClientExtension();
        $savedName = date('Ymd_His') . '_' . random_string('alnum', 8) . '_' . $sess['username'] . '.' . $ext;
        $localPath = $uploadDir . $savedName;

        try {
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $file->move($uploadDir, $savedName);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }

        if (!file_exists($localPath)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'File tidak tersimpan pada path tidak ditemukan.'
            ]);
        }

        $fileSize = filesize($localPath);
        $mimeType = $file->getClientMimeType();
        $checksum = hash_file('sha256', $localPath);
        $ipAddress = $this->request->getIPAddress();
        $userAgent = $this->request->getUserAgent() ? $this->request->getUserAgent()->getAgentString() : null;

        $logData = [
            'period'            => $period,
            'period_date'       => $syncdate1,
            'period_start_date' => $syncdate1,
            'period_end_date'   => $syncdate2,            
            'layanan_id'   => $layananId,
            'doc_type'     => $docType,
            'doc_category' => $docCategory,
            'file_name'    => $savedName,
            'file_size'    => $fileSize,
            'mime_type'    => $mimeType,
            'path_local'   => $localPath, 
            'checksum'     => $checksum,
            'ip_address'   => $ipAddress,
            'user_agent'   => $userAgent,
            'remarks'      => $remaks,
            'created_by'   => $sess['username'],
            'created_at'   => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s')
        ];

        $logID = $this->apps->storeData($logData, 'txn_activity_upload_logs');
        $rows = $this->uploader->parseExcel($localPath);

        if (!is_array($rows) || count($rows) < 1) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data kosong atau format salah.',
            ]);
        }

        $mappingFunction = [];
        if ($layananId == 21) {
            $mappingFunction = $this->getMapperForStatistik($docCategory, $logID, $syncdate);
            $table = $this->getTableForJenis($docCategory);
        }elseif ($layananId == 24) {
            $mappingFunction = $this->getMapperForJenis($layananId, $sess['username'],$logID);
            $table = 'txn_activity_integrasi';            
        }else{
            $mappingFunction = $this->getMapperForJenis($layananId, $sess['username'],$logID);
            $table = 'txn_activity_upload_detail';
        }
        
        $dataBatch = [];
        foreach (array_slice($rows, 2) as $row) {
            if (empty($row[0])) continue;
            $mapped = $mappingFunction($row);
            if (isset($mapped[0])) {
                $dataBatch = array_merge($dataBatch, $mapped);
            } else {
                $dataBatch[] = $mapped;
            }
        }

        if ($dataBatch) { 
            $this->apps->insertBatchData($dataBatch, $table); 
            $this->apps->storeData(
                [
                    'layanan_id' => $layananId,
                    'tanggal'    => date('Y-m-d'),
                    'created_by' => $sess['username']
                ],
                'activity_daily_logs'
            );
        }
        
        return $this->response->setJSON([
            'status'   => 'success',
            'message'  => 'Upload dan import data berhasil.',
        ]);
    }

    private function getMapperForJenis($jenis, $sessname, $logID){
        if ($jenis === '2') {
            return function ($row) use ($sessname, $logID) {
                $instansiID = $this->apps->getInstansiID($row[1]);
                return [
                    'upload_id'         => $logID,
                    'instansi_id'       => $instansiID,
                    'formasi'           => $row[2],
                    'belum_terisi'      => $row[3],
                    'pembatalan_pertek' => $row[4],
                    'usul_masuk'        => $row[5],
                    'ms'                => $row[6],
                    'bts'               => $row[7],
                    'tms'               => $row[8],
                    'sisa'              => $row[11],
                    'sudah_cetak'       => $row[13],
                    'belum_cetak'       => $row[15],
                    // 'serah_terima_sk'  => ExcelUploader::excelDate($row[9]),
                    'keterangan'        => $row[17],
                    'sk_cpppk_proses' => !empty($row[18]) ? 'Y' : 'N',
                    'sk_cpppk_done'   => !empty($row[19]) ? 'Y' : 'N',
                    'usul_input'      => !empty($row[20]) ? 'Y' : 'N',
                    'ni_proses'       => !empty($row[21]) ? 'Y' : 'N',
                    'ni_done'         => !empty($row[22]) ? 'Y' : 'N',
                    'sk_cetak_proses' => !empty($row[23]) ? 'Y' : 'N',
                    'sk_cetak_done'   => !empty($row[24]) ? 'Y' : 'N',
                    'jadwal_wait'     => !empty($row[25]) ? 'Y' : 'N',
                    'sk_pppk_done'    => !empty($row[26]) ? 'Y' : 'N',
                    'created_by'        => $sessname,
                ];
            };
        }

        if (in_array($jenis, ['3','4','5','6','7','8','9','10','11','12'])) {
            return function ($row) use ($sessname, $logID) {
                $instansiID = $this->apps->getInstansiID($row[1]);
                return [
                    'upload_id'    => $logID,
                    'instansi_id'  => $instansiID,
                    'target_tahun' => $row[2],
                    'target_bulan' => $row[3],
                    'usul_masuk'   => $row[4],
                    'ms'           => $row[5],
                    'bts'          => $row[6],
                    'tms'          => $row[7],
                    'sisa'         => $row[8],
                    'sla_bawah'    => $row[9],
                    'sla_atas'     => $row[10],
                    'keterangan'   => $row[11],
                    'created_by'   => $sessname,
                ];
            };
        }

        if (in_array($jenis, ['24'])) {
            return function ($row) use ($sessname, $logID) {
                $instansiID = $this->apps->getInstansiID($row[2]);

                $params = [];
                $paramMapping = [
                    // 4  => 'Riwayat Jabatan',
                    // 6  => 'Riwayat Diklat',
                    // 12 => 'Riwayat Hukuman Disiplin',
                    // 22 => 'Riwayat CPNS',
                    // 20 => 'Riwayat Penghargaan',
                    // 14 => 'Riwayat Angka Kredit',
                    // 3  => 'Riwayat Kinerja',
                    // 31 => 'Riwayat Data Profil',
                    4  => 1,
                    6  => 2,
                    12 => 3,
                    22 => 4,
                    20 => 5,
                    14 => 6,
                    3  => 7,
                    24 => 8,                    
                ];
                            
                foreach ($paramMapping as $colIndex => $paramName) {
                    if (isset($row[$colIndex]) && $row[$colIndex] === 'TRUE') { //inii karena nilainya emang text ya "TRUE" bukan boolean
                        $params[] = [
                            'upload_id'   => $logID,
                            'instansi_id' => $instansiID,
                            'jenis_integrasi_id'   => $paramName,
                            'created_by'  => $sessname,
                        ];
                    }
                }

                return $params;
            };
        }

        throw new \Exception('Jenis tidak dikenali.');
    }

    private function getMapperForStatistik($jenis, $insertID, $syncdate){
        switch ($jenis) {
            case 'Jumlah ASN':
                return function ($row) use ($insertID, $syncdate) {
                    return [
                        'log_input_id'  => $insertID,
                        'kode_instansi' => $row[0],
                        'pns'           => ExcelUploader::cleanNumber($row[2]),
                        'pppk'          => ExcelUploader::cleanNumber($row[3]),
                        'jumlah'        => ExcelUploader::cleanNumber($row[4]),
                        'tanggal'       => $syncdate
                    ];
                };

            case 'Golongan ASN':
                return function ($row) use ($insertID, $syncdate) {
                    return [
                        'log_input_id'  => $insertID,
                        'kode_instansi' => $row[0],
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
                        'jumlah'        => ExcelUploader::cleanNumber($row[23]),
                        'tanggal'       => $syncdate
                    ];
                };

            case 'Jenis Kelamin ASN':
                return function ($row) use ($insertID, $syncdate) {
                    return [
                        'log_input_id'  => $insertID,
                        'kode_instansi' => $row[0],
                        'pns_pria'      => ExcelUploader::cleanNumber($row[2]),
                        'pns_wanita'    => ExcelUploader::cleanNumber($row[3]),
                        'pppk_pria'     => ExcelUploader::cleanNumber($row[4]),
                        'pppk_wanita'   => ExcelUploader::cleanNumber($row[5]),
                        'jumlah'        => ExcelUploader::cleanNumber($row[6]),
                        'tanggal'       => $syncdate
                    ];
                };
            case 'Pendidikan ASN':
                return function ($row) use ($insertID, $syncdate) {
                    return [
                        'log_input_id'  => $insertID,
                        'kode_instansi' => $row[0],
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
                        'jumlah'        => ExcelUploader::cleanNumber($row[20]),
                        'tanggal'       => $syncdate
                    ];
                };
            case 'Usia ASN':                
                return function ($row) use ($insertID, $syncdate) {
                    return [
                        'log_input_id'  => $insertID,
                        'kode_instansi' => $row[0],
                        'pns_kurang_sama_31'    => ExcelUploader::cleanNumber($row[2]),
                        'pns_31_40'             => ExcelUploader::cleanNumber($row[3]),
                        'pns_41_50'             => ExcelUploader::cleanNumber($row[4]),
                        'pns_lebih_sama_51'     => ExcelUploader::cleanNumber($row[5]),
                        'pppk_kurang_sama_31'   => ExcelUploader::cleanNumber($row[6]),
                        'pppk_31_40'            => ExcelUploader::cleanNumber($row[7]),
                        'pppk_41_50'            => ExcelUploader::cleanNumber($row[8]),
                        'pppk_lebih_sama_51'    => ExcelUploader::cleanNumber($row[9]),
                        'jumlah'                => ExcelUploader::cleanNumber($row[10]),
                        'tanggal'       => $syncdate
                    ];
                };

            default:
                throw new \Exception('Jenis tidak dikenali.');
        }
    }

    private function getTableForJenis($jenis){
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
            default:
                throw new \Exception('Jenis tidak dikenali.');
        }
    }  

}