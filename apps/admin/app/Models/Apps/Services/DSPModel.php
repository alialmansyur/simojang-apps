<?php

namespace App\Models\Apps\Services;

use CodeIgniter\Model;

class DSPModel extends Model
{
    protected $table            = 'txn_disparitas';
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

        $builder = $this->db->table('txn_disparitas a')
            ->select('a.*, SUM(b.jumlah) total ')
            ->join('txn_disparitas_detail b', 'b.disparitas_log_id = a.id', 'left')
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
        return $this->db->table('txn_disparitas a')
            ->select('
                a.*,
                c.logo,
                c.kodeins AS kode_instansi,
                c.nama AS nama_instansi,
                b.jenis_anomali,
                b.jumlah,
                a.created_by AS upload_by,
                b.created_at AS tanggal_upload
            ')
            ->join('txn_disparitas_detail b', 'b.disparitas_log_id = a.id', 'left')
            ->join('data_instansi c', 'c.kodeins = b.instansi_id', 'left')
            ->where('b.instansi_id IS NOT NULL', null, false)
            ->where('a.id', (int) $param);
    }

    public function getSummary($bulan = [])
    {
        $builder = $this->db->table('txn_disparitas a');
        $builder->select("
            COUNT(DISTINCT a.id) AS total_upload,
            SUM(COALESCE(b.jumlah, 0)) AS total_anomali,
            COUNT(DISTINCT b.instansi_id) AS total_instansi,
            COUNT(DISTINCT b.jenis_anomali) AS total_jenis_anomali,
            MAX(COALESCE(a.updated_at, a.created_at)) AS last_update
        ", false);
        $builder->join('txn_disparitas_detail b', 'b.disparitas_log_id = a.id', 'left');
        $builder->where('a.created_at >=', date('Y-01-01 00:00:00'));
        $builder->where('a.created_at <', (date('Y') + 1) . '-01-01 00:00:00');

        if (is_array($bulan) && !empty($bulan)) {
            $builder->whereIn('MONTH(a.created_at)', $bulan);
        }

        return $builder->get()->getRowArray() ?? [
            'total_upload' => 0,
            'total_anomali' => 0,
            'total_instansi' => 0,
            'total_jenis_anomali' => 0,
            'last_update' => null,
        ];
    }

}
