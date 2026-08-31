<?php

namespace App\Models\Auth;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'auth_users';

    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'email',
        'username',
        'fullname',
        'userimage',
        'password',
        'role',
        'status',
        'status_message',
        'is_active',
        'last_login',
        'force_pass_reset',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Mengambil data user berdasarkan username (case-insensitive)
     */
    public function getUserByUsername(string $username): ?array
    {
        return $this->where('LOWER(username)', strtolower($username))->first();
    }

    /**
     * Mencatat waktu login terakhir pengguna
     */
    public function recordLastLogin(int $userId): bool
    {
        if ($userId <= 0) {
            return false;
        }

        return (bool) $this->db->table('auth_users')
            ->where('id', $userId)
            ->update([
                'last_login' => date('Y-m-d H:i:s'),
            ]);
    }

    /**
     * Mengambil daftar pengguna dengan filter, search, dan pagination
     */
    public function getUsersPaginated(string $search = '', string $roleFilter = '', string $statusFilter = '', int $page = 1, int $perPage = 15): array
    {
        $builder = $this->db->table('auth_users u')
            ->select('
                u.id,
                u.username,
                u.fullname,
                u.email,
                u.userimage,
                u.role,
                COALESCE(r.role_name, u.role) AS role_name,
                u.is_active AS active,
                u.status,
                u.last_login,
                u.created_at,
                u.updated_at,
                dp.nama AS pegawai_nama,
                dp.jabatan AS pegawai_jabatan,
                dp.nip AS pegawai_nip,
                dp.phone AS pegawai_phone
            ')
            ->join('auth_roles r', 'r.role_code = u.role', 'left')
            ->join('data_pegawai dp', 'dp.nip = u.username', 'left');

        $this->applyUserFilters($builder, $search, $roleFilter, $statusFilter);

        $offset = max(0, ($page - 1) * $perPage);
        $builder->orderBy("CASE WHEN u.role = 'ADM' THEN 1 ELSE 2 END", 'ASC', false)
            ->orderBy('u.fullname', 'ASC')
            ->limit($perPage, $offset);

        $results = $builder->get()->getResultArray();
        foreach ($results as &$row) {
            $row['id'] = (int) $row['id'];
            $row['active'] = (int) ($row['active'] ?? 0) === 1;
            $row['display_name'] = !empty($row['fullname']) ? $row['fullname'] : (!empty($row['pegawai_nama']) ? $row['pegawai_nama'] : $row['username']);
        }
        unset($row);

        return $results;
    }

    /**
     * Builder query untuk DataTables server-side
     */
    public function getUsersDataTableBuilder(string $roleFilter = '', string $statusFilter = '')
    {
        $builder = $this->db->table('auth_users u')
            ->select('
                u.id,
                u.username,
                COALESCE(NULLIF(u.fullname, ""), dp.nama, u.username) AS fullname,
                u.email,
                u.role,
                COALESCE(r.role_name, u.role) AS role_name,
                u.is_active AS active,
                u.last_login,
                u.created_at,
                dp.nama AS pegawai_nama,
                dp.nip AS pegawai_nip
            ')
            ->join('auth_roles r', 'r.role_code = u.role', 'left')
            ->join('data_pegawai dp', 'dp.nip = u.username', 'left');

        if ($roleFilter !== '') {
            $builder->where('u.role', $roleFilter);
        }

        if ($statusFilter === 'active') {
            $builder->where('u.is_active', 1);
        } elseif ($statusFilter === 'inactive') {
            $builder->where('u.is_active', 0);
        }

        return $builder;
    }

    /**
     * Menghitung total data pengguna sesuai filter
     */
    public function getUsersCount(string $search = '', string $roleFilter = '', string $statusFilter = ''): int
    {
        $builder = $this->db->table('auth_users u')
            ->join('auth_roles r', 'r.role_code = u.role', 'left')
            ->join('data_pegawai dp', 'dp.nip = u.username', 'left');

        $this->applyUserFilters($builder, $search, $roleFilter, $statusFilter);

        return (int) $builder->countAllResults();
    }

    /**
     * Mengambil statistik ringkasan pengguna untuk dashboard banner
     */
    public function getUserStats(): array
    {
        $totalUsers = (int) $this->db->table('auth_users')->countAllResults();
        $activeUsers = (int) $this->db->table('auth_users')->where('is_active', 1)->countAllResults();
        $inactiveUsers = (int) $this->db->table('auth_users')->where('is_active !=', 1)->countAllResults();
        $totalRoles = (int) $this->db->table('auth_roles')->countAllResults();

        return [
            'total_users'    => $totalUsers,
            'active_users'   => $activeUsers,
            'inactive_users' => $inactiveUsers,
            'total_roles'    => $totalRoles,
        ];
    }

    /**
     * Mengambil detail satu pengguna
     */
    public function getUserDetail(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $row = $this->db->table('auth_users u')
            ->select('
                u.id,
                u.username,
                u.fullname,
                u.email,
                u.userimage,
                u.role,
                COALESCE(r.role_name, u.role) AS role_name,
                r.id AS role_id,
                u.is_active AS active,
                u.status,
                u.last_login,
                u.created_at,
                u.updated_at,
                dp.nama AS pegawai_nama,
                dp.jabatan AS pegawai_jabatan,
                dp.nip AS pegawai_nip,
                dp.phone AS pegawai_phone
            ')
            ->join('auth_roles r', 'r.role_code = u.role', 'left')
            ->join('data_pegawai dp', 'dp.nip = u.username', 'left')
            ->where('u.id', $id)
            ->limit(1)
            ->get()
            ->getRowArray();

        if (!$row) {
            return null;
        }

        $row['id'] = (int) $row['id'];
        $row['role_id'] = isset($row['role_id']) ? (int) $row['role_id'] : 0;
        $row['active'] = (int) ($row['active'] ?? 0) === 1;
        $row['display_name'] = !empty($row['fullname']) ? $row['fullname'] : (!empty($row['pegawai_nama']) ? $row['pegawai_nama'] : $row['username']);

        return $row;
    }

    /**
     * Menambahkan pengguna baru
     */
    public function createUser(array $data): array
    {
        $username = trim((string) ($data['username'] ?? ''));
        $fullname = trim((string) ($data['fullname'] ?? ''));
        $email = strtolower(trim((string) ($data['email'] ?? '')));
        $password = (string) ($data['password'] ?? '');
        $role = strtoupper(trim((string) ($data['role'] ?? 'USR')));
        $active = !isset($data['active']) || in_array((string) $data['active'], ['1', 'true', 'on'], true) ? 1 : 0;

        // 1. Validasi Username / NIP
        if ($username === '') {
            return [
                'status'  => false,
                'message' => 'Username / NIP wajib diisi.',
                'field'   => 'username',
            ];
        }

        if (strlen($username) > 30) {
            return [
                'status'  => false,
                'message' => 'Username / NIP maksimal 30 karakter.',
                'field'   => 'username',
            ];
        }

        // Cek duplikasi username / NIP
        $existsUser = $this->db->table('auth_users')
            ->where('LOWER(username)', strtolower($username))
            ->get()
            ->getRowArray();

        if ($existsUser) {
            $existingName = !empty($existsUser['fullname']) ? $existsUser['fullname'] : $existsUser['username'];
            return [
                'status'  => false,
                'message' => "Username / NIP '{$username}' sudah terdaftar dalam sistem atas nama \"{$existingName}\". Silakan gunakan NIP lain atau cari akun tersebut di daftar pengguna.",
                'field'   => 'username',
            ];
        }

        // 2. Validasi Nama Lengkap
        if ($fullname === '') {
            return [
                'status'  => false,
                'message' => 'Nama lengkap pengguna wajib diisi.',
                'field'   => 'fullname',
            ];
        }

        // 3. Validasi Email
        if ($email === '') {
            return [
                'status'  => false,
                'message' => 'Alamat email wajib diisi.',
                'field'   => 'email',
            ];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'status'  => false,
                'message' => "Format email '{$email}' tidak valid. Contoh: nama@instansi.go.id",
                'field'   => 'email',
            ];
        }

        // Cek duplikasi email
        $existsEmail = $this->db->table('auth_users')
            ->where('LOWER(email)', $email)
            ->get()
            ->getRowArray();

        if ($existsEmail) {
            $existingUser = !empty($existsEmail['fullname']) ? $existsEmail['fullname'] : $existsEmail['username'];
            return [
                'status'  => false,
                'message' => "Alamat email '{$email}' sudah terdaftar pada akun lain atas nama \"{$existingUser}\". Silakan gunakan alamat email yang berbeda.",
                'field'   => 'email',
            ];
        }

        // 4. Validasi Password
        if ($password === '') {
            return [
                'status'  => false,
                'message' => 'Password awal wajib diisi (minimal 6 karakter).',
                'field'   => 'password',
            ];
        }

        if (strlen($password) < 6) {
            return [
                'status'  => false,
                'message' => 'Password terlalu pendek. Password minimal terdiri dari 6 karakter.',
                'field'   => 'password',
            ];
        }

        // 5. Validasi Role
        if ($role === '') {
            $role = 'USR';
        }

        try {
            $now = date('Y-m-d H:i:s');
            $payload = [
                'username'         => $username,
                'fullname'         => $fullname,
                'email'            => $email,
                'password'         => password_hash($password, PASSWORD_BCRYPT),
                'role'             => $role,
                'is_active'        => $active,
                'status'           => $active === 1 ? 'active' : 'inactive',
                'force_pass_reset' => 0,
                'created_at'       => $now,
                'updated_at'       => $now,
            ];

            $ok = (bool) $this->db->table('auth_users')->insert($payload);
            $newId = $ok ? (int) $this->db->insertID() : 0;

            if ($newId <= 0) {
                $dbError = $this->db->error();
                $errDetail = !empty($dbError['message']) ? ' (' . $dbError['message'] . ')' : '';
                return [
                    'status'  => false,
                    'message' => 'Gagal menambahkan pengguna ke database' . $errDetail,
                    'field'   => 'general',
                ];
            }

            return [
                'status'  => true,
                'user_id' => $newId,
                'message' => "Pengguna baru '{$fullname}' ({$username}) berhasil ditambahkan ke sistem.",
            ];
        } catch (\Throwable $e) {
            return [
                'status'  => false,
                'message' => 'Terjadi kesalahan sistem database: ' . $e->getMessage(),
                'field'   => 'general',
            ];
        }
    }

    /**
     * Memperbarui informasi pengguna
     */
    public function updateUser(int $id, array $data): array
    {
        if ($id <= 0) {
            return ['status' => false, 'message' => 'ID pengguna tidak valid atau tidak ditemukan.', 'field' => 'id'];
        }

        $user = $this->getUserDetail($id);
        if (!$user) {
            return ['status' => false, 'message' => 'Pengguna tidak ditemukan dalam sistem.', 'field' => 'id'];
        }

        $payload = [
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if (isset($data['fullname'])) {
            $fullname = trim((string) $data['fullname']);
            if ($fullname === '') {
                return ['status' => false, 'message' => 'Nama lengkap pengguna tidak boleh kosong.', 'field' => 'fullname'];
            }
            $payload['fullname'] = $fullname;
        }

        if (isset($data['email'])) {
            $email = strtolower(trim((string) $data['email']));
            if ($email === '') {
                return ['status' => false, 'message' => 'Alamat email tidak boleh kosong.', 'field' => 'email'];
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['status' => false, 'message' => "Format email '{$email}' tidak valid. Contoh: nama@instansi.go.id", 'field' => 'email'];
            }

            // Cek duplikasi email ke user lain
            $existsEmail = $this->db->table('auth_users')
                ->where('LOWER(email)', $email)
                ->where('id !=', $id)
                ->get()
                ->getRowArray();

            if ($existsEmail) {
                $existingUser = !empty($existsEmail['fullname']) ? $existsEmail['fullname'] : $existsEmail['username'];
                return [
                    'status'  => false,
                    'message' => "Alamat email '{$email}' sudah digunakan oleh pengguna lain atas nama \"{$existingUser}\". Silakan gunakan email yang berbeda.",
                    'field'   => 'email',
                ];
            }

            $payload['email'] = $email;
        }

        if (isset($data['role']) && trim((string) $data['role']) !== '') {
            $payload['role'] = strtoupper(trim((string) $data['role']));
        }

        if (isset($data['active'])) {
            $active = in_array((string) $data['active'], ['1', 'true', 'on'], true) ? 1 : 0;
            $payload['is_active'] = $active;
            $payload['status'] = $active === 1 ? 'active' : 'inactive';
        }

        if (!empty($data['password'])) {
            $pwd = (string) $data['password'];
            if (strlen($pwd) < 6) {
                return ['status' => false, 'message' => 'Password baru minimal terdiri dari 6 karakter.', 'field' => 'password'];
            }
            $payload['password'] = password_hash($pwd, PASSWORD_BCRYPT);
        }

        try {
            $ok = (bool) $this->db->table('auth_users')
                ->where('id', $id)
                ->update($payload);

            return [
                'status'  => $ok,
                'message' => $ok ? 'Data pengguna berhasil diperbarui.' : 'Tidak ada perubahan data yang disimpan.',
            ];
        } catch (\Throwable $e) {
            return [
                'status'  => false,
                'message' => 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage(),
                'field'   => 'general',
            ];
        }
    }

    /**
     * Reset password pengguna
     */
    public function resetPassword(int $id, string $newPassword): array
    {
        if ($id <= 0) {
            return ['status' => false, 'message' => 'ID pengguna tidak valid atau tidak ditemukan.'];
        }

        $pwd = trim($newPassword);
        if ($pwd === '') {
            return ['status' => false, 'message' => 'Password baru wajib diisi.'];
        }

        if (strlen($pwd) < 6) {
            return ['status' => false, 'message' => 'Password baru terlalu pendek (minimal 6 karakter).'];
        }

        $user = $this->getUserDetail($id);
        if (!$user) {
            return ['status' => false, 'message' => 'Pengguna tidak ditemukan dalam sistem.'];
        }

        try {
            $ok = (bool) $this->db->table('auth_users')
                ->where('id', $id)
                ->update([
                    'password'   => password_hash($pwd, PASSWORD_BCRYPT),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

            $userName = !empty($user['fullname']) ? $user['fullname'] : $user['username'];
            return [
                'status'  => $ok,
                'message' => $ok ? "Password untuk akun '{$userName}' ({$user['username']}) berhasil diperbarui." : 'Gagal memperbarui password pengguna.',
            ];
        } catch (\Throwable $e) {
            return [
                'status'  => false,
                'message' => 'Terjadi kesalahan saat reset password: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Memperbarui password pengguna (updatePassword)
     */
    public function updatePassword(int $id, string $hashedPassword): bool
    {
        return (bool) $this->db->table('auth_users')
            ->where('id', $id)
            ->update([
                'password'   => $hashedPassword,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }

    /**
     * Toggle status aktif/non-aktif akun pengguna
     */
    public function toggleUserStatus(int $id, ?int $forceStatus = null): array
    {
        if ($id <= 0) {
            return ['status' => false, 'message' => 'ID pengguna tidak valid'];
        }

        $user = $this->getUserDetail($id);
        if (!$user) {
            return ['status' => false, 'message' => 'Pengguna tidak ditemukan'];
        }

        $newActive = $forceStatus !== null ? ($forceStatus === 1 ? 1 : 0) : ($user['active'] ? 0 : 1);
        $newStatusStr = $newActive === 1 ? 'active' : 'inactive';

        $ok = (bool) $this->db->table('auth_users')
            ->where('id', $id)
            ->update([
                'is_active'  => $newActive,
                'status'     => $newStatusStr,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        return [
            'status'     => $ok,
            'active'     => $newActive === 1,
            'message'    => $newActive === 1 ? 'Akun pengguna berhasil diaktifkan' : 'Akun pengguna berhasil dinonaktifkan',
        ];
    }

    /**
     * Menghapus pengguna dari sistem
     */
    public function deleteUser(int $id, int $currentUserId = 0): array
    {
        if ($id <= 0) {
            return ['status' => false, 'message' => 'ID pengguna tidak valid'];
        }

        if ($id === $currentUserId) {
            return ['status' => false, 'message' => 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif'];
        }

        $user = $this->getUserDetail($id);
        if (!$user) {
            return ['status' => false, 'message' => 'Pengguna tidak ditemukan'];
        }

        // Proteksi Super Admin
        if ($user['username'] === '199707252024211004' || (strtoupper($user['role']) === 'ADM' && $this->db->table('auth_users')->where('role', 'ADM')->countAllResults() <= 1)) {
            return ['status' => false, 'message' => 'Akun Administrator utama tidak dapat dihapus'];
        }

        $this->db->transStart();

        $this->db->table('auth_users_permissions')->where('user_id', $id)->delete();
        $this->db->table('auth_users')->where('id', $id)->delete();

        $this->db->transComplete();

        return [
            'status'  => $this->db->transStatus() !== false,
            'message' => $this->db->transStatus() !== false ? 'Pengguna berhasil dihapus' : 'Gagal menghapus pengguna',
        ];
    }

    /**
     * Pencarian autocomplete data pegawai untuk mempermudah form tambah user
     */
    public function searchPegawaiLookup(string $query, int $limit = 15): array
    {
        $query = trim($query);
        $builder = $this->db->table('data_pegawai dp')
            ->select('
                dp.id,
                dp.nip,
                dp.nama,
                dp.email,
                dp.phone,
                dp.jabatan,
                (SELECT u.id FROM auth_users u WHERE u.username = dp.nip LIMIT 1) AS existing_user_id
            ')
            ->orderBy('dp.nama', 'ASC')
            ->limit($limit);

        if ($query !== '') {
            $builder->groupStart()
                ->like('dp.nip', $query)
                ->orLike('dp.nama', $query)
                ->orLike('dp.email', $query)
                ->groupEnd();
        }

        $rows = $builder->get()->getResultArray();
        foreach ($rows as &$row) {
            $row['has_user'] = !empty($row['existing_user_id']);
        }
        unset($row);

        return $rows;
    }

    /**
     * Helper privat untuk filtering query pengguna
     */
    private function applyUserFilters($builder, string $search, string $roleFilter, string $statusFilter): void
    {
        $search = trim($search);
        if ($search !== '') {
            $builder->groupStart()
                ->like('u.username', $search)
                ->orLike('u.fullname', $search)
                ->orLike('u.email', $search)
                ->orLike('dp.nama', $search)
                ->orLike('dp.nip', $search)
                ->groupEnd();
        }

        $roleFilter = strtoupper(trim($roleFilter));
        if ($roleFilter !== '' && $roleFilter !== 'ALL') {
            $builder->where('u.role', $roleFilter);
        }

        $statusFilter = strtolower(trim($statusFilter));
        if ($statusFilter === 'active' || $statusFilter === '1') {
            $builder->where('u.is_active', 1);
        } elseif ($statusFilter === 'inactive' || $statusFilter === '0') {
            $builder->where('u.is_active', 0);
        }
    }
}
