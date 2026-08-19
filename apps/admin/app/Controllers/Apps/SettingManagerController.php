<?php

namespace App\Controllers\Apps;

use App\Controllers\BaseController;
use App\Models\Apps\SettingManagerModel;
use App\Models\Apps\SystemSettingModel;
use App\Models\Auth\UserModel;
use App\Libraries\DataTablesLib;

class SettingManagerController extends BaseController
{
    private SettingManagerModel $manager;
    private SystemSettingModel $settings;
    private UserModel $users;
    private DataTablesLib $dataTables;

    public function __construct()
    {
        $this->manager = new SettingManagerModel();
        $this->settings = new SystemSettingModel();
        $this->users = new UserModel();
        $this->dataTables = new DataTablesLib();
    }

    public function roleManager()
    {
        $roles = $this->manager->getRoles();
        return $this->renderView('Apps/pages/data/role_manager', [
            'title' => 'Kelola Role & Hak Akses',
            'roles' => $roles,
        ]);
    }

    public function getRoles()
    {
        $onlyActive = (string) $this->request->getGet('active') === '1';
        $roles = $this->manager->getRoles($onlyActive);
        return $this->jsonSuccess('OK', $roles);
    }

    public function createRole()
    {
        $payload = $this->getPayload();

        $roleCode = strtoupper(trim((string) ($payload['role_code'] ?? '')));
        $roleName = trim((string) ($payload['role_name'] ?? ''));
        $description = trim((string) ($payload['description'] ?? ''));
        $copyFromRoleId = !empty($payload['copy_from_role_id']) ? (int) $payload['copy_from_role_id'] : null;

        $errors = [];
        if ($roleCode === '') {
            $errors['role_code'] = 'Kode role wajib diisi';
        } elseif (!preg_match('/^[A-Z0-9_]{2,30}$/', $roleCode)) {
            $errors['role_code'] = 'Kode role hanya boleh huruf besar, angka, dan underscore (2-30 karakter)';
        }

        if ($roleName === '') {
            $errors['role_name'] = 'Nama role wajib diisi';
        } elseif (strlen($roleName) < 3 || strlen($roleName) > 100) {
            $errors['role_name'] = 'Nama role harus 3-100 karakter';
        }

        if (!empty($errors)) {
            return $this->jsonError('Validasi gagal', 422, $errors);
        }

        $existingRole = (new \App\Models\Auth\RoleModel())->getRoleByCode($roleCode);
        if ($existingRole) {
            return $this->jsonError('Kode role sudah digunakan', 422, ['role_code' => 'Kode role sudah digunakan']);
        }

        $newRoleId = $this->manager->createRole([
            'role_code'   => $roleCode,
            'role_name'   => $roleName,
            'description' => $description,
        ], $copyFromRoleId);

        if ($newRoleId <= 0) {
            return $this->jsonError('Gagal menambahkan role baru', 500);
        }

        $createdRole = $this->manager->getRoleById($newRoleId);
        return $this->jsonSuccess('Role baru berhasil ditambahkan', $createdRole, 201);
    }

    public function updateRole()
    {
        $payload = $this->getPayload();

        $roleId = (int) ($payload['role_id'] ?? 0);
        $roleName = trim((string) ($payload['role_name'] ?? ''));
        $description = trim((string) ($payload['description'] ?? ''));
        $isActive = isset($payload['is_active']) ? in_array((string) $payload['is_active'], ['1', 'true', 'on'], true) : true;

        if ($roleId <= 0) {
            return $this->jsonError('role_id wajib valid', 422, ['role_id' => 'role_id wajib valid']);
        }

        if ($roleName === '') {
            return $this->jsonError('Nama role wajib diisi', 422, ['role_name' => 'Nama role wajib diisi']);
        }

        $ok = $this->manager->updateRole($roleId, [
            'role_name'   => $roleName,
            'description' => $description,
            'is_active'   => $isActive,
        ]);

        if (!$ok) {
            return $this->jsonError('Gagal memperbarui role', 500);
        }

        $updatedRole = $this->manager->getRoleById($roleId);
        return $this->jsonSuccess('Role berhasil diperbarui', $updatedRole);
    }

    public function deleteRole()
    {
        $payload = $this->getPayload();

        $roleId = (int) ($payload['role_id'] ?? 0);
        if ($roleId <= 0) {
            return $this->jsonError('role_id wajib valid', 422, ['role_id' => 'role_id wajib valid']);
        }

        $result = $this->manager->deleteRole($roleId);
        if (!($result['status'] ?? false)) {
            return $this->jsonError($result['message'] ?? 'Gagal menghapus role', 400);
        }

        return $this->jsonSuccess($result['message'] ?? 'Role berhasil dihapus', ['role_id' => $roleId]);
    }

    public function getRoleTree()
    {
        $roleId = (int) ($this->request->getGet('role_id') ?? 0);
        
        // Backward compatibility jika yang dikirim user_id
        if ($roleId <= 0) {
            $userId = (int) ($this->request->getGet('user_id') ?? 0);
            if ($userId > 0) {
                $user = (new \App\Models\Auth\UserModel())->find($userId);
                if ($user && !empty($user['role'])) {
                    $role = (new \App\Models\Auth\RoleModel())->getRoleByCode($user['role']);
                    if ($role) {
                        $roleId = (int) $role['id'];
                    }
                }
            }
        }

        if ($roleId <= 0) {
            return $this->jsonError('role_id wajib diisi dan valid', 422, ['role_id' => 'role_id wajib diisi dan valid']);
        }

        $role = $this->manager->getRoleById($roleId);
        if (!$role) {
            return $this->jsonError('Role tidak ditemukan', 404);
        }

        $tree = $this->manager->getMenuTreeWithRolePermission($roleId);

        return $this->jsonSuccess('OK', [
            'role' => $role,
            'tree' => $tree,
        ]);
    }

    public function toggleRolePermission()
    {
        $payload = $this->getPayload();

        $roleId = (int) ($payload['role_id'] ?? 0);
        $menuId = (int) ($payload['menu_id'] ?? 0);
        $allowedRaw = $payload['allowed'] ?? false;
        $allowed = in_array((string) $allowedRaw, ['1', 'true', 'on', 'true'], true) || $allowedRaw === true;
        $cascade = !isset($payload['cascade']) || in_array((string) $payload['cascade'], ['1', 'true', 'on'], true) || $payload['cascade'] === true;

        // Fallback jika dikirim user_id
        if ($roleId <= 0) {
            $userId = (int) ($payload['user_id'] ?? 0);
            if ($userId > 0) {
                $user = (new \App\Models\Auth\UserModel())->find($userId);
                if ($user && !empty($user['role'])) {
                    $role = (new \App\Models\Auth\RoleModel())->getRoleByCode($user['role']);
                    if ($role) {
                        $roleId = (int) $role['id'];
                    }
                }
            }
        }

        if ($roleId <= 0 || $menuId <= 0) {
            return $this->jsonError(
                'role_id dan menu_id wajib valid',
                422,
                ['role_id' => 'role_id wajib valid', 'menu_id' => 'menu_id wajib valid']
            );
        }

        $result = $this->manager->toggleRolePermission($roleId, $menuId, $allowed, $cascade);
        if (!($result['status'] ?? false)) {
            return $this->jsonError('Gagal memperbarui permission role', 500);
        }

        $role = $this->manager->getRoleById($roleId);

        return $this->jsonSuccess('Hak akses role berhasil diperbarui', [
            'role_id'      => $roleId,
            'menu_id'      => $menuId,
            'allowed'      => $allowed,
            'affected_ids' => $result['affected_ids'] ?? [$menuId],
            'role'         => $role,
        ]);
    }

    public function getRoleUsers()
    {
        $roleId = (int) ($this->request->getGet('role_id') ?? 0);
        $q = trim((string) ($this->request->getGet('q') ?? ''));

        if ($roleId > 0) {
            $assignedUsers = $this->manager->getRoleUsersList($roleId, $q, 100);
            $availableUsers = $this->manager->getAvailableUsersForRole($roleId, $q, 50);
            return $this->jsonSuccess('OK', [
                'role_id'         => $roleId,
                'assigned_users'  => $assignedUsers,
                'available_users' => $availableUsers,
            ]);
        }

        // Fallback global user search
        $list = $this->manager->getUsers($q, 30);
        return $this->jsonSuccess('OK', $list);
    }

    public function assignUserRole()
    {
        $payload = $this->getPayload();

        $userId = (int) ($payload['user_id'] ?? 0);
        $roleId = (int) ($payload['role_id'] ?? 0);

        if ($userId <= 0 || $roleId <= 0) {
            return $this->jsonError('user_id dan role_id wajib valid', 422, [
                'user_id' => 'user_id wajib valid',
                'role_id' => 'role_id wajib valid',
            ]);
        }

        $ok = $this->manager->assignUserRole($userId, $roleId);
        if (!$ok) {
            return $this->jsonError('Gagal mengubah role user', 500);
        }

        $role = $this->manager->getRoleById($roleId);
        return $this->jsonSuccess('Role user berhasil diubah', [
            'user_id' => $userId,
            'role'    => $role,
        ]);
    }

    private function getPayload(): array
    {
        $post = $this->request->getPost();
        if (!empty($post) && is_array($post)) {
            return $post;
        }

        try {
            $raw = $this->request->getBody();
            if (!empty($raw) && is_string($raw)) {
                $trimmed = trim($raw);
                if (str_starts_with($trimmed, '{') || str_starts_with($trimmed, '[')) {
                    $decoded = json_decode($trimmed, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        return $decoded;
                    }
                }
            }
        } catch (\Throwable $e) {
            // Ignore error
        }

        $rawInput = $this->request->getRawInput();
        if (!empty($rawInput) && is_array($rawInput)) {
            return $rawInput;
        }

        return [];
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
        $payload = $this->getPayload();

        $keys = $this->getSystemSettingKeys();
        $normalized = [];
        $errors = [];
        foreach ($keys as $key => $group) {
            $inputKey = str_replace('.', '__', $key);
            $val = trim((string) ($payload[$inputKey] ?? ''));
            if ($key === 'app.maintenance_mode') {
                $val = (isset($payload[$inputKey]) && in_array((string) $payload[$inputKey], ['1', 'true', 'on', 1], true)) ? '1' : '0';
            }
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
        $payload = $this->getPayload();

        $keys = ['smtp.host', 'smtp.port', 'smtp.username', 'smtp.password', 'smtp.encryption', 'smtp.from_name', 'smtp.from_email'];
        $errors = [];
        $normalized = [];
        foreach ($keys as $key) {
            $inputKey = str_replace('.', '__', $key);
            $val = trim((string) ($payload[$inputKey] ?? ''));
            if ($key === 'smtp.password' && $val === '') {
                $val = (string) $this->settings->getValue('smtp.password', '');
            }
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

    public function testSmtpConnectionApi()
    {
        $payload = $this->getPayload();
        $testEmail = trim((string) ($payload['test_email'] ?? ''));
        if ($testEmail === '' || !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            return $this->jsonError('Email tujuan uji coba tidak valid', 422, ['test_email' => 'Email tujuan tidak valid']);
        }

        $smtp = $this->settings->getGroup('smtp');
        if (empty($smtp['smtp.host']) || empty($smtp['smtp.username'])) {
            return $this->jsonError('Konfigurasi SMTP belum lengkap. Silakan simpan Host dan Username terlebih dahulu.', 422);
        }

        try {
            $config = new \Config\Email();
            $config->protocol = 'smtp';
            $config->SMTPHost = (string) ($smtp['smtp.host'] ?? '');
            $config->SMTPPort = (int) ($smtp['smtp.port'] ?? 587);
            $config->SMTPUser = (string) ($smtp['smtp.username'] ?? '');
            $config->SMTPPass = (string) ($smtp['smtp.password'] ?? '');
            $encryption = strtolower(trim((string) ($smtp['smtp.encryption'] ?? 'tls')));
            $config->SMTPCrypto = in_array($encryption, ['tls', 'ssl'], true) ? $encryption : '';
            $config->fromEmail = (string) ($smtp['smtp.from_email'] ?? 'no-reply@kanreg.bkn.go.id');
            $config->fromName = (string) ($smtp['smtp.from_name'] ?? 'SIMOJANG');
            $config->mailType = 'html';
            $config->SMTPTimeout = 10;

            $email = \Config\Services::email($config);
            $email->setFrom($config->fromEmail, $config->fromName);
            $email->setTo($testEmail);
            $email->setSubject('Uji Coba Konfigurasi SMTP - SIMOJANG');
            $email->setMessage('<div style="font-family: Arial, sans-serif; padding: 20px; line-height: 1.5; color: #1e293b;">
                <h3 style="color: #1040c1; margin-bottom: 10px;">Uji Coba Konfigurasi SMTP SIMOJANG Berhasil</h3>
                <p>Halo,</p>
                <p>Email ini dikirim sebagai pengujian koneksi dan otentikasi SMTP dari aplikasi <strong>SIMOJANG</strong>.</p>
                <table style="border-collapse: collapse; margin-top: 15px; font-size: 14px;">
                    <tr><td style="padding: 4px 12px 4px 0; font-weight: bold;">Host:</td><td>' . esc($config->SMTPHost) . '</td></tr>
                    <tr><td style="padding: 4px 12px 4px 0; font-weight: bold;">Port:</td><td>' . (int) $config->SMTPPort . '</td></tr>
                    <tr><td style="padding: 4px 12px 4px 0; font-weight: bold;">Enkripsi:</td><td>' . esc(strtoupper($encryption ?: 'None')) . '</td></tr>
                    <tr><td style="padding: 4px 12px 4px 0; font-weight: bold;">Pengirim:</td><td>' . esc($config->fromName) . ' &lt;' . esc($config->fromEmail) . '&gt;</td></tr>
                    <tr><td style="padding: 4px 12px 4px 0; font-weight: bold;">Waktu Pengujian:</td><td>' . date('d-m-Y H:i:s') . '</td></tr>
                </table>
                <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;">
                <small style="color: #64748b;">SIMOJANG - Kantor Regional III BKN Bandung</small>
            </div>');

            if ($email->send(false)) {
                return $this->jsonSuccess('Koneksi SMTP berhasil! Email uji coba telah dikirim ke ' . $testEmail);
            }

            $debugger = $email->printDebugger(['headers', 'subject']);
            return $this->jsonError('Gagal mengirim email uji coba: ' . strip_tags($debugger), 500);
        } catch (\Throwable $e) {
            return $this->jsonError('Terjadi kesalahan pengujian SMTP: ' . $e->getMessage(), 500);
        }
    }

    // ==========================================
    // USER MANAGER METHODS (/manage-user)
    // ==========================================

    public function userManager()
    {
        $roles = $this->manager->getRoles(true);
        $stats = $this->users->getUserStats();
        return $this->renderView('Apps/pages/data/user_manager', [
            'title' => 'Kelola Pengguna',
            'roles' => $roles,
            'stats' => $stats,
        ]);
    }

    public function getUserDatatableApi()
    {
        $roleFilter = trim((string) ($this->request->getPost('role') ?? ($this->request->getGet('role') ?? '')));
        $statusFilter = trim((string) ($this->request->getPost('status') ?? ($this->request->getGet('status') ?? '')));

        $builder = $this->users->getUsersDataTableBuilder($roleFilter, $statusFilter);
        $columns = [
            [
                'data'   => 'fullname',
                'search' => ['u.fullname', 'dp.nama', 'u.username'],
                'order'  => 'u.fullname',
            ],
            [
                'data'   => 'username',
                'search' => 'u.username',
                'order'  => 'u.username',
            ],
            [
                'data'   => 'email',
                'search' => 'u.email',
                'order'  => 'u.email',
            ],
            [
                'data'   => 'role_name',
                'search' => ['r.role_name', 'u.role'],
                'order'  => 'u.role',
            ],
            [
                'data'   => 'role',
                'search' => 'u.role',
                'order'  => 'u.role',
            ],
            [
                'data'   => 'active',
                'search' => false,
                'order'  => 'u.is_active',
            ],
            [
                'data'   => 'created_at',
                'search' => 'u.created_at',
                'order'  => 'u.created_at',
            ],
        ];

        $result = $this->dataTables->render($builder, $columns);
        return $this->response->setJSON($result);
    }

    public function getUserListApi()
    {
        $search = trim((string) ($this->request->getGet('search') ?? ''));
        $roleFilter = trim((string) ($this->request->getGet('role') ?? ''));
        $statusFilter = trim((string) ($this->request->getGet('status') ?? ''));
        $page = max(1, (int) ($this->request->getGet('page') ?? 1));
        $perPage = max(5, min(100, (int) ($this->request->getGet('per_page') ?? 15)));

        $rows = $this->users->getUsersPaginated($search, $roleFilter, $statusFilter, $page, $perPage);
        $total = $this->users->getUsersCount($search, $roleFilter, $statusFilter);
        $stats = $this->users->getUserStats();

        return $this->jsonSuccess('OK', [
            'data'        => $rows,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => (int) ceil($total / $perPage),
            'stats'       => $stats,
        ]);
    }

    public function getUserDetailApi(int $id = 0)
    {
        if ($id <= 0) {
            $id = (int) ($this->request->getGet('id') ?? 0);
        }

        if ($id <= 0) {
            return $this->jsonError('ID pengguna wajib valid', 422, ['id' => 'ID pengguna wajib valid']);
        }

        $user = $this->users->getUserDetail($id);
        if (!$user) {
            return $this->jsonError('Pengguna tidak ditemukan', 404);
        }

        return $this->jsonSuccess('OK', $user);
    }

    public function createUserApi()
    {
        $payload = $this->getPayload();
        $result = $this->users->createUser($payload);

        if (!$result['status']) {
            return $this->jsonError($result['message'] ?? 'Gagal membuat pengguna', 422, [
                $result['field'] ?? 'general' => $result['message'] ?? 'Gagal membuat pengguna',
            ]);
        }

        $created = $this->users->getUserDetail((int) $result['user_id']);
        return $this->jsonSuccess($result['message'] ?? 'Pengguna berhasil dibuat', $created, 201);
    }

    public function updateUserApi()
    {
        $payload = $this->getPayload();
        $id = (int) ($payload['id'] ?? ($payload['user_id'] ?? 0));
        if ($id <= 0) {
            return $this->jsonError('ID pengguna wajib valid', 422, ['id' => 'ID pengguna wajib valid']);
        }

        $result = $this->users->updateUser($id, $payload);
        if (!$result['status']) {
            return $this->jsonError($result['message'] ?? 'Gagal memperbarui pengguna', 422, [
                $result['field'] ?? 'general' => $result['message'] ?? 'Gagal memperbarui pengguna',
            ]);
        }

        $updated = $this->users->getUserDetail($id);
        return $this->jsonSuccess($result['message'] ?? 'Data pengguna berhasil diperbarui', $updated);
    }

    public function resetUserPasswordApi()
    {
        $payload = $this->getPayload();
        $id = (int) ($payload['id'] ?? ($payload['user_id'] ?? 0));
        $newPassword = (string) ($payload['password'] ?? '');

        if ($id <= 0) {
            return $this->jsonError('ID pengguna wajib valid', 422, ['id' => 'ID pengguna wajib valid']);
        }

        if (strlen($newPassword) < 6) {
            return $this->jsonError('Password minimal 6 karakter', 422, ['password' => 'Password minimal 6 karakter']);
        }

        $result = $this->users->resetPassword($id, $newPassword);
        if (!$result['status']) {
            return $this->jsonError($result['message'] ?? 'Gagal mereset password', 500);
        }

        return $this->jsonSuccess($result['message'] ?? 'Password pengguna berhasil direset');
    }

    public function toggleUserStatusApi()
    {
        $payload = $this->getPayload();
        $id = (int) ($payload['id'] ?? ($payload['user_id'] ?? 0));
        $active = isset($payload['active']) ? (in_array((string) $payload['active'], ['1', 'true', 'on'], true) ? 1 : 0) : null;

        if ($id <= 0) {
            return $this->jsonError('ID pengguna wajib valid', 422, ['id' => 'ID pengguna wajib valid']);
        }

        $result = $this->users->toggleUserStatus($id, $active);
        if (!$result['status']) {
            return $this->jsonError($result['message'] ?? 'Gagal mengubah status pengguna', 500);
        }

        return $this->jsonSuccess($result['message'], [
            'id'     => $id,
            'active' => $result['active'],
        ]);
    }

    public function deleteUserApi()
    {
        $payload = $this->getPayload();
        $id = (int) ($payload['id'] ?? ($payload['user_id'] ?? 0));
        $currentUserId = (int) (session()->get('userid') ?? 0);

        if ($id <= 0) {
            return $this->jsonError('ID pengguna wajib valid', 422, ['id' => 'ID pengguna wajib valid']);
        }

        $result = $this->users->deleteUser($id, $currentUserId);
        if (!$result['status']) {
            return $this->jsonError($result['message'] ?? 'Gagal menghapus pengguna', 400);
        }

        return $this->jsonSuccess($result['message'] ?? 'Pengguna berhasil dihapus', ['id' => $id]);
    }

    public function pegawaiLookupApi()
    {
        $query = trim((string) ($this->request->getGet('q') ?? ''));
        $limit = max(5, min(30, (int) ($this->request->getGet('limit') ?? 15)));
        $rows = $this->users->searchPegawaiLookup($query, $limit);
        return $this->jsonSuccess('OK', $rows);
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
