<?php

namespace App\Models\Apps\Services;

use CodeIgniter\Model;

class CATModel extends Model
{
    protected $table            = 'txn_cat_tilok'; 
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
    public function getBuilder($type, $param = null){
        switch ($type) {
            case 'recap-seleksi':
                return $this->getDataRecapSeleksi($param);
            case 'recap-tilok':
                return $this->getDataRecap($param);
            case 'recap-hasil':
                return $this->getRecapHasil($param);                
            case 'detail':
                return $this->getDataDetail($param);
            default:
                throw new \Exception("Unknown builder type: $type");
        }
    }    

    // ----------------------------
    //  DAPATKAN NAMA KOLOM OTOMATIS
    // ----------------------------    
    public function getColumns($type, $param = null){
        $builder = $this->getBuilder($type, $param);
        $query = $builder->get();
        return $query->getFieldNames();
    }    

    public function getDataRecapSeleksi($params = [])
    {
        $tahun = $params['tahun'] ?? [];

        $builder = $this->db->table('txn_cat_seleksi a')
            ->select('
                a.*, 
                b.kode AS jenis_tes_kode, 
                b.nama AS jenis_tes_nama, 
                MAX(th.created_at) AS last_rekap_date,
                MAX(th.updated_at) AS last_rekap_updated,
                MAX(t.created_at) AS last_tilok_created,
                MAX(t.updated_at) AS last_tilok_updated
            ')
            ->join('data_support_jenis_tes b', 'b.id = a.jenis_tes_id', 'left')
            ->join('txn_cat_tilok t', 't.seleksi_id = a.id', 'left')
            ->join('txn_cat_hasil th', 'th.tilok_id = t.id', 'left')
            ->groupBy('a.id');

        if (!empty($tahun)) {
            $builder->whereIn('a.periode', $tahun);
        }

        $builder->orderBy('last_rekap_date', 'DESC');
        $builder->orderBy('a.created_at', 'DESC');
        return $builder;
    }

    public function getDataRecap($params = [])
    {
        $seleksi_uid = $params['seleksi_uid'] ?? null;

        $builder = $this->db->table('txn_cat_tilok a')
            ->select('a.*, b.kode AS jenis_tes, s.nama_seleksi, s.periode, (SELECT COUNT(id) FROM txn_cat_hasil WHERE tilok_id = a.id) as total_rekap')
            ->join('data_support_jenis_tes b', 'b.id = a.jenis_tes_id', 'left')
            ->join('txn_cat_seleksi s', 's.id = a.seleksi_id', 'left');

        if (!empty($seleksi_uid)) {
            $builder->where('s.uid', $seleksi_uid);
        }

        $builder->orderBy('a.created_at', 'DESC');
        return $builder;
    }

    public function getRecapHasil($params = [])
    {
        $id          = $params['id'] ?? null;
        $bulan       = $params['bulan'] ?? [];
        $instansi_id = $params['instansi_id'] ?? [];

        $builder = $this->db->table('txn_cat_hasil a')
            ->select('
                b.period,
                b.nama_tilok,
                c.nama AS jenis_tes,
                a.*,
                d.nama AS instansi_nama
            ')
            ->join('txn_cat_tilok b', 'b.id = a.tilok_id', 'left')
            ->join('data_support_jenis_tes c', 'c.id = b.jenis_tes_id', 'left')
            ->join('data_instansi d', 'd.kodeins = a.instansi_id', 'left')
            ->orderBy('a.period_date', 'DESC')
            ->orderBy('a.sesi', 'ASC')
            ->orderBy('a.created_at', 'DESC')
            ->where('b.uid', $id);

        if (!empty($bulan)) {
            $builder->whereIn('MONTH(a.period_date)', $bulan);
        }
        if (!empty($instansi_id)) {
            $builder->whereIn('a.instansi_id', $instansi_id);
        }

        return $builder;
    }

    public function getSummaryTilok($seleksi_uid = null)
    {
        $builder = $this->db->table('txn_cat_tilok a');
        $builder->select("
            COUNT(a.id) AS total_tilok,
            SUM(COALESCE(a.kapasitas, 0)) AS total_kapasitas,
            COUNT(DISTINCT a.jenis_tes_id) AS total_jenis_tes,
            COUNT(DISTINCT a.period) AS total_periode,
            MAX(COALESCE(a.updated_at, a.created_at)) AS last_update
        ", false);
        
        $builder->join('txn_cat_seleksi s', 's.id = a.seleksi_id', 'left');

        if (!empty($seleksi_uid)) {
            $builder->where('s.uid', $seleksi_uid);
        }

        return $builder->get()->getRowArray() ?? [
            'total_tilok' => 0,
            'total_kapasitas' => 0,
            'total_jenis_tes' => 0,
            'total_periode' => 0,
            'last_update' => null,
        ];
    }

    public function getDetailMeta(string $uid): ?array
    {
        $row = $this->db->table('txn_cat_tilok a')
            ->select('a.id, a.uid, a.period, a.nama_tilok, a.period_start_date, a.period_end_date, a.kapasitas, a.created_at, b.kode AS jenis_tes, s.nama_seleksi, s.uid AS seleksi_uid')
            ->join('data_support_jenis_tes b', 'b.id = a.jenis_tes_id', 'left')
            ->join('txn_cat_seleksi s', 's.id = a.seleksi_id', 'left')
            ->where('a.uid', $uid)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    public function getSummaryDetail(string $uid, array $bulan = []): array
    {
        $builder = $this->db->table('txn_cat_hasil a');
        $builder->select("
            COUNT(a.id) AS total_rekap,
            COUNT(DISTINCT a.instansi_id) AS total_instansi,
            SUM(COALESCE(a.hadir, 0)) AS total_hadir,
            SUM(COALESCE(a.tidak_hadir, 0)) AS total_tidak_hadir,
            SUM(COALESCE(a.hadir, 0) + COALESCE(a.tidak_hadir, 0)) AS total_peserta,
            MAX(COALESCE(a.updated_at, a.created_at)) AS last_update
        ", false);
        $builder->join('txn_cat_tilok b', 'b.id = a.tilok_id', 'inner');
        $builder->where('b.uid', $uid);

        if (!empty($bulan)) {
            $builder->whereIn('MONTH(a.period_date)', $bulan);
        }

        return $builder->get()->getRowArray() ?? [
            'total_rekap' => 0,
            'total_instansi' => 0,
            'total_hadir' => 0,
            'total_tidak_hadir' => 0,
            'total_peserta' => 0,
            'last_update' => null,
        ];
    }

    public function getInstansiTilokGrouped(string $tilokUid, array $bulan = []): array
    {
        $builder = $this->db->table('txn_cat_hasil a');
        $builder->select("
            a.instansi_id,
            COALESCE(d.nama, a.instansi_id) AS instansi_nama,
            d.logo,
            COUNT(a.id) AS total_sesi,
            SUM(COALESCE(a.hadir, 0)) AS total_hadir,
            SUM(COALESCE(a.tidak_hadir, 0)) AS total_tidak_hadir,
            SUM(COALESCE(a.reschedule, 0)) AS total_reschedule,
            SUM(COALESCE(a.hadir, 0) + COALESCE(a.tidak_hadir, 0)) AS total_peserta,
            SUM(COALESCE(a.memenuhi, 0)) AS total_memenuhi,
            SUM(COALESCE(a.tidak_memenuhi, 0)) AS total_tidak_memenuhi,
            MIN(a.nilai_min) AS min_nilai,
            MAX(a.nilai_max) AS max_nilai,
            MIN(a.period_date) AS min_date,
            MAX(a.period_date) AS max_date,
            MAX(COALESCE(a.updated_at, a.created_at)) AS last_update
        ", false);
        $builder->join('txn_cat_tilok b', 'b.id = a.tilok_id', 'inner');
        $builder->join('data_instansi d', 'd.kodeins = a.instansi_id', 'left');
        $builder->where('b.uid', $tilokUid);

        if (!empty($bulan)) {
            $builder->whereIn('MONTH(a.period_date)', $bulan);
        }

        $builder->groupBy('a.instansi_id, d.nama, d.logo');
        $builder->orderBy('last_update', 'DESC');
        $builder->orderBy('d.nama', 'ASC');

        return $builder->get()->getResultArray() ?? [];
    }

}
