<?php

namespace App\Models\Apps\Services;

use CodeIgniter\Model;

class KompetensiModel extends Model
{
    protected $table            = 'txn_kompetensi'; 
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
            case 'recap-kompetensi':
                return $this->getDataRecap($param);
            default:
                throw new \Exception("Unknown builder type: $type");
        }
    }    

    public function getDataRecap($params = [])
    {
        $bulan = $params['bulan'] ?? [];
        $instansi_id = $params['instansi_id'] ?? [];

        $builder = $this->db->table('txn_kompetensi a')
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
        $builder = $this->db->table('txn_kompetensi a');
        $builder->select("
            COUNT(a.id) AS total_rekap,
            COUNT(DISTINCT a.instansi_id) AS total_instansi,
            SUM(CASE WHEN a.metode = 'CACT' THEN COALESCE(a.total_peserta, 0) ELSE 0 END) AS total_peserta_cact,
            SUM(CASE WHEN a.metode = 'Pro ASN' THEN COALESCE(a.total_peserta, 0) ELSE 0 END) AS total_peserta_proasn,
            SUM(CASE WHEN a.metode = 'Integrasi Data' THEN COALESCE(a.total_peserta, 0) ELSE 0 END) AS total_peserta_integrasi,
            MAX(COALESCE(a.updated_at, a.created_at)) AS last_update
        ", false);

        if (!empty($bulan)) {
            $builder->whereIn('MONTH(a.tanggal)', $bulan);
        }

        return $builder->get()->getRowArray() ?? [
            'total_rekap' => 0,
            'total_instansi' => 0,
            'total_peserta_cact' => 0,
            'total_peserta_proasn' => 0,
            'total_peserta_integrasi' => 0,
            'last_update' => null,
        ];
    }
}
