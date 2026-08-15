<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGambarToBarangHilang extends Migration
{
    public function up()
    {
        $fields = [
            'gambar' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'keterangan'
            ],
        ];
        $this->forge->addColumn('txn_barang_hilang', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('txn_barang_hilang', 'gambar');
    }
}
