<?php

namespace App\Controllers\Apps\Services;
use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

use App\Libraries\DataTablesLib;

use App\Models\Apps\AppsModel;
use App\Models\Apps\Services\ManageProjectModel;

class ManageProjectController extends BaseController
{
    protected $apps;
    protected $manageProject;
    protected $dataTables;

    public function __construct(){
        $this->apps          = new AppsModel();
        $this->manageProject = new ManageProjectModel();
        $this->dataTables    = new DataTablesLib();        
    }

    public function index(){
        $projects = $this->manageProject->getProjects();
        return $this->renderView('Apps/pages/services/manage-project/index', [
            'seslog'   => session()->get(),
            'projects' => $projects
        ]);
    }     

    public function detail($uid){
        $project = $this->manageProject->getProjectByUid($uid);
        if (!$project) {
            return redirect()->to('/apps-manage-project')->with('error', 'Proyek tidak ditemukan');
        }

        return $this->renderView('Apps/pages/services/manage-project/detail', [
            'seslog'  => session()->get(),
            'project' => $project
        ]);
    }

    public function getProjectOverview(){
        $projectUid = $this->request->getPost('project_uid');
        $project = $this->manageProject->getProjectByUid($projectUid);
        
        if (!$project) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Project not found']);
        }
        
        return $this->response->setJSON([
            'status' => 'success',
            'data' => $project
        ]);
    }

    public function getData(){
        $projectUid = $this->request->getPost('project_uid');
        $type       = $this->request->getPost('type'); // 'progress' or 'budget'

        $builder = $this->manageProject->getBuilder($type, $projectUid);
        $columns = $this->manageProject->getColumns($type, $projectUid);
        $result  = $this->dataTables->render($builder, $columns);

        return $this->response->setJSON($result);
    }

    public function storeProject(){
        $sess = session()->get();
        
        $name        = $this->request->getPost('name');
        $description = $this->request->getPost('description');
        $category    = $this->request->getPost('category');
        $startDate   = $this->request->getPost('start_date');
        $endDate     = $this->request->getPost('target_end_date');
        $budget      = str_replace(',', '', $this->request->getPost('budget_amount') ?? '0');
        
        $rules = [
            'name'            => 'required',
            'start_date'      => 'required|valid_date',
            'target_end_date' => 'required|valid_date',
        ];

        if (!$this->validate($rules)) {
            $errorsArray = $this->validator->getErrors();
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => implode(', ', $errorsArray),
                'data'    => $this->request->getPost()
            ]);
        }

        $uid = sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );

        $dataInsert = [
            'uid'                 => $uid,
            'name'                => $name,
            'description'         => $description,
            'category'            => $category,
            'status'              => 'Ongoing',
            'start_date'          => $startDate,
            'target_end_date'     => $endDate,
            'budget_amount'       => (float) $budget,
            'created_at'          => date('Y-m-d H:i:s'),
            'created_by'          => $sess['username']
        ];

        $this->apps->storeData($dataInsert, 'data_projects');

        return $this->response->setStatusCode(200)->setJSON([
            'status'  => 'success',
            'message' => 'Proyek berhasil ditambahkan.',
        ]);
    }

    public function updateProject(){
        $sess = session()->get();
        $uid = $this->request->getPost('project_uid');
        
        $name        = $this->request->getPost('name');
        $description = $this->request->getPost('description');
        $category    = $this->request->getPost('category');
        $startDate   = $this->request->getPost('start_date');
        $endDate     = $this->request->getPost('target_end_date');
        $budget      = str_replace(',', '', $this->request->getPost('budget_amount') ?? '0');
        
        $rules = [
            'project_uid'     => 'required',
            'name'            => 'required',
            'start_date'      => 'required|valid_date',
            'target_end_date' => 'required|valid_date',
        ];

        if (!$this->validate($rules)) {
            $errorsArray = $this->validator->getErrors();
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => implode(', ', $errorsArray),
                'data'    => $this->request->getPost()
            ]);
        }

        $dataUpdate = [
            'name'                => $name,
            'description'         => $description,
            'category'            => $category,
            'start_date'          => $startDate,
            'target_end_date'     => $endDate,
            'budget_amount'       => (float) $budget,
        ];

        // Ensure apps model has an updateData method, assuming it does based on standard CI models
        // Using common pattern: updateData($data, $table, $where)
        $this->apps->updateData($dataUpdate, 'data_projects', ['uid' => $uid]);

        return $this->response->setStatusCode(200)->setJSON([
            'status'  => 'success',
            'message' => 'Proyek berhasil diperbarui.',
        ]);
    }

    public function storeProgress()
    {
        $sess       = session()->get();
        $projectUid = $this->request->getPost('project_uid');
        $logDate    = $this->request->getPost('log_date');
        $target     = $this->request->getPost('target_percentage');
        $actual     = $this->request->getPost('actual_percentage');
        $notes      = $this->request->getPost('notes');

        $rules = [
            'project_uid'       => 'required',
            'log_date'          => 'required|valid_date',
            'actual_percentage' => 'required|numeric'
        ];

        if (!$this->validate($rules)) {
            $errorsArray = $this->validator->getErrors();
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => implode(', ', $errorsArray)
            ]);
        }

        $project = $this->manageProject->getProjectByUid($projectUid);
        if (!$project) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Project not found']);
        }

        $id         = $this->request->getPost('id');

        $dataLog = [
            'project_id'        => $project['id'],
            'log_date'          => $logDate,
            'target_percentage' => (float) $target,
            'actual_percentage' => (float) $actual,
            'notes'             => $notes,
            'created_by'        => $sess['username']
        ];

        if (!empty($id)) {
            $this->apps->updateData($dataLog, $id, 'data_project_progress_logs');
            $msg = 'Progres berhasil diperbarui';
        } else {
            $dataLog['created_at'] = date('Y-m-d H:i:s');
            $this->apps->storeData($dataLog, 'data_project_progress_logs');
            $msg = 'Progres berhasil disimpan';
        }

        // Update main project progress
        $this->manageProject->updateProjectProgress($project['id'], (float) $actual);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => $msg
        ]);
    }

    public function storeBudget()
    {
        $sess       = session()->get();
        $projectUid = $this->request->getPost('project_uid');
        $date       = $this->request->getPost('realization_date');
        $amount     = str_replace(',', '', $this->request->getPost('amount') ?? '0');
        $desc       = $this->request->getPost('description');

        $rules = [
            'project_uid'      => 'required',
            'realization_date' => 'required|valid_date',
            'amount'           => 'required'
        ];

        if (!$this->validate($rules)) {
            $errorsArray = $this->validator->getErrors();
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => implode(', ', $errorsArray)
            ]);
        }

        $project = $this->manageProject->getProjectByUid($projectUid);
        if (!$project) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Project not found']);
        }

        $id         = $this->request->getPost('id');

        $dataRealization = [
            'project_id'       => $project['id'],
            'realization_date' => $date,
            'amount'           => (float) $amount,
            'description'      => $desc,
            'created_by'       => $sess['username']
        ];

        if (!empty($id)) {
            $this->apps->updateData($dataRealization, $id, 'data_project_budget_realizations');
            $msg = 'Realisasi anggaran berhasil diperbarui';
        } else {
            $dataRealization['created_at'] = date('Y-m-d H:i:s');
            $this->apps->storeData($dataRealization, 'data_project_budget_realizations');
            $msg = 'Realisasi anggaran berhasil disimpan';
        }

        // Update main project budget
        $this->manageProject->updateProjectBudget($project['id']);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => $msg
        ]);
    }

    public function removeProject(){
        $sess = session()->get();
        $uid  = trim((string) $this->request->getPost('uid'));
        if ($uid === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => false,
                'message' => 'UID proyek tidak valid',
            ]);
        }
        
        $project = $this->manageProject->getProjectByUid($uid);
        if (!$project) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => false,
                'message' => 'Proyek tidak ditemukan',
            ]);
        }

        $this->apps->removeData($project['id'], 'data_projects');
        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Proyek berhasil dihapus',
        ]);
    }


}
