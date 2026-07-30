<?php

namespace App\Controllers\Apps;

use App\Controllers\BaseController;
use CodeIgniter\Database\BaseConnection;
use RuntimeException;
use Throwable;

class DatabaseBackupController extends BaseController
{
    private const TARGET_ITEM_TABLE = 'txn_anggaran_realisasi_item';
    private const TARGET_REALISASI_TABLE = 'txn_anggaran_realisasi';

    public function download()
    {
        try {
            $db = db_connect();
            $databaseName = $this->resolveDatabaseName($db);

            if ($databaseName === '') {
                return $this->response->setStatusCode(500)->setBody('Nama database tidak ditemukan.');
            }

            $backupDir = WRITEPATH . 'backups';
            if (!is_dir($backupDir) && !mkdir($backupDir, 0755, true) && !is_dir($backupDir)) {
                return $this->response->setStatusCode(500)->setBody('Folder backup tidak dapat dibuat.');
            }

            $timestamp = date('Ymd_His');
            $fileName = sprintf('%s_backup_%s.sql', $databaseName, $timestamp);
            $filePath = $backupDir . DIRECTORY_SEPARATOR . $fileName;

            $handle = fopen($filePath, 'wb');
            if ($handle === false) {
                return $this->response->setStatusCode(500)->setBody('File backup tidak dapat dibuat.');
            }

            $this->writeHeader($handle, $databaseName);

            foreach ($this->getTableNames($db) as $tableName) {
                $this->writeTableStructure($db, $handle, $tableName);
                $this->writeTableData($db, $handle, $tableName);
            }

            fwrite($handle, PHP_EOL . 'SET FOREIGN_KEY_CHECKS=1;' . PHP_EOL);
            fclose($handle);

            return $this->response
                ->download($filePath, null)
                ->setFileName($fileName);
        } catch (Throwable $e) {
            if (isset($handle) && is_resource($handle)) {
                fclose($handle);
            }

            if (isset($filePath) && is_file($filePath)) {
                @unlink($filePath);
            }

            log_message('error', 'Database backup gagal: {message}', ['message' => $e->getMessage()]);

            return $this->response->setStatusCode(500)->setBody('Backup database gagal dibuat.');
        }
    }

    public function auditSchema090326()
    {
        try {
            $db = db_connect();
            $diff = $this->buildInlineSchemaAudit($db);

            return $this->response->setJSON([
                'status' => true,
                'source' => 'inline_target_schema_090326',
                'summary' => [
                    'database' => $this->resolveDatabaseName($db),
                    'current_table_count' => count($this->getTableNames($db)),
                    'new_tables' => count($diff['new_tables']),
                    'changed_tables' => count($diff['changed_tables']),
                ],
                'diff' => $diff,
                'migration_preview' => $this->buildMigrationPreview(),
            ]);
        } catch (Throwable $e) {
            log_message('error', 'Audit schema gagal: {message}', ['message' => $e->getMessage()]);

            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Audit schema gagal dijalankan.',
            ]);
        }
    }

    public function migrateSchema090326()
    {
        try {
            $db = db_connect();
            $executed = [];
            $skipped = [];

            $createTableSql = $this->getInlineCreateStatement(self::TARGET_ITEM_TABLE);

            if (!$this->tableExists($db, self::TARGET_ITEM_TABLE)) {
                $db->query($createTableSql);
                $executed[] = [
                    'type' => 'create_table',
                    'table' => self::TARGET_ITEM_TABLE,
                    'message' => 'Tabel txn_anggaran_realisasi_item dibuat.',
                ];
            } else {
                $skipped[] = [
                    'type' => 'create_table',
                    'table' => self::TARGET_ITEM_TABLE,
                    'message' => 'Tabel txn_anggaran_realisasi_item sudah ada.',
                ];
            }

            $columnPlans = [
                [
                    'table' => self::TARGET_REALISASI_TABLE,
                    'column' => 'no_spm',
                    'sql' => "ALTER TABLE `txn_anggaran_realisasi` ADD COLUMN `no_spm` varchar(100) DEFAULT NULL AFTER `period_date`",
                ],
                [
                    'table' => self::TARGET_REALISASI_TABLE,
                    'column' => 'no_sp2d',
                    'sql' => "ALTER TABLE `txn_anggaran_realisasi` ADD COLUMN `no_sp2d` varchar(100) DEFAULT NULL AFTER `spm_date`",
                ],
                [
                    'table' => self::TARGET_REALISASI_TABLE,
                    'column' => 'updated_at',
                    'sql' => "ALTER TABLE `txn_anggaran_realisasi` ADD COLUMN `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() AFTER `created_at`",
                ],
            ];

            foreach ($columnPlans as $plan) {
                if ($this->columnExists($db, $plan['table'], $plan['column'])) {
                    $skipped[] = [
                        'type' => 'add_column',
                        'table' => $plan['table'],
                        'column' => $plan['column'],
                        'message' => 'Kolom sudah tersedia.',
                    ];
                    continue;
                }

                $db->query($plan['sql']);
                $executed[] = [
                    'type' => 'add_column',
                    'table' => $plan['table'],
                    'column' => $plan['column'],
                    'message' => 'Kolom berhasil ditambahkan.',
                ];
            }

            $indexPlans = [
                [
                    'table' => self::TARGET_ITEM_TABLE,
                    'index' => 'idx_anggaran_realisasi_item_header',
                    'sql' => "ALTER TABLE `txn_anggaran_realisasi_item` ADD INDEX `idx_anggaran_realisasi_item_header` (`realisasi_id`)",
                ],
                [
                    'table' => self::TARGET_ITEM_TABLE,
                    'index' => 'idx_anggaran_realisasi_item_struktur',
                    'sql' => "ALTER TABLE `txn_anggaran_realisasi_item` ADD INDEX `idx_anggaran_realisasi_item_struktur` (`struktur_id`)",
                ],
            ];

            foreach ($indexPlans as $plan) {
                if ($this->indexExists($db, $plan['table'], $plan['index'])) {
                    $skipped[] = [
                        'type' => 'add_index',
                        'table' => $plan['table'],
                        'index' => $plan['index'],
                        'message' => 'Index sudah tersedia.',
                    ];
                    continue;
                }

                $db->query($plan['sql']);
                $executed[] = [
                    'type' => 'add_index',
                    'table' => $plan['table'],
                    'index' => $plan['index'],
                    'message' => 'Index berhasil ditambahkan.',
                ];
            }

            if ($this->needsStatusEnumMigration($db)) {
                $db->query(
                    "ALTER TABLE `txn_anggaran_realisasi` " .
                    "MODIFY COLUMN `status` ENUM('PENDING','POSTED','DRAFT','CANCEL') DEFAULT 'PENDING'"
                );
                $executed[] = [
                    'type' => 'modify_column',
                    'table' => self::TARGET_REALISASI_TABLE,
                    'column' => 'status',
                    'message' => 'Enum status diselaraskan ke schema lokal.',
                ];
            } else {
                $skipped[] = [
                    'type' => 'modify_column',
                    'table' => self::TARGET_REALISASI_TABLE,
                    'column' => 'status',
                    'message' => 'Enum status sudah sesuai schema lokal.',
                ];
            }

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Migrasi schema non-destruktif selesai.',
                'executed' => $executed,
                'skipped' => $skipped,
            ]);
        } catch (Throwable $e) {
            log_message('error', 'Migrasi schema gagal: {message}', ['message' => $e->getMessage()]);

            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Migrasi schema gagal dijalankan.',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function downloadRebuildScript090326()
    {
        try {
            $tables = $this->getInlineRebuildTableNames(db_connect());
            if ($tables === []) {
                return $this->response->setStatusCode(404)->setBody('Tidak ada tabel yang perlu di-drop/create.');
            }

            $sql = $this->buildRebuildScript($tables);
            $fileName = 'schema_rebuild_090326_' . date('Ymd_His') . '.sql';

            return $this->response
                ->setHeader('Content-Type', 'application/sql; charset=UTF-8')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"')
                ->setBody($sql);
        } catch (Throwable $e) {
            log_message('error', 'Download rebuild script gagal: {message}', ['message' => $e->getMessage()]);

            return $this->response->setStatusCode(500)->setBody('Script rebuild gagal dibuat.');
        }
    }

    public function executeRebuildScript090326()
    {
        try {
            $db = db_connect();
            $tables = $this->getInlineRebuildTableNames($db);

            if ($tables === []) {
                return $this->response->setJSON([
                    'status' => true,
                    'message' => 'Tidak ada tabel yang perlu di-drop/create.',
                    'executed' => [],
                ]);
            }

            $statements = $this->buildRebuildStatements($tables);
            $executed = [];

            foreach ($statements as $statement) {
                $ok = $db->simpleQuery($statement);
                if ($ok === false) {
                    $error = $db->error();
                    throw new RuntimeException(
                        sprintf(
                            'Gagal menjalankan SQL: %s | %s',
                            $statement,
                            trim(($error['message'] ?? 'Unknown database error'))
                        )
                    );
                }

                $executed[] = $statement;
            }

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Execute SQL drop/create selesai dijalankan.',
                'tables' => $tables,
                'database' => $this->resolveDatabaseName($db),
                'executed_count' => count($executed),
                'executed' => $executed,
            ]);
        } catch (Throwable $e) {
            log_message('error', 'Execute rebuild script gagal: {message}', ['message' => $e->getMessage()]);

            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Execute rebuild script gagal dijalankan.',
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function resolveDatabaseName(BaseConnection $db): string
    {
        $databaseName = (string) ($db->database ?? '');
        if ($databaseName !== '') {
            return $databaseName;
        }

        $row = $db->query('SELECT DATABASE() AS db_name')->getRowArray();
        return (string) ($row['db_name'] ?? '');
    }

    private function getTableNames(BaseConnection $db): array
    {
        $tables = [];
        $result = $db->query('SHOW TABLES')->getResultArray();

        foreach ($result as $row) {
            $tableName = reset($row);
            if ($tableName !== false) {
                $tables[] = (string) $tableName;
            }
        }

        return $tables;
    }

    private function writeHeader($handle, string $databaseName): void
    {
        $databaseSql = $this->quoteIdentifier($databaseName);
        $header = [
            '-- --------------------------------------------------------',
            '-- SIMOJANG Database Backup',
            '-- Database : ' . $databaseName,
            '-- Generated: ' . date('Y-m-d H:i:s'),
            '-- --------------------------------------------------------',
            '',
            'SET NAMES utf8mb4;',
            'SET FOREIGN_KEY_CHECKS=0;',
            'CREATE DATABASE IF NOT EXISTS ' . $databaseSql . ' /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;',
            'USE ' . $databaseSql . ';',
            '',
        ];

        fwrite($handle, implode(PHP_EOL, $header));
    }

    private function writeTableStructure(BaseConnection $db, $handle, string $tableName): void
    {
        $tableSql = $this->quoteIdentifier($tableName);
        $row = $db->query("SHOW CREATE TABLE {$tableSql}")->getRowArray();

        if (empty($row)) {
            return;
        }

        $createSql = '';
        foreach ($row as $key => $value) {
            if (stripos((string) $key, 'Create Table') !== false) {
                $createSql = (string) $value;
                break;
            }
        }

        if ($createSql === '' && count($row) > 1) {
            $values = array_values($row);
            $createSql = (string) ($values[1] ?? '');
        }

        if ($createSql === '') {
            return;
        }

        fwrite($handle, PHP_EOL . '-- Structure for table ' . $tableSql . PHP_EOL);
        fwrite($handle, 'DROP TABLE IF EXISTS ' . $tableSql . ';' . PHP_EOL);
        fwrite($handle, $createSql . ';' . PHP_EOL);
    }

    private function writeTableData(BaseConnection $db, $handle, string $tableName): void
    {
        $tableSql = $this->quoteIdentifier($tableName);
        $rows = $db->query("SELECT * FROM {$tableSql}")->getResultArray();

        if ($rows === []) {
            return;
        }

        $columns = array_keys($rows[0]);
        $columnSql = implode(', ', array_map([$this, 'quoteIdentifier'], $columns));

        fwrite($handle, PHP_EOL . '-- Data for table ' . $tableSql . PHP_EOL);

        $batch = [];
        $batchSize = 100;

        foreach ($rows as $row) {
            $values = [];
            foreach ($columns as $column) {
                $values[] = $this->escapeValue($db, $row[$column] ?? null);
            }

            $batch[] = '(' . implode(', ', $values) . ')';

            if (count($batch) >= $batchSize) {
                $this->flushInsertBatch($handle, $tableSql, $columnSql, $batch);
                $batch = [];
            }
        }

        if ($batch !== []) {
            $this->flushInsertBatch($handle, $tableSql, $columnSql, $batch);
        }
    }

    private function flushInsertBatch($handle, string $tableSql, string $columnSql, array $batch): void
    {
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES%s%s;%s',
            $tableSql,
            $columnSql,
            PHP_EOL,
            implode(',' . PHP_EOL, $batch),
            PHP_EOL
        );

        fwrite($handle, $sql);
    }

    private function escapeValue(BaseConnection $db, $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return $db->escape((string) $value);
    }

    private function quoteIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }

    private function parseSchemaDump(string $filePath): array
    {
        if (!is_file($filePath)) {
            throw new RuntimeException('File dump tidak ditemukan: ' . $filePath);
        }

        $sql = (string) file_get_contents($filePath);
        if ($sql === '') {
            throw new RuntimeException('File dump kosong: ' . $filePath);
        }

        preg_match_all('/CREATE TABLE(?: IF NOT EXISTS)? `([^`]+)` \\((.*?)\\) ENGINE=.*?;/si', $sql, $matches, PREG_SET_ORDER);

        $tables = [];
        foreach ($matches as $match) {
            $tableName = $match[1];
            $body = $match[2];

            $columns = [];
            $indexes = [];
            $foreignKeys = [];

            $lines = preg_split('/\r\n|\r|\n/', $body) ?: [];
            foreach ($lines as $line) {
                $normalized = rtrim(trim($line), ',');
                if ($normalized === '') {
                    continue;
                }

                if (preg_match('/^`([^`]+)`\s+(.+)$/', $normalized, $columnMatch) === 1) {
                    $columns[$columnMatch[1]] = trim($columnMatch[2]);
                    continue;
                }

                if (preg_match('/^PRIMARY KEY\s*\((.+)\)$/i', $normalized, $pkMatch) === 1) {
                    $indexes['PRIMARY'] = trim($pkMatch[1]);
                    continue;
                }

                if (preg_match('/^((?:UNIQUE\s+)?)KEY\s+`([^`]+)`\s*\((.+)\)$/i', $normalized, $keyMatch) === 1) {
                    $indexes[$keyMatch[2]] = trim(($keyMatch[1] ?: '') . $keyMatch[3]);
                    continue;
                }

                if (preg_match('/^CONSTRAINT\s+`([^`]+)`\s+FOREIGN KEY\s*\((.+?)\)\s+REFERENCES\s+`([^`]+)`\s*\((.+?)\)(.*)$/i', $normalized, $fkMatch) === 1) {
                    $foreignKeys[$fkMatch[1]] = [
                        'columns' => trim($fkMatch[2]),
                        'reference_table' => $fkMatch[3],
                        'reference_columns' => trim($fkMatch[4]),
                        'suffix' => trim($fkMatch[5]),
                    ];
                }
            }

            $tables[$tableName] = [
                'columns' => $columns,
                'indexes' => $indexes,
                'foreign_keys' => $foreignKeys,
            ];
        }

        return $tables;
    }

    private function buildSchemaDiff(array $serverSchema, array $localSchema): array
    {
        $serverTables = array_keys($serverSchema);
        $localTables = array_keys($localSchema);

        $newTables = array_values(array_diff($localTables, $serverTables));
        sort($newTables);

        $removedTables = array_values(array_diff($serverTables, $localTables));
        sort($removedTables);

        $commonTables = array_values(array_intersect($serverTables, $localTables));
        sort($commonTables);

        $changedTables = [];
        foreach ($commonTables as $tableName) {
            $server = $serverSchema[$tableName];
            $local = $localSchema[$tableName];

            $columnAdd = array_values(array_diff(array_keys($local['columns']), array_keys($server['columns'])));
            $columnDrop = array_values(array_diff(array_keys($server['columns']), array_keys($local['columns'])));
            $indexAdd = array_values(array_diff(array_keys($local['indexes']), array_keys($server['indexes'])));
            $indexDrop = array_values(array_diff(array_keys($server['indexes']), array_keys($local['indexes'])));
            $fkAdd = array_values(array_diff(array_keys($local['foreign_keys']), array_keys($server['foreign_keys'])));
            $fkDrop = array_values(array_diff(array_keys($server['foreign_keys']), array_keys($local['foreign_keys'])));

            $columnModify = [];
            foreach ($server['columns'] as $name => $definition) {
                if (isset($local['columns'][$name]) && $local['columns'][$name] !== $definition) {
                    $columnModify[] = [
                        'column' => $name,
                        'server' => $definition,
                        'local' => $local['columns'][$name],
                    ];
                }
            }

            $indexModify = [];
            foreach ($server['indexes'] as $name => $definition) {
                if (isset($local['indexes'][$name]) && $local['indexes'][$name] !== $definition) {
                    $indexModify[] = [
                        'index' => $name,
                        'server' => $definition,
                        'local' => $local['indexes'][$name],
                    ];
                }
            }

            $fkModify = [];
            foreach ($server['foreign_keys'] as $name => $definition) {
                if (isset($local['foreign_keys'][$name]) && $local['foreign_keys'][$name] !== $definition) {
                    $fkModify[] = [
                        'foreign_key' => $name,
                        'server' => $definition,
                        'local' => $local['foreign_keys'][$name],
                    ];
                }
            }

            if (
                $columnAdd !== [] || $columnDrop !== [] || $columnModify !== [] ||
                $indexAdd !== [] || $indexDrop !== [] || $indexModify !== [] ||
                $fkAdd !== [] || $fkDrop !== [] || $fkModify !== []
            ) {
                $changedTables[] = [
                    'table' => $tableName,
                    'column_add' => $columnAdd,
                    'column_drop' => $columnDrop,
                    'column_modify' => $columnModify,
                    'index_add' => $indexAdd,
                    'index_drop' => $indexDrop,
                    'index_modify' => $indexModify,
                    'foreign_key_add' => $fkAdd,
                    'foreign_key_drop' => $fkDrop,
                    'foreign_key_modify' => $fkModify,
                ];
            }
        }

        return [
            'new_tables' => $newTables,
            'removed_tables' => $removedTables,
            'changed_tables' => $changedTables,
        ];
    }

    private function buildMigrationPreview(): array
    {
        return [
            [
                'type' => 'create_table',
                'table' => self::TARGET_ITEM_TABLE,
                'note' => 'Tambah tabel detail realisasi anggaran.',
            ],
            [
                'type' => 'add_column',
                'table' => self::TARGET_REALISASI_TABLE,
                'column' => 'no_spm',
            ],
            [
                'type' => 'add_column',
                'table' => self::TARGET_REALISASI_TABLE,
                'column' => 'no_sp2d',
            ],
            [
                'type' => 'add_column',
                'table' => self::TARGET_REALISASI_TABLE,
                'column' => 'updated_at',
            ],
            [
                'type' => 'modify_column',
                'table' => self::TARGET_REALISASI_TABLE,
                'column' => 'status',
                'note' => "Ubah enum menjadi ('PENDING','POSTED','DRAFT','CANCEL') dengan default PENDING.",
            ],
        ];
    }

    private function buildInlineSchemaAudit(BaseConnection $db): array
    {
        $newTables = [];
        $changedTables = [];

        if (!$this->tableExists($db, self::TARGET_ITEM_TABLE)) {
            $newTables[] = self::TARGET_ITEM_TABLE;
        }

        $realisasiChanges = [
            'table' => self::TARGET_REALISASI_TABLE,
            'column_add' => [],
            'column_modify' => [],
        ];

        if (!$this->tableExists($db, self::TARGET_REALISASI_TABLE)) {
            $newTables[] = self::TARGET_REALISASI_TABLE;
        } else {
            foreach (['no_spm', 'no_sp2d', 'updated_at'] as $columnName) {
                if (!$this->columnExists($db, self::TARGET_REALISASI_TABLE, $columnName)) {
                    $realisasiChanges['column_add'][] = $columnName;
                }
            }

            if ($this->needsStatusEnumMigration($db)) {
                $realisasiChanges['column_modify'][] = [
                    'column' => 'status',
                    'target' => "ENUM('PENDING','POSTED','DRAFT','CANCEL') DEFAULT 'PENDING'",
                ];
            }
        }

        if ($realisasiChanges['column_add'] !== [] || $realisasiChanges['column_modify'] !== []) {
            $changedTables[] = $realisasiChanges;
        }

        return [
            'new_tables' => $newTables,
            'removed_tables' => [],
            'changed_tables' => $changedTables,
        ];
    }

    private function getInlineCreateStatement(string $tableName): string
    {
        $statements = [
            self::TARGET_REALISASI_TABLE => <<<'SQL'
CREATE TABLE `txn_anggaran_realisasi` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` varchar(50) NOT NULL DEFAULT uuid(),
  `tahun_id` int(11) DEFAULT NULL,
  `struktur_id` int(11) DEFAULT NULL,
  `period_date` date DEFAULT NULL,
  `no_spm` varchar(100) DEFAULT NULL,
  `spm_date` date DEFAULT NULL,
  `no_sp2d` varchar(100) DEFAULT NULL,
  `sp2d_date` date DEFAULT NULL,
  `nominal` decimal(18,2) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `status` enum('PENDING','POSTED','DRAFT','CANCEL') DEFAULT 'PENDING',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `tahun_id` (`tahun_id`),
  KEY `idx_txn_anggaran_realisasi_uid` (`uid`),
  KEY `jenis_belanja_id` (`struktur_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
SQL,
            self::TARGET_ITEM_TABLE => <<<'SQL'
CREATE TABLE `txn_anggaran_realisasi_item` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `realisasi_id` bigint(20) NOT NULL,
  `struktur_id` bigint(20) NOT NULL,
  `nominal` decimal(18,2) NOT NULL DEFAULT 0.00,
  `keterangan` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_anggaran_realisasi_item_header` (`realisasi_id`),
  KEY `idx_anggaran_realisasi_item_struktur` (`struktur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
SQL,
        ];

        if (!isset($statements[$tableName])) {
            throw new RuntimeException('Target create statement tidak tersedia untuk tabel ' . $tableName);
        }

        return $statements[$tableName];
    }

    private function extractCreateTableStatement(string $filePath, string $tableName): string
    {
        if (!is_file($filePath)) {
            throw new RuntimeException('File target schema tidak ditemukan: ' . $filePath);
        }

        $sql = (string) file_get_contents($filePath);
        $pattern = sprintf('/CREATE TABLE(?: IF NOT EXISTS)? `%s` \((.*?)\) ENGINE=.*?;/si', preg_quote($tableName, '/'));
        if (preg_match($pattern, $sql, $matches) !== 1) {
            throw new RuntimeException('CREATE TABLE target tidak ditemukan untuk tabel ' . $tableName);
        }

        preg_match(sprintf('/CREATE TABLE(?: IF NOT EXISTS)? `%s` \((.*?)\) ENGINE=.*?;/si', preg_quote($tableName, '/')), $sql, $fullMatch);
        return (string) ($fullMatch[0] ?? '');
    }

    private function tableExists(BaseConnection $db, string $tableName): bool
    {
        $row = $db->query(
            'SELECT COUNT(*) AS total FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?',
            [$tableName]
        )->getRowArray();

        return (int) ($row['total'] ?? 0) > 0;
    }

    private function columnExists(BaseConnection $db, string $tableName, string $columnName): bool
    {
        $row = $db->query(
            'SELECT COUNT(*) AS total FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?',
            [$tableName, $columnName]
        )->getRowArray();

        return (int) ($row['total'] ?? 0) > 0;
    }

    private function indexExists(BaseConnection $db, string $tableName, string $indexName): bool
    {
        $row = $db->query(
            'SELECT COUNT(*) AS total FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?',
            [$tableName, $indexName]
        )->getRowArray();

        return (int) ($row['total'] ?? 0) > 0;
    }

    private function needsStatusEnumMigration(BaseConnection $db): bool
    {
        $row = $db->query("SHOW COLUMNS FROM `txn_anggaran_realisasi` LIKE 'status'")->getRowArray();
        if (empty($row)) {
            return false;
        }

        $type = strtolower((string) ($row['Type'] ?? ''));
        $default = strtoupper((string) ($row['Default'] ?? ''));
        $expectedType = "enum('pending','posted','draft','cancel')";

        return $type !== $expectedType || $default !== 'PENDING';
    }

    private function getInlineRebuildTableNames(BaseConnection $db): array
    {
        $diff = $this->buildInlineSchemaAudit($db);
        $tables = [];

        foreach ($diff['changed_tables'] as $item) {
            $tables[] = (string) $item['table'];
        }

        foreach ($diff['new_tables'] as $tableName) {
            $tables[] = (string) $tableName;
        }

        $tables = array_values(array_unique($tables));

        usort($tables, static function (string $left, string $right): int {
            if ($left === self::TARGET_ITEM_TABLE) {
                return -1;
            }

            if ($right === self::TARGET_ITEM_TABLE) {
                return 1;
            }

            return strcmp($left, $right);
        });

        return $tables;
    }

    private function buildRebuildScript(array $tables): string
    {
        $dropStatements = [];
        $createStatements = [];

        foreach ($tables as $tableName) {
            $dropStatements[] = '-- DROP TABLE ' . $this->quoteIdentifier($tableName);
            $dropStatements[] = 'DROP TABLE IF EXISTS ' . $this->quoteIdentifier($tableName) . ';';
            $dropStatements[] = '';
        }

        foreach ($tables as $tableName) {
            $createStatements[] = '-- CREATE TABLE ' . $this->quoteIdentifier($tableName);
            $createStatements[] = $this->getInlineCreateStatement($tableName) . ';';
            $createStatements[] = '';
        }

        $statements = [
            '-- --------------------------------------------------------',
            '-- Rebuild Script: inline target schema 090326',
            '-- Generated     : ' . date('Y-m-d H:i:s'),
            '-- WARNING       : script ini destructive untuk tabel terkait',
            '-- --------------------------------------------------------',
            '',
            'SET NAMES utf8mb4;',
            'SET FOREIGN_KEY_CHECKS=0;',
            '',
        ];

        $statements = array_merge($statements, $dropStatements, $createStatements);

        $statements[] = 'SET FOREIGN_KEY_CHECKS=1;';
        $statements[] = '';

        return implode(PHP_EOL, $statements);
    }

    private function buildRebuildStatements(array $tables): array
    {
        $statements = [
            'SET NAMES utf8mb4',
            'SET FOREIGN_KEY_CHECKS=0',
        ];

        foreach ($tables as $tableName) {
            $statements[] = 'DROP TABLE IF EXISTS ' . $this->quoteIdentifier($tableName);
        }

        foreach ($tables as $tableName) {
            $statements[] = $this->getInlineCreateStatement($tableName);
        }

        $statements[] = 'SET FOREIGN_KEY_CHECKS=1';

        return $statements;
    }
}
