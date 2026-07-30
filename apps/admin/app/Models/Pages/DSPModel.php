<?php

namespace App\Models\Pages;

use CodeIgniter\Model;

class DSPModel extends Model
{
    protected $table            = 'txn_disparitas_log';
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
            SELECT * FROM txn_disparitas_log WHERE uid = ?
        ", [$param])->getRow();
    }

    public function getDataRecap(){
        $rawSql = "
                    SELECT 
                        xx.uid,
                        xx.title,
                        xx.created_at,
                        xx.period,
                        CONCAT(COUNT(DISTINCT xx.kode_instansi),' Instansi') AS total_instansi,
                        SUM(xx.anomali_awal) AS anomali_awal,
                        SUM(xx.tidak_ada_anomali_awal) AS tidak_ada_anomali_awal,
                        SUM(xx.sisa_anomali) AS sisa_anomali,
                        SUM(xx.penyelesaian_anomali) AS penyelesaian_anomali
                    FROM (
                        SELECT 
                            a.uid,
                            a.period,
                            a.title,
                            a.created_at,
                            b.kode_instansi,
                            c.nama,
                            b.anomali_awal,
                            b.tidak_ada_anomali_awal,
                            b.sisa_anomali,
                            b.penyelesaian_anomali
                        FROM txn_disparitas_log a
                        LEFT JOIN txn_disparitas_progress b ON b.disparitas_log_id = a.id
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
                b.anomali_awal,
                b.tidak_ada_anomali_awal,
                b.sisa_anomali,
                b.penyelesaian_anomali,               
                CONCAT(
                    ROUND(
                        COALESCE(
                            (b.sisa_anomali * 100.0) / NULLIF((b.anomali_awal + b.tidak_ada_anomali_awal), 0),
                            0
                        ),
                        2
                    ),
                    '%'
                ) AS persentase_sisa_anomali,
                CONCAT(
                    ROUND(
                        COALESCE(
                            (b.penyelesaian_anomali * 100.0) / NULLIF((b.anomali_awal + b.tidak_ada_anomali_awal), 0),
                            0
                        ),
                        2
                    ),
                    '%'
                ) AS persentase_penyelesaian               
            FROM 
            txn_disparitas_log a
            LEFT JOIN txn_disparitas_progress b ON b.disparitas_log_id = a.id
            LEFT JOIN data_instansi c ON c.kodeins = b.kode_instansi
            WHERE b.kode_instansi IS NOT NULL AND a.id = $param
        ";

        return $this->db->table("($rawSql) AS recap");
    }

}
