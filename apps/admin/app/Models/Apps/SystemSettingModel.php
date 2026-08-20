<?php

namespace App\Models\Apps;

use CodeIgniter\Model;

class SystemSettingModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'sys_system_settings';

    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'setting_group',
        'setting_key',
        'setting_value',
        'setting_type',
        'description',
        'is_public',
        'updated_by',
    ];

    public function getValue(string $key, $default = null)
    {
        $row = $this->where('setting_key', $key)->first();
        return $row['setting_value'] ?? $default;
    }

    public function getGroup(string $group): array
    {
        $rows = $this->where('setting_group', $group)->findAll();
        $result = [];
        foreach ($rows as $row) {
            $result[$row['setting_key']] = $row['setting_value'];
        }
        return $result;
    }

    public function getAllAsMap(): array
    {
        $rows = $this->findAll();
        $result = [];
        foreach ($rows as $row) {
            $result[$row['setting_key']] = $row['setting_value'];
        }
        return $result;
    }

    public function upsert(string $group, string $key, string $value, string $type = 'string', ?string $description = null, ?int $updatedBy = null): bool
    {
        $existing = $this->where('setting_key', $key)->first();
        $payload = [
            'setting_group' => $group,
            'setting_key'   => $key,
            'setting_value' => $value,
            'setting_type'  => $type,
            'description'   => $description,
            'updated_by'    => $updatedBy,
        ];

        if ($existing) {
            return (bool) $this->update((int) $existing['id'], $payload);
        }

        return (bool) $this->insert($payload);
    }

    public function bulkUpsert(array $items, ?int $updatedBy = null): bool
    {
        $this->db->transStart();
        foreach ($items as $key => $meta) {
            $group = (string) ($meta['group'] ?? 'general');
            $value = trim((string) ($meta['value'] ?? ''));
            $ok = $this->upsert($group, (string) $key, $value, 'string', null, $updatedBy);
            if (!$ok) {
                $this->db->transRollback();
                return false;
            }
        }
        $this->db->transComplete();
        return $this->db->transStatus() !== false;
    }
}
