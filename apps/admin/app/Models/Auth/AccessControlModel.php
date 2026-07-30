<?php

namespace App\Models\Auth;

use CodeIgniter\Model;

class AccessControlModel extends Model
{
    public function canNipAccessServiceByPath(string $nip, string $path): ?bool
    {
        if (!$this->tableExists('auth_service_access')) {
            return null;
        }

        $normalizedUrl = trim((string) $path, '/');
        if ($normalizedUrl === '') {
            return null;
        }

        $layanan = $this->db->table('data_timkerja_layanan')
            ->select('id, url')
            ->where('url', $normalizedUrl)
            ->limit(1)
            ->get()
            ->getRowArray();

        if (empty($layanan)) {
            return null;
        }

        $rules = $this->db->table('auth_service_access')
            ->select('access_mode, nip')
            ->where('layanan_id', (int) $layanan['id'])
            ->where('is_active', 1)
            ->get()
            ->getResultArray();

        // Fallback rule: no access record means service remains open (legacy behavior).
        if (empty($rules)) {
            return null;
        }

        foreach ($rules as $rule) {
            if (($rule['access_mode'] ?? '') === 'everyone') {
                return true;
            }
        }

        foreach ($rules as $rule) {
            if (($rule['access_mode'] ?? '') === 'assigned' && (string) $rule['nip'] === $nip) {
                return true;
            }
        }

        return false;
    }

    private function tableExists(string $table): bool
    {
        return in_array($table, $this->db->listTables(), true);
    }
}
