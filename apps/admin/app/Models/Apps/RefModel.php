<?php

namespace App\Models\Apps;

use CodeIgniter\Model;

class RefModel extends Model
{
    private const TABLE_PREFIX = 'data_support_';
    private const EXTRA_TABLES = [
        'instansi' => 'data_instansi',
    ];
    private const RESERVED_COLUMNS = [
        'created_at', 'updated_at', 'deleted_at',
        'created_by', 'updated_by', 'deleted_by',
    ];

    private array $schemaCache = [];

    public function getSupportTables(): array
    {
        $rows = $this->db->query(
            "SELECT table_name
             FROM information_schema.tables
             WHERE table_schema = ?
               AND table_type = 'BASE TABLE'
               AND table_name LIKE ?
             ORDER BY table_name ASC",
            [$this->db->database, self::TABLE_PREFIX . '%']
        )->getResultArray();

        $result = [];
        foreach ($rows as $row) {
            $result[] = (string) $row['table_name'];
        }

        foreach (self::EXTRA_TABLES as $table) {
            if ($this->db->tableExists($table) && !in_array($table, $result, true)) {
                $result[] = $table;
            }
        }

        sort($result);
        return $result;
    }

    public function resolveTableBySlug(string $slug): ?string
    {
        $slug = strtolower(trim($slug));
        if ($slug === '' || preg_match('/^[a-z0-9_]+$/', $slug) !== 1) {
            return null;
        }

        $table = self::EXTRA_TABLES[$slug] ?? (self::TABLE_PREFIX . $slug);
        $allowed = $this->getSupportTables();
        if (!in_array($table, $allowed, true)) {
            return null;
        }
        return $table;
    }

    public function getAllowedTables(int $userId): array
    {
        $rows = $this->db->table('auth_users_permissions up')
            ->select('p.id, p.name, p.url, p.icon, p.parent_id')
            ->join('auth_permissions p', 'p.id = up.permission_id', 'inner')
            ->where('up.user_id', $userId)
            ->where('COALESCE(up.is_read, 0) =', 1, false)
            ->where('p.url !=', 'ref')
            ->like('p.url', 'ref/', 'after')
            ->orderBy('p.is_order', 'ASC')
            ->get()
            ->getResultArray();

        $result = [];
        foreach ($rows as $row) {
            $url = (string) ($row['url'] ?? '');
            $slug = trim(substr($url, 4));
            $table = $this->resolveTableBySlug($slug);
            if ($table === null) {
                continue;
            }

            $result[] = [
                'permission_id' => (int) $row['id'],
                'table' => $table,
                'slug' => $slug,
                'label' => (string) ($row['name'] ?? $slug),
                'icon' => (string) ($row['icon'] ?? 'bi bi-table'),
                'url' => $url,
            ];
        }
        return $result;
    }

    public function canUserAccessSlug(int $userId, string $slug): bool
    {
        $slug = strtolower(trim($slug));
        if ($slug === '') {
            return false;
        }

        $url = 'ref/' . $slug;
        $row = $this->db->table('auth_users_permissions up')
            ->select('up.permission_id')
            ->join('auth_permissions p', 'p.id = up.permission_id', 'inner')
            ->where('up.user_id', $userId)
            ->where('COALESCE(up.is_read, 0) =', 1, false)
            ->where('p.url', $url)
            ->limit(1)
            ->get()
            ->getRowArray();

        return !empty($row);
    }

    public function getSchema(string $table): array
    {
        if (isset($this->schemaCache[$table])) {
            return $this->schemaCache[$table];
        }

        $rows = $this->db->query(
            "SELECT column_name, data_type, column_type, is_nullable, column_key, column_default, extra, ordinal_position
             FROM information_schema.columns
             WHERE table_schema = ? AND table_name = ?
             ORDER BY ordinal_position ASC",
            [$this->db->database, $table]
        )->getResultArray();

        $columns = [];
        $pk = null;
        foreach ($rows as $row) {
            $col = (string) $row['column_name'];
            $dataType = strtolower((string) $row['data_type']);
            $columnKey = (string) ($row['column_key'] ?? '');
            $extra = strtolower((string) ($row['extra'] ?? ''));

            $isPk = $columnKey === 'PRI';
            if ($isPk && $pk === null) {
                $pk = $col;
            }

            $isAuto = strpos($extra, 'auto_increment') !== false;
            $isReserved = in_array(strtolower($col), self::RESERVED_COLUMNS, true);
            $isWritable = !$isPk && !$isAuto && !$isReserved;

            $columns[] = [
                'name' => $col,
                'label' => ucwords(str_replace('_', ' ', $col)),
                'data_type' => $dataType,
                'column_type' => (string) ($row['column_type'] ?? ''),
                'is_nullable' => ((string) $row['is_nullable']) === 'YES',
                'is_pk' => $isPk,
                'is_auto' => $isAuto,
                'default' => $row['column_default'],
                'is_writable' => $isWritable,
                'input_type' => $this->mapInputType($dataType),
            ];
        }

        if ($pk === null) {
            foreach ($columns as $col) {
                if (strtolower($col['name']) === 'id') {
                    $pk = $col['name'];
                    break;
                }
            }
        }

        $searchable = [];
        foreach ($columns as $col) {
            if (in_array($col['data_type'], ['char', 'varchar', 'text', 'tinytext', 'mediumtext', 'longtext'], true)) {
                $searchable[] = $col['name'];
            }
        }

        $schema = [
            'table' => $table,
            'pk' => $pk,
            'columns' => $columns,
            'searchable' => $searchable,
        ];

        $this->schemaCache[$table] = $schema;
        return $schema;
    }

    public function getList(string $table, array $params): array
    {
        $schema = $this->getSchema($table);
        $pk = (string) ($schema['pk'] ?? 'id');

        $page = max(1, (int) ($params['page'] ?? 1));
        $perPage = (int) ($params['per_page'] ?? 10);
        if ($perPage <= 0) {
            $perPage = 10;
        }
        if ($perPage > 100) {
            $perPage = 100;
        }

        $search = trim((string) ($params['search'] ?? ''));
        $sortBy = trim((string) ($params['sort_by'] ?? $pk));
        $sortDir = strtolower((string) ($params['sort_dir'] ?? 'desc')) === 'asc' ? 'ASC' : 'DESC';

        $allowedColumns = array_column($schema['columns'], 'name');
        if (!in_array($sortBy, $allowedColumns, true)) {
            $sortBy = $pk;
        }

        $builder = $this->db->table($table);
        if ($search !== '' && !empty($schema['searchable'])) {
            $builder->groupStart();
            foreach ($schema['searchable'] as $idx => $col) {
                if ($idx === 0) {
                    $builder->like($col, $search);
                } else {
                    $builder->orLike($col, $search);
                }
            }
            $builder->groupEnd();
        }

        $countBuilder = clone $builder;
        $total = (int) $countBuilder->countAllResults();

        $offset = ($page - 1) * $perPage;
        $rows = $builder
            ->orderBy($sortBy, $sortDir)
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        return [
            'rows' => $rows,
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_page' => (int) ceil($total / $perPage),
                'sort_by' => $sortBy,
                'sort_dir' => strtolower($sortDir),
            ],
        ];
    }

    public function insertRow(string $table, array $data): int
    {
        $schema = $this->getSchema($table);
        $payload = $this->buildWritablePayload($schema, $data);
        if (empty($payload)) {
            return 0;
        }
        $this->db->table($table)->insert($payload);
        return (int) $this->db->insertID();
    }

    public function updateRow(string $table, string $pk, string $id, array $data): bool
    {
        if ($pk === '') {
            return false;
        }
        $schema = $this->getSchema($table);
        $payload = $this->buildWritablePayload($schema, $data);
        if (empty($payload)) {
            return false;
        }

        return (bool) $this->db->table($table)
            ->where($pk, $id)
            ->update($payload);
    }

    public function deleteRow(string $table, string $pk, string $id): bool
    {
        if ($pk === '') {
            return false;
        }
        return (bool) $this->db->table($table)->where($pk, $id)->delete();
    }

    private function tableToSlug(string $table): string
    {
        return str_replace(self::TABLE_PREFIX, '', $table);
    }

    private function buildWritablePayload(array $schema, array $data): array
    {
        $payload = [];
        $columns = $schema['columns'] ?? [];
        foreach ($columns as $col) {
            if (empty($col['is_writable'])) {
                continue;
            }
            $name = (string) $col['name'];
            if (!array_key_exists($name, $data)) {
                continue;
            }
            $payload[$name] = $data[$name];
        }
        return $payload;
    }

    private function mapInputType(string $dataType): string
    {
        $map = [
            'tinyint' => 'number',
            'smallint' => 'number',
            'int' => 'number',
            'bigint' => 'number',
            'decimal' => 'number',
            'float' => 'number',
            'double' => 'number',
            'date' => 'date',
            'datetime' => 'datetime-local',
            'timestamp' => 'datetime-local',
            'time' => 'time',
            'text' => 'textarea',
            'longtext' => 'textarea',
            'mediumtext' => 'textarea',
            'tinytext' => 'textarea',
        ];

        return $map[$dataType] ?? 'text';
    }
}
