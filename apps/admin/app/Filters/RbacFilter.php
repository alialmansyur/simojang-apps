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
