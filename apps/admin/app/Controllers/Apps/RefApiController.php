<?php

namespace App\Controllers\Apps;

use App\Controllers\BaseController;
use App\Models\Apps\RefModel;
use Throwable;

class RefApiController extends BaseController
{
    private RefModel $refModel;

    public function __construct()
    {
        $this->refModel = new RefModel();
    }

    public function tables()
    {
        try {
            $userId = (int) (session()->get('userid') ?? 0);
            $data = $this->refModel->getAllowedTables($userId);
            return $this->jsonSuccess('OK', $data);
        } catch (Throwable $e) {
            return $this->jsonError('Gagal memuat tabel referensi.', 500);
        }
    }

    public function schema(string $slug = '')
    {
        $access = $this->resolveAccess($slug);
        if ($access['error'] !== null) {
            return $access['error'];
        }

        $schema = $this->refModel->getSchema($access['table']);
        return $this->jsonSuccess('OK', [
            'slug' => $access['slug'],
            'table' => $access['table'],
            'pk' => $schema['pk'],
            'columns' => $schema['columns'],
        ]);
    }

    public function list(string $slug = '')
    {
        $access = $this->resolveAccess($slug);
        if ($access['error'] !== null) {
            return $access['error'];
        }

        $params = [
            'page' => $this->request->getGet('page'),
            'per_page' => $this->request->getGet('per_page'),
            'search' => $this->request->getGet('search'),
            'sort_by' => $this->request->getGet('sort_by'),
            'sort_dir' => $this->request->getGet('sort_dir'),
        ];

        try {
            $result = $this->refModel->getList($access['table'], $params);
            return $this->jsonSuccess('OK', $result['rows'], $result['meta']);
        } catch (Throwable $e) {
            return $this->jsonError('Gagal memuat data referensi.', 500);
        }
    }

    public function create(string $slug = '')
    {
        $access = $this->resolveAccess($slug);
        if ($access['error'] !== null) {
            return $access['error'];
        }

        $payload = (array) $this->request->getJSON(true);
        if (empty($payload)) {
            $payload = $this->request->getPost();
        }

        try {
            $newId = $this->refModel->insertRow($access['table'], $payload);
            if ($newId <= 0) {
                return $this->jsonError('Data tidak valid atau tidak ada kolom yang bisa disimpan.', 400);
            }
            return $this->jsonSuccess('Data berhasil ditambahkan.', ['id' => $newId], [], 201);
        } catch (Throwable $e) {
            return $this->jsonError('Gagal menyimpan data referensi.', 500);
        }
    }

    public function update(string $slug = '', string $id = '')
    {
        $access = $this->resolveAccess($slug);
        if ($access['error'] !== null) {
            return $access['error'];
        }

        $id = trim($id);
        if ($id === '') {
            return $this->jsonError('ID wajib diisi.', 400);
        }

        $payload = (array) $this->request->getJSON(true);
        if (empty($payload)) {
            $payload = $this->request->getRawInput();
        }

        try {
            $ok = $this->refModel->updateRow($access['table'], (string) $access['pk'], $id, $payload);
            if (!$ok) {
                return $this->jsonError('Gagal memperbarui data referensi.', 400);
            }
            return $this->jsonSuccess('Data berhasil diperbarui.', ['id' => $id]);
        } catch (Throwable $e) {
            return $this->jsonError('Gagal memperbarui data referensi.', 500);
        }
    }

    public function delete(string $slug = '', string $id = '')
    {
        $access = $this->resolveAccess($slug);
        if ($access['error'] !== null) {
            return $access['error'];
        }

        $id = trim($id);
        if ($id === '') {
            return $this->jsonError('ID wajib diisi.', 400);
        }

        try {
            $ok = $this->refModel->deleteRow($access['table'], (string) $access['pk'], $id);
            if (!$ok) {
                return $this->jsonError('Gagal menghapus data referensi.', 400);
            }
            return $this->jsonSuccess('Data berhasil dihapus.', ['id' => $id]);
        } catch (Throwable $e) {
            return $this->jsonError('Gagal menghapus data referensi.', 500);
        }
    }

    private function resolveAccess(string $slug): array
    {
        $userId = (int) (session()->get('userid') ?? 0);
        $normalized = strtolower(trim($slug));
        if ($normalized === '') {
            return ['error' => $this->jsonError('Table slug wajib diisi.', 400)];
        }

        $table = $this->refModel->resolveTableBySlug($normalized);
        if ($table === null) {
            return ['error' => $this->jsonError('Tabel referensi tidak ditemukan.', 404)];
        }

        if (!$this->refModel->canUserAccessSlug($userId, $normalized)) {
            return ['error' => $this->jsonError('Anda tidak memiliki izin akses tabel ini.', 403)];
        }

        $schema = $this->refModel->getSchema($table);
        return [
            'error' => null,
            'slug' => $normalized,
            'table' => $table,
            'pk' => $schema['pk'] ?? 'id',
        ];
    }

    private function jsonSuccess(string $message, $data = [], array $meta = [], int $code = 200)
    {
        return $this->response->setStatusCode($code)->setJSON([
            'status' => true,
            'message' => $message,
            'data' => $data,
            'meta' => $meta,
            'errors' => [],
        ]);
    }

    private function jsonError(string $message, int $code = 400, array $errors = [])
    {
        return $this->response->setStatusCode($code)->setJSON([
            'status' => false,
            'message' => $message,
            'data' => [],
            'meta' => [],
            'errors' => $errors,
        ]);
    }
}
