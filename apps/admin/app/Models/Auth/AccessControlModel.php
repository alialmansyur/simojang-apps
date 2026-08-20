<?php

namespace App\Models\Auth;

use CodeIgniter\Model;

class AccessControlModel extends Model
{
    protected $DBGroup = 'default';
    private ServicePermissionModel $permissionModel;


    public function __construct()
    {
        parent::__construct();
        $this->permissionModel = new ServicePermissionModel();
    }

    public function canNipAccessServiceByPath(string $nip, string $path): ?bool
    {
        $normalizedUrl = trim((string) $path, '/');
        if ($normalizedUrl === '') {
            return null;
        }

        // Cek apakah URL terdaftar di data_timkerja_layanan
        $layanan = $this->db->table('data_timkerja_layanan')
            ->select('id, url')
            ->where('url', $normalizedUrl)
            ->limit(1)
            ->get()
            ->getRowArray();

        // Jika bukan URL layanan tim kerja, loloskan
        if (empty($layanan)) {
            return null;
        }

        return $this->permissionModel->canNipAccessService($nip, (int) $layanan['id']);
    }
}
