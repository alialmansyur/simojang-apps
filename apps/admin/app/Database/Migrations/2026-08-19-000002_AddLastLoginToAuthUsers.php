<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLastLoginToAuthUsers extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('auth_users')) {
            $fields = $this->db->getFieldNames('auth_users');
            if (!in_array('last_login', $fields, true)) {
                $this->forge->addColumn('auth_users', [
                    'last_login' => [
                        'type'    => 'DATETIME',
                        'null'    => true,
                        'default' => null,
                        'after'   => 'is_active',
                    ],
                ]);
            }
        }
    }

    public function down()
    {
        if ($this->db->tableExists('auth_users')) {
            $fields = $this->db->getFieldNames('auth_users');
            if (in_array('last_login', $fields, true)) {
                $this->forge->dropColumn('auth_users', 'last_login');
            }
        }
    }
}
