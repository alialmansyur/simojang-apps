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

    public function __construct()
    {
        $this->apps         = new AppsModel();
        $this->manageAssets = new ManageAssetsModel();
        $this->dataTables   = new DataTablesLib();
        helper(['form', 'url']);
    }

    public function index()
    {
        $categories = $this->manageAssets->getCategories();
        return $this->renderView('Apps/pages/services/manage-assets/index', [
            'seslog'     => session()->get(),
            'categories' => $categories
        ]);
    }

    public function detail($uid)
    {
        $category = $this->manageAssets->getCategoryByUid($uid);
        if (!$category) {
            return redirect()->to('/apps-manage-assets')->with('error', 'Kategori tidak ditemukan');
        }

        return $this->renderView('Apps/pages/services/manage-assets/detail', [
            'seslog'   => session()->get(),
            'category' => $category
        ]);
    }

    // ----------------------------------------------------
    // CATEGORY CRUD
    // ----------------------------------------------------
    public function storeCategory()
    {
        $rules = [
            'name' => [
                'rules'  => 'required|min_length[2]|max_length[255]',
                'errors' => [
                    'required'   => 'Nama kategori wajib diisi.',
                    'min_length' => 'Nama kategori minimal 2 karakter.',
                    'max_length' => 'Nama kategori maksimal 255 karakter.',
                ],
            ],
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => implode(' ', $this->validator->getErrors()),
            ]);
        }

        $name = trim((string) $this->request->getPost('name'));

        $data = [
            'name' => $name,
        ];

        if ($this->manageAssets->insertCategory($data)) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Kategori asset berhasil ditambahkan.',
            ]);
        }

        return $this->response->setJSON([
            'status'  => 'error',
            'message' => 'Gagal menambahkan kategori asset.',
        ]);
    }

    public function updateCategory()
    {
        $rules = [
            'uid' => [
                'rules'  => 'required',
                'errors' => ['required' => 'UID kategori tidak valid.'],
            ],
            'name' => [
                'rules'  => 'required|min_length[2]|max_length[255]',
                'errors' => [
                    'required'   => 'Nama kategori wajib diisi.',
                    'min_length' => 'Nama kategori minimal 2 karakter.',
                    'max_length' => 'Nama kategori maksimal 255 karakter.',
                ],
            ],
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => implode(' ', $this->validator->getErrors()),
            ]);
        }

        $uid  = (string) $this->request->getPost('uid');
        $name = trim((string) $this->request->getPost('name'));

        $existing = $this->manageAssets->getCategoryByUid($uid);
        if (!$existing) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Kategori tidak ditemukan.',
            ]);
        }

        $data = ['name' => $name];

        if ($this->manageAssets->updateCategoryByUid($uid, $data)) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Kategori asset berhasil diperbarui.',
            ]);
        }

        return $this->response->setJSON([
            'status'  => 'error',
            'message' => 'Gagal memperbarui kategori asset.',
        ]);
    }

    public function getCategory()
    {
        $uid = (string) $this->request->getPost('uid');
        if (!$uid) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'UID tidak valid.',
            ]);
        }

        $category = $this->manageAssets->getCategoryByUid($uid);
        if ($category) {
            return $this->response->setJSON([
                'status' => 'success',
                'data'   => $category,
            ]);
        }

        return $this->response->setJSON([
            'status'  => 'error',
            'message' => 'Kategori tidak ditemukan.',
        ]);
    }

    public function removeCategory()
    {
        $uid = (string) $this->request->getPost('uid');
        if (!$uid) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'UID kategori tidak valid.',
            ]);
        }

        $category = $this->manageAssets->getCategoryByUid($uid);
        if (!$category) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Kategori tidak ditemukan.',
            ]);
        }

        // Cek relasi item aset
        $totalAssets = $this->manageAssets->countAssetsByCategoryUid($uid);
        if ($totalAssets > 0) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => "Kategori \"{$category['name']}\" tidak dapat dihapus karena masih memuat {$totalAssets} item aset. Hapus semua item terlebih dahulu.",
            ]);
        }

        if ($this->manageAssets->deleteCategoryByUid($uid)) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Kategori berhasil dihapus.',
            ]);
        }

        return $this->response->setJSON([
            'status'  => 'error',
            'message' => 'Gagal menghapus kategori.',
        ]);
    }

    // ----------------------------------------------------
    // ASSET DETAIL CRUD & DATATABLE
    // ----------------------------------------------------
    public function getData()
    {
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
            'status'  => 'success',
            'summary' => $this->manageAssets->getSummary($categoryUid),
        ]);
    }

    public function storeDetail()
    {
        $rules = [
            'category_uid' => [
                'rules'  => 'required',
                'errors' => ['required' => 'Kategori tidak valid.'],
            ],
            'kode' => [
                'rules'  => 'required|min_length[2]|max_length[100]',
                'errors' => [
                    'required'   => 'Kode asset wajib diisi.',
                    'min_length' => 'Kode asset minimal 2 karakter.',
                    'max_length' => 'Kode asset maksimal 100 karakter.',
                ],
            ],
            'uraian' => [
                'rules'  => 'required|min_length[2]|max_length[500]',
                'errors' => [
                    'required'   => 'Uraian asset wajib diisi.',
                    'min_length' => 'Uraian asset minimal 2 karakter.',
                    'max_length' => 'Uraian asset maksimal 500 karakter.',
                ],
            ],
            'satuan' => [
                'rules'  => 'required|max_length[50]',
                'errors' => [
                    'required'   => 'Satuan wajib diisi.',
                    'max_length' => 'Satuan maksimal 50 karakter.',
                ],
            ],
            'qty' => [
                'rules'  => 'required|numeric',
                'errors' => [
                    'required' => 'Qty wajib diisi.',
                    'numeric'  => 'Qty harus berupa angka yang valid.',
                ],
            ],
        ];

        // Format qty jika user input koma (e.g., "10,5")
        $rawQty = $this->request->getPost('qty');
        $normalizedQty = str_replace(',', '.', (string) $rawQty);
        $_POST['qty'] = $normalizedQty;

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => implode(' ', $this->validator->getErrors()),
            ]);
        }

        $categoryUid = (string) $this->request->getPost('category_uid');
        $category = $this->manageAssets->getCategoryByUid($categoryUid);
        if (!$category) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Kategori tidak ditemukan.']);
        }

        // Sanitasi nilai perolehan (convert ke bentuk angka murni integer/float)
        $rawNilai = (string) $this->request->getPost('nilai_perolehan');
        $cleanNilai = preg_replace('/[^\d]/', '', $rawNilai);
        $nilaiPerolehan = !empty($cleanNilai) ? (float) $cleanNilai : 0.00;

        // Kondisi
        $kondisiInput = (string) $this->request->getPost('kondisi');
        $kondisi = in_array($kondisiInput, ['Baik', 'Rusak Ringan', 'Rusak Berat'], true) ? $kondisiInput : 'Baik';

        // Area
        $areaInput = (string) $this->request->getPost('area');
        $area = in_array($areaInput, ['Kantor Regional III BKN', 'UPT Bandung', 'UPT Serang'], true) ? $areaInput : 'Kantor Regional III BKN';

        // Tahun perolehan
        $rawTahun = $this->request->getPost('tahun_perolehan');
        $tahun = (!empty($rawTahun) && is_numeric($rawTahun)) ? (int) $rawTahun : null;

        // Upload foto
        $fotoName = null;
        try {
            $fotoName = $this->handlePhotoUpload();
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ]);
        }

        $data = [
            'category_id'     => $category['id'],
            'kode'            => trim((string) $this->request->getPost('kode')),
            'subcode'         => trim((string) ($this->request->getPost('subcode') ?? '')) ?: null,
            'uraian'          => trim((string) $this->request->getPost('uraian')),
            'satuan'          => trim((string) $this->request->getPost('satuan')),
            'qty'             => (float) $normalizedQty,
            'foto'            => $fotoName,
            'kondisi'         => $kondisi,
            'area'            => $area,
            'lokasi'          => trim((string) ($this->request->getPost('lokasi') ?? '')) ?: null,
            'tahun_perolehan' => $tahun,
            'nilai_perolehan' => $nilaiPerolehan,
            'keterangan'      => trim((string) ($this->request->getPost('keterangan') ?? '')) ?: null,
        ];

        if ($this->manageAssets->insertDetail($data)) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data asset berhasil disimpan.']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menyimpan data asset.']);
    }

    public function updateDetail()
    {
        $rawQty = $this->request->getPost('qty');
        $normalizedQty = str_replace(',', '.', (string) $rawQty);
        $_POST['qty'] = $normalizedQty;

        $rules = [
            'id' => [
                'rules'  => 'required|is_natural_no_zero',
                'errors' => ['required' => 'ID data tidak valid.', 'is_natural_no_zero' => 'ID data tidak valid.'],
            ],
            'kode' => [
                'rules'  => 'required|min_length[2]|max_length[100]',
                'errors' => [
                    'required'   => 'Kode asset wajib diisi.',
                    'min_length' => 'Kode asset minimal 2 karakter.',
                    'max_length' => 'Kode asset maksimal 100 karakter.',
                ],
            ],
            'uraian' => [
                'rules'  => 'required|min_length[2]|max_length[500]',
                'errors' => [
                    'required'   => 'Uraian asset wajib diisi.',
                    'min_length' => 'Uraian asset minimal 2 karakter.',
                    'max_length' => 'Uraian asset maksimal 500 karakter.',
                ],
            ],
            'satuan' => [
                'rules'  => 'required|max_length[50]',
                'errors' => [
                    'required'   => 'Satuan wajib diisi.',
                    'max_length' => 'Satuan maksimal 50 karakter.',
                ],
            ],
            'qty' => [
                'rules'  => 'required|numeric',
                'errors' => [
                    'required' => 'Qty wajib diisi.',
                    'numeric'  => 'Qty harus berupa angka yang valid.',
                ],
            ],
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => implode(' ', $this->validator->getErrors()),
            ]);
        }

        $id = (int) $this->request->getPost('id');
        $existing = $this->manageAssets->getDetailById($id);
        if (!$existing) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data asset tidak ditemukan.']);
        }

        // Sanitasi nilai perolehan (convert ke bentuk angka murni integer/float)
        $rawNilai = (string) $this->request->getPost('nilai_perolehan');
        $cleanNilai = preg_replace('/[^\d]/', '', $rawNilai);
        $nilaiPerolehan = !empty($cleanNilai) ? (float) $cleanNilai : 0.00;

        // Kondisi
        $kondisiInput = (string) $this->request->getPost('kondisi');
        $kondisi = in_array($kondisiInput, ['Baik', 'Rusak Ringan', 'Rusak Berat'], true) ? $kondisiInput : ($existing['kondisi'] ?? 'Baik');

        // Area
        $areaInput = (string) $this->request->getPost('area');
        $area = in_array($areaInput, ['Kantor Regional III BKN', 'UPT Bandung', 'UPT Serang'], true) ? $areaInput : ($existing['area'] ?? 'Kantor Regional III BKN');

        // Tahun perolehan
        $rawTahun = $this->request->getPost('tahun_perolehan');
        $tahun = (!empty($rawTahun) && is_numeric($rawTahun)) ? (int) $rawTahun : null;

        $data = [
            'kode'            => trim((string) $this->request->getPost('kode')),
            'subcode'         => trim((string) ($this->request->getPost('subcode') ?? '')) ?: null,
            'uraian'          => trim((string) $this->request->getPost('uraian')),
            'satuan'          => trim((string) $this->request->getPost('satuan')),
            'qty'             => (float) $normalizedQty,
            'kondisi'         => $kondisi,
            'area'            => $area,
            'lokasi'          => trim((string) ($this->request->getPost('lokasi') ?? '')) ?: null,
            'tahun_perolehan' => $tahun,
            'nilai_perolehan' => $nilaiPerolehan,
            'keterangan'      => trim((string) ($this->request->getPost('keterangan') ?? '')) ?: null,
        ];

        // Handle penghapusan foto jika diminta user
        if ($this->request->getPost('remove_foto') === '1' && !empty($existing['foto'])) {
            $oldFilePath = FCPATH . 'apps/assets/uploads/assets/' . $existing['foto'];
            if (is_file($oldFilePath)) {
                @unlink($oldFilePath);
            }
            $data['foto'] = null;
        }

        // Handle upload foto baru
        try {
            $newFoto = $this->handlePhotoUpload($existing['foto'] ?? null);
            if ($newFoto !== null) {
                $data['foto'] = $newFoto;
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ]);
        }

        if ($this->manageAssets->updateDetail($id, $data)) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data asset berhasil diperbarui.']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal memperbarui data asset.']);
    }

    public function deleteDetail()
    {
        $id = (int) $this->request->getPost('id');
        if (!$id) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID tidak valid.']);
        }

        $existing = $this->manageAssets->getDetailById($id);
        if (!$existing) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data asset tidak ditemukan.']);
        }

        // Hapus file foto dari disk jika ada
        if (!empty($existing['foto'])) {
            $filePath = FCPATH . 'apps/assets/uploads/assets/' . $existing['foto'];
            if (is_file($filePath)) {
                @unlink($filePath);
            }
        }

        if ($this->manageAssets->deleteDetail($id)) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data asset berhasil dihapus.']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghapus data asset.']);
    }

    public function getDetail()
    {
        $id = (int) $this->request->getPost('id');
        if (!$id) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID tidak valid.']);
        }

        $data = $this->manageAssets->getDetailById($id);
        if ($data) {
            $data['foto_url'] = !empty($data['foto']) ? base_url('apps/assets/uploads/assets/' . $data['foto']) : null;
            return $this->response->setJSON(['status' => 'success', 'data' => $data]);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Data asset tidak ditemukan.']);
    }

    private function handlePhotoUpload(?string $oldPhoto = null): ?string
    {
        $file = $this->request->getFile('foto');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
            $mimeType     = $file->getMimeType();
            if (!in_array($mimeType, $allowedTypes, true)) {
                throw new \RuntimeException('Format foto harus berupa JPG, PNG, atau WEBP.');
            }

            if ($file->getSizeByUnit('mb') > 3) {
                throw new \RuntimeException('Ukuran foto maksimal 3MB.');
            }

            $uploadPath = FCPATH . 'apps/assets/uploads/assets/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $ext = $file->getClientExtension();
            $fileName = 'asset_' . date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '.' . ($ext ?: 'jpg');
            $file->move($uploadPath, $fileName);

            // Hapus file lama jika ada
            if ($oldPhoto) {
                $oldFilePath = $uploadPath . $oldPhoto;
                if (is_file($oldFilePath)) {
                    @unlink($oldFilePath);
                }
            }

            return $fileName;
        }

        return null;
    }
}
