<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateIkkTables extends Migration
{
    public function up()
    {
        // 1. data_ikk_sasaran
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'no_urut' => [
                'type' => 'INT',
                'null' => true,
            ],
            'nama_sasaran' => [
                'type' => 'TEXT',
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
        $this->forge->createTable('data_ikk_sasaran', true);

        // 2. data_ikk_indikator
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'sasaran_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'no_urut' => [
                'type' => 'INT',
                'null' => true,
            ],
            'nama_indikator' => [
                'type' => 'TEXT',
            ],
            'satuan' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'target_volume' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
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
        $this->forge->addForeignKey('sasaran_id', 'data_ikk_sasaran', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('data_ikk_indikator', true);

        // 3. data_ikk_kegiatan
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'indikator_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'no_urut' => [
                'type' => 'INT',
                'null' => true,
            ],
            'nama_kegiatan' => [
                'type' => 'TEXT',
            ],
            'satuan' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'target_volume' => [
                'type' => 'INT',
                'null'       => true,
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
        $this->forge->addForeignKey('indikator_id', 'data_ikk_indikator', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('data_ikk_kegiatan', true);

        // 4. data_ikk_pengampu (Pivot table)
        // Note: Assuming data_timkerja table has `id` column.
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'tipe_relasi' => [
                'type'       => 'VARCHAR',
                'constraint' => 50, // 'indikator' or 'kegiatan'
            ],
            'relasi_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'timkerja_id' => [
                'type'     => 'BIGINT', // Adjust type if data_timkerja uses different type for ID
                'unsigned' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        // We cannot add simple foreign key for polymorphic `relasi_id`. We'll just manage via app logic.
        $this->forge->createTable('data_ikk_pengampu', true);

        // 5. txn_ikk_harian
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

    public function down()
    {
        $this->forge->dropTable('txn_ikk_harian', true);
        $this->forge->dropTable('data_ikk_pengampu', true);
        $this->forge->dropTable('data_ikk_kegiatan', true);
        $this->forge->dropTable('data_ikk_indikator', true);
        $this->forge->dropTable('data_ikk_sasaran', true);
    }
}
