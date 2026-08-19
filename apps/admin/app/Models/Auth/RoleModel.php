<?php

namespace App\Models\Auth;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table            = 'auth_roles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['role_code', 'role_name', 'description', 'is_active', 'created_at', 'updated_at'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Mengambil seluruh daftar role dengan statistik user dan permission
     */
    public function getRoles(bool $onlyActive = false): array
    {
        $builder = $this->db->table('auth_roles r')
            ->select("
                r.id,
                r.role_code,
                r.role_name,
                r.description,
                r.is_active,
                r.created_at,
                r.updated_at,
                (SELECT COUNT(*) FROM auth_users u WHERE u.role = r.role_code) AS total_users,
                (SELECT COUNT(*) FROM auth_role_permissions rp WHERE rp.role_id = r.id AND COALESCE(rp.is_read, 0) = 1) AS total_permissions
            ")
            ->orderBy("CASE WHEN r.role_code = 'ADM' THEN 1 WHEN r.role_code = 'USR' THEN 2 ELSE 3 END", 'ASC', false)
            ->orderBy('r.role_name', 'ASC');

        if ($onlyActive) {
            $builder->where('r.is_active', 1);
        }

        $roles = $builder->get()->getResultArray();
        $totalMenus = (int) $this->db->table('auth_permissions')->where('is_show', 1)->countAllResults();

        foreach ($roles as &$role) {
            $role['id'] = (int) $role['id'];
            $role['is_active'] = (int) $role['is_active'] === 1;
            $role['total_users'] = (int) ($role['total_users'] ?? 0);
            $role['total_permissions'] = (int) ($role['total_permissions'] ?? 0);
            $role['total_menus'] = $totalMenus;
        }
        unset($role);

        return $roles;
    }

    /**
     * Mengambil detail role berdasarkan ID
     */
    public function getRoleById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $row = $this->db->table('auth_roles')
            ->select('id, role_code, role_name, description, is_active, created_at, updated_at')
            ->where('id', $id)
            ->limit(1)
            ->get()
            ->getRowArray();

        if (!$row) {
            return null;
        }

        $row['id'] = (int) $row['id'];
        $row['is_active'] = (int) $row['is_active'] === 1;
        $row['total_users'] = (int) $this->db->table('auth_users')->where('role', $row['role_code'])->countAllResults();
        $row['total_permissions'] = (int) $this->db->table('auth_role_permissions')
            ->where('role_id', $row['id'])
            ->where('COALESCE(is_read, 0) =', 1, false)
            ->countAllResults();

        return $row;
    }

    /**
     * Mengambil detail role berdasarkan role_code
     */
    public function getRoleByCode(string $code): ?array
    {
        $code = trim(strtoupper($code));
        if ($code === '') {
            return null;
        }

        $row = $this->db->table('auth_roles')
            ->where('role_code', $code)
            ->limit(1)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    /**
     * Menambahkan role baru beserta opsi menyalin permission dari role lain
     */
    public function createRole(array $data, ?int $copyFromRoleId = null): int
    {
        $roleCode = strtoupper(trim((string) ($data['role_code'] ?? '')));
        $roleName = trim((string) ($data['role_name'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));

        if ($roleCode === '' || $roleName === '') {
            return 0;
        }

        // Cek duplikasi kode role
        $exists = $this->db->table('auth_roles')
            ->where('role_code', $roleCode)
            ->countAllResults();

        if ($exists > 0) {
            return 0;
        }

        $this->db->transStart();

        $now = date('Y-m-d H:i:s');
        $this->db->table('auth_roles')->insert([
            'role_code'   => $roleCode,
            'role_name'   => $roleName,
            'description' => $description !== '' ? $description : null,
            'is_active'   => 1,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        $newRoleId = (int) $this->db->insertID();

        // Salin hak akses jika copyFromRoleId diberikan
        if ($newRoleId > 0 && $copyFromRoleId !== null && $copyFromRoleId > 0) {
            $sourcePerms = $this->db->table('auth_role_permissions')
                ->where('role_id', $copyFromRoleId)
                ->where('COALESCE(is_read, 0) =', 1, false)
                ->get()
                ->getResultArray();

            foreach ($sourcePerms as $sp) {
                $this->db->table('auth_role_permissions')->insert([
                    'role_id'       => $newRoleId,
                    'permission_id' => (int) $sp['permission_id'],
                    'is_create'     => (int) ($sp['is_create'] ?? 0),
                    'is_read'       => 1,
                    'is_update'     => (int) ($sp['is_update'] ?? 0),
                    'is_delete'     => (int) ($sp['is_delete'] ?? 0),
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]);
            }
        }

        $this->db->transComplete();

        return $this->db->transStatus() !== false ? $newRoleId : 0;
    }

    /**
     * Memperbarui informasi role
     */
    public function updateRole(int $id, array $data): bool
    {
        if ($id <= 0) {
            return false;
        }

        $role = $this->getRoleById($id);
        if (!$role) {
            return false;
        }

        $payload = [
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if (isset($data['role_name']) && trim($data['role_name']) !== '') {
            $payload['role_name'] = trim($data['role_name']);
        }

        if (isset($data['description'])) {
            $payload['description'] = trim($data['description']);
        }

        if (isset($data['is_active'])) {
            $payload['is_active'] = $data['is_active'] ? 1 : 0;
        }

        return (bool) $this->db->table('auth_roles')
            ->where('id', $id)
            ->update($payload);
    }

    /**
     * Menghapus role custom (role default ADM dan USR diproteksi)
     */
    public function deleteRole(int $id): array
    {
        if ($id <= 0) {
            return ['status' => false, 'message' => 'ID role tidak valid'];
        }

        $role = $this->getRoleById($id);
        if (!$role) {
            return ['status' => false, 'message' => 'Role tidak ditemukan'];
        }

        if (in_array(strtoupper($role['role_code']), ['ADM', 'USR'], true)) {
            return ['status' => false, 'message' => 'Role bawaan sistem (' . $role['role_code'] . ') tidak dapat dihapus'];
        }

        // Cek user yang sedang menggunakan role ini
        $userCount = (int) $this->db->table('auth_users')
            ->where('role', $role['role_code'])
            ->countAllResults();

        if ($userCount > 0) {
            return [
                'status'  => false,
                'message' => "Terdapat {$userCount} pengguna yang masih menggunakan role ini. Silakan pindahkan role pengguna terlebih dahulu.",
            ];
        }

        $this->db->transStart();

        $this->db->table('auth_role_permissions')->where('role_id', $id)->delete();
        $this->db->table('auth_roles')->where('id', $id)->delete();

        $this->db->transComplete();

        return [
            'status'  => $this->db->transStatus() !== false,
            'message' => $this->db->transStatus() !== false ? 'Role berhasil dihapus' : 'Gagal menghapus role',
        ];
    }

    /**
     * Mengambil peta permission_id yang diizinkan untuk suatu role
     */
    public function getRolePermissionsMap(int $roleId): array
    {
        $rows = $this->db->table('auth_role_permissions')
            ->select('permission_id')
            ->where('role_id', $roleId)
            ->where('COALESCE(is_read, 0) =', 1, false)
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['permission_id']] = true;
        }
        return $map;
    }

    /**
     * Membangun Tree Hierarchy Menu lengkap dengan status allowed untuk role tertentu
     */
    public function getMenuTreeWithRolePermission(int $roleId): array
    {
        $allPermissions = $this->db->table('auth_permissions')
            ->select('id, name, url, icon, parent_id, is_show, is_order')
            ->where('is_show', 1)
            ->orderBy('COALESCE(parent_id, 0)', 'ASC', false)
            ->orderBy('is_order', 'ASC')
            ->get()
            ->getResultArray();

        $permMap = $this->getRolePermissionsMap($roleId);

        $nodes = [];
        foreach ($allPermissions as $p) {
            $id = (int) $p['id'];
            $parent = $p['parent_id'] === null ? null : (int) $p['parent_id'];
            $nodes[$id] = [
                'id'        => $id,
                'parent_id' => $parent,
                'name'      => (string) $p['name'],
                'url'       => (string) ($p['url'] ?? ''),
                'icon'      => (string) ($p['icon'] ?? ''),
                'sort'      => (int) ($p['is_order'] ?? 0),
                'allowed'   => isset($permMap[$id]),
                'level'     => 0,
                'children'  => [],
            ];
        }

        // Susun tree & hitung level kedalaman
        $tree = [];
        foreach ($nodes as $id => &$node) {
            $pid = $node['parent_id'];
            if ($pid !== null && isset($nodes[$pid])) {
                $nodes[$pid]['children'][] = &$node;
            } else {
                $tree[] = &$node;
            }
        }
        unset($node);

        // Atur level kedalaman secara rekursif
        $setLevel = function (array &$list, int $level = 0) use (&$setLevel) {
            foreach ($list as &$item) {
                $item['level'] = $level;
                if (!empty($item['children'])) {
                    $setLevel($item['children'], $level + 1);
                }
            }
        };
        $setLevel($tree, 0);

        return $tree;
    }

    /**
     * Toggle hak akses menu untuk role tertentu dengan aturan konsistensi cascade
     */
    public function toggleRolePermission(int $roleId, int $menuId, bool $allowed, bool $cascade = true): array
    {
        if ($roleId <= 0 || $menuId <= 0) {
            return ['status' => false, 'affected_ids' => []];
        }

        $allPermissions = $this->db->table('auth_permissions')
            ->select('id, parent_id')
            ->where('is_show', 1)
            ->get()
            ->getResultArray();

        $parentMap = [];
        $childrenMap = [];
        foreach ($allPermissions as $p) {
            $id = (int) $p['id'];
            $pid = $p['parent_id'] !== null ? (int) $p['parent_id'] : null;
            $parentMap[$id] = $pid;
            if ($pid !== null) {
                $childrenMap[$pid][] = $id;
            }
        }

        $this->db->transStart();
        $now = date('Y-m-d H:i:s');
        $affectedIds = [$menuId];

        if ($allowed) {
            // 1. Berikan akses pada menu target
            $this->upsertPermission($roleId, $menuId, 1, $now);

            // 2. Cascade UP: Jika menu child diizinkan, seluruh parent / ancestor harus aktif
            if ($cascade) {
                $currentParent = $parentMap[$menuId] ?? null;
                while ($currentParent !== null && $currentParent > 0) {
                    $this->upsertPermission($roleId, $currentParent, 1, $now);
                    $affectedIds[] = $currentParent;
                    $currentParent = $parentMap[$currentParent] ?? null;
                }
            }
        } else {
            // 1. Cabut akses pada menu target
            $this->removePermission($roleId, $menuId);

            // 2. Cascade DOWN: Jika menu parent dicabut, seluruh child / descendant juga dicabut
            if ($cascade) {
                $collectDescendants = function (int $pid) use (&$collectDescendants, &$childrenMap) {
                    $descendants = [];
                    if (!empty($childrenMap[$pid])) {
                        foreach ($childrenMap[$pid] as $childId) {
                            $descendants[] = $childId;
                            $descendants = array_merge($descendants, $collectDescendants($childId));
                        }
                    }
                    return $descendants;
                };

                $childIds = $collectDescendants($menuId);
                foreach ($childIds as $cid) {
                    $this->removePermission($roleId, $cid);
                    $affectedIds[] = $cid;
                }
            }
        }

        $this->db->transComplete();
        $ok = $this->db->transStatus() !== false;

        return [
            'status'       => $ok,
            'allowed'      => $allowed,
            'role_id'      => $roleId,
            'menu_id'      => $menuId,
            'affected_ids' => array_values(array_unique($affectedIds)),
        ];
    }

    /**
     * Mengambil daftar user yang memiliki role tertentu
     */
    public function getRoleUsers(int $roleId, string $search = '', int $limit = 50): array
    {
        $role = $this->getRoleById($roleId);
        if (!$role) {
            return [];
        }

        $builder = $this->db->table('auth_users u')
            ->select('u.id, u.username, u.fullname, u.email, u.role, u.status, u.created_at, dm.nama AS pegawai_nama, dm.nip')
            ->join('data_pegawai dm', 'dm.nip = u.username', 'left')
            ->where('u.role', $role['role_code'])
            ->orderBy('u.fullname', 'ASC')
            ->limit($limit);

        $search = trim($search);
        if ($search !== '') {
            $builder->groupStart()
                ->like('u.username', $search)
                ->orLike('u.fullname', $search)
                ->orLike('u.email', $search)
                ->groupEnd();
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Mengambil daftar user dari role lain untuk dapat di-assign ke role ini
     */
    public function getAvailableUsersForRole(int $roleId, string $search = '', int $limit = 50): array
    {
        $role = $this->getRoleById($roleId);
        if (!$role) {
            return [];
        }

        $builder = $this->db->table('auth_users u')
            ->select('u.id, u.username, u.fullname, u.email, u.role, r.role_name AS current_role_name')
            ->join('auth_roles r', 'r.role_code = u.role', 'left')
            ->where('u.role !=', $role['role_code'])
            ->orderBy('u.fullname', 'ASC')
            ->limit($limit);

        $search = trim($search);
        if ($search !== '') {
            $builder->groupStart()
                ->like('u.username', $search)
                ->orLike('u.fullname', $search)
                ->orLike('u.email', $search)
                ->groupEnd();
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Mengubah role user dan menyinkronkan hak akses
     */
    public function assignUserRole(int $userId, int $roleId): bool
    {
        if ($userId <= 0 || $roleId <= 0) {
            return false;
        }

        $role = $this->getRoleById($roleId);
        if (!$role) {
            return false;
        }

        return (bool) $this->db->table('auth_users')
            ->where('id', $userId)
            ->update([
                'role'       => $role['role_code'],
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }

    /**
     * Helper untuk insert/update tabel auth_role_permissions
     */
    private function upsertPermission(int $roleId, int $menuId, int $isRead, string $now): void
    {
        $exists = $this->db->table('auth_role_permissions')
            ->select('id')
            ->where('role_id', $roleId)
            ->where('permission_id', $menuId)
            ->limit(1)
            ->get()
            ->getRowArray();

        if ($exists) {
            $this->db->table('auth_role_permissions')
                ->where('id', (int) $exists['id'])
                ->update([
                    'is_read'    => $isRead,
                    'updated_at' => $now,
                ]);
        } else {
            $this->db->table('auth_role_permissions')->insert([
                'role_id'       => $roleId,
                'permission_id' => $menuId,
                'is_create'     => 0,
                'is_read'       => $isRead,
                'is_update'     => 0,
                'is_delete'     => 0,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);
        }
    }

    /**
     * Helper untuk menghapus permission dari role
     */
    private function removePermission(int $roleId, int $menuId): void
    {
        $this->db->table('auth_role_permissions')
            ->where('role_id', $roleId)
            ->where('permission_id', $menuId)
            ->delete();
    }
}
