<?php

namespace App\Controllers\Apps;

use App\Controllers\BaseController;
use App\Models\Apps\RefModel;

class RefController extends BaseController
{
    private RefModel $refModel;

    public function __construct()
    {
        $this->refModel = new RefModel();
    }

    public function index()
    {
        return $this->renderView('Apps/pages/data/ref_landing', [
            'title' => 'Referensi',
        ]);
    }

    public function detail(string $slug = '')
    {
        $userId = (int) (session()->get('userid') ?? 0);
        $slug = strtolower(trim($slug));

        if ($slug === '' || !$this->refModel->canUserAccessSlug($userId, $slug)) {
            return redirect()->to('/ref')->with('error', 'Akses tabel referensi ditolak.');
        }

        return $this->renderView('Apps/pages/data/ref_detail', [
            'title' => 'Referensi',
        ]);
    }
}
