<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RbacFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $userId = (int) session()->get('userid');
        if ($userId <= 0) {
            return redirect()->to('/login')->with('error', 'Sesi login tidak valid.');
        }

        $path = trim($request->getUri()->getPath(), '/');
        if ($path === '') {
            return;
        }

        // Endpoints without menu mapping stay open for backward compatibility.
        $db = db_connect();
        $permission = $db->table('auth_permissions')
            ->select('id, url')
            ->where('url', $path)
            ->limit(1)
            ->get()
            ->getRowArray();

        if (empty($permission)) {
            return;
        }

        // 1. Cek User dan Role
        $user = $db->table('auth_users')
            ->select('role')
            ->where('id', $userId)
            ->limit(1)
            ->get()
            ->getRowArray();

        $roleCode = strtoupper(trim((string) ($user['role'] ?? 'USR')));
        if ($roleCode === 'ADM') {
            return;
        }

        // 2. Cek izin via auth_role_permissions
        if (in_array('auth_role_permissions', $db->listTables(), true) && in_array('auth_roles', $db->listTables(), true)) {
            $rolePermit = $db->table('auth_role_permissions rp')
                ->select('rp.is_read')
                ->join('auth_roles r', 'r.id = rp.role_id', 'inner')
                ->where('r.role_code', $roleCode)
                ->where('r.is_active', 1)
                ->where('rp.permission_id', (int) $permission['id'])
                ->limit(1)
                ->get()
                ->getRowArray();

            if (!empty($rolePermit) && (int) ($rolePermit['is_read'] ?? 0) === 1) {
                return;
            }
        }

        // 3. Fallback ke auth_users_permissions
        $permit = $db->table('auth_users_permissions')
            ->select('is_read')
            ->where('user_id', $userId)
            ->where('permission_id', (int) $permission['id'])
            ->limit(1)
            ->get()
            ->getRowArray();

        if (!empty($permit) && (int) ($permit['is_read'] ?? 0) === 1) {
            return;
        }

        if ($request->isAJAX()) {
            return service('response')
                ->setStatusCode(403)
                ->setJSON([
                    'status'  => false,
                    'message' => 'Anda tidak memiliki izin akses halaman ini.',
                ]);
        }

        return redirect()->to('/home')->with('error', 'Anda tidak memiliki izin akses halaman ini.');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
