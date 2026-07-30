<?php

namespace App\Models\Apps\Services;

use CodeIgniter\Model;

class MTModel extends Model
{
    protected $table = 'txn_mt';

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
        $query = $builder->get();
        return $query->getFieldNames();
    }    

    public function getDataRecap($params = [])
    {
        $bulan = $params['bulan'] ?? [];

        $builder = $this->db->table('txn_mt a')
            ->select("
                a.*,
                b.nama AS instansi_name,
                b.logo,
                c.step_name,
                DATE_FORMAT(a.period_date, '%d %M %Y') AS mulai_implemen,
                DATE_FORMAT(a.updated_at, '%d %M %Y %H:%i:%s') AS diperbaharui,
                CONCAT(
                    (
                        SELECT SUM(d.percentage)
                        FROM data_support_mt d
                        WHERE d.id <= a.rw_mt_id
                    ),
                    '%'
                ) AS total_percentase
            ")
            ->join('data_instansi b', 'b.kodeins = a.instansi_id', 'left')
            ->join('data_support_mt c', 'c.id = a.rw_mt_id', 'left')
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

    public function getStepMT(){
        $rawSql = "
            SELECT 
                a.*
            FROM data_support_mt a
            ORDER BY a.id ASC
        ";
        return $this->db->table("($rawSql) recap");
    }
}
