<?php

namespace App\Controllers\Apps;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\Apps\ActivityGalleryModel;

class ActivityGalleryController extends BaseController
{
    protected $galleryModel;

    public function __construct()
    {
        $this->galleryModel = new ActivityGalleryModel();
        helper(['form', 'url', 'filesystem']);
    }

    public function index()
    {
        $data = [
            'title'    => 'Galeri Kegiatan',
            'seslog'   => session()->get(),
            'timkerja' => $this->galleryModel->getTimKerja(),
        ];
        return $this->renderView('Apps/pages/activity-gallery', $data);
    }

    public function getLayanan()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setBody('Forbidden');
        }

        $timkerjaId = $this->request->getPost('timkerja_id');
        if (empty($timkerjaId)) {
            return $this->response->setJSON(['status' => 'success', 'data' => []]);
        }

        $layanan = $this->galleryModel->getLayananByTimKerja($timkerjaId);

        return $this->response->setJSON(['status' => 'success', 'data' => $layanan]);
    }

    public function getData()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setBody('Forbidden');
        }

        $search = $this->request->getPost('search');
        $timkerja = $this->request->getPost('timkerja');
        $bulan = $this->request->getPost('bulan');

        $data = $this->galleryModel->getAllData($search, $timkerja, $bulan);

        // Format data for JSON response
        $formatted = [];
        foreach ($data as $row) {
            $date = new \DateTime($row['tanggal_kegiatan']);
            // Indonesian month formatting
            $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            $monthName = $months[(int)$date->format('n') - 1];
            $formattedDate = $date->format('d') . ' ' . $monthName . ' ' . $date->format('Y');

            $formatted[] = [
                'id'             => $row['id'],
                'uid'            => $row['uid'],
                'team_id'        => $row['timkerja_id'],
                'team_name'      => $row['team_name'] ?? 'Tidak Diketahui',
                'layanan_id'     => $row['layanan_id'],
                'layanan_name'   => $row['nama_layanan'] ?? '',
                'title'          => $row['judul'],
                'date'           => $row['tanggal_kegiatan'],
                'date_formatted' => $formattedDate,
                'desc'           => $row['deskripsi'],
                'img'            => base_url($row['file_path'] . '/' . $row['file_name'])
            ];
        }

        return $this->response->setJSON(['status' => 'success', 'data' => $formatted]);
    }

    public function store()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setBody('Forbidden');
        }

        $id = $this->request->getPost('id');
        
        $rules = [
            'timkerja' => 'required',
            'layanan'  => 'permit_empty',
            'tanggal'  => 'required|valid_date',
            'judul'    => 'required',
            'deskripsi'=> 'required'
        ];

        // Only require foto if it's a new insert
        if (empty($id)) {
            $rules['foto'] = 'uploaded[foto]|max_size[foto,2048]|is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png,image/webp]';
        } else {
            // Allow empty if updating without changing photo, but validate if exists
            $file = $this->request->getFile('foto');
            if ($file && $file->isValid()) {
                $rules['foto'] = 'max_size[foto,2048]|is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png,image/webp]';
            }
        }

        if (!$this->validate($rules)) {
            $errors = $this->validator->getErrors();
            $firstError = reset($errors);
            return $this->response->setJSON(['status' => 'error', 'message' => $firstError]);
        }

        $layananId = $this->request->getPost('layanan');
        $dbData = [
            'timkerja_id'      => $this->request->getPost('timkerja'),
            'layanan_id'       => !empty($layananId) ? $layananId : null,
            'tanggal_kegiatan' => $this->request->getPost('tanggal'),
            'judul'            => $this->request->getPost('judul'),
            'deskripsi'        => $this->request->getPost('deskripsi'),
        ];

        $file = $this->request->getFile('foto');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Convert to webp and save
            $uploadPath = FCPATH . 'apps/assets/uploads/gallery/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $originalName = $file->getClientName();
            $fileSize = $file->getSize();
            $newName = $file->getRandomName(); // e.g. 12345.jpg
            $newNameWithoutExt = pathinfo($newName, PATHINFO_FILENAME);
            $webpName = $newNameWithoutExt . '.webp';

            // Convert to WEBP
            try {
                $image = \Config\Services::image()
                            ->withFile($file->getTempName())
                            ->convert(IMAGETYPE_WEBP)
                            ->save($uploadPath . $webpName, 80); // 80% quality
                
                // Set file metadata for DB
                $dbData['file_name'] = $webpName;
                $dbData['file_original_name'] = $originalName;
                $dbData['file_size'] = filesize($uploadPath . $webpName); // new webp size
                $dbData['file_ext'] = '.webp';
                $dbData['file_path'] = 'apps/assets/uploads/gallery';

                // Delete old file if updating
                if (!empty($id)) {
                    $oldData = $this->galleryModel->find($id);
                    if ($oldData && !empty($oldData['file_name'])) {
                        $oldPath = FCPATH . $oldData['file_path'] . '/' . $oldData['file_name'];
                        if (file_exists($oldPath)) {
                            unlink($oldPath);
                        }
                    }
                }

            } catch (\Exception $e) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal mengonversi gambar ke WebP: ' . $e->getMessage()]);
            }
        }

        if (empty($id)) {
            $dbData['uid'] = md5(uniqid(rand(), true));
            $this->galleryModel->insert($dbData);
            $message = 'Data dokumentasi kegiatan berhasil ditambahkan.';
        } else {
            $this->galleryModel->update($id, $dbData);
            $message = 'Data dokumentasi kegiatan berhasil diperbarui.';
        }

        return $this->response->setJSON(['status' => 'success', 'message' => $message]);
    }

    public function delete()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setBody('Forbidden');
        }

        $id = $this->request->getPost('id');
        $oldData = $this->galleryModel->find($id);
        
        if ($oldData) {
            if (!empty($oldData['file_name'])) {
                $oldPath = FCPATH . $oldData['file_path'] . '/' . $oldData['file_name'];
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            $this->galleryModel->delete($id);
            return $this->response->setJSON(['status' => 'success', 'message' => 'Dokumentasi berhasil dihapus.']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak ditemukan.']);
    }
}

