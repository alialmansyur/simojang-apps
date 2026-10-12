<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPeriodMappingToTxnIkkHarian extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        if ($db->tableExists('txn_ikk_harian')) {
            $fieldsToAdd = [];

            if (!$db->fieldExists('tahun', 'txn_ikk_harian')) {
                $fieldsToAdd['tahun'] = [
                    'type'     => 'SMALLINT',
                    'null'     => true,
                    'after'    => 'period_end_date',
                ];
            }

            if (!$db->fieldExists('triwulan', 'txn_ikk_harian')) {
                $fieldsToAdd['triwulan'] = [
                    'type'     => 'TINYINT',
                    'null'     => true,
                    'after'    => 'tahun',
                ];
            }

            if (!$db->fieldExists('bulan', 'txn_ikk_harian')) {
                $fieldsToAdd['bulan'] = [
                    'type'     => 'TINYINT',
                    'null'     => true,
                    'after'    => 'triwulan',
                ];
            }

            if (!$db->fieldExists('periode_key', 'txn_ikk_harian')) {
                $fieldsToAdd['periode_key'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'null'       => true,
                    'after'      => 'bulan',
                ];
            }

            if (!empty($fieldsToAdd)) {
                $this->forge->addColumn('txn_ikk_harian', $fieldsToAdd);
            }

            // Tambahkan index jika belum ada
            $indexes = $db->query("SHOW INDEX FROM txn_ikk_harian")->getResultArray();
            $indexNames = array_column($indexes, 'Key_name');

            if (!in_array('idx_ikk_tahun_triwulan_kegiatan', $indexNames)) {
                $db->query("ALTER TABLE txn_ikk_harian ADD INDEX idx_ikk_tahun_triwulan_kegiatan (tahun, triwulan, kegiatan_id)");
            }

            if (!in_array('idx_ikk_periode_key', $indexNames)) {
                $db->query("ALTER TABLE txn_ikk_harian ADD INDEX idx_ikk_periode_key (periode_key)");
            }

            // Backfill data yang ada
            $db->query("
                UPDATE txn_ikk_harian
                SET 
                    tahun = COALESCE(YEAR(period_end_date), YEAR(period_start_date), YEAR(created_at), YEAR(CURDATE())),
                    bulan = COALESCE(MONTH(period_end_date), MONTH(period_start_date), MONTH(created_at), MONTH(CURDATE())),
                    triwulan = COALESCE(QUARTER(period_end_date), QUARTER(period_start_date), QUARTER(created_at), QUARTER(CURDATE())),
                    periode_key = CONCAT(
                        COALESCE(YEAR(period_end_date), YEAR(period_start_date), YEAR(created_at), YEAR(CURDATE())),
                        '-TW',
                        COALESCE(QUARTER(period_end_date), QUARTER(period_start_date), QUARTER(created_at), QUARTER(CURDATE()))
                    )
                WHERE tahun IS NULL OR periode_key IS NULL
            ");
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        if ($db->tableExists('txn_ikk_harian')) {
            $colsToDrop = [];
            foreach (['periode_key', 'bulan', 'triwulan', 'tahun'] as $col) {
                if ($db->fieldExists($col, 'txn_ikk_harian')) {
                    $colsToDrop[] = $col;
                }
            }
            if (!empty($colsToDrop)) {
                $this->forge->dropColumn('txn_ikk_harian', $colsToDrop);
            }
        }
    }
}
