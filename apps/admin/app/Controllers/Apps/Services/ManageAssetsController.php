<?php

namespace App\Controllers\Apps\Services;
use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

use App\Libraries\DataTablesLib;

use App\Models\Apps\AppsModel;
use App\Models\Apps\Services\ManageAssetsModel;

class ManageAssetsController extends BaseController
{
    protected $apps;
    protected $manageAssets;
    protected $dataTables;

    public function __construct(){
        $this->apps         = new AppsModel();
        $this->manageAssets = new ManageAssetsModel();
        $this->dataTables   = new DataTablesLib();        
    }

    public function index(){
        $categories = $this->manageAssets->getCategories();
        return $this->renderView('Apps/pages/services/manage-assets/index', [
            'seslog'     => session()->get(),
            'categories' => $categories
        ]);
    }     

    public function detail($uid){
        $category = $this->manageAssets->getCategoryByUid($uid);
        if (!$category) {
            return redirect()->to('/apps-manage-assets')->with('error', 'Kategori tidak ditemukan');
        }

        return $this->renderView('Apps/pages/services/manage-assets/detail', [
            'seslog'   => session()->get(),
            'category' => $category
        ]);
    }

    public function getData(){
        $categoryUid = $this->request->getPost('category_uid');

        $builder = $this->manageAssets->getBuilder('detail', $categoryUid);
        $columns = $this->manageAssets->getColumns('detail');
        $result  = $this->dataTables->render($builder, $columns);

        return $this->response->setJSON($result);
    }

    public function getSummary()
    {
        $categoryUid = $this->request->getPost('category_uid');
        
        return $this->response->setJSON([
            'status' => 'success',
            'summary' => $this->manageAssets->getSummary($categoryUid),
        ]);
    }

}
