<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class IkkSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        
        // Bersihkan tabel sebelum seeding ulang (opsional)
        $db->table('txn_ikk_harian')->emptyTable();
        $db->table('data_ikk_pengampu')->emptyTable();
        $db->table('data_ikk_kegiatan')->emptyTable();
        $db->table('data_ikk_indikator')->emptyTable();
        $db->table('data_ikk_sasaran')->emptyTable();

        $jsonPath = FCPATH . '../clean_data.json';
        if (!file_exists($jsonPath)) {
            echo "File clean_data.json tidak ditemukan!\n";
            return;
        }

        $jsonData = file_get_contents($jsonPath);
        $sasarans = json_decode($jsonData, true);

        if (!$sasarans) {
            echo "Gagal decode JSON.\n";
            return;
        }

        foreach ($sasarans as $sasaran) {
            // Insert Sasaran
            $db->table('data_ikk_sasaran')->insert([
                'no_urut' => $sasaran['no_urut'],
                'nama_sasaran' => $sasaran['nama_sasaran'],
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $sasaranId = $db->insertID();

            // Insert Indikator
            foreach ($sasaran['indikators'] as $indikator) {
                $db->table('data_ikk_indikator')->insert([
                    'sasaran_id' => $sasaranId,
                    'no_urut' => $indikator['no_urut'],
                    'nama_indikator' => $indikator['nama_indikator'],
                    'satuan' => $indikator['satuan'],
                    'target_volume' => $indikator['target_volume'],
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                $indikatorId = $db->insertID();

                // Mapping Unit Pengampu Indikator
                foreach ($indikator['pengampu_ids'] as $timId) {
                    $db->table('data_ikk_pengampu')->insert([
                        'tipe_relasi' => 'indikator',
                        'relasi_id' => $indikatorId,
                        'timkerja_id' => $timId
                    ]);
                }

                // Insert Kegiatan
                foreach ($indikator['kegiatans'] as $kegiatan) {
                    $db->table('data_ikk_kegiatan')->insert([
                        'indikator_id' => $indikatorId,
                        'no_urut' => $kegiatan['no_urut'],
                        'nama_kegiatan' => $kegiatan['nama_kegiatan'],
                        'satuan' => $kegiatan['satuan'],
                        'target_volume' => $kegiatan['target_volume'],
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                    $kegiatanId = $db->insertID();

                    // Mapping Unit Pengampu Kegiatan
                    foreach ($kegiatan['pengampu_ids'] as $timId) {
                        $db->table('data_ikk_pengampu')->insert([
                            'tipe_relasi' => 'kegiatan',
                            'relasi_id' => $kegiatanId,
                            'timkerja_id' => $timId
                        ]);
                    }
                }
            }
        }
        
        echo "Seeding selesai. Data berhasil diekstrak dan di-mapping.\n";
    }
}
