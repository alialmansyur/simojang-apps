<?php

namespace App\Controllers\Apps;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    /**
     * Halaman Utama / Welcome Landing Dashboard SIMOJANG
     */
    public function index()
    {
        $session = session()->get();
        $fullname = trim((string) ($session['fullname'] ?? $session['username'] ?? 'Rekan Kerja'));

        $data = [
            'title'       => 'Dashboard',
            'page_title'  => 'Dashboard Utama',
            'user_name'   => $fullname,
            'user_role'   => trim((string) ($session['role'] ?? 'User')),
            'seslog'      => $session,
        ];

        return $this->renderView('Apps/pages/dashboard', $data);
    }
}
