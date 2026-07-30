<?php

namespace App\Models\Pages;

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

    public function checkData($param){
        return $this->db->query("
            SELECT * FROM txn_integrasi_log WHERE uid = ?
        ", [$param])->getRow();
    }

    public function getDataRecap(){
        $rawSql = "
            SELECT 
                xx.uid,
                xx.title,
                xx.created_at,
                xx.period,
                CONCAT(COUNT(*),' Instansi') AS total_instansi,
                CONCAT(ROUND(AVG(xx.persentase), 2),'%') AS persentase
            FROM (
                SELECT 
                    a.uid,
                    a.period,
                    a.title,
                    a.created_at,
                    b.kode_instansi,
                    c.nama,
                    ROUND((
                        (
                            COALESCE(b.rw_jabatan,0) +
                            COALESCE(b.rw_diklat,0) +
                            COALESCE(b.rw_hukdis,0) +
                            COALESCE(b.rw_angka_kredit,0) +
                            1 +
                            COALESCE(b.rw_penghargaan,0) +
                            COALESCE(b.rw_cpns,0) +
                            COALESCE(b.data_pribadi,0)
                        ) / 8.0
                    ) * 100, 2) AS persentase
                FROM txn_integrasi_log a
                LEFT JOIN txn_integrasi_progress b ON b.integrasi_log_id = a.id
                LEFT JOIN data_instansi c ON c.kodeins = b.kode_instansi
                WHERE b.kode_instansi IS NOT NULL
            ) AS xx
            GROUP BY xx.uid, xx.title, xx.created_at, xx.period
        ";

        return $this->db->table("($rawSql) AS recap");
    }

    public function getDataRecapByID($param){
        $param = (int) $param;
        $rawSql = "
           	SELECT 
                a.id,
                a.uid,
                a.period, 
                a.title,
                a.created_at,
                b.kode_instansi, 
                c.nama nama_instansi,
                b.rw_jabatan,
                b.rw_diklat,
                b.rw_hukdis,
                b.rw_angka_kredit,
                1 AS rw_kinerja,
                b.rw_penghargaan,
                b.rw_cpns,
                b.data_pribadi,
                CONCAT(ROUND(
                (
                (
                    COALESCE(b.rw_jabatan,0) +
                    COALESCE(b.rw_diklat,0) +
                    COALESCE(b.rw_hukdis,0) +
                    COALESCE(b.rw_angka_kredit,0) +
                    1 +
                    COALESCE(b.rw_penghargaan,0) +
                    COALESCE(b.rw_cpns,0) +
                    COALESCE(b.data_pribadi,0)
                ) / 8.0
                ) * 100, 2
                ),'%') AS persentase
            FROM 
            txn_integrasi_log a
            LEFT JOIN txn_integrasi_progress b ON b.integrasi_log_id = a.id
            LEFT JOIN data_instansi c ON c.kodeins = b.kode_instansi
            WHERE b.kode_instansi IS NOT NULL AND a.id = $param
        ";

        return $this->db->table("($rawSql) AS recap");
    }

}
