<?php

namespace App\Models\Pages;

use CodeIgniter\Model;

class PDMModel extends Model
{
    protected $table            = 'txn_pdm';
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

    public function getDataRecap($param){
        $param = $this->db->escape($param);
        $rawSql = "
            SELECT 
            a.*, b.nama nama_instansi
            FROM txn_pdm a
            LEFT JOIN data_instansi b ON b.kodeins = a.kode_instansi
            WHERE a.tanggal_proses = CURDATE() AND a.created_by = '$param'
        ";
        return $this->db->table("($rawSql) AS recap");
    }

    public function getDataRecapV2($param){
        $param = $this->db->escape($param);
        $rawSql = "
            SELECT 
            a.*
            FROM txn_activity_pdm a
            WHERE a.created_at >= CURDATE()
            AND a.created_at < CURDATE() + INTERVAL 1 DAY
            AND a.created_by = '$param'
        ";
        return $this->db->table("($rawSql) AS recap");
    }


    public function getResumeData($param){
        $rawSql = "
            SELECT 
                COUNT(1) total,
                SUM(CASE WHEN a.tindak_lanjut = 'Disetujui' THEN 1 ELSE 0 END) AS acc,
                SUM(CASE WHEN a.tindak_lanjut = 'BTS' THEN 1 ELSE 0 END) AS bts,
                SUM(CASE WHEN a.tindak_lanjut = 'TMS' THEN 1 ELSE 0 END) AS tms
            FROM txn_pdm a
            LEFT JOIN data_instansi b ON b.kodeins = a.kode_instansi
                WHERE a.tanggal_proses = CURDATE() AND a.created_by = ?
            ";
        return $this->db->query($rawSql, [$param])->getRow();
    }

    public function getDailyData($param){
        $rawSql = "
            SELECT 
                DATE_FORMAT(a.tanggal_proses, '%Y-%m-%dT00:00:00.000Z') tanggal, COUNT(1) total
            FROM txn_pdm a
            LEFT JOIN data_instansi b ON b.kodeins = a.kode_instansi
            WHERE WEEK(a.tanggal_proses) = WEEK(CURDATE()) AND a.created_by = ?
            GROUP BY a.tanggal_proses
            ORDER BY a.tanggal_proses asc
        ";
        return $this->db->query($rawSql, [$param])->getResultArray();
    }

}
