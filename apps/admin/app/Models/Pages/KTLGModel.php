<?php

namespace App\Models\Pages;

use CodeIgniter\Model;

class KTLGModel extends Model
{
    protected $table            = 'txn_takah';
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
            FROM txn_takah a
            LEFT JOIN data_instansi b ON b.kodeins = a.kode_instansi
            WHERE a.tanggal_proses = CURDATE() AND a.created_by = '$param'
        ";
        return $this->db->table("($rawSql) AS recap");
    }

    public function getDataRecapUpload($param){
        $param = $this->db->escape($param);
        $rawSql = "
            SELECT 
            a.*, b.nama nama_instansi
            FROM txn_takah a
            LEFT JOIN data_instansi b ON b.kodeins = a.kode_instansi
            WHERE a.skema = 'upload' AND a.tanggal_proses = CURDATE() AND a.created_by = '$param'
        ";
        return $this->db->table("($rawSql) AS recap");
    }

    public function getResumeData($param){
        $rawSql = "
            SELECT 
                (SELECT COUNT(DISTINCT nip) FROM txn_takah WHERE MONTH(tanggal_proses) = MONTH(CURDATE()) AND created_by = ? ) total,
                COUNT(DISTINCT a.kode_instansi) AS total_instansi,
                COUNT(DISTINCT a.nip) AS total_nip,
                (
                    SUM(CASE WHEN a.d2nip IS NOT NULL AND a.d2nip != '' THEN 1 ELSE 0 END) +
                    SUM(CASE WHEN a.ijazah IS NOT NULL AND a.ijazah != '' THEN 1 ELSE 0 END) +
                    SUM(CASE WHEN a.akta IS NOT NULL AND a.akta != '' THEN 1 ELSE 0 END) +
                    SUM(CASE WHEN a.drh IS NOT NULL AND a.drh != '' THEN 1 ELSE 0 END) +
                    SUM(CASE WHEN a.cpns IS NOT NULL AND a.cpns != '' THEN 1 ELSE 0 END) +
                    SUM(CASE WHEN a.pns IS NOT NULL AND a.pns != '' THEN 1 ELSE 0 END) +
                    SUM(CASE WHEN a.perubahan IS NOT NULL AND a.perubahan != '' THEN 1 ELSE 0 END) +
                    SUM(CASE WHEN a.kp IS NOT NULL AND a.kp != '' THEN 1 ELSE 0 END) +
                    SUM(CASE WHEN a.jabatan IS NOT NULL AND a.jabatan != '' THEN 1 ELSE 0 END) +
                    SUM(CASE WHEN a.berhenti IS NOT NULL AND a.berhenti != '' THEN 1 ELSE 0 END) +
                    SUM(CASE WHEN a.pensiun IS NOT NULL AND a.pensiun != '' THEN 1 ELSE 0 END)
                ) AS total_dokumen
            FROM txn_takah a
            WHERE a.tanggal_proses = CURDATE() AND a.created_by = ?
            ";
        return $this->db->query($rawSql, [$param, $param])->getRow();
    }

    public function getDailyData($param){
        $rawSql = "
            SELECT 
                DATE_FORMAT(a.tanggal_proses, '%Y-%m-%dT00:00:00.000Z') tanggal, COUNT(1) total
            FROM txn_takah a
            LEFT JOIN data_instansi b ON b.kodeins = a.kode_instansi
            WHERE a.created_by = ? AND WEEK(tanggal_proses) = WEEK(CURDATE())
            GROUP BY a.tanggal_proses
            ORDER BY a.tanggal_proses asc
        ";
        return $this->db->query($rawSql, [$param])->getResultArray();
    }

    public function getRefInstansi($param){
        $rawSql = "
            SELECT 
                LPAD(IFNULL(MAX(CAST(a.no_ref AS UNSIGNED)), 0) + 1, 5, '0') AS last
            FROM txn_takah a
            WHERE a.kode_instansi = 6108
        ";
        return $this->db->query($rawSql, [$param])->getRow();
    }

}
