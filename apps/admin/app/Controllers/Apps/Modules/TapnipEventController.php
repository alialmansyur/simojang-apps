<?php

namespace App\Controllers\Apps\Modules;

use App\Controllers\BaseController;

class TapnipEventController extends BaseController
{
    private const TAPNIP_LAYANAN_ID = 2;

    private function fallbackEvents(): array
    {
        return [
            '2025 - Penetapan NIP CPNS',
            '2025 - Penetapan NI PPPK Tahap I',
            '2025 - Penetapan NI PPPK Tahap II',
            '2025 - Penetapan NI PPPK Paruh Waktu',
        ];
    }

    private function currentUser(): ?string
    {
        $user = session()->get('username');
        return is_string($user) && $user !== '' ? $user : null;
    }

    public function index()
    {
        $rows = $this->apps->getServiceEvents(self::TAPNIP_LAYANAN_ID);

        if (empty($rows)) {
            $rows = array_map(static function ($name) {
                return [
                    'id'          => null,
                    'nama'        => $name,
                    'is_fallback' => true,
                ];
            }, $this->fallbackEvents());
        } else {
            $rows = array_map(static function ($row) {
                $row['is_fallback'] = false;
                return $row;
            }, $rows);
        }

        return $this->response->setJSON([
            'status'      => 'success',
            'table_ready' => $this->apps->serviceEventsTableExists(),
            'data'        => $rows,
        ]);
    }

    public function create()
    {
        if (!$this->apps->serviceEventsTableExists()) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Table event belum tersedia. Jalankan SQL patch terlebih dahulu.',
            ]);
        }

        $nama = trim((string) $this->request->getPost('nama'));
        if ($nama === '') {
            return $this->response->setStatusCode(422)->setJSON([
                'status'  => 'error',
                'message' => 'Nama event wajib diisi.',
            ]);
        }

        if ($this->apps->hasDuplicateServiceEvent(self::TAPNIP_LAYANAN_ID, $nama)) {
            return $this->response->setStatusCode(409)->setJSON([
                'status'  => 'error',
                'message' => 'Nama event sudah ada.',
            ]);
        }

        $id = $this->apps->createServiceEvent(self::TAPNIP_LAYANAN_ID, $nama, $this->currentUser());
        if (!$id) {
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => 'Gagal menyimpan event.',
            ]);
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Event berhasil ditambahkan.',
            'id'      => (int) $id,
        ]);
    }

    public function update()
    {
        if (!$this->apps->serviceEventsTableExists()) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Table event belum tersedia. Jalankan SQL patch terlebih dahulu.',
            ]);
        }

        $id = (int) $this->request->getPost('id');
        $nama = trim((string) $this->request->getPost('nama'));

        if ($id <= 0) {
            return $this->response->setStatusCode(422)->setJSON([
                'status'  => 'error',
                'message' => 'ID event tidak valid.',
            ]);
        }

        if ($nama === '') {
            return $this->response->setStatusCode(422)->setJSON([
                'status'  => 'error',
                'message' => 'Nama event wajib diisi.',
            ]);
        }

        if ($this->apps->hasDuplicateServiceEvent(self::TAPNIP_LAYANAN_ID, $nama, $id)) {
            return $this->response->setStatusCode(409)->setJSON([
                'status'  => 'error',
                'message' => 'Nama event sudah ada.',
            ]);
        }

        $ok = $this->apps->updateServiceEvent($id, self::TAPNIP_LAYANAN_ID, $nama, $this->currentUser());
        if (!$ok) {
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => 'Gagal memperbarui event.',
            ]);
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Event berhasil diperbarui.',
        ]);
    }

    public function delete()
    {
        if (!$this->apps->serviceEventsTableExists()) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Table event belum tersedia. Jalankan SQL patch terlebih dahulu.',
            ]);
        }

        $id = (int) $this->request->getPost('id');
        if ($id <= 0) {
            return $this->response->setStatusCode(422)->setJSON([
                'status'  => 'error',
                'message' => 'ID event tidak valid.',
            ]);
        }

        $ok = $this->apps->deleteServiceEvent($id, self::TAPNIP_LAYANAN_ID);
        if (!$ok) {
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => 'Gagal menghapus event.',
            ]);
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Event berhasil dihapus.',
        ]);
    }
}

