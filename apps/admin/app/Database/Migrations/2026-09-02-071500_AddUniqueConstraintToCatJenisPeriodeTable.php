<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUniqueConstraintToCatJenisPeriodeTable extends Migration
{
    public function up()
    {
        // Drop non-unique index if exists, then add unique constraint
        $this->db->query("ALTER TABLE `txn_cat_jenis_periode` ADD UNIQUE KEY `uq_cat_jenis_periode` (`jenis_tes_id`, `periode`)");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE `txn_cat_jenis_periode` DROP INDEX `uq_cat_jenis_periode`");
    }
}
