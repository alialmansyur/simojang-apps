<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddJenisPeriodeIdToCatTilokTable extends Migration
{
    public function up()
    {
        $fields = [
            'jenis_periode_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'null'       => true,
                'after'      => 'seleksi_id',
            ],
        ];

        if (!$this->db->fieldExists('jenis_periode_id', 'txn_cat_tilok')) {
            $this->forge->addColumn('txn_cat_tilok', $fields);
        }

        // Backfill jenis_periode_id on txn_cat_tilok
        $db = \Config\Database::connect();
        $db->query("
            UPDATE txn_cat_tilok t
            INNER JOIN txn_cat_jenis_periode jp 
                ON jp.jenis_tes_id = t.jenis_tes_id 
                AND jp.periode = SUBSTRING(t.period, 1, 4)
            SET t.jenis_periode_id = jp.id
            WHERE t.jenis_periode_id IS NULL OR t.jenis_periode_id = 0
        ");
    }

    public function down()
    {
        if ($this->db->fieldExists('jenis_periode_id', 'txn_cat_tilok')) {
            $this->forge->dropColumn('txn_cat_tilok', 'jenis_periode_id');
        }
    }
}
