<?php

namespace App\Models\Pages;

use CodeIgniter\Model;

class KPModel extends Model
{
    protected $table            = 'txn_kp';
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

    public function getDataUploaded(){
        $builder = $this->db->table('txn_kp');
        $builder->where('created_at >=', date('Y-m-d 00:00:00'));
        $builder->where('created_at <', date('Y-m-d 00:00:00', strtotime('+1 day')));
        return $builder->get()->getResultArray();
    }

    public function getCurrentData($user,$status){
        $builder = $this->db->table('txn_kp a')
            ->select('a.*')
            ->where('a.verified_by', $user);

        if ((string) $status === '0') {
            $builder->where('a.status IS NULL', null, false);
        } else {
            $builder->where('a.status', $status);
        }

        return $builder;
    }    

    public function getAvaData() {
        return $this->db->query("
            SELECT 
            COUNT(*) AS total, CURDATE() taskdate
            FROM txn_kp 
            WHERE verified_by IS NULL
            AND created_at >= CURDATE()
            AND created_at < CURDATE() + INTERVAL 1 DAY
        ")->getRow();
    }

    public function getEnrolledTask(){
        return $this->db->query("
            SELECT 
            'Kenaikan Pangkat' layanan,
            a.nip, b.nama, a.target
            FROM txn_enroll a
            JOIN data_member b ON b.nip = a.nip
            WHERE a.layanan_id = 8
        ")->getResultArray();
    }

    public function getAllocatedTask($nip){
        $builder = $this->db->table('txn_kp_target');
        $builder->where('nip', $nip);
        $builder->where('task_date', date('Y-m-d'));
        $data = $builder->get()->getRow();

        if (!$data) return [];
        if ((int)$data->allocated < 1) return [];

        return $this->db->table('txn_kp')
            ->select('id, nip')
            ->where('verified_by', null)
            ->where('created_at >=', date('Y-m-d 00:00:00'))
            ->where('created_at <', date('Y-m-d 00:00:00', strtotime('+1 day')))
            ->orderBy('RAND()')
            ->limit((int)$data->allocated)
            ->get()
            ->getResultArray();
    }

    public function getResumeData($param){
        $rawSql = "
            SELECT 
                COUNT(1) total,
                SUM(CASE WHEN a.status = 'Disetujui' THEN 1 ELSE 0 END) AS acc,
                SUM(CASE WHEN a.status = 'BTS' THEN 1 ELSE 0 END) AS bts,
                SUM(CASE WHEN a.status = 'TMS' THEN 1 ELSE 0 END) AS tms
            FROM txn_kp a
            WHERE a.created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01 00:00:00')
            AND a.created_at < DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01 00:00:00')
            AND a.verified_by = ?
            ";
        return $this->db->query($rawSql, [$param])->getRow();
    }

    public function getDailyData($param){
        $rawSql = "
            SELECT 
                DATE_FORMAT(a.created_at, '%Y-%m-%dT00:00:00.000Z') tanggal, COUNT(a.kp_id) total
            FROM txn_kp_log_data a
            WHERE WEEK(a.created_at) = WEEK(CURDATE()) AND a.verified_by = ?
            GROUP BY DATE(a.created_at)
            ORDER BY a.created_at asc
        ";
        return $this->db->query($rawSql, [$param])->getResultArray();
    }    

}
