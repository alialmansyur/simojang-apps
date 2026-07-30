<?php

namespace App\Models\Apps\Services;

use CodeIgniter\Model;

class TKHModel extends Model
{
    protected $table            = 'txn_takah';
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

        $builder = $this->db->table('txn_takah a')
            ->select('a.*, COUNT(b.id) total')
            ->join('txn_takah_detail b', 'b.takah_log_id = a.id', 'left')
            ->where('a.created_at >=', date('Y-01-01 00:00:00'))
            ->where('a.created_at <', (date('Y') + 1) . '-01-01 00:00:00')
            ->groupBy('a.id')
            ->orderBy('a.created_at', 'DESC');

        // 🔹 FILTER BULAN (IN)
        if (!empty($bulan)) {
            $builder->whereIn('MONTH(a.created_at)', $bulan);
        }

        return $builder;
    }
 
    public function getDataDetail($param){
        return $this->db->table('txn_takah a')
            ->select('
                a.id AS log_id,
                b.*,
                c.logo,
                c.kodeins AS kode_instansi,
                c.nama AS nama_instansi,
                a.created_by AS upload_by,
                b.created_at AS tanggal_upload
            ')
            ->join('txn_takah_detail b', 'b.takah_log_id = a.id', 'left')
            ->join('data_instansi c', 'c.kodeins = b.instansi_id', 'left')
            ->where('b.instansi_id IS NOT NULL', null, false)
            ->where('a.id', (int) $param);
    }

    public function getSummary($bulan = [])
    {
        $builder = $this->db->table('txn_takah a');
        $builder->select("
            COUNT(DISTINCT a.id) AS total_upload,
            COUNT(DISTINCT b.nip) AS total_nip,
            COUNT(DISTINCT b.instansi_id) AS total_instansi,
            SUM(
                COALESCE(b.d2nip,0) + COALESCE(b.ijazah,0) + COALESCE(b.akta,0) + COALESCE(b.drh,0) +
                COALESCE(b.cpns,0) + COALESCE(b.pns,0) + COALESCE(b.perubahan,0) + COALESCE(b.kp,0) +
                COALESCE(b.jabatan,0) + COALESCE(b.berhenti,0) + COALESCE(b.pensiun,0)
            ) AS total_dokumen,
            MAX(COALESCE(a.updated_at, a.created_at)) AS last_update
        ", false);
        $builder->join('txn_takah_detail b', 'b.takah_log_id = a.id', 'left');
        $builder->where('a.created_at >=', date('Y-01-01 00:00:00'));
        $builder->where('a.created_at <', (date('Y') + 1) . '-01-01 00:00:00');

        if (is_array($bulan) && !empty($bulan)) {
            $builder->whereIn('MONTH(a.created_at)', $bulan);
        }

        return $builder->get()->getRowArray() ?? [
            'total_upload' => 0,
            'total_nip' => 0,
            'total_instansi' => 0,
            'total_dokumen' => 0,
            'last_update' => null,
        ];
    }    

    // public function getDataRecap($param){
    //     $rawSql = "
    //         SELECT 
    //         a.*, b.nama nama_instansi
    //         FROM txn_takah a
    //         LEFT JOIN data_instansi b ON b.kodeins = a.kode_instansi
    //         WHERE a.tanggal_proses = CURDATE() AND a.created_by = '$param'
    //     ";
    //     return $this->db->table("($rawSql) AS recap");
    // }

    // public function getDataRecapUpload($param){
    //     $rawSql = "
    //         SELECT 
    //         a.*, b.nama nama_instansi
    //         FROM txn_takah a
    //         LEFT JOIN data_instansi b ON b.kodeins = a.kode_instansi
    //         WHERE a.skema = 'upload' AND a.tanggal_proses = CURDATE() AND a.created_by = '$param'
    //     ";
    //     return $this->db->table("($rawSql) AS recap");
    // }

    // public function getResumeData($param){
    //     $rawSql = "
    //         SELECT 
    //             (SELECT COUNT(DISTINCT nip) FROM txn_takah WHERE MONTH(tanggal_proses) = MONTH(CURDATE()) AND created_by = ? ) total,
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
    //         FROM txn_takah a
    //         WHERE a.tanggal_proses = CURDATE() AND a.created_by = ?
    //         ";
    //     return $this->db->query($rawSql, [$param, $param])->getRow();
    // }

    // public function getDailyData($param){
    //     $rawSql = "
    //         SELECT 
    //             DATE_FORMAT(a.tanggal_proses, '%Y-%m-%dT00:00:00.000Z') tanggal, COUNT(1) total
    //         FROM txn_takah a
    //         LEFT JOIN data_instansi b ON b.kodeins = a.kode_instansi
    //         WHERE a.created_by = ? AND WEEK(tanggal_proses) = WEEK(CURDATE())
    //         GROUP BY a.tanggal_proses
    //         ORDER BY a.tanggal_proses asc
    //     ";
    //     return $this->db->query($rawSql, [$param])->getResultArray();
    // }

    // public function getRefInstansi($param){
    //     $rawSql = "
    //         SELECT 
    //             LPAD(IFNULL(MAX(CAST(a.no_ref AS UNSIGNED)), 0) + 1, 5, '0') AS last
    //         FROM txn_takah a
    //         WHERE a.kode_instansi = 6108
    //     ";
    //     return $this->db->query($rawSql, [$param])->getRow();
    // }

}
