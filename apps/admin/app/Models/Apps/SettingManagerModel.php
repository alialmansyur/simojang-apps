<?php

namespace App\Models\Apps;

use CodeIgniter\Model;

class SettingManagerModel extends Model
{
    public function canUserReadMenuUrl(int $userId, string $url): bool
    {
        $path = trim($url, '/');
        if ($userId <= 0 || $path === '') {
            return false;
        }

        // 1. Cek role user
        $user = $this->db->table('auth_users')
            ->select('role')
            ->where('id', $userId)
            ->limit(1)
            ->get()
            ->getRowArray();

        $roleCode = strtoupper(trim((string) ($user['role'] ?? 'USR')));
        if ($roleCode === 'ADM') {
            return true;
        }

        // 2. Cek via auth_role_permissions
        if (in_array('auth_role_permissions', $this->db->listTables(), true) && in_array('auth_roles', $this->db->listTables(), true)) {
            $row = $this->db->table('auth_role_permissions rp')
                ->select('rp.is_read')
                ->join('auth_roles r', 'r.id = rp.role_id', 'inner')
                ->join('auth_permissions p', 'p.id = rp.permission_id', 'inner')
                ->where('r.role_code', $roleCode)
                ->where('p.url', $path)
                ->where('r.is_active', 1)
                ->limit(1)
                ->get()
                ->getRowArray();

            if (!empty($row) && (int) ($row['is_read'] ?? 0) === 1) {
                return true;
            }
        }

        // 3. Fallback via auth_users_permissions
        $row = $this->db->table('auth_users_permissions up')
            ->select('up.is_read')
            ->join('auth_permissions p', 'p.id = up.permission_id', 'inner')
            ->where('up.user_id', $userId)
            ->where('p.url', $path)
            ->limit(1)
            ->get()
            ->getRowArray();

        return !empty($row) && (int) ($row['is_read'] ?? 0) === 1;
    }

    public function getRoles(bool $onlyActive = false): array
    {
        $roleModel = new \App\Models\Auth\RoleModel();
        return $roleModel->getRoles($onlyActive);
    }

    public function getRoleById(int $id): ?array
    {
        $roleModel = new \App\Models\Auth\RoleModel();
        return $roleModel->getRoleById($id);
    }

    public function createRole(array $data, ?int $copyFromRoleId = null): int
    {
        $roleModel = new \App\Models\Auth\RoleModel();
        return $roleModel->createRole($data, $copyFromRoleId);
    }

    public function updateRole(int $id, array $data): bool
    {
        $roleModel = new \App\Models\Auth\RoleModel();
        return $roleModel->updateRole($id, $data);
    }

    public function deleteRole(int $id): array
    {
        $roleModel = new \App\Models\Auth\RoleModel();
        return $roleModel->deleteRole($id);
    }

    public function getMenuTreeWithRolePermission(int $roleId): array
    {
        $roleModel = new \App\Models\Auth\RoleModel();
        return $roleModel->getMenuTreeWithRolePermission($roleId);
    }

    public function toggleRolePermission(int $roleId, int $menuId, bool $allowed, bool $cascade = true): array
    {
        $roleModel = new \App\Models\Auth\RoleModel();
        return $roleModel->toggleRolePermission($roleId, $menuId, $allowed, $cascade);
    }

    public function getRoleUsersList(int $roleId, string $search = '', int $limit = 50): array
    {
        $roleModel = new \App\Models\Auth\RoleModel();
        return $roleModel->getRoleUsers($roleId, $search, $limit);
    }

    public function getAvailableUsersForRole(int $roleId, string $search = '', int $limit = 50): array
    {
        $roleModel = new \App\Models\Auth\RoleModel();
        return $roleModel->getAvailableUsersForRole($roleId, $search, $limit);
    }

    public function assignUserRole(int $userId, int $roleId): bool
    {
        $roleModel = new \App\Models\Auth\RoleModel();
        return $roleModel->assignUserRole($userId, $roleId);
    }

    public function getMenusTree(): array
    {
        $rows = $this->db->table('auth_permissions')
            ->select('id, parent_id, name, url, icon, is_order, is_show')
            ->orderBy('COALESCE(parent_id, 0)', 'ASC', false)
            ->orderBy('is_order', 'ASC')
            ->get()
            ->getResultArray();

        $nodes = [];
        foreach ($rows as $row) {
            $id = (int) $row['id'];
            $parent = $row['parent_id'] === null ? null : (int) $row['parent_id'];
            $nodes[$id] = [
                'id' => $id,
                'parent_id' => $parent,
                'name' => (string) ($row['name'] ?? ''),
                'url' => (string) ($row['url'] ?? ''),
                'icon' => (string) ($row['icon'] ?? ''),
                'sort' => (int) ($row['is_order'] ?? 0),
                'is_active' => (int) ($row['is_show'] ?? 0) === 1,
                'children' => [],
            ];
        }

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

        return $tree;
    }

    public function getMenuById(int $id): ?array
    {
        $row = $this->db->table('auth_permissions')
            ->select('id, parent_id, name, url, icon, is_order, is_show')
            ->where('id', $id)
            ->limit(1)
            ->get()
            ->getRowArray();

        if (!$row) {
            return null;
        }

        return [
            'id' => (int) $row['id'],
            'parent_id' => $row['parent_id'] === null ? null : (int) $row['parent_id'],
            'name' => (string) ($row['name'] ?? ''),
            'url' => (string) ($row['url'] ?? ''),
            'icon' => (string) ($row['icon'] ?? ''),
            'sort' => (int) ($row['is_order'] ?? 0),
            'is_active' => (int) ($row['is_show'] ?? 0) === 1,
        ];
    }

    public function createMenu(array $payload): int
    {
        $ok = $this->db->table('auth_permissions')->insert([
            'parent_id' => $payload['parent_id'],
            'name' => $payload['name'],
            'url' => $payload['url'],
            'icon' => $payload['icon'],
            'is_order' => $payload['sort'],
            'is_show' => $payload['is_active'] ? 1 : 0,
        ]);

        if (!$ok) {
            return 0;
        }

        return (int) $this->db->insertID();
    }

    public function updateMenuById(int $id, array $payload): bool
    {
        return (bool) $this->db->table('auth_permissions')
            ->where('id', $id)
            ->update([
                'parent_id' => $payload['parent_id'],
                'name' => $payload['name'],
                'url' => $payload['url'],
                'icon' => $payload['icon'],
                'is_order' => $payload['sort'],
                'is_show' => $payload['is_active'] ? 1 : 0,
            ]);
    }

    public function deleteMenuById(int $id): bool
    {
        $this->db->transStart();

        $childIds = $this->db->table('auth_permissions')
            ->select('id')
            ->where('parent_id', $id)
            ->get()
            ->getResultArray();
        $deleteIds = [$id];
        foreach ($childIds as $row) {
            $deleteIds[] = (int) $row['id'];
        }

        $this->db->table('auth_users_permissions')->whereIn('permission_id', $deleteIds)->delete();
        $this->db->table('auth_permissions')->whereIn('id', $deleteIds)->delete();

        $this->db->transComplete();
        return $this->db->transStatus() !== false;
    }

    public function getUsersByKeyword(string $keyword = '', int $limit = 30): array
    {
        return $this->getUsers($keyword, $limit);
    }

    public function usernameExists(string $username): bool
    {
        $row = $this->db->table('auth_users')
            ->select('id')
            ->where('username', $username)
            ->limit(1)
            ->get()
            ->getRowArray();

        return !empty($row);
    }

    public function emailExists(string $email): bool
    {
        $row = $this->db->table('auth_users')
            ->select('id')
            ->where('email', $email)
            ->limit(1)
            ->get()
            ->getRowArray();

        return !empty($row);
    }

    public function createUser(array $payload): int
    {
        $ok = $this->db->table('auth_users')->insert([
            'email' => $payload['email'],
            'username' => $payload['username'],
            'fullname' => $payload['fullname'],
            'password' => $payload['password_hash'],
            'role' => $payload['role'] ?? 'USR',
            'status' => $payload['status'] ?? '1',
            'is_active' => $payload['is_active'] ?? ($payload['active'] ?? 1),
            'force_pass_reset' => $payload['force_pass_reset'] ?? 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$ok) {
            return 0;
        }

        return (int) $this->db->insertID();
    }

    public function getUserById(int $id): ?array
    {
        $row = $this->db->table('auth_users')
            ->select('id, username, fullname, email, role, status, is_active AS active, created_at')
            ->where('id', $id)
            ->limit(1)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    public function getUserPermissionIds(int $userId): array
    {
        $rows = $this->db->table('auth_users_permissions')
            ->select('permission_id')
            ->where('user_id', $userId)
            ->where('COALESCE(is_read, 0) =', 1, false)
            ->get()
            ->getResultArray();

        $ids = [];
        foreach ($rows as $row) {
            $ids[] = (int) $row['permission_id'];
        }

        return $ids;
    }

    public function getUserPermissionsTree(int $userId): array
    {
        return $this->getMenuTreeWithUserPermission($userId);
    }

    public function syncUserPermissions(int $userId, array $permissionIds): bool
    {
        $normalized = [];
        foreach ($permissionIds as $pid) {
            $id = (int) $pid;
            if ($id > 0) {
                $normalized[$id] = $id;
            }
        }
        $permissionIds = array_values($normalized);

        $existing = $this->db->table('auth_users_permissions')
            ->select('permission_id')
            ->where('user_id', $userId)
            ->get()
            ->getResultArray();

        $existingIds = [];
        foreach ($existing as $row) {
            $existingIds[] = (int) $row['permission_id'];
        }

        $toDelete = array_values(array_diff($existingIds, $permissionIds));
        $toAddOrUpdate = $permissionIds;

        $this->db->transStart();

        if (!empty($toDelete)) {
            $this->db->table('auth_users_permissions')
                ->where('user_id', $userId)
                ->whereIn('permission_id', $toDelete)
                ->delete();
        }

        foreach ($toAddOrUpdate as $pid) {
            $exists = $this->db->table('auth_users_permissions')
                ->select('permission_id')
                ->where('user_id', $userId)
                ->where('permission_id', $pid)
                ->limit(1)
                ->get()
                ->getRowArray();

            $payload = [
                'is_create' => 0,
                'is_read' => 1,
                'is_update' => 0,
                'is_delete' => 0,
            ];

            if ($exists) {
                $this->db->table('auth_users_permissions')
                    ->where('user_id', $userId)
                    ->where('permission_id', $pid)
                    ->update($payload);
            } else {
                $payload['user_id'] = $userId;
                $payload['permission_id'] = $pid;
                $this->db->table('auth_users_permissions')->insert($payload);
            }
        }

        $this->db->transComplete();
        return $this->db->transStatus() !== false;
    }
    public function getUsers(string $search = '', int $limit = 20): array
    {
        $builder = $this->db->table('auth_users')
            ->select('id, username, fullname, email')
            ->orderBy('fullname', 'ASC')
            ->limit($limit);

        $search = trim($search);
        if ($search !== '') {
            $builder->groupStart()
                ->like('username', $search)
                ->orLike('fullname', $search)
                ->groupEnd();
        }

        return $builder->get()->getResultArray();
    }

    public function getPermissionsTree(): array
    {
        return $this->db->table('auth_permissions')
            ->select('id, name, url, icon, parent_id, is_show, is_order')
            ->where('is_show', 1)
            ->orderBy('parent_id', 'ASC')
            ->orderBy('is_order', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function getMenuTree(): array
    {
        $rows = $this->getPermissionsTree();
        $nodes = [];

        foreach ($rows as $row) {
            $id = (int) $row['id'];
            $parent = $row['parent_id'] === null ? null : (int) $row['parent_id'];
            $nodes[$id] = [
                'id'        => $id,
                'name'      => (string) $row['name'],
                'url'       => (string) ($row['url'] ?? ''),
                'parent_id' => $parent,
                'children'  => [],
            ];
        }

        $tree = [];
        foreach ($nodes as $id => &$node) {
            if ($node['parent_id'] !== null && isset($nodes[$node['parent_id']])) {
                $nodes[$node['parent_id']]['children'][] = &$node;
            } else {
                $tree[] = &$node;
            }
        }
        unset($node);

        return $tree;
    }

    public function getUserPermissionsMap(int $userId): array
    {
        $rows = $this->db->table('auth_users_permissions')
            ->select('permission_id')
            ->where('user_id', $userId)
            ->where('COALESCE(is_read, 0) =', 1, false)
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['permission_id']] = true;
        }
        return $map;
    }

    public function getMenuTreeWithUserPermission(int $userId): array
    {
        $tree = $this->getMenuTree();
        $map = $this->getUserPermissionsMap($userId);

        $apply = function (array &$nodes) use (&$apply, $map) {
            foreach ($nodes as &$node) {
                $node['allowed'] = isset($map[(int) $node['id']]);
                if (!empty($node['children'])) {
                    $apply($node['children']);
                }
            }
        };
        $apply($tree);

        return $tree;
    }

    public function getUserPermissions(int $userId): array
    {
        return $this->db->table('auth_users_permissions up')
            ->select('up.user_id, up.permission_id, up.is_create, up.is_read, up.is_update, up.is_delete, p.name, p.url, p.parent_id')
            ->join('auth_permissions p', 'p.id = up.permission_id', 'inner')
            ->where('up.user_id', $userId)
            ->orderBy('p.parent_id', 'ASC')
            ->orderBy('p.is_order', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function togglePermission(int $userId, int $menuId, bool $allowed): bool
    {
        if ($allowed) {
            $exists = $this->db->table('auth_users_permissions')
                ->where('user_id', $userId)
                ->where('permission_id', $menuId)
                ->get()
                ->getRowArray();

            $payload = [
                'is_create' => 0,
                'is_read'   => 1,
                'is_update' => 0,
                'is_delete' => 0,
            ];

            if ($exists) {
                return (bool) $this->db->table('auth_users_permissions')
                    ->where('user_id', $userId)
                    ->where('permission_id', $menuId)
                    ->update($payload);
            }

            $payload['user_id'] = $userId;
            $payload['permission_id'] = $menuId;
            return (bool) $this->db->table('auth_users_permissions')->insert($payload);
        }

        return (bool) $this->db->table('auth_users_permissions')
            ->where('user_id', $userId)
            ->where('permission_id', $menuId)
            ->delete();
    }

    public function saveUserPermission(int $userId, int $permissionId, int $create, int $read, int $update, int $delete): bool
    {
        $existing = $this->db->table('auth_users_permissions')
            ->where('user_id', $userId)
            ->where('permission_id', $permissionId)
            ->get()
            ->getRowArray();

        $payload = [
            'is_create' => $create,
            'is_read'   => $read,
            'is_update' => $update,
            'is_delete' => $delete,
        ];

        if ($existing) {
            return (bool) $this->db->table('auth_users_permissions')
                ->where('user_id', $userId)
                ->where('permission_id', $permissionId)
                ->update($payload);
        }

        $payload['user_id'] = $userId;
        $payload['permission_id'] = $permissionId;
        return (bool) $this->db->table('auth_users_permissions')->insert($payload);
    }

    public function deleteUserPermission(int $userId, int $permissionId): bool
    {
        return (bool) $this->db->table('auth_users_permissions')
            ->where('user_id', $userId)
            ->where('permission_id', $permissionId)
            ->delete();
    }

    public function createPermission(array $data): int
    {
        $this->db->table('auth_permissions')->insert($data);
        return (int) $this->db->insertID();
    }

    public function updatePermission(int $id, array $data): bool
    {
        return (bool) $this->db->table('auth_permissions')->where('id', $id)->update($data);
    }

    public function deletePermission(int $id): bool
    {
        $this->db->table('auth_users_permissions')->where('permission_id', $id)->delete();
        return (bool) $this->db->table('auth_permissions')->where('id', $id)->delete();
    }

    public function getServiceAccessRows(): array
    {
        return $this->db->table('data_timkerja_layanan l')
            ->select("
                l.id,
                l.nama_layanan,
                l.url,
                l.is_show,
                CASE
                    WHEN SUM(CASE WHEN sa.is_active = 1 AND sa.nip = '' THEN 1 ELSE 0 END) > 0 THEN 'everyone'
                    WHEN SUM(CASE WHEN sa.is_active = 0 AND sa.nip = '' THEN 1 ELSE 0 END) > 0 THEN 'assigned'
                    WHEN SUM(CASE WHEN sa.is_active = 1 AND sa.nip <> '' THEN 1 ELSE 0 END) > 0 THEN 'assigned'
                    ELSE 'everyone'
                END AS access_mode,
                SUM(CASE WHEN sa.is_active = 1 AND sa.nip <> '' THEN 1 ELSE 0 END) AS total_assigned
            ")
            ->join('auth_service_access sa', 'sa.layanan_id = l.id', 'left')
            ->groupBy('l.id')
            ->orderBy('l.id', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function getServiceAccessRowById(int $layananId): ?array
    {
        $row = $this->db->table('data_timkerja_layanan l')
            ->select("
                l.id,
                l.nama_layanan,
                l.url,
                l.is_show,
                CASE
                    WHEN SUM(CASE WHEN sa.is_active = 1 AND sa.nip = '' THEN 1 ELSE 0 END) > 0 THEN 'everyone'
                    WHEN SUM(CASE WHEN sa.is_active = 0 AND sa.nip = '' THEN 1 ELSE 0 END) > 0 THEN 'assigned'
                    WHEN SUM(CASE WHEN sa.is_active = 1 AND sa.nip <> '' THEN 1 ELSE 0 END) > 0 THEN 'assigned'
                    ELSE 'everyone'
                END AS access_mode,
                SUM(CASE WHEN sa.is_active = 1 AND sa.nip <> '' THEN 1 ELSE 0 END) AS total_assigned
            ")
            ->join('auth_service_access sa', 'sa.layanan_id = l.id', 'left')
            ->where('l.id', $layananId)
            ->groupBy('l.id')
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    public function getServiceAssignments(int $layananId): array
    {
        return $this->db->table('auth_service_access')
            ->select('id, nip, access_mode, is_active, created_at')
            ->where('layanan_id', $layananId)
            ->where('nip <>', '')
            ->where('is_active', 1)
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function getServiceAssignmentsWithPegawai(int $layananId): array
    {
        return $this->db->table('auth_service_access sa')
            ->select('sa.id, sa.nip, sa.access_mode, sa.is_active, sa.created_at, COALESCE(dm.nama, \'-\') AS nama')
            ->join('data_pegawai dm', 'dm.nip = sa.nip', 'left')
            ->where('sa.layanan_id', $layananId)
            ->where('sa.nip <>', '')
            ->where('sa.is_active', 1)
            ->orderBy('sa.id', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function getServiceById(int $layananId): ?array
    {
        return $this->getServiceAccessRowById($layananId);
    }

    public function getMasterPegawaiBySearch(string $search = '', int $limit = 20): array
    {
        $builder = $this->db->table('data_pegawai')
            ->select('nip, nama')
            ->where('nip IS NOT NULL', null, false)
            ->where('nip <>', '')
            ->orderBy('nama', 'ASC')
            ->limit($limit);

        $search = trim($search);
        if ($search !== '') {
            $builder->groupStart()
                ->like('nip', $search)
                ->orLike('nama', $search)
                ->groupEnd();
        }

        return $builder->get()->getResultArray();
    }

    public function setServiceMode(int $layananId, string $mode): bool
    {
        $mode = $mode === 'assigned' ? 'assigned' : 'everyone';
        $userId = (int) (session()->get('userid') ?? 0);

        $this->db->transStart();

        if ($mode === 'everyone') {
            $this->upsertServiceAccess($layananId, '', 'everyone', 1, $userId);

            $this->db->table('auth_service_access')
                ->where('layanan_id', $layananId)
                ->where('nip <>', '')
                ->update([
                    'access_mode' => 'assigned',
                    'is_active'   => 0,
                    'updated_by'  => $userId > 0 ? $userId : null,
                ]);
        } else {
            $this->upsertServiceAccess($layananId, '', 'everyone', 0, $userId);
        }

        $this->db->transComplete();
        return $this->db->transStatus() !== false;
    }

    public function addAssignedNip(int $layananId, string $nip): bool
    {
        $nip = trim($nip);
        if ($nip === '' || !$this->pegawaiExistsByNip($nip)) {
            return false;
        }

        $userId = (int) (session()->get('userid') ?? 0);
        $this->db->transStart();

        $this->upsertServiceAccess($layananId, '', 'everyone', 0, $userId);
        $this->upsertServiceAccess($layananId, $nip, 'assigned', 1, $userId);

        $this->db->transComplete();
        return $this->db->transStatus() !== false;
    }

    public function syncAssignedNips(int $layananId, array $nips): bool
    {
        $clean = [];
        foreach ($nips as $nip) {
            $v = trim((string) $nip);
            if ($v !== '') {
                $clean[$v] = $v;
            }
        }
        $nips = array_values($clean);
        $userId = (int) (session()->get('userid') ?? 0);

        $this->db->transStart();

        // Disable all current assigned records first.
        $this->db->table('auth_service_access')
            ->where('layanan_id', $layananId)
            ->where('nip <>', '')
            ->update([
                'access_mode' => 'assigned',
                'is_active' => 0,
                'updated_by' => $userId > 0 ? $userId : null,
            ]);

        foreach ($nips as $nip) {
            if (!$this->pegawaiExistsByNip($nip)) {
                continue;
            }
            $this->upsertServiceAccess($layananId, $nip, 'assigned', 1, $userId);
        }

        // Keep mode consistent: no assignee means everyone active.
        if (empty($nips)) {
            $this->upsertServiceAccess($layananId, '', 'everyone', 1, $userId);
        } else {
            $this->upsertServiceAccess($layananId, '', 'everyone', 0, $userId);
        }

        $this->db->transComplete();
        return $this->db->transStatus() !== false;
    }

    public function removeAssignedNip(int $layananId, string $nip): bool
    {
        $nip = trim($nip);
        if ($layananId <= 0 || $nip === '') {
            return false;
        }

        $userId = (int) (session()->get('userid') ?? 0);
        return (bool) $this->db->table('auth_service_access')
            ->where('layanan_id', $layananId)
            ->where('nip', $nip)
            ->update([
                'access_mode' => 'assigned',
                'is_active'   => 0,
                'updated_by'  => $userId > 0 ? $userId : null,
            ]);
    }

    private function pegawaiExistsByNip(string $nip): bool
    {
        $row = $this->db->table('data_pegawai')
            ->select('nip')
            ->where('nip', $nip)
            ->limit(1)
            ->get()
            ->getRowArray();

        return !empty($row);
    }

    private function upsertServiceAccess(int $layananId, string $nip, string $accessMode, int $isActive, int $userId = 0): bool
    {
        $actor = $userId > 0 ? $userId : null;
        $existing = $this->db->table('auth_service_access')
            ->select('id')
            ->where('layanan_id', $layananId)
            ->where('nip', $nip)
            ->limit(1)
            ->get()
            ->getRowArray();

        if (!empty($existing)) {
            return (bool) $this->db->table('auth_service_access')
                ->where('id', (int) $existing['id'])
                ->update([
                    'access_mode' => $accessMode,
                    'is_active'   => $isActive,
                    'updated_by'  => $actor,
                ]);
        }

        return (bool) $this->db->table('auth_service_access')->insert([
            'layanan_id'  => $layananId,
            'nip'         => $nip,
            'access_mode' => $accessMode,
            'is_active'   => $isActive,
            'created_by'  => $actor,
            'updated_by'  => $actor,
        ]);
    }
}
