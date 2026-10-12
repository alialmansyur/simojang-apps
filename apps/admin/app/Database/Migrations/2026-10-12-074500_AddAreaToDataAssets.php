<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAreaToDataAssets extends Migration
{
    public function up()
    {
        $fields = [
            'area' => [
                'type'       => 'ENUM',
                'constraint' => ['Kantor Regional III BKN', 'UPT Bandung', 'UPT Serang'],
                'default'    => 'Kantor Regional III BKN',
                'null'       => false,
                'after'      => 'lokasi',
            ],
        ];

        $this->forge->addColumn('data_assets', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('data_assets', 'area');
    }
}
