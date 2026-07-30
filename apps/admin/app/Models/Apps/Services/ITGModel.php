<?php

namespace App\Models\Apps\Services;

use CodeIgniter\Model;

class ITGModel extends Model
{
    protected $table            = 'txn_integrasi_log';
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
    public function getBuilder($type, $id = null){
        switch ($type) {
            case 'recap':
                return $this->getIntegrasiList($id);
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

    public function getIntegrasiList($id = null){
        $addon = $id != 0 ? " AND a.rw_integrasi_id = $id " : "";
        $rawSql = "
            SELECT 
            	a.*,
                c.jenis,
               b.kodeins, b.nama instansi_name, b.kanreg, UPPER(b.wilayah) wilayah, b.logo
            FROM txn_integrasi a
            LEFT JOIN data_instansi b ON b.kodeins = a.instansi_id
            LEFT JOIN data_support_integrasi c ON c.id = a.rw_integrasi_id
            WHERE a.created_at >= CONCAT(YEAR(CURDATE()), '-01-01 00:00:00')
              AND a.created_at < CONCAT(YEAR(CURDATE()) + 1, '-01-01 00:00:00') $addon
				ORDER BY a.updated_at DESC
        ";
        return $this->db->table("($rawSql) recap");
    }    

    public function isDuplicateIntegrasi($instansiId, $riwayat){
        return $this->db->table('txn_integrasi')
            ->where('instansi_id', $instansiId)
            ->where('rw_integrasi_id', $riwayat)
            ->countAllResults() > 0;
    }

    public function getDataList(
        int $jenis,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $builder = $this->db->table('txn_integrasi a');

        $builder->select([
            'a.*',
            'c.jenis',
            'b.kodeins',
            'b.nama AS instansi_name',
            'b.kanreg',
            'UPPER(b.wilayah) AS wilayah',
            'b.logo'
        ]);

        $builder->join('data_instansi b', 'b.kodeins = a.instansi_id', 'left');
        $builder->join('data_support_integrasi c', 'c.id = a.rw_integrasi_id', 'left');

        // filter jenis (wajib)
        $builder->where('a.rw_integrasi_id', $jenis);

        // FILTER RANGE TANGGAL
        if ($startDate && $endDate) {
            $builder->where('a.created_at >=', $startDate . ' 00:00:00');
            $builder->where('a.created_at <=', $endDate . ' 23:59:59');
        }

        $builder->orderBy('a.updated_at', 'DESC');

        return $builder->get()->getResultArray();
    }

    public function getSummary($jenis = 0)
    {
        $builder = $this->db->table('txn_integrasi a');
        $builder->select("
            COUNT(a.id) AS total_data,
            COUNT(DISTINCT a.instansi_id) AS total_instansi,
            COUNT(DISTINCT a.rw_integrasi_id) AS total_riwayat,
            COUNT(DISTINCT UPPER(b.wilayah)) AS total_wilayah,
            MAX(COALESCE(a.updated_at, a.created_at)) AS last_update
        ", false);
        $builder->join('data_instansi b', 'b.kodeins = a.instansi_id', 'left');
        $builder->where('a.created_at >=', date('Y-01-01 00:00:00'));
        $builder->where('a.created_at <', (date('Y') + 1) . '-01-01 00:00:00');

        if (!empty($jenis) && (int) $jenis > 0) {
            $builder->where('a.rw_integrasi_id', (int) $jenis);
        }

        return $builder->get()->getRowArray() ?? [
            'total_data' => 0,
            'total_instansi' => 0,
            'total_riwayat' => 0,
            'total_wilayah' => 0,
            'last_update' => null,
        ];
    }




    // public function checkData($param){
    //     return $this->db->query("
    //         SELECT * FROM txn_integrasi_log WHERE uid = '$param'
    //     ")->getRow();
    // }

    // public function getDataRecap(){
    //     $rawSql = "
    //         SELECT 
    //             xx.uid,
    //             xx.title,
    //             xx.created_at,
    //             xx.period,
    //             CONCAT(COUNT(*),' Instansi') AS total_instansi,
    //             CONCAT(ROUND(AVG(xx.persentase), 2),'%') AS persentase
    //         FROM (
    //             SELECT 
    //                 a.uid,
    //                 a.period,
    //                 a.title,
    //                 a.created_at,
    //                 b.kode_instansi,
    //                 c.nama,
    //                 ROUND((
    //                     (
    //                         COALESCE(b.rw_jabatan,0) +
    //                         COALESCE(b.rw_diklat,0) +
    //                         COALESCE(b.rw_hukdis,0) +
    //                         COALESCE(b.rw_angka_kredit,0) +
    //                         1 +
    //                         COALESCE(b.rw_penghargaan,0) +
    //                         COALESCE(b.rw_cpns,0) +
    //                         COALESCE(b.data_pribadi,0)
    //                     ) / 8.0
    //                 ) * 100, 2) AS persentase
    //             FROM txn_integrasi_log a
    //             LEFT JOIN txn_integrasi_progress b ON b.integrasi_log_id = a.id
    //             LEFT JOIN data_instansi c ON c.kodeins = b.kode_instansi
    //             WHERE b.kode_instansi IS NOT NULL
    //         ) AS xx
    //         GROUP BY xx.uid, xx.title, xx.created_at, xx.period
    //     ";

    //     return $this->db->table("($rawSql) AS recap");
    // }

    // public function getDataRecapByID($param){
    //     $rawSql = "
    //        	SELECT 
    //             a.id,
    //             a.uid,
    //             a.period, 
    //             a.title,
    //             a.created_at,
    //             b.kode_instansi, 
    //             c.nama nama_instansi,
    //             b.rw_jabatan,
    //             b.rw_diklat,
    //             b.rw_hukdis,
    //             b.rw_angka_kredit,
    //             1 AS rw_kinerja,
    //             b.rw_penghargaan,
    //             b.rw_cpns,
    //             b.data_pribadi,
    //             CONCAT(ROUND(
    //             (
    //             (
    //                 COALESCE(b.rw_jabatan,0) +
    //                 COALESCE(b.rw_diklat,0) +
    //                 COALESCE(b.rw_hukdis,0) +
    //                 COALESCE(b.rw_angka_kredit,0) +
    //                 1 +
    //                 COALESCE(b.rw_penghargaan,0) +
    //                 COALESCE(b.rw_cpns,0) +
    //                 COALESCE(b.data_pribadi,0)
    //             ) / 8.0
    //             ) * 100, 2
    //             ),'%') AS persentase
    //         FROM 
    //         txn_integrasi_log a
    //         LEFT JOIN txn_integrasi_progress b ON b.integrasi_log_id = a.id
    //         LEFT JOIN data_instansi c ON c.kodeins = b.kode_instansi
    //         WHERE b.kode_instansi IS NOT NULL AND a.id = $param
    //     ";

    //     return $this->db->table("($rawSql) AS recap");
    // }

}
