<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuthRolesAndPermissions extends Migration
{
    public function up()
    {
        // 1. Create auth_roles table
        if (!$this->db->tableExists('auth_roles')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'role_code' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                ],
                'role_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                ],
                'description' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'is_active' => [
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
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('role_code');
            $this->forge->createTable('auth_roles', true);
        }

        // 2. Create auth_role_permissions table
        if (!$this->db->tableExists('auth_role_permissions')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'role_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'permission_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'is_create' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                ],
                'is_read' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 1,
                ],
                'is_update' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                ],
                'is_delete' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
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
            $this->forge->addUniqueKey(['role_id', 'permission_id'], 'uk_role_permission');
            $this->forge->addKey('role_id', false, false, 'idx_role_id');
            $this->forge->addKey('permission_id', false, false, 'idx_permission_id');
            $this->forge->createTable('auth_role_permissions', true);
        }
    }

    public function down()
    {
        $this->forge->dropTable('auth_role_permissions', true);
        $this->forge->dropTable('auth_roles', true);
    }
}
