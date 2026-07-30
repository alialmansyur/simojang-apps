<?php

namespace App\Models\Apps\Services;

use CodeIgniter\Model;

class KonsulModel extends Model
{
    protected $table = 'txn_konsultasi';

    public function __construct(){
        parent::__construct();
    }

    // ----------------------------
    //  QUERY BUILDER UTAMA
    // ----------------------------    
    public function getBuilder($type, $param = null){
        switch ($type) {
            case 'recap':
                return $this->getDataRecap($param);
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
        $builder = $this->db->table('txn_konsultasi')
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

    public function getSummary(array $bulan = [])
    {
        $builder = $this->db->table('txn_konsultasi')
            ->select('
                COUNT(1) AS total_data,
                SUM(COALESCE(jumlah, 0)) AS total_pelayanan,
                COUNT(DISTINCT pelayanan) AS total_kanal,
                MAX(created_at) AS last_update
            ')
            ->where('created_at >=', date('Y-01-01 00:00:00'))
            ->where('created_at <', (date('Y') + 1) . '-01-01 00:00:00');

        if (!empty($bulan)) {
            $builder->whereIn('MONTH(created_at)', $bulan);
        }

        return $builder->get()->getRowArray();
    }

}
