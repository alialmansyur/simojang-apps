<?php

namespace App\Models\Apps\Services;

use CodeIgniter\Model;

class EKINModel extends Model
{
    protected $table            = 'txn_ekin'; 
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function getDataLog(){
        $builder = $this->db->table($this->table);
        return $builder;
    }

    // ----------------------------
    //  QUERY BUILDER UTAMA
    // ----------------------------    
    public function getBuilder($type, $params = null){
        switch ($type) {
            case 'recap':
                return $this->getDataRecap($params);
            case 'detail':
                return $this->getDataDetail($params);
            default:
                throw new \Exception("Unknown builder type: $type");
        }
    }    

    // ----------------------------
    //  DAPATKAN NAMA KOLOM OTOMATIS
    // ----------------------------    
    public function getColumns($type, $id = null){
        $builder = $this->getBuilder($type, $id);
        $query = $builder->get();
        return $query->getFieldNames();
    }    

    public function getDataRecap($params = [])
    {
        $bulan = $params['bulan'] ?? [];

        $rawSql = "
            SELECT 
                a.period_date,
                DATE_FORMAT(a.period_date, '%d %M %Y') AS period_date_label,
                COUNT(DISTINCT a.id) AS total_upload,
                COUNT(DISTINCT h.sub_unit) AS total_sub_unit,
                COUNT(DISTINCT d.nip) AS total_nip,
                COUNT(d.id) AS total_kegiatan,
                SUM(CASE WHEN d.realisasi >= 1 THEN 1 ELSE 0 END) AS total_realisasi,
                MAX(a.created_at) AS last_upload_at
            FROM txn_ekin a
            LEFT JOIN txn_ekin_header h ON h.ekin_log_id = a.id
            LEFT JOIN txn_ekin_detail d ON d.ekin_header_id = h.id
            WHERE a.created_at >= CONCAT(YEAR(CURDATE()), '-01-01 00:00:00')
              AND a.created_at < CONCAT(YEAR(CURDATE()) + 1, '-01-01 00:00:00')
            GROUP BY a.period_date
        ";
        $builder = $this->db->table("($rawSql) AS recap");
        if (!empty($bulan)) {
            $builder->whereIn('MONTH(period_date)', $bulan);
        }
        $builder->orderBy('period_date', 'DESC');
        $builder->orderBy('last_upload_at', 'DESC');
        return $builder;
    }

    public function getChildByPeriodDate(string $periodDate): array
    {
        return $this->db->table('txn_ekin a')
            ->select("
                a.id AS ekin_key,
                a.period,
                a.period_date,
                a.period_start_date,
                a.period_end_date,
                GROUP_CONCAT(DISTINCT h.sub_unit SEPARATOR ' | ') AS sub_unit,
                MAX(h.periode) AS periode,
                MAX(h.tanggal_kegiatan) AS tanggal_kegiatan,
                COUNT(DISTINCT d.nip) AS total_nip,
                COUNT(d.id) AS total_kegiatan,
                SUM(CASE WHEN d.realisasi >= 1 THEN 1 ELSE 0 END) AS total_realisasi,
                a.created_by,
                a.created_at
            ", false)
            ->join('txn_ekin_header h', 'h.ekin_log_id = a.id', 'left')
            ->join('txn_ekin_detail d', 'd.ekin_header_id = h.id', 'left')
            ->where('a.period_date', $periodDate)
            ->groupBy('a.id')
            ->orderBy('a.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function getSummary(array $bulan = [])
    {
        $builder = $this->db->table('txn_ekin a')
            ->select('
                COUNT(1) AS total_data,
                COUNT(DISTINCT a.period_date) AS total_harian,
                SUM(COALESCE(x.total_nip, 0)) AS total_nip,
                SUM(COALESCE(x.total_realisasi, 0)) AS total_realisasi,
                MAX(a.created_at) AS last_update
            ')
            ->join('(
                SELECT 
                    eh.ekin_log_id,
                    COUNT(DISTINCT ed.nip) AS total_nip,
                    SUM(CASE WHEN ed.realisasi >= 1 THEN 1 ELSE 0 END) AS total_realisasi
                FROM txn_ekin_header eh
                LEFT JOIN txn_ekin_detail ed ON ed.ekin_header_id = eh.id
                GROUP BY eh.ekin_log_id
            ) x', 'x.ekin_log_id = a.id', 'left');

        $builder->where('a.created_at >=', date('Y-01-01 00:00:00'));
        $builder->where('a.created_at <', (date('Y') + 1) . '-01-01 00:00:00');

        if (!empty($bulan)) {
            $builder->whereIn('MONTH(a.period_date)', $bulan);
        }

        return $builder->get()->getRowArray();
    }

    public function getDataDetail($param){
        return $this->db->table('txn_ekin_detail d')
            ->select('
                d.id,
                d.ekin_log_id,
                d.ekin_header_id,
                d.nip,
                p.nama,
                d.waktu,
                d.kegiatan,
                d.realisasi,
                d.created_by,
                d.created_at,
                h.sub_unit,
                h.periode,
                h.tanggal_kegiatan
            ')
            ->join('txn_ekin_header h', 'h.id = d.ekin_header_id', 'left')
            ->join('data_pegawai p', 'p.nip = d.nip', 'left')
            ->groupStart()
                ->where('d.ekin_log_id', $param)
                ->orWhere('h.ekin_log_id', $param)
            ->groupEnd()
            ->orderBy('d.id', 'ASC');
    }    

    // public function getDataRecap($param){
    //     $rawSql = "
    //         SELECT 
    //         a.*, b.nama nama_instansi
    //         FROM txn_dms a
    //         LEFT JOIN data_instansi b ON b.kodeins = a.kode_instansi
    //         WHERE a.tanggal_proses = CURDATE() AND a.created_by = '$param'
    //     ";
    //     return $this->db->table("($rawSql) AS recap");
    // }

    // public function getDataRecapV2($param){
    //     $rawSql = "
    //         SELECT  
    //         a.*, b.nama nama_instansi, b.logo
    //         FROM txn_activity_dms a
    //         LEFT JOIN data_instansi b ON b.kodeins = a.kode_instansi
    //         WHERE a.tanggal_proses = CURDATE() AND a.created_by = '$param'
    //     ";
    //     return $this->db->table("($rawSql) AS recap");
    // }

    // public function getDataRecapUpload($param){
    //     $rawSql = "
    //         SELECT 
    //         a.*, b.nama nama_instansi, b.logo
    //         FROM txn_dms a
    //         LEFT JOIN data_instansi b ON b.kodeins = a.kode_instansi
    //         WHERE a.skema = 'upload' AND a.tanggal_proses = CURDATE() AND a.created_by = '$param'
    //     ";
    //     return $this->db->table("($rawSql) AS recap");
    // }    

    // public function getResumeData($param){
    //     $rawSql = "
    //         SELECT 
    //             (SELECT COUNT(DISTINCT nip) FROM txn_dms WHERE MONTH(tanggal_proses) = MONTH(CURDATE()) AND created_by = ? ) total,
    //             COUNT(DISTINCT a.kode_instansi) AS total_instansi,
    //             COUNT(DISTINCT a.nip) AS total_nip,
    //             (
    //                 SUM(CASE WHEN a.d2nip IS NOT NULL AND a.d2nip != '' THEN 1 ELSE 0 END) +
    //                 SUM(CASE WHEN a.ijazah IS NOT NULL AND a.ijazah != '' THEN 1 ELSE 0 END) +
    //                 SUM(CASE WHEN a.akta IS NOT NULL AND a.akta != '' THEN 1 ELSE 0 END) +
    //                 SUM(CASE WHEN a.drh IS NOT NULL AND a.drh != '' THEN 1 ELSE 0 END) +
    //                 SUM(CASE WHEN a.cpns IS NOT NULL AND a.cpns != '' THEN 1 ELSE 0 END) +
    //                 SUM(CASE WHEN a.pns IS NOT NULL AND a.pns != '' THEN 1 ELSE 0 END) +
    //                 SUM(CASE WHEN a.perubahan IS NOT NULL AND a.perubahan != '' THEN 1 ELSE 0 END) +
    //                 SUM(CASE WHEN a.kp IS NOT NULL AND a.kp != '' THEN 1 ELSE 0 END) +
    //                 SUM(CASE WHEN a.jabatan IS NOT NULL AND a.jabatan != '' THEN 1 ELSE 0 END) +
    //                 SUM(CASE WHEN a.berhenti IS NOT NULL AND a.berhenti != '' THEN 1 ELSE 0 END) +
    //                 SUM(CASE WHEN a.pensiun IS NOT NULL AND a.pensiun != '' THEN 1 ELSE 0 END)
    //             ) AS total_dokumen
    //         FROM txn_dms a
    //         WHERE a.tanggal_proses = CURDATE() AND a.created_by = ?
    //         ";
    //     return $this->db->query($rawSql, [$param, $param])->getRow();
    // }

    // public function getDailyData($param){
    //     $rawSql = "
    //         SELECT 
    //             DATE_FORMAT(a.tanggal_proses, '%Y-%m-%dT00:00:00.000Z') tanggal, COUNT(1) total
    //         FROM txn_dms a
    //         LEFT JOIN data_instansi b ON b.kodeins = a.kode_instansi
    //         WHERE a.created_by = ? AND WEEK(tanggal_proses) = WEEK(CURDATE())
    //         GROUP BY a.tanggal_proses
    //         ORDER BY a.tanggal_proses asc
    //     ";
    //     return $this->db->query($rawSql, [$param])->getResultArray();
    // }

}
