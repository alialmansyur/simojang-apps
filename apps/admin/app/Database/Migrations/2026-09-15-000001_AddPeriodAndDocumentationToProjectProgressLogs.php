<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPeriodAndDocumentationToProjectProgressLogs extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('data_project_progress_logs')) {
            $fields = [];

            if (!$this->db->fieldExists('start_date', 'data_project_progress_logs')) {
                $fields['start_date'] = [
                    'type'  => 'DATE',
                    'null'  => true,
                    'after' => 'log_date',
                ];
            }

            if (!$this->db->fieldExists('end_date', 'data_project_progress_logs')) {
                $fields['end_date'] = [
                    'type'  => 'DATE',
                    'null'  => true,
                    'after' => 'start_date',
                ];
            }

            if (!$this->db->fieldExists('file_name', 'data_project_progress_logs')) {
                $fields['file_name'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'notes',
                ];
            }

            if (!$this->db->fieldExists('file_original_name', 'data_project_progress_logs')) {
                $fields['file_original_name'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'file_name',
                ];
            }

            if (!$this->db->fieldExists('file_size', 'data_project_progress_logs')) {
                $fields['file_size'] = [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                    'after'      => 'file_original_name',
                ];
            }

            if (!$this->db->fieldExists('file_ext', 'data_project_progress_logs')) {
                $fields['file_ext'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'null'       => true,
                    'after'      => 'file_size',
                ];
            }

            if (!$this->db->fieldExists('file_path', 'data_project_progress_logs')) {
                $fields['file_path'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'file_ext',
                ];
            }

            if (!empty($fields)) {
                $this->forge->addColumn('data_project_progress_logs', $fields);
            }
        }
    }

    public function down()
    {
        if ($this->db->tableExists('data_project_progress_logs')) {
            $columns = ['start_date', 'end_date', 'file_name', 'file_original_name', 'file_size', 'file_ext', 'file_path'];
            foreach ($columns as $column) {
                if ($this->db->fieldExists($column, 'data_project_progress_logs')) {
                    $this->forge->dropColumn('data_project_progress_logs', $column);
                }
            }
        }
    }
}
