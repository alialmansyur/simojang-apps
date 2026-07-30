<?php

namespace App\Controllers\Apps;

use App\Controllers\BaseController;
use App\Models\Apps\SettingManagerModel;
use App\Models\Apps\SystemSettingModel;

class SettingManagerController extends BaseController
{
    private SettingManagerModel $manager;
    private SystemSettingModel $settings;

    public function __construct()
    {
        $this->manager = new SettingManagerModel();
        $this->settings = new SystemSettingModel();
    }

    public function roleManager()
    {
        return $this->renderView('Apps/pages/data/role_manager', [
            'title' => 'Role Manager',
        ]);
    }

    public function getRoleUsers()
    {
        $q = trim((string) ($this->request->getGet('q') ?? ''));
        $list = $this->manager->getUsers($q, 30);

        return $this->jsonSuccess('OK', $list);
    }

    public function getRoleTree()
    {
        $userId = (int) ($this->request->getGet('user_id') ?? 0);
        if ($userId <= 0) {
            return $this->jsonError('user_id wajib diisi', 422, ['user_id' => 'user_id wajib diisi']);
        }

        $tree = $this->manager->getMenuTreeWithUserPermission($userId);
        return $this->jsonSuccess('OK', $tree);
    }

    public function toggleRolePermission()
    {
        $userId = (int) ($this->request->getPost('user_id') ?? 0);
        $menuId = (int) ($this->request->getPost('menu_id') ?? 0);
        $allowedRaw = $this->request->getPost('allowed');
        $allowed = in_array((string) $allowedRaw, ['1', 'true', 'on'], true);

        if ($userId <= 0 || $menuId <= 0) {
            return $this->jsonError(
                'user_id dan menu_id wajib valid',
                422,
                ['user_id' => 'user_id wajib valid', 'menu_id' => 'menu_id wajib valid']
            );
        }

        $ok = $this->manager->togglePermission($userId, $menuId, $allowed);
        if (!$ok) {
            return $this->jsonError('Gagal memperbarui permission', 500);
        }

        return $this->jsonSuccess('Permission diperbarui', [
            'user_id' => $userId,
            'menu_id' => $menuId,
            'allowed' => $allowed,
        ]);
    }

    public function serviceManager()
    {
        return $this->renderView('Apps/pages/data/service_manager', [
            'title' => 'Service Manager',
        ]);
    }

    public function getServiceList()
    {
        $rows = $this->manager->getServiceAccessRows();
        return $this->jsonSuccess('OK', $rows);
    }

    public function getServiceDetail()
    {
        $serviceId = (int) ($this->request->getGet('service_id') ?? 0);
        if ($serviceId <= 0) {
            return $this->jsonError('service_id wajib diisi', 422, ['service_id' => 'service_id wajib diisi']);
        }

        $service = $this->manager->getServiceAccessRowById($serviceId);
        if (!$service) {
            return $this->jsonError('Layanan tidak ditemukan', 404);
        }

        $assignments = $this->manager->getServiceAssignmentsWithPegawai($serviceId);

        return $this->jsonSuccess('OK', [
            'service'     => $service,
            'assignments' => $assignments,
        ]);
    }

    public function getServicePegawai()
    {
        $q = trim((string) ($this->request->getGet('q') ?? ''));
        $list = $this->manager->getMasterPegawaiBySearch($q, 30);

        return $this->jsonSuccess('OK', $list);
    }

    public function saveServiceModeApi()
    {
        $serviceId = (int) ($this->request->getPost('layanan_id') ?? 0);
        $mode = (string) ($this->request->getPost('access_mode') ?? '');

        if ($serviceId <= 0) {
            return $this->jsonError('layanan_id wajib valid', 422, ['layanan_id' => 'layanan_id wajib valid']);
        }

        $ok = $this->manager->setServiceMode($serviceId, $mode);
        if (!$ok) {
            return $this->jsonError('Gagal memperbarui mode layanan', 500);
        }

        return $this->jsonSuccess('Mode akses layanan diperbarui', [
            'layanan_id'  => $serviceId,
            'access_mode' => $mode === 'assigned' ? 'assigned' : 'everyone',
        ]);
    }

    public function addServiceAssigneeApi()
    {
        $serviceId = (int) ($this->request->getPost('layanan_id') ?? 0);
        $nip = trim((string) ($this->request->getPost('nip') ?? ''));

        if ($serviceId <= 0 || $nip === '') {
            return $this->jsonError(
                'layanan_id dan nip wajib diisi',
                422,
                ['layanan_id' => 'layanan_id wajib diisi', 'nip' => 'nip wajib diisi']
            );
        }

        $ok = $this->manager->addAssignedNip($serviceId, $nip);
        if (!$ok) {
            return $this->jsonError(
                'Gagal menambahkan NIP assign. Pastikan NIP valid dari master pegawai.',
                500,
                ['nip' => 'NIP tidak valid atau gagal disimpan']
            );
        }

        return $this->jsonSuccess('NIP berhasil ditambahkan', [
            'layanan_id' => $serviceId,
            'nip'        => $nip,
        ]);
    }

    public function removeServiceAssigneeApi()
    {
        $serviceId = (int) ($this->request->getPost('layanan_id') ?? 0);
        $nip = trim((string) ($this->request->getPost('nip') ?? ''));

        if ($serviceId <= 0 || $nip === '') {
            return $this->jsonError(
                'layanan_id dan nip wajib valid',
                422,
                ['layanan_id' => 'layanan_id wajib valid', 'nip' => 'nip wajib valid']
            );
        }

        $ok = $this->manager->removeAssignedNip($serviceId, $nip);
        if (!$ok) {
            return $this->jsonError('Gagal menghapus NIP assign', 500);
        }

        return $this->jsonSuccess('NIP berhasil dihapus', [
            'layanan_id' => $serviceId,
            'nip'        => $nip,
        ]);
    }

    public function saveServiceMode()
    {
        $serviceId = (int) $this->request->getPost('layanan_id');
        $mode = (string) $this->request->getPost('access_mode');
        if ($serviceId > 0) {
            $this->manager->setServiceMode($serviceId, $mode);
        }
        return redirect()->to('/manage-layanan?service_id=' . $serviceId)->with('success', 'Mode akses layanan diperbarui.');
    }

    public function addServiceAssignee()
    {
        $serviceId = (int) $this->request->getPost('layanan_id');
        $nip = trim((string) $this->request->getPost('nip'));
        if ($serviceId > 0 && $nip !== '') {
            $this->manager->addAssignedNip($serviceId, $nip);
        }
        return redirect()->to('/manage-layanan?service_id=' . $serviceId)->with('success', 'NIP berhasil ditambahkan.');
    }

    public function removeServiceAssignee()
    {
        $serviceId = (int) $this->request->getPost('layanan_id');
        $nip = trim((string) $this->request->getPost('nip'));
        if ($serviceId > 0 && $nip !== '') {
            $this->manager->removeAssignedNip($serviceId, $nip);
        }
        return redirect()->to('/manage-layanan?service_id=' . $serviceId)->with('success', 'NIP berhasil dihapus.');
    }

    public function systemSetting()
    {
        return $this->renderView('Apps/pages/data/system_setting', [
            'title' => 'System Setting',
        ]);
    }

    public function getSystemSettingData()
    {
        return $this->jsonSuccess('OK', $this->settings->getAllAsMap());
    }

    public function saveSystemSetting()
    {
        return redirect()->to('/manage-setting')->with('warning', 'Gunakan endpoint API /api/manage-setting/save');
    }

    public function saveSystemSettingApi()
    {
        $userId = (int) (session()->get('userid') ?? 0);
        $payload = $this->request->getJSON(true);
        if (!is_array($payload) || empty($payload)) {
            $payload = $this->request->getPost();
        }

        $keys = $this->getSystemSettingKeys();
        $normalized = [];
        $errors = [];
        foreach ($keys as $key => $group) {
            $inputKey = str_replace('.', '__', $key);
            $val = trim((string) ($payload[$inputKey] ?? ''));
            if (in_array($key, ['app.name', 'app.timezone', 'env.flag'], true) && $val === '') {
                $errors[$inputKey] = 'Wajib diisi';
            }
            if (in_array($key, ['pagination.default_per_page', 'session.timeout_minutes', 'security.max_login_attempt', 'security.lock_duration_minutes', 'upload.max_size_mb'], true)
                && $val !== '' && !ctype_digit($val)) {
                $errors[$inputKey] = 'Harus berupa angka bulat';
            }
            $normalized[$key] = ['group' => $group, 'value' => $val];
        }

        if (!empty($errors)) {
            return $this->jsonError('Validasi gagal', 422, $errors);
        }

        $ok = $this->settings->bulkUpsert($normalized, $userId);
        if (!$ok) {
            return $this->jsonError('Gagal menyimpan system setting', 500);
        }

        return $this->jsonSuccess('System setting berhasil disimpan', $this->settings->getAllAsMap());
    }

    public function smtpSetting()
    {
        return $this->renderView('Apps/pages/data/smtp_setting', [
            'title' => 'SMTP Setting',
        ]);
    }

    public function getSmtpSettingData()
    {
        return $this->jsonSuccess('OK', $this->settings->getGroup('smtp'));
    }

    public function saveSmtpSetting()
    {
        return redirect()->to('/manage-smtp')->with('warning', 'Gunakan endpoint API /api/manage-smtp/save');
    }

    public function saveSmtpSettingApi()
    {
        $userId = (int) (session()->get('userid') ?? 0);
        $payload = $this->request->getJSON(true);
        if (!is_array($payload) || empty($payload)) {
            $payload = $this->request->getPost();
        }

        $keys = ['smtp.host', 'smtp.port', 'smtp.username', 'smtp.password', 'smtp.encryption', 'smtp.from_name', 'smtp.from_email'];
        $errors = [];
        $normalized = [];
        foreach ($keys as $key) {
            $inputKey = str_replace('.', '__', $key);
            $val = trim((string) ($payload[$inputKey] ?? ''));
            if (in_array($key, ['smtp.host', 'smtp.port', 'smtp.username', 'smtp.from_email'], true) && $val === '') {
                $errors[$inputKey] = 'Wajib diisi';
            }
            if ($key === 'smtp.port' && $val !== '' && !ctype_digit($val)) {
                $errors[$inputKey] = 'Port harus angka';
            }
            if ($key === 'smtp.from_email' && $val !== '' && !filter_var($val, FILTER_VALIDATE_EMAIL)) {
                $errors[$inputKey] = 'Format email tidak valid';
            }
            $normalized[$key] = ['group' => 'smtp', 'value' => $val];
        }

        if (!empty($errors)) {
            return $this->jsonError('Validasi gagal', 422, $errors);
        }

        $ok = $this->settings->bulkUpsert($normalized, $userId);
        if (!$ok) {
            return $this->jsonError('Gagal menyimpan SMTP setting', 500);
        }

        return $this->jsonSuccess('SMTP setting berhasil disimpan', $this->settings->getGroup('smtp'));
    }

    private function getSystemSettingKeys(): array
    {
        return [
            'app.name' => 'general',
            'app.logo' => 'general',
            'app.favicon' => 'general',
            'app.maintenance_mode' => 'general',
            'app.timezone' => 'general',
            'pagination.default_per_page' => 'general',
            'upload.max_size_mb' => 'upload',
            'upload.allowed_types' => 'upload',
            'session.timeout_minutes' => 'session',
            'security.max_login_attempt' => 'security',
            'security.lock_duration_minutes' => 'security',
            'security.default_role_code' => 'security',
            'env.flag' => 'env',
        ];
    }

    private function jsonSuccess(string $message, $data = [], int $code = 200)
    {
        return $this->response->setStatusCode($code)->setJSON([
            'status'  => true,
            'message' => $message,
            'data'    => $data,
            'errors'  => [],
        ]);
    }

    private function jsonError(string $message, int $code = 400, array $errors = [])
    {
        return $this->response->setStatusCode($code)->setJSON([
            'status'  => false,
            'message' => $message,
            'data'    => [],
            'errors'  => $errors,
        ]);
    }
}
