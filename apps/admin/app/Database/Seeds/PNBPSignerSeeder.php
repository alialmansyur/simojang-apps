<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PNBPSignerSeeder extends Seeder
{
    public function run()
    {
        $db = $this->db;

        if (!$db->tableExists('cfg_pnbp_signers')) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $defaultSigners = [
            [
                'kode'                   => 'kakanreg',
                'role_title'             => 'Kepala Kantor Regional',
                'default_nip'            => '196805121994031001',
                'default_nama'           => 'Dr. H. Heri Purwanto, S.E., M.M.',
                'default_pangkat_gol'    => 'Pembina Utama Muda (IV/c)',
                'default_jabatan'        => 'Kepala Kantor Regional III BKN Bandung',
                'default_signature_path' => null,
                'is_active'              => 1,
                'created_at'             => $now,
                'updated_at'             => $now,
            ],
            [
                'kode'                   => 'ppk',
                'role_title'             => 'Pejabat Pembuat Komitmen (PPK)',
                'default_nip'            => '197509182002121002',
                'default_nama'           => 'Ahmad Fauzi, S.Kom., M.T.I.',
                'default_pangkat_gol'    => 'Pembina (IV/a)',
                'default_jabatan'        => 'Pejabat Pembuat Komitmen Kanreg III BKN',
                'default_signature_path' => null,
                'is_active'              => 1,
                'created_at'             => $now,
                'updated_at'             => $now,
            ],
            [
                'kode'                   => 'bendahara',
                'role_title'             => 'Bendahara Pengeluaran',
                'default_nip'            => '198304152008012003',
                'default_nama'           => 'Siti Rahmawati, S.E., Ak.',
                'default_pangkat_gol'    => 'Penata Tk. I (III/d)',
                'default_jabatan'        => 'Bendahara Pengeluaran Kanreg III BKN',
                'default_signature_path' => null,
                'is_active'              => 1,
                'created_at'             => $now,
                'updated_at'             => $now,
            ],
            [
                'kode'                   => 'koordinator',
                'role_title'             => 'Koordinator Pelaksana Tilok',
                'default_nip'            => null,
                'default_nama'           => null,
                'default_pangkat_gol'    => null,
                'default_jabatan'        => 'Koordinator Titik Lokasi CAT',
                'default_signature_path' => null,
                'is_active'              => 1,
                'created_at'             => $now,
                'updated_at'             => $now,
            ],
        ];

        foreach ($defaultSigners as $signer) {
            $exists = $db->table('cfg_pnbp_signers')->where('kode', $signer['kode'])->countAllResults();
            if ($exists === 0) {
                $db->table('cfg_pnbp_signers')->insert($signer);
            }
        }
    }
}
