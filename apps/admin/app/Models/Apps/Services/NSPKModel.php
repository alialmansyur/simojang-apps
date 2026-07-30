<?php

namespace App\Models\Apps\Services;

use CodeIgniter\Model;

class NSPKModel extends Model
{
    protected $table = 'txn_nspk';

    public function __construct(){
        parent::__construct();
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
        $query = $builder->limit(1)->get();
        return $query->getFieldNames();
    }    

    public function getDataRecap($params = [])
    {
        $bulan = $params['bulan'] ?? [];

        $builder = $this->db->table('txn_nspk a')
            ->select('a.*, b.kodeins, b.nama instansi_name, b.kanreg, UPPER(b.wilayah) wilayah, b.logo')
            ->join('data_instansi b', 'b.kodeins = a.instansi_id', 'left')
            ->where('a.created_at >=', date('Y-01-01 00:00:00'))
            ->where('a.created_at <', (date('Y') + 1) . '-01-01 00:00:00')
            ->orderBy('a.created_at', 'DESC');

        // 🔹 FILTER BULAN (IN)
        if (!empty($bulan)) {
            $builder->whereIn('MONTH(a.created_at)', $bulan);
        }

        return $builder;
    }

    public function getSummary(array $bulan = [])
    {
        $builder = $this->db->table('txn_nspk a')
            ->select('
                COUNT(1) AS total_data,
                COUNT(DISTINCT a.instansi_id) AS total_instansi,
                SUM(CASE WHEN a.level = "A" THEN 1 ELSE 0 END) AS level_a,
                SUM(CASE WHEN a.level = "B" THEN 1 ELSE 0 END) AS level_b,
                SUM(CASE WHEN a.level = "C" THEN 1 ELSE 0 END) AS level_c,
                MAX(COALESCE(a.updated_at, a.created_at)) AS last_update
            ')
            ->where('a.created_at >=', date('Y-01-01 00:00:00'))
            ->where('a.created_at <', (date('Y') + 1) . '-01-01 00:00:00');

        if (!empty($bulan)) {
            $builder->whereIn('MONTH(a.created_at)', $bulan);
        }

        return $builder->get()->getRowArray();
    }

    public function isDuplicateNSPK($instansiId, $period){
        return $this->db->table('txn_nspk')
            ->where('instansi_id', $instansiId)
            ->where('period', $period)
            ->countAllResults() > 0;
    }

} 
