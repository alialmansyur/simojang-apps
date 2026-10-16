<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdatePembinaanDisiplinBudayaCitraSchema extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 1. Create txn_pembinaan_disiplin_budaya_citra if not exists
        $db->query("
            CREATE TABLE IF NOT EXISTS `txn_pembinaan_disiplin_budaya_citra` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `layanan_id` bigint(20) DEFAULT NULL,
              `instansi_id` varchar(12) DEFAULT NULL,
              `kategori_id` bigint(20) unsigned DEFAULT NULL,
              `period_year` smallint(6) DEFAULT NULL,
              `period_date` date DEFAULT NULL,
              `source_konsultasi` varchar(30) DEFAULT NULL,
              `tempat_kegiatan` varchar(255) DEFAULT NULL,
              `judul_kegiatan` varchar(255) DEFAULT NULL,
              `no_surat_kegiatan` varchar(120) DEFAULT NULL,
              `catatan` text DEFAULT NULL,
              `created_by` varchar(30) DEFAULT NULL,
              `updated_by` varchar(30) DEFAULT NULL,
              `created_at` timestamp NULL DEFAULT NULL,
              `updated_at` timestamp NULL DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `idx_txn_pembinaan_disiplin_budaya_citra_instansi_id` (`instansi_id`),
              KEY `idx_txn_pembinaan_disiplin_budaya_citra_kategori_id` (`kategori_id`),
              KEY `idx_txn_pembinaan_disiplin_budaya_citra_layanan_id` (`layanan_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");

        // 2. Create txn_pembinaan_disiplin_budaya_citra_pegawai if not exists
        $db->query("
            CREATE TABLE IF NOT EXISTS `txn_pembinaan_disiplin_budaya_citra_pegawai` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `txn_id` bigint(20) unsigned NOT NULL,
              `pegawai_id` bigint(20) DEFAULT NULL,
              `pegawai_nip` varchar(30) DEFAULT NULL,
              `pegawai_nama` varchar(200) DEFAULT NULL,
              `created_at` timestamp NULL DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `idx_tpdbc_pegawai_txn` (`txn_id`),
              KEY `idx_txn_pembinaan_disiplin_budaya_citra_pegawai_pegawai_id` (`pegawai_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");

        // 3. Update existing categories in data_support_pembinaan_disiplin_kategori
        $db->table('data_support_pembinaan_disiplin_kategori')
            ->where('id', 1)
            ->update([
                'nama' => 'Asistensi Disiplin, Budaya Kerja & Citra Institusi',
                'jenis_layanan' => 'ASISTENSI',
                'sort_order' => 1,
                'is_active' => 1,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        $db->table('data_support_pembinaan_disiplin_kategori')
            ->where('id', 2)
            ->update([
                'nama' => 'Konsultasi/Pendampingan Disiplin, Budaya Kerja & Citra Institusi',
                'jenis_layanan' => 'KONSULTASI',
                'sort_order' => 2,
                'is_active' => 1,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        $db->table('data_support_pembinaan_disiplin_kategori')
            ->where('id', 3)
            ->update([
                'code' => 'REGULASI_BUDAYA_KERJA_BERAKHLAK',
                'nama' => 'Regulasi budaya kerja berakhlak',
                'jenis_layanan' => 'REGULASI',
                'deskripsi' => 'Regulasi budaya kerja berakhlak',
                'sort_order' => 3,
                'is_active' => 1,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        // 4. Insert or update category 4: Regulasi Disiplin PPPK
        $cat4 = $db->table('data_support_pembinaan_disiplin_kategori')
            ->where('code', 'REGULASI_DISIPLIN_PPPK')
            ->get()
            ->getRowArray();

        if ($cat4) {
            $db->table('data_support_pembinaan_disiplin_kategori')
                ->where('id', $cat4['id'])
                ->update([
                    'nama' => 'Regulasi Disiplin PPPK',
                    'jenis_layanan' => 'REGULASI',
                    'deskripsi' => 'Regulasi disiplin PPPK',
                    'sort_order' => 4,
                    'is_active' => 1,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
        } else {
            $db->table('data_support_pembinaan_disiplin_kategori')->insert([
                'code' => 'REGULASI_DISIPLIN_PPPK',
                'nama' => 'Regulasi Disiplin PPPK',
                'jenis_layanan' => 'REGULASI',
                'deskripsi' => 'Regulasi disiplin PPPK',
                'sort_order' => 4,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function down()
    {
        // No down operation needed
    }
}
