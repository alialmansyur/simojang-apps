<?php

namespace App\Models\Apps\Services;

use CodeIgniter\Model;

class IKMModel extends Model
{
    protected $table = 'txn_survey_ikm';

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
        $builder = $this->db->table('txn_survey_ikm')
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
        $builder = $this->db->table('txn_survey_ikm')
            ->select('
                COUNT(1) AS total_data,
                SUM(COALESCE(jumlah_responden, 0)) AS total_responden,
                AVG(COALESCE(nilai, 0)) AS rata_nilai,
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
