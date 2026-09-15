<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTitleToProjectProgressLogs extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('data_project_progress_logs')) {
            if (!$this->db->fieldExists('title', 'data_project_progress_logs')) {
                $this->forge->addColumn('data_project_progress_logs', [
                    'title' => [
                        'type'       => 'VARCHAR',
                        'constraint' => 255,
                        'null'       => true,
                        'after'      => 'log_date',
                    ],
                ]);
            }
        }
    }

    public function down()
    {
        if ($this->db->tableExists('data_project_progress_logs')) {
            if ($this->db->fieldExists('title', 'data_project_progress_logs')) {
                $this->forge->dropColumn('data_project_progress_logs', 'title');
            }
        }
    }
}
