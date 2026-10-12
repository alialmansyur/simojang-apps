<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateIkkTablesSchema extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 1. Pastikan tabel txn_ikk_harian ada
        if (!$db->tableExists('txn_ikk_harian')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'BIGINT',
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'kegiatan_id' => [
                    'type'     => 'BIGINT',
                    'unsigned' => true,
                ],
                'pelaksana_kegiatan_harian' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'period_start_date' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'period_end_date' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'target_mingguan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                ],
                'jumlah_realisasi' => [
                    'type' => 'DOUBLE',
                    'null' => true,
                ],
                'progres_realisasi' => [
                    'type' => 'DOUBLE',
                    'null' => true,
                ],
                'bukti_pekerjaan' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('kegiatan_id', 'data_ikk_kegiatan', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('txn_ikk_harian', true);
        }

        // 2. Ubah tipe data target_volume di data_ikk_kegiatan menjadi VARCHAR(100)
        $fields = [
            'target_volume' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
        ];
        $this->forge->modifyColumn('data_ikk_kegiatan', $fields);

        // 3. Bersihkan duplikasi di data_ikk_pengampu sebelum membuat unique index
        $db->query("
            DELETE p1 FROM data_ikk_pengampu p1
            INNER JOIN data_ikk_pengampu p2 
            WHERE p1.id > p2.id 
              AND p1.tipe_relasi = p2.tipe_relasi 
              AND p1.relasi_id = p2.relasi_id 
              AND p1.timkerja_id = p2.timkerja_id
        ");

        // 4. Bersihkan orphaned records di data_ikk_pengampu
        $db->query("
            DELETE FROM data_ikk_pengampu 
            WHERE tipe_relasi = 'indikator' 
              AND relasi_id NOT IN (SELECT id FROM data_ikk_indikator)
        ");

        $db->query("
            DELETE FROM data_ikk_pengampu 
            WHERE tipe_relasi = 'kegiatan' 
              AND relasi_id NOT IN (SELECT id FROM data_ikk_kegiatan)
        ");

        // 5. Tambahkan composite unique index pada data_ikk_pengampu jika belum ada
        $indexExists = $db->query("
            SHOW INDEX FROM data_ikk_pengampu WHERE Key_name = 'idx_unique_ikk_pengampu'
        ")->getRow();

        if (!$indexExists) {
            $db->query("
                ALTER TABLE data_ikk_pengampu 
                ADD UNIQUE INDEX idx_unique_ikk_pengampu (tipe_relasi, relasi_id, timkerja_id)
            ");
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();

        // Hapus unique index
        $indexExists = $db->query("
            SHOW INDEX FROM data_ikk_pengampu WHERE Key_name = 'idx_unique_ikk_pengampu'
        ")->getRow();

        if ($indexExists) {
            $db->query("ALTER TABLE data_ikk_pengampu DROP INDEX idx_unique_ikk_pengampu");
        }

        // Kembalikan tipe target_volume ke INT
        $fields = [
            'target_volume' => [
                'type' => 'INT',
                'null' => true,
            ],
        ];
        $this->forge->modifyColumn('data_ikk_kegiatan', $fields);
    }
}
