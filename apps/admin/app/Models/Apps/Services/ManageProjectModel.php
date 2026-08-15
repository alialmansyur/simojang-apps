<?php

namespace App\Models\Apps\Services;

use CodeIgniter\Model;

class ManageProjectModel extends Model
{
    protected $table = 'data_projects';

    public function __construct(){
        parent::__construct();
    }

    // ----------------------------
    //  QUERY BUILDER UTAMA
    // ----------------------------    
    public function getBuilder($type, $projectUid = null){
        switch ($type) {
            case 'progress':
                return $this->getProgressLogs($projectUid);
            case 'budget':
                return $this->getBudgetLogs($projectUid);
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

    public function getProjects()
    {
        $builder = $this->db->table('data_projects')
            ->select('*')
            ->where('is_active', 1)
            ->orderBy('created_at', 'DESC');

        return $builder->get()->getResultArray();
    }

    public function getProjectByUid($uid)
    {
        return $this->db->table('data_projects')
            ->where('uid', $uid)
            ->get()
            ->getRowArray();
    }

    public function getProgressLogs($projectUid)
    {
        $builder = $this->db->table('data_project_progress_logs a')
            ->select('a.*')
            ->join('data_projects b', 'b.id = a.project_id')
            ->where('b.uid', $projectUid)
            ->orderBy('a.log_date', 'DESC')
            ->orderBy('a.created_at', 'DESC');

        return $builder;
    }

    public function getBudgetLogs($projectUid)
    {
        $builder = $this->db->table('data_project_budget_realizations a')
            ->select('a.*')
            ->join('data_projects b', 'b.id = a.project_id')
            ->where('b.uid', $projectUid)
            ->orderBy('a.realization_date', 'DESC')
            ->orderBy('a.created_at', 'DESC');

        return $builder;
    }

    public function updateProjectProgress($projectId)
    {
        $latest = $this->db->table('data_project_progress_logs')
            ->where('project_id', $projectId)
            ->orderBy('log_date', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->limit(1)
            ->get()
            ->getRow();

        $percentage = $latest ? (float) $latest->actual_percentage : 0;

        $this->db->table('data_projects')
            ->where('id', $projectId)
            ->update([
                'progress_percentage' => $percentage,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
    }

    public function updateProjectBudget($projectId)
    {
        $totalRealization = $this->db->table('data_project_budget_realizations')
            ->selectSum('amount')
            ->where('project_id', $projectId)
            ->get()
            ->getRow()
            ->amount ?? 0;

        $this->db->table('data_projects')
            ->where('id', $projectId)
            ->update([
                'realized_budget_amount' => $totalRealization,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
    }

}
