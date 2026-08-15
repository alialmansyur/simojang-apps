<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TxnBarangHilang extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nama_barang' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'tanggal_ditemukan' => [
                'type' => 'DATE',
            ],
            'lokasi_ditemukan' => [
                'type' => 'TEXT',
            ],
            'status_penyerahan' => [
                'type'       => 'ENUM',
                'constraint' => ['Belum Diserahkan', 'Diserahkan'],
                'default'    => 'Belum Diserahkan',
            ],
            'tanggal_diserahkan' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'penerima' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'keterangan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_by' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('txn_barang_hilang');
    }

    public function down()
    {
        $this->forge->dropTable('txn_barang_hilang');
    }
}
