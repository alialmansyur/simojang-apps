<?php

namespace App\Filters;

use App\Models\Auth\AccessControlModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ServiceAccessFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $nip = (string) session()->get('username');
        if ($nip === '') {
            return redirect()->to('/login')->with('error', 'Sesi login tidak valid.');
        }

        $path = trim($request->getUri()->getPath(), '/');
        if ($path === '') {
            return;
        }

        $acl = new AccessControlModel();
        $result = $acl->canNipAccessServiceByPath($nip, $path);

        // Fallback mode: service without any rule is still accessible.
        if ($result === null || $result === true) {
            return;
        }

        if ($request->isAJAX()) {
            return service('response')
                ->setStatusCode(403)
                ->setJSON([
                    'status'  => false,
                    'message' => 'Akses layanan ditolak. Hubungi administrator.',
                ]);
        }

        return redirect()->to('/timkerja')->with('error', 'Akses layanan ditolak.');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}

