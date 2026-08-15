<?php

namespace App\Models\Apps\Services;

use CodeIgniter\Model;

class IKPAModel extends Model
{
    protected $table            = 'txn_ikpa'; 
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

        $rawSql = "
            SELECT 
                a.id,
                a.period,
                a.period_start_date,
                a.period_end_date,
                a.created_at,

                b.kode_kppn,
                b.kode_ba,
                b.kode_satker,
                b.uraian_satker,
                b.nilai_total,
                b.konversi_bobot,
                b.dispensasi_spm,
                b.nilai_akhir,

                n.nama_kategori,
                n.warna,
                n.keterangan
            FROM txn_ikpa a
            LEFT JOIN txn_ikpa_header b 
                ON b.ikpa_log_id = a.id
            LEFT JOIN data_support_ikpa_nilai n
                ON b.nilai_akhir >= n.nilai_min
            AND (
                    n.nilai_max IS NULL 
                    OR b.nilai_akhir < n.nilai_max
            )
            GROUP BY a.id
        ";
        $builder = $this->db->table("($rawSql) AS recap");
        if (!empty($bulan)) {
            $builder->whereIn('MONTH(created_at)', $bulan);
        }
        return $builder;
    }

    public function getSummary(array $bulan = [])
    {
        $builder = $this->db->table('txn_ikpa a')
            ->select('
                COUNT(1) AS total_data,
                AVG(COALESCE(h.nilai_akhir, 0)) AS rata_nilai_akhir,
                SUM(COALESCE(h.nilai_total, 0)) AS total_nilai,
                MAX(a.created_at) AS last_update
            ')
            ->join('txn_ikpa_header h', 'h.ikpa_log_id = a.id', 'left');

        if (!empty($bulan)) {
            $builder->whereIn('MONTH(a.created_at)', $bulan);
        }

        return $builder->get()->getRowArray();
    }

    public function getDataDetail($param){
        return $this->db->table('txn_ikpa_detail d')
            ->select('
                d.id,
                d.ikpa_log_id,
                d.ikpa_header_id,
                i.kelompok,
                i.nama_indikator,
                d.nilai,
                d.bobot,
                d.nilai_akhir
            ')
            ->join('data_support_ikpa_indikator i', 'i.id = d.ikpa_indikator_id', 'left')
            ->where('d.ikpa_log_id', $param)
            ->orderBy('i.urutan', 'ASC');
    }    

}
