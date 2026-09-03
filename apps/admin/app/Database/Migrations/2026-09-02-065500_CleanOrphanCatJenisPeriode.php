<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CleanOrphanCatJenisPeriode extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 1. Update tilok with invalid jenis_tes_id = 12 to NON ASN (id = 6)
        $jpNonAsn2026 = $db->table('txn_cat_jenis_periode')
            ->where('jenis_tes_id', 6)
            ->where('periode', '2026')
            ->get()->getRowArray();

        $targetJpId = $jpNonAsn2026 ? $jpNonAsn2026['id'] : null;

        $db->query("
            UPDATE txn_cat_tilok 
            SET jenis_tes_id = 6, jenis_periode_id = " . ($targetJpId ? $targetJpId : "NULL") . " 
            WHERE jenis_tes_id = 12 OR jenis_tes_id NOT IN (SELECT id FROM data_support_jenis_tes)
        ");

        // 2. Delete orphan records in txn_cat_jenis_periode where jenis_tes_id not in data_support_jenis_tes
        $db->query("
            DELETE FROM txn_cat_jenis_periode 
            WHERE jenis_tes_id NOT IN (SELECT id FROM data_support_jenis_tes)
        ");
    }

    public function down()
    {
        // No reverse needed
    }
}
