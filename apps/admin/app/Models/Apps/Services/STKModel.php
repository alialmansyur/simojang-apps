<?php

namespace App\Models\Apps\Services;

use CodeIgniter\Model;

class STKModel extends Model
{
    protected $table            = 'txn_asn';
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

    public function getDataLog($jenis = null){
        $builder = $this->db->table($this->table);
        if ($jenis) {
            $builder->where('title', $jenis);
        }
        return $builder;
    }

    // ----------------------------
    //  QUERY BUILDER UTAMA
    // ----------------------------    
    public function getBuilder($type, $key = null){
        switch ($type) {
            case 'recap':
                return $this->getDataRecap($key);
            case 'detail':
                return $this->getDataDetail($key);
            default:
                throw new \Exception("Unknown builder type: $type");
        }
    }    

    // ----------------------------
    //  DAPATKAN NAMA KOLOM OTOMATIS
    // ----------------------------    
    public function getColumns($type, $key = null){
        $builder = $this->getBuilder($type, $key);
        $query = $builder->get();
        return $query->getFieldNames();
    }    

    public function getDataRecap($jenis = null){
        $mapTable = $this->getJenisTableMap();

        // validasi jenis
        if (!$jenis || !isset($mapTable[$jenis])) {
            return null; // atau throw exception
        }

        $table = $mapTable[$jenis];

        return $this->db->table('txn_asn a')
            ->select('a.*, COUNT(b.id) AS total')
            ->join($table . ' b', 'b.asn_log_id = a.id', 'left')
            ->where('a.jenis', $jenis)
            ->groupBy('a.id')
            ->orderBy('a.created_at', 'DESC');
    }

    public function getDataDetail($param){
        $mapTable = $this->getJenisTableMap();

        $log = $this->db->table('txn_asn')
            ->select('id, jenis')
            ->where('id', $param)
            ->get()
            ->getRowArray();

        if (!$log || !isset($mapTable[$log['jenis']])) {
            return $this->db->table('(SELECT 1 AS empty_col WHERE 1=0) AS recap');
        }

        $table = $mapTable[$log['jenis']];
        return $this->db->table('txn_asn a')
            ->select('
                a.id AS log_id,
                b.*,
                c.logo,
                c.kodeins AS kode_instansi,
                c.nama AS nama_instansi,
                a.created_by AS upload_by,
                b.created_at AS tanggal_upload
            ')
            ->join($table . ' b', 'b.asn_log_id = a.id', 'left')
            ->join('data_instansi c', 'c.kodeins = b.instansi_id', 'left')
            ->where('b.instansi_id IS NOT NULL', null, false)
            ->where('a.id', (int) $param);
    }

    public function getSummary($jenis = null)
    {
        $mapTable = $this->getJenisTableMap();

        if (!$jenis || !isset($mapTable[$jenis])) {
            return [
                'total_upload' => 0,
                'total_data' => 0,
                'total_instansi' => 0,
                'total_periode' => 0,
                'last_update' => null,
            ];
        }

        $table = $mapTable[$jenis];
        $builder = $this->db->table('txn_asn a');
        $builder->select("
            COUNT(DISTINCT a.id) AS total_upload,
            COUNT(b.id) AS total_data,
            COUNT(DISTINCT b.instansi_id) AS total_instansi,
            COUNT(DISTINCT a.period) AS total_periode,
            MAX(COALESCE(a.updated_at, a.created_at)) AS last_update
        ", false);
        $builder->join($table . ' b', 'b.asn_log_id = a.id', 'left');
        $builder->where('a.jenis', $jenis);
        $builder->where('a.created_at >=', date('Y-01-01 00:00:00'));
        $builder->where('a.created_at <', (date('Y') + 1) . '-01-01 00:00:00');

        return $builder->get()->getRowArray() ?? [
            'total_upload' => 0,
            'total_data' => 0,
            'total_instansi' => 0,
            'total_periode' => 0,
            'last_update' => null,
        ];
    }

    private function getJenisTableMap(): array
    {
        return [
            'Jumlah ASN' => 'txn_asn_jumlahs',
            'Golongan ASN' => 'txn_asn_golongans',
            'Pendidikan ASN' => 'txn_asn_pendidikans',
            'Usia ASN' => 'txn_asn_usias',
            'Jenis Kelamin ASN' => 'txn_asn_jenis_kelamins',
            'Generasi ASN' => 'txn_asn_generasis',
            'Kelompok Jabatan ASN' => 'txn_asn_kelompok_jabatans',
            'Masa Kerja ASN' => 'txn_asn_masa_kerjas',
        ];
    }

}
