<?php

namespace App\Controllers\Apps;

use App\Controllers\BaseController;
use App\Models\Apps\SettingManagerModel;

class AccessManagerApiController extends BaseController
{
    private SettingManagerModel $manager;

    public function __construct()
    {
        $this->manager = new SettingManagerModel();
    }

    public function menus()
    {
        if ($denied = $this->forbiddenUnlessCanRead('manage-role')) return $denied;
        return $this->ok('OK', $this->manager->getMenusTree());
    }

    public function createMenu()
    {
        if ($denied = $this->forbiddenUnlessCanRead('manage-role')) return $denied;
        $payload = $this->menuPayload();
        $errors = $this->validateMenuPayload($payload);
        if (!empty($errors)) {
            return $this->fail('Validasi gagal', $errors, 422);
        }

        $id = $this->manager->createMenu($payload);
        if ($id <= 0) {
            return $this->fail('Gagal membuat menu', [], 500);
        }

        $this->writeAudit('menu.create', ['menu_id' => $id] + $payload);
        return $this->ok('Menu berhasil dibuat', $this->manager->getMenuById($id), 201);
    }

    public function updateMenu(int $id = 0)
    {
        if ($denied = $this->forbiddenUnlessCanRead('manage-role')) return $denied;
        if ($id <= 0) {
            return $this->fail('id menu wajib valid', ['id' => 'id menu wajib valid'], 422);
        }

        $current = $this->manager->getMenuById($id);
        if (!$current) {
            return $this->fail('Menu tidak ditemukan', [], 404);
        }

        $payload = $this->menuPayload();
        $errors = $this->validateMenuPayload($payload);
        if (!empty($errors)) {
            return $this->fail('Validasi gagal', $errors, 422);
        }

        $ok = $this->manager->updateMenuById($id, $payload);
        if (!$ok) {
            return $this->fail('Gagal memperbarui menu', [], 500);
        }

        $this->writeAudit('menu.update', ['menu_id' => $id] + $payload);
        return $this->ok('Menu berhasil diperbarui', $this->manager->getMenuById($id));
    }

    public function deleteMenu(int $id = 0)
    {
        if ($denied = $this->forbiddenUnlessCanRead('manage-role')) return $denied;
        if ($id <= 0) {
            return $this->fail('id menu wajib valid', ['id' => 'id menu wajib valid'], 422);
        }

        $current = $this->manager->getMenuById($id);
        if (!$current) {
            return $this->fail('Menu tidak ditemukan', [], 404);
        }

        $ok = $this->manager->deleteMenuById($id);
        if (!$ok) {
            return $this->fail('Gagal menghapus menu', [], 500);
        }

        $this->writeAudit('menu.delete', ['menu_id' => $id, 'name' => $current['name']]);
        return $this->ok('Menu berhasil dihapus', ['id' => $id]);
    }

    public function usersSearch()
    {
        if ($denied = $this->forbiddenUnlessCanRead('manage-role')) return $denied;
        $keyword = trim((string) ($this->request->getGet('keyword') ?? ''));
        $rows = $this->manager->getUsersByKeyword($keyword, 30);
        return $this->ok('OK', $rows);
    }

    public function createUser()
    {
        if ($denied = $this->forbiddenUnlessCanRead('manage-role')) return $denied;

        $payload = $this->userPayload();
        $errors = $this->validateUserPayload($payload);
        if (!empty($errors)) {
            return $this->fail('Validasi gagal', $errors, 422);
        }

        if ($this->manager->usernameExists($payload['username'])) {
            return $this->fail('Validasi gagal', ['username' => 'Username sudah digunakan'], 422);
        }

        if ($this->manager->emailExists($payload['email'])) {
            return $this->fail('Validasi gagal', ['email' => 'Email sudah digunakan'], 422);
        }

        $payload['password_hash'] = password_hash($payload['password'], PASSWORD_DEFAULT);
        $id = $this->manager->createUser($payload);
        if ($id <= 0) {
            return $this->fail('Gagal membuat user', [], 500);
        }

        $created = $this->manager->getUserById($id);
        $this->writeAudit('user.create', [
            'target_user_id' => $id,
            'username' => $payload['username'],
            'email' => $payload['email'],
        ]);

        return $this->ok('User berhasil dibuat', $created, 201);
    }

    public function userPermissions(int $userId = 0)
    {
        if ($denied = $this->forbiddenUnlessCanRead('manage-role')) return $denied;
        if ($userId <= 0) {
            return $this->fail('user_id wajib valid', ['user_id' => 'user_id wajib valid'], 422);
        }

        $tree = $this->manager->getUserPermissionsTree($userId);
        $ids = $this->manager->getUserPermissionIds($userId);

        return $this->ok('OK', [
            'user_id' => $userId,
            'permission_ids' => $ids,
            'tree' => $tree,
        ]);
    }

    public function syncUserPermissions(int $userId = 0)
    {
        if ($denied = $this->forbiddenUnlessCanRead('manage-role')) return $denied;
        if ($userId <= 0) {
            return $this->fail('user_id wajib valid', ['user_id' => 'user_id wajib valid'], 422);
        }

        $payload = $this->request->getJSON(true);
        if (!is_array($payload)) {
            $payload = $this->request->getRawInput();
        }
        if (!is_array($payload)) {
            $payload = $this->request->getPost();
        }

        $permissionIds = $payload['permission_ids'] ?? [];
        if (!is_array($permissionIds)) {
            return $this->fail('permission_ids wajib array', ['permission_ids' => 'permission_ids wajib array'], 422);
        }

        $ok = $this->manager->syncUserPermissions($userId, $permissionIds);
        if (!$ok) {
            return $this->fail('Gagal sinkronisasi permission user', [], 500);
        }

        $updated = $this->manager->getUserPermissionIds($userId);
        $this->writeAudit('user.permission.sync', [
            'target_user_id' => $userId,
            'permission_ids' => $updated,
        ]);

        return $this->ok('Permission user berhasil diperbarui', [
            'user_id' => $userId,
            'permission_ids' => $updated,
        ]);
    }

    public function services()
    {
        if ($denied = $this->forbiddenUnlessCanRead('manage-layanan')) return $denied;
        return $this->ok('OK', $this->manager->getServiceAccessRows());
    }

    public function serviceDetail(int $id = 0)
    {
        if ($denied = $this->forbiddenUnlessCanRead('manage-layanan')) return $denied;
        if ($id <= 0) {
            return $this->fail('id layanan wajib valid', ['id' => 'id layanan wajib valid'], 422);
        }

        $service = $this->manager->getServiceById($id);
        if (!$service) {
            return $this->fail('Layanan tidak ditemukan', [], 404);
        }

        return $this->ok('OK', [
            'service' => $service,
            'assignees' => $this->manager->getServiceAssignmentsWithPegawai($id),
        ]);
    }

    public function updateServiceAccess(int $id = 0)
    {
        if ($denied = $this->forbiddenUnlessCanRead('manage-layanan')) return $denied;
        if ($id <= 0) {
            return $this->fail('id layanan wajib valid', ['id' => 'id layanan wajib valid'], 422);
        }

        $payload = $this->request->getJSON(true);
        if (!is_array($payload)) {
            $payload = $this->request->getRawInput();
        }
        if (!is_array($payload)) {
            $payload = $this->request->getPost();
        }

        $accessType = strtoupper(trim((string) ($payload['access_type'] ?? 'EVERYONE')));
        if (!in_array($accessType, ['EVERYONE', 'ASSIGNED'], true)) {
            return $this->fail('access_type wajib EVERYONE atau ASSIGNED', ['access_type' => 'access_type tidak valid'], 422);
        }

        $ok = $this->manager->setServiceMode($id, $accessType === 'ASSIGNED' ? 'assigned' : 'everyone');
        if (!$ok) {
            return $this->fail('Gagal memperbarui access layanan', [], 500);
        }

        $service = $this->manager->getServiceById($id);
        $this->writeAudit('service.access.update', [
            'service_id' => $id,
            'access_type' => $accessType,
        ]);

        return $this->ok('Akses layanan berhasil diperbarui', $service);
    }

    public function serviceAssignees(int $id = 0)
    {
        if ($denied = $this->forbiddenUnlessCanRead('manage-layanan')) return $denied;
        if ($id <= 0) {
            return $this->fail('id layanan wajib valid', ['id' => 'id layanan wajib valid'], 422);
        }

        return $this->ok('OK', $this->manager->getServiceAssignmentsWithPegawai($id));
    }

    public function syncServiceAssignees(int $id = 0)
    {
        if ($denied = $this->forbiddenUnlessCanRead('manage-layanan')) return $denied;
        if ($id <= 0) {
            return $this->fail('id layanan wajib valid', ['id' => 'id layanan wajib valid'], 422);
        }

        $payload = $this->request->getJSON(true);
        if (!is_array($payload)) {
            $payload = $this->request->getRawInput();
        }
        if (!is_array($payload)) {
            $payload = $this->request->getPost();
        }

        $assignees = $payload['assignees'] ?? [];
        if (!is_array($assignees)) {
            return $this->fail('assignees wajib array', ['assignees' => 'assignees wajib array'], 422);
        }

        $ok = $this->manager->syncAssignedNips($id, $assignees);
        if (!$ok) {
            return $this->fail('Gagal sinkronisasi assignee layanan', [], 500);
        }

        $rows = $this->manager->getServiceAssignmentsWithPegawai($id);
        $this->writeAudit('service.assignee.sync', [
            'service_id' => $id,
            'assignees' => array_values($assignees),
        ]);

        return $this->ok('Assignee layanan berhasil diperbarui', $rows);
    }

    private function menuPayload(): array
    {
        $payload = $this->request->getJSON(true);
        if (!is_array($payload)) {
            $payload = $this->request->getRawInput();
        }
        if (!is_array($payload)) {
            $payload = $this->request->getPost();
        }

        $parentId = $payload['parent_id'] ?? null;
        $parentId = ($parentId === '' || $parentId === null) ? null : (int) $parentId;

        return [
            'parent_id' => $parentId,
            'name' => trim((string) ($payload['name'] ?? '')),
            'url' => trim((string) ($payload['url'] ?? '')),
            'icon' => trim((string) ($payload['icon'] ?? '')),
            'sort' => (int) ($payload['sort'] ?? 0),
            'is_active' => in_array((string) ($payload['is_active'] ?? '1'), ['1', 'true', 'on'], true),
        ];
    }

    private function userPayload(): array
    {
        $payload = $this->request->getJSON(true);
        if (!is_array($payload)) {
            $payload = $this->request->getRawInput();
        }
        if (!is_array($payload)) {
            $payload = $this->request->getPost();
        }

        return [
            'username' => trim((string) ($payload['username'] ?? '')),
            'fullname' => trim((string) ($payload['fullname'] ?? '')),
            'email' => strtolower(trim((string) ($payload['email'] ?? ''))),
            'password' => (string) ($payload['password'] ?? ''),
            'status' => in_array((string) ($payload['status'] ?? '1'), ['1', 'true', 'on'], true) ? '1' : '0',
            'active' => in_array((string) ($payload['active'] ?? '1'), ['1', 'true', 'on'], true) ? 1 : 0,
            'role' => strtoupper(trim((string) ($payload['role'] ?? 'USR'))),
            'force_pass_reset' => in_array((string) ($payload['force_pass_reset'] ?? '0'), ['1', 'true', 'on'], true) ? 1 : 0,
        ];
    }

    private function validateMenuPayload(array $payload): array
    {
        $errors = [];
        if ($payload['name'] === '') {
            $errors['name'] = 'Nama menu wajib diisi';
        }
        if ($payload['url'] === '') {
            $errors['url'] = 'URL menu wajib diisi';
        }
        if ($payload['sort'] < 0) {
            $errors['sort'] = 'Sort tidak boleh negatif';
        }
        if ($payload['parent_id'] !== null && $payload['parent_id'] <= 0) {
            $errors['parent_id'] = 'parent_id tidak valid';
        }
        return $errors;
    }

    private function validateUserPayload(array $payload): array
    {
        $errors = [];
        if ($payload['username'] === '') {
            $errors['username'] = 'Username wajib diisi';
        } elseif (strlen($payload['username']) < 4 || strlen($payload['username']) > 64) {
            $errors['username'] = 'Username harus 4-64 karakter';
        }

        if ($payload['fullname'] === '') {
            $errors['fullname'] = 'Nama lengkap wajib diisi';
        } elseif (strlen($payload['fullname']) < 3 || strlen($payload['fullname']) > 120) {
            $errors['fullname'] = 'Nama lengkap harus 3-120 karakter';
        }

        if ($payload['email'] === '') {
            $errors['email'] = 'Email wajib diisi';
        } elseif (!filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Format email tidak valid';
        } elseif (strlen($payload['email']) > 120) {
            $errors['email'] = 'Email maksimal 120 karakter';
        }

        if ($payload['password'] === '') {
            $errors['password'] = 'Password wajib diisi';
        } elseif (strlen($payload['password']) < 8 || strlen($payload['password']) > 72) {
            $errors['password'] = 'Password harus 8-72 karakter';
        }

        if (!in_array($payload['role'], ['USR', 'ADM'], true)) {
            $errors['role'] = 'Role tidak valid';
        }

        return $errors;
    }

    private function ok(string $message, $data = [], int $statusCode = 200)
    {
        return $this->response->setStatusCode($statusCode)->setJSON([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'errors' => [],
        ]);
    }

    private function fail(string $message, array $errors = [], int $statusCode = 400)
    {
        return $this->response->setStatusCode($statusCode)->setJSON([
            'success' => false,
            'message' => $message,
            'data' => [],
            'errors' => $errors,
        ]);
    }

    private function writeAudit(string $action, array $context = []): void
    {
        $actorId = (int) (session()->get('userid') ?? 0);
        log_message('notice', '[AUDIT] {action} actor={actor} context={context}', [
            'action' => $action,
            'actor' => $actorId,
            'context' => json_encode($context, JSON_UNESCAPED_UNICODE),
        ]);
    }

    private function forbiddenUnlessCanRead(string $menuUrl)
    {
        $userId = (int) (session()->get('userid') ?? 0);
        if ($this->manager->canUserReadMenuUrl($userId, $menuUrl)) {
            return null;
        }

        return $this->fail('Forbidden', ['authorization' => 'Akses endpoint ditolak.'], 403);
    }
}
