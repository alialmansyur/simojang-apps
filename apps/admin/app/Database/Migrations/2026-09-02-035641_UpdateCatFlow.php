<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateCatFlow extends Migration
{
    public function up()
    {
        // 1. Add seleksi_id and jenis_tes_id to txn_cat_hasil
        $this->forge->addColumn('txn_cat_hasil', [
            'seleksi_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'after' => 'tilok_id',
            ],
            'jenis_tes_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'after' => 'seleksi_id',
            ],
        ]);

        // 2. Make seleksi_id in txn_cat_tilok nullable
        $this->forge->modifyColumn('txn_cat_tilok', [
            'seleksi_id' => [
                'name' => 'seleksi_id',
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
        ]);

        // 3. Create table txn_cat_tilok_instansi
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'tilok_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'instansi_id' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('txn_cat_tilok_instansi', true);

        // 4. Backfill data
        $db = \Config\Database::connect();
        
        // Update txn_cat_hasil with seleksi_id and jenis_tes_id from txn_cat_tilok
        $db->query("
            UPDATE txn_cat_hasil h
            JOIN txn_cat_tilok t ON h.tilok_id = t.id
            SET h.seleksi_id = t.seleksi_id, h.jenis_tes_id = t.jenis_tes_id
        ");

        // Backfill txn_cat_tilok_instansi from distinct tilok_id and instansi_id in txn_cat_hasil
        $db->query("
            INSERT INTO txn_cat_tilok_instansi (tilok_id, instansi_id, created_at)
            SELECT tilok_id, instansi_id, MIN(created_at)
            FROM txn_cat_hasil
            WHERE instansi_id IS NOT NULL AND instansi_id != ''
            GROUP BY tilok_id, instansi_id
        ");
    }

    public function down()
    {
        $this->forge->dropTable('txn_cat_tilok_instansi', true);

        $this->forge->dropColumn('txn_cat_hasil', 'seleksi_id');
        $this->forge->dropColumn('txn_cat_hasil', 'jenis_tes_id');

        // Note: reverting seleksi_id in txn_cat_tilok to NOT NULL might fail if there are nulls.
    }
}
