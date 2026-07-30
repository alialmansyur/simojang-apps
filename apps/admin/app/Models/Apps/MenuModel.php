<?php

namespace App\Models\Apps;

use CodeIgniter\Model;

class MenuModel extends Model
{
    protected $table = 'auth_users_permissions';
     
    public function __construct()
    {
        parent::__construct();
    }

    public function getMenusPermissions($userId)
    {
        $userId = (int) $userId;
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

    public function getPermissions($userId, $menuId)
    {
        $userId = (int) $userId;
        $menuId = (int) $menuId;

        if ($menuId <= 0) {
            return [];
        }

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

        return isset($row['id']) ? (int) $row['id'] : null;
    }
}
