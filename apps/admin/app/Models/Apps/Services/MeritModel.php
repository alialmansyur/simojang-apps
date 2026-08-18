<?php

namespace App\Models\Apps\Services;

use CodeIgniter\Model;

class MeritModel extends Model
{
    protected $table = 'txn_merit';

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

    public function saveData($data, $key = null)
    {
        if ($key) {
            $this->db->table($this->table)->where('id', $key)->update($data);
            return $key;
        } else {
            $this->db->table($this->table)->insert($data);
            return $this->db->insertID();
        }
    }

    public function deleteData($key)
    {
        return $this->db->table($this->table)->where('id', $key)->delete();
    }

    public function logActivity($data)
    {
        $this->db->table('activity_daily_logs')->insert($data);
        return $this->db->insertID();
    }

    public function getDataRecap($params = [])
    {
        $bulan = $params['bulan'] ?? [];

        $builder = $this->db->table('txn_merit a')
            ->select('a.*')
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
        $builder = $this->db->table('txn_merit')
            ->select('
                COUNT(1) AS total_data,
                SUM(COALESCE(usul_masuk, 0)) AS total_usul,
                SUM(COALESCE(total_realisasi, 0)) AS total_realisasi,
                ROUND(AVG(COALESCE(persentase_sla, 0)), 2) AS rata_sla,
                MAX(COALESCE(updated_at, created_at)) AS last_update
            ')
            ->where('created_at >=', date('Y-01-01 00:00:00'))
            ->where('created_at <', (date('Y') + 1) . '-01-01 00:00:00');

        if (!empty($bulan)) {
            $builder->whereIn('MONTH(created_at)', $bulan);
        }

        return $builder->get()->getRowArray();
    }

}
