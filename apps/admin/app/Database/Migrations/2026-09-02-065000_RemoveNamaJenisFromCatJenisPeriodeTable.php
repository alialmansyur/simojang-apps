<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveNamaJenisFromCatJenisPeriodeTable extends Migration
{
    public function up()
    {
        if ($this->db->fieldExists('nama_jenis', 'txn_cat_jenis_periode')) {
            $this->forge->dropColumn('txn_cat_jenis_periode', 'nama_jenis');
        }
    }

    public function down()
    {
        if (!$this->db->fieldExists('nama_jenis', 'txn_cat_jenis_periode')) {
            $this->forge->addColumn('txn_cat_jenis_periode', [
                'nama_jenis' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
            ]);
        }
    }
}
