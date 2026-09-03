<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameKegiatanGaleriAndAddLayananId extends Migration
{
    public function up()
    {
        // 1. Rename table data_kegiatan_galeri to txn_kegiatan_galeri if exists
        if ($this->db->tableExists('data_kegiatan_galeri') && !$this->db->tableExists('txn_kegiatan_galeri')) {
            $this->forge->renameTable('data_kegiatan_galeri', 'txn_kegiatan_galeri');
        }

        // 2. Add layanan_id column if not exists in txn_kegiatan_galeri
        if ($this->db->tableExists('txn_kegiatan_galeri')) {
            if (!$this->db->fieldExists('layanan_id', 'txn_kegiatan_galeri')) {
                $fields = [
                    'layanan_id' => [
                        'type'       => 'INT',
                        'constraint' => 11,
                        'null'       => true,
                        'after'      => 'timkerja_id',
                    ],
                ];
                $this->forge->addColumn('txn_kegiatan_galeri', $fields);
            }
        }
    }

    public function down()
    {
        if ($this->db->tableExists('txn_kegiatan_galeri')) {
            if ($this->db->fieldExists('layanan_id', 'txn_kegiatan_galeri')) {
                $this->forge->dropColumn('txn_kegiatan_galeri', 'layanan_id');
            }

            if (!$this->db->tableExists('data_kegiatan_galeri')) {
                $this->forge->renameTable('txn_kegiatan_galeri', 'data_kegiatan_galeri');
            }
        }
    }
}
