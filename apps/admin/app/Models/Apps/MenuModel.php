<?php

namespace App\Models\Apps;

use CodeIgniter\Model;

class MenuModel extends Model
{
    protected $DBGroup = 'default';
    protected $table = 'auth_users_permissions';

     
    public function __construct()
    {
        parent::__construct();
    }

    public function getMenusPermissions($userId)
    {
        $userId = (int) $userId;
        $roleId = $this->getRoleIdByUserId($userId);

        if ($roleId > 0 && $this->tableExists('auth_role_permissions')) {
            $sql = "SELECT b.*, a.is_create, a.is_read, a.is_update, a.is_delete
                    FROM auth_role_permissions a
                    INNER JOIN auth_permissions b ON b.id = a.permission_id
                    WHERE a.role_id = ?
                      AND b.parent_id IS NULL
                      AND b.is_show <> 0
                      AND COALESCE(a.is_read, 0) = 1
                    ORDER BY b.is_order ASC";
            $results = $this->db->query($sql, [$roleId])->getResultArray();
            if (!empty($results)) {
                return $results;
            }
        }

        // Fallback ke auth_users_permissions
        $sql = "SELECT b.*, a.is_create, a.is_read, a.is_update, a.is_delete
                FROM auth_users_permissions a
                INNER JOIN auth_permissions b ON b.id = a.permission_id
                WHERE a.user_id = ?
                  AND b.parent_id IS NULL
                  AND b.is_show <> 0
                  AND COALESCE(a.is_read, 0) = 1
                ORDER BY b.is_order ASC";
        return $this->db->query($sql, [$userId])->getResultArray();
    }

    public function getSubMenus($parentId, ?int $userId = null)
    {
        if ($userId !== null) {
            $roleId = $this->getRoleIdByUserId((int) $userId);
            if ($roleId > 0 && $this->tableExists('auth_role_permissions')) {
                $sql = "SELECT p.*
                        FROM auth_role_permissions rp
                        INNER JOIN auth_permissions p ON p.id = rp.permission_id
                        WHERE rp.role_id = ?
                          AND p.parent_id = ?
                          AND p.is_show <> 0
                          AND COALESCE(rp.is_read, 0) = 1
                        ORDER BY p.is_order ASC";
                $results = $this->db->query($sql, [$roleId, (int) $parentId])->getResultArray();
                if (!empty($results)) {
                    return $results;
                }
            }

            // Fallback ke auth_users_permissions
            $sql = "SELECT p.*
                    FROM auth_users_permissions up
                    INNER JOIN auth_permissions p ON p.id = up.permission_id
                    WHERE up.user_id = ?
                      AND p.parent_id = ?
                      AND p.is_show <> 0
                      AND COALESCE(up.is_read, 0) = 1
                    ORDER BY p.is_order ASC";
            return $this->db->query($sql, [(int) $userId, (int) $parentId])->getResultArray();
        }

        $sql = "SELECT * FROM auth_permissions a WHERE parent_id = ? AND is_show <> 0 ORDER BY is_order ASC";
        return $this->db->query($sql, [(int) $parentId])->getResultArray();
    }

    public function getAllSubMenus(?int $userId = null)
    {
        if ($userId !== null) {
            $roleId = $this->getRoleIdByUserId((int) $userId);
            if ($roleId > 0 && $this->tableExists('auth_role_permissions')) {
                $sql = "SELECT p.*
                        FROM auth_role_permissions rp
                        INNER JOIN auth_permissions p ON p.id = rp.permission_id
                        WHERE rp.role_id = ?
                          AND p.parent_id IS NOT NULL
                          AND p.is_show <> 0
                          AND COALESCE(rp.is_read, 0) = 1
                        ORDER BY p.is_order ASC";
                $results = $this->db->query($sql, [$roleId])->getResultArray();
                if (!empty($results)) {
                    return $results;
                }
            }

            // Fallback ke auth_users_permissions
            $sql = "SELECT p.*
                    FROM auth_users_permissions up
                    INNER JOIN auth_permissions p ON p.id = up.permission_id
                    WHERE up.user_id = ?
                      AND p.parent_id IS NOT NULL
                      AND p.is_show <> 0
                      AND COALESCE(up.is_read, 0) = 1
                    ORDER BY p.is_order ASC";
            return $this->db->query($sql, [(int) $userId])->getResultArray();
        }

        $sql = "SELECT * FROM auth_permissions WHERE parent_id IS NOT NULL AND is_show <> 0 ORDER BY is_order ASC";
        return $this->db->query($sql)->getResultArray();
    }

    public function getPermissions($userId, $menuId)
    {
        $userId = (int) $userId;
        $menuId = (int) $menuId;

        if ($menuId <= 0) {
            return [];
        }

        $roleId = $this->getRoleIdByUserId($userId);
        if ($roleId > 0 && $this->tableExists('auth_role_permissions')) {
            $sql = "SELECT permission_id, is_create, is_read, is_update, is_delete 
                    FROM auth_role_permissions WHERE role_id = ? AND permission_id = ?";
            $permissions = $this->db->query($sql, [$roleId, $menuId])->getResultArray();
            if (!empty($permissions)) {
                $formatted = [];
                foreach ($permissions as $perm) {
                    $formatted = [
                        'create' => $perm['is_create'],
                        'update' => $perm['is_update'],
                        'delete' => $perm['is_delete'],
                        'view'   => $perm['is_read'],
                    ];
                }
                return $formatted;
            }
        }

        // Fallback
        $sql = "SELECT permission_id, is_create, is_read, is_update, is_delete 
                FROM auth_users_permissions WHERE user_id = ? AND permission_id = ?";
        $permissions = $this->db->query($sql, [$userId, $menuId])->getResultArray();
        $formatted = [];
        foreach ($permissions as $perm) {
            $formatted = [
                'create' => $perm['is_create'],
                'update' => $perm['is_update'],
                'delete' => $perm['is_delete'],
                'view'   => $perm['is_read'],
            ];
        }
        return $formatted;
    }

    public function findPermissionIdByUrl(string $url): ?int
    {
        $normalizedUrl = trim($url, '/');
        if ($normalizedUrl === '') {
            return null;
        }

        $row = $this->db->table('auth_permissions')
            ->select('id')
            ->where('url', $normalizedUrl)
            ->limit(1)
            ->get()
            ->getRowArray();

        if (empty($row) && ($normalizedUrl === 'dashboard' || $normalizedUrl === 'home')) {
            $fallbackUrl = ($normalizedUrl === 'dashboard') ? 'home' : 'dashboard';
            $row = $this->db->table('auth_permissions')
                ->select('id')
                ->where('url', $fallbackUrl)
                ->limit(1)
                ->get()
                ->getRowArray();
        }

        return isset($row['id']) ? (int) $row['id'] : null;
    }

    private function getRoleIdByUserId(int $userId): int
    {
        if ($userId <= 0) {
            return 0;
        }

        $user = $this->db->table('auth_users')
            ->select('role')
            ->where('id', $userId)
            ->limit(1)
            ->get()
            ->getRowArray();

        if (!$user || empty($user['role'])) {
            return 0;
        }

        if (!$this->tableExists('auth_roles')) {
            return 0;
        }

        $role = $this->db->table('auth_roles')
            ->select('id')
            ->where('role_code', strtoupper(trim((string) $user['role'])))
            ->where('is_active', 1)
            ->limit(1)
            ->get()
            ->getRowArray();

        return isset($role['id']) ? (int) $role['id'] : 0;
    }

    private function tableExists(string $tableName): bool
    {
        return in_array($tableName, $this->db->listTables(), true);
    }
}
