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

    public function storeDetail()
    {
        $categoryUid = $this->request->getPost('category_uid');
        $category = $this->manageAssets->getCategoryByUid($categoryUid);
        if (!$category) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Kategori tidak valid.']);
        }

        $data = [
            'category_id' => $category['id'],
            'kode'        => $this->request->getPost('kode'),
            'subcode'     => $this->request->getPost('subcode'),
            'uraian'      => $this->request->getPost('uraian'),
            'satuan'      => $this->request->getPost('satuan'),
            'qty'         => str_replace(',', '.', $this->request->getPost('qty')),
        ];

        if ($this->manageAssets->insertDetail($data)) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data berhasil disimpan.']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menyimpan data.']);
    }

    public function updateDetail()
    {
        $id = $this->request->getPost('id');
        if (!$id) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID tidak ditemukan.']);
        }

        $data = [
            'kode'    => $this->request->getPost('kode'),
            'subcode' => $this->request->getPost('subcode'),
            'uraian'  => $this->request->getPost('uraian'),
            'satuan'  => $this->request->getPost('satuan'),
            'qty'     => str_replace(',', '.', $this->request->getPost('qty')),
        ];

        if ($this->manageAssets->updateDetail($id, $data)) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data berhasil diperbarui.']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal memperbarui data.']);
    }

    public function deleteDetail()
    {
        $id = $this->request->getPost('id');
        if (!$id) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID tidak valid.']);
        }

        if ($this->manageAssets->deleteDetail($id)) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data berhasil dihapus.']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghapus data.']);
    }

    public function getDetail()
    {
        $id = $this->request->getPost('id');
        $data = $this->manageAssets->getDetailById($id);

        if ($data) {
            return $this->response->setJSON(['status' => 'success', 'data' => $data]);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak ditemukan.']);
    }

}
