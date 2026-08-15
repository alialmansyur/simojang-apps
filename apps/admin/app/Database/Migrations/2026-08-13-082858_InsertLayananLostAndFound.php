<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class InsertLayananLostAndFound extends Migration
{
    private function generateUuid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    public function up()
    {
        $db = \Config\Database::connect();
        
        // Find timkerja ID
        $timkerja = $db->table('data_timkerja')
            ->where('uid', '68f31eef-ec3d-11f0-ad8c-58020559d237')
            ->get()
            ->getRow();

        if ($timkerja) {
            $data = [
                'uuid'         => $this->generateUuid(),
                'timkerja_id'  => $timkerja->id,
                'nama_layanan' => 'Informasi Barang Hilang & Ditemukan',
                'deskripsi'    => 'Pusat informasi barang hilang dan ditemukan',
                'alias'        => 'Lost and Found',
                'url'          => 'apps-lost-and-found',
                'is_group'     => 0,
                'is_count'     => 0,
                'is_show'      => 1,
                'update_at'    => date('Y-m-d H:i:s'),
                'update_by'    => 'system'
            ];
            
            // Avoid duplicate insertion
            $exists = $db->table('data_timkerja_layanan')
                ->where('url', 'apps-lost-and-found')
                ->where('timkerja_id', $timkerja->id)
                ->countAllResults();

            if ($exists === 0) {
                $db->table('data_timkerja_layanan')->insert($data);
            }
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $db->table('data_timkerja_layanan')
            ->where('url', 'apps-lost-and-found')
            ->delete();
    }
}
