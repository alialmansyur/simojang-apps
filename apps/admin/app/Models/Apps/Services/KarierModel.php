<?php

namespace App\Models\Apps\Services;

use CodeIgniter\Model;

class KarierModel extends Model
{
    protected $table            = 'txn_karier'; 
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

    public function getBuilder($type, $param = null){
        switch ($type) {
            case 'recap-karier':
                return $this->getDataRecap($param);
            default:
                throw new \Exception("Unknown builder type: $type");
        }
    }    

    public function getDataRecap($params = [])
    {
        $bulan = $params['bulan'] ?? [];
        $instansi_id = $params['instansi_id'] ?? [];

        $builder = $this->db->table('txn_karier a')
            ->select('
                a.*,
                d.nama AS instansi_nama
            ')
            ->join('data_instansi d', 'd.kodeins = a.instansi_id', 'left')
            ->orderBy('a.tanggal', 'DESC')
            ->orderBy('a.created_at', 'DESC');

        if (!empty($bulan)) {
            $builder->whereIn('MONTH(a.tanggal)', $bulan);
        }
        if (!empty($instansi_id)) {
            $builder->whereIn('a.instansi_id', $instansi_id);
        }

        return $builder;
    }

    public function getSummary($bulan = [])
    {
        $builder = $this->db->table('txn_karier a');
        $builder->select("
            COUNT(a.id) AS total_rekap,
            COUNT(DISTINCT a.instansi_id) AS total_instansi,
            SUM(COALESCE(a.total_peserta, 0)) AS total_peserta,
            SUM(COALESCE(a.memenuhi, 0) + COALESCE(a.lulus, 0)) AS total_memenuhi,
            SUM(COALESCE(a.tidak_memenuhi, 0) + COALESCE(a.tidak_lulus, 0)) AS total_tidak_memenuhi,
            MAX(COALESCE(a.updated_at, a.created_at)) AS last_update
        ", false);

        if (!empty($bulan)) {
            $builder->whereIn('MONTH(a.tanggal)', $bulan);
        }

        return $builder->get()->getRowArray() ?? [
            'total_rekap' => 0,
            'total_instansi' => 0,
            'total_peserta' => 0,
            'total_memenuhi' => 0,
            'last_update' => null,
        ];
    }
}
