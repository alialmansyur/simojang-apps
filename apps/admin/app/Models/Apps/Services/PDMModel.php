<?php

namespace App\Models\Apps\Services;

use CodeIgniter\Model;

class PDMModel extends Model 
{
    protected $table            = 'txn_pdm';
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
                return $this->getDataRecapV2($params);
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


    public function getDataRecap($param){
        $rawSql = "
            SELECT 
            a.*, b.nama nama_instansi
            FROM txn_pdm a
            LEFT JOIN data_instansi b ON b.kodeins = a.kode_instansi
            WHERE a.tanggal_proses = CURDATE() AND a.created_by = '$param'
        ";
        return $this->db->table("($rawSql) AS recap");
    }

    public function getDataRecapV2($params = [])
    {
        $bulan = $params['bulan'] ?? [];
        $builder = $this->db->table('txn_pdm')
            ->select('*')
            ->where('created_at >=', date('Y-01-01 00:00:00'))
            ->where('created_at <', (date('Y') + 1) . '-01-01 00:00:00');

        // 🔹 FILTER BULAN (IN)
        if (!empty($bulan)) {
            $builder->whereIn('MONTH(created_at)', $bulan);
        }

        $builder->orderBy('created_at', 'DESC');

        return $builder;
    }


    public function getResumeData($param){
        $rawSql = "
            SELECT 
                COUNT(1) total,
                SUM(CASE WHEN a.tindak_lanjut = 'Disetujui' THEN 1 ELSE 0 END) AS acc,
                SUM(CASE WHEN a.tindak_lanjut = 'BTS' THEN 1 ELSE 0 END) AS bts,
                SUM(CASE WHEN a.tindak_lanjut = 'TMS' THEN 1 ELSE 0 END) AS tms
            FROM txn_pdm a
            LEFT JOIN data_instansi b ON b.kodeins = a.kode_instansi
                WHERE a.tanggal_proses = CURDATE() AND a.created_by = ?
            ";
        return $this->db->query($rawSql, [$param])->getRow();
    }

    public function getDailyData($param){
        $rawSql = "
            SELECT 
                DATE_FORMAT(a.tanggal_proses, '%Y-%m-%dT00:00:00.000Z') tanggal, COUNT(1) total
            FROM txn_pdm a
            LEFT JOIN data_instansi b ON b.kodeins = a.kode_instansi
            WHERE WEEK(a.tanggal_proses) = WEEK(CURDATE()) AND a.created_by = ?
            GROUP BY a.tanggal_proses
            ORDER BY a.tanggal_proses asc
        ";
        return $this->db->query($rawSql, [$param])->getResultArray();
    }

    public function getSummary($bulan = [])
    {
        $builder = $this->db->table('txn_pdm');
        $builder->select("
            COUNT(DISTINCT scema_group) AS total_skema,
            COUNT(id) AS total_data,
            SUM(COALESCE(total_acc, 0)) AS total_acc,
            SUM(COALESCE(total_btl, 0)) AS total_btl,
            SUM(COALESCE(total_tms, 0)) AS total_tms,
            MAX(COALESCE(updated_at, created_at)) AS last_update
        ", false);
        $builder->where('created_at >=', date('Y-01-01 00:00:00'));
        $builder->where('created_at <', (date('Y') + 1) . '-01-01 00:00:00');

        if (is_array($bulan) && !empty($bulan)) {
            $builder->whereIn('MONTH(created_at)', $bulan);
        }

        return $builder->get()->getRowArray() ?? [
            'total_skema' => 0,
            'total_data' => 0,
            'total_acc' => 0,
            'total_btl' => 0,
            'total_tms' => 0,
            'last_update' => null,
        ];
    }

}
