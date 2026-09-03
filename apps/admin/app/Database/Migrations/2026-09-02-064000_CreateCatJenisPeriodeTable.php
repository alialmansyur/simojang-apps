<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCatJenisPeriodeTable extends Migration
{
    public function up()
    {
        // 1. Create table txn_cat_jenis_periode if not exists
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => false,
                'auto_increment' => true,
            ],
            'uid' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'jenis_tes_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'null'       => false,
            ],
            'periode' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'nama_jenis' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'created_by' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('uid');
        $this->forge->addKey(['jenis_tes_id', 'periode']);
        $this->forge->createTable('txn_cat_jenis_periode', true);

        // 2. Populate / Migrate existing distinct (jenis_tes_id, periode)
        $db = \Config\Database::connect();
        
        $rowsTilok = $db->query("
            SELECT DISTINCT jenis_tes_id, SUBSTRING(period, 1, 4) as periode 
            FROM txn_cat_tilok 
            WHERE jenis_tes_id IS NOT NULL AND jenis_tes_id > 0 AND period IS NOT NULL AND period != ''
        ")->getResultArray();

        $rowsSeleksi = $db->query("
            SELECT DISTINCT jenis_tes_id, SUBSTRING(periode, 1, 4) as periode 
            FROM txn_cat_seleksi 
            WHERE jenis_tes_id IS NOT NULL AND jenis_tes_id > 0 AND periode IS NOT NULL AND periode != ''
        ")->getResultArray();

        $allCombinations = [];
        foreach (array_merge($rowsTilok, $rowsSeleksi) as $r) {
            $jId = (int)$r['jenis_tes_id'];
            $per = trim((string)$r['periode']);
            if ($jId > 0 && preg_match('/^\d{4}$/', $per)) {
                $allCombinations[$jId . '_' . $per] = [
                    'jenis_tes_id' => $jId,
                    'periode' => $per
                ];
            }
        }

        foreach ($allCombinations as $item) {
            $existing = $db->table('txn_cat_jenis_periode')
                ->where('jenis_tes_id', $item['jenis_tes_id'])
                ->where('periode', $item['periode'])
                ->get()->getRowArray();

            if (!$existing) {
                $master = $db->table('data_support_jenis_tes')
                    ->where('id', $item['jenis_tes_id'])
                    ->get()->getRowArray();

                $namaJenis = $master ? ($master['nama'] ?: $master['kode']) : 'Jenis Tes CAT';

                $db->table('txn_cat_jenis_periode')->insert([
                    'uid' => bin2hex(random_bytes(16)),
                    'jenis_tes_id' => $item['jenis_tes_id'],
                    'periode' => $item['periode'],
                    'nama_jenis' => $namaJenis,
                    'created_by' => 'system_migration',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    public function down()
    {
        $this->forge->dropTable('txn_cat_jenis_periode', true);
    }
}
