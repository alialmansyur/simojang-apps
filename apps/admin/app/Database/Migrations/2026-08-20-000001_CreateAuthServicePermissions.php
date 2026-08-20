<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuthServicePermissions extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('auth_service_permission')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'pegawai_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'nip' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                ],
                'layanan_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'is_allowed' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 1,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'created_by' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'updated_by' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey(['nip', 'layanan_id'], 'uk_nip_layanan');
            $this->forge->addKey('pegawai_id', false, false, 'idx_pegawai_id');
            $this->forge->addKey('layanan_id', false, false, 'idx_layanan_id');
            $this->forge->addKey('nip', false, false, 'idx_nip');
            $this->forge->createTable('auth_service_permission', true);
        }
    }

    public function down()
    {
        $this->forge->dropTable('auth_service_permission', true);
    }
}
