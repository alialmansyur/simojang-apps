<?php

namespace App\Models\Apps\Services;

use CodeIgniter\Model;

class WasdalModel extends Model
{
    protected $table = 'txn_wasdal';

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

        $builder = $this->db->table('txn_wasdal a')
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
        $baseFilter = function ($builder) use ($bulan) {
            $builder->where('a.created_at >=', date('Y-01-01 00:00:00'));
            $builder->where('a.created_at <', (date('Y') + 1) . '-01-01 00:00:00');
            if (!empty($bulan)) {
                $builder->whereIn('MONTH(a.created_at)', $bulan);
            }
            return $builder;
        };

        $totals = $baseFilter(
            $this->db->table('txn_wasdal a')
                ->select('
                    COUNT(1) AS total_data,
                    SUM(COALESCE(a.total, 0)) AS total_kasus,
                    COUNT(DISTINCT a.instansi_id) AS total_instansi,
                    MAX(COALESCE(a.updated_at, a.created_at)) AS last_update
                ')
        )->get()->getRowArray();

        $topInstansi = $baseFilter(
            $this->db->table('txn_wasdal a')
                ->select('
                    a.instansi_id,
                    COALESCE(i.nama, "-") AS instansi_name,
                    SUM(COALESCE(a.total, 0)) AS total_kasus
                ')
                ->join('data_instansi i', 'i.kodeins = a.instansi_id', 'left')
                ->groupBy('a.instansi_id, i.nama')
                ->orderBy('total_kasus', 'DESC')
                ->limit(5)
        )->get()->getResultArray();

        $topPermasalahan = $baseFilter(
            $this->db->table('txn_wasdal a')
                ->select('
                    COALESCE(NULLIF(TRIM(a.permasalahan), ""), "-") AS permasalahan,
                    SUM(COALESCE(a.total, 0)) AS total_kasus
                ')
                ->groupBy('COALESCE(NULLIF(TRIM(a.permasalahan), ""), "-")')
                ->orderBy('total_kasus', 'DESC')
                ->limit(5)
        )->get()->getResultArray();

        return [
            'total_data' => (int) ($totals['total_data'] ?? 0),
            'total_kasus' => (int) ($totals['total_kasus'] ?? 0),
            'total_instansi' => (int) ($totals['total_instansi'] ?? 0),
            'last_update' => $totals['last_update'] ?? null,
            'top_instansi' => $topInstansi,
            'top_permasalahan' => $topPermasalahan,
        ];
    }

}
