<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNominatifFieldsToPnbpPersonel extends Migration
{
    public function up()
    {
        // 1. Tambah kolom pendukung dokumen Nominatif pada txn_pnbp_doc_personel
        $fields = [
            'nik' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
                'after'      => 'pangkat_gol',
            ],
            'bank_nama' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'no_rekening',
            ],
            'status_pegawai' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'bank_nama',
            ],
            'jumlah' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
                'after'      => 'total_biaya',
            ],
            'pajak_persen' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 0.00,
                'after'      => 'jumlah',
            ],
            'pajak_nominal' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
                'after'      => 'pajak_persen',
            ],
            'jumlah_diterima' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
                'after'      => 'pajak_nominal',
            ],
        ];

        $existingCols = $this->db->getFieldNames('txn_pnbp_doc_personel');
        $toAdd = [];
        foreach ($fields as $colName => $colDef) {
            if (!in_array($colName, $existingCols, true)) {
                $toAdd[$colName] = $colDef;
            }
        }

        if (!empty($toAdd)) {
            $this->forge->addColumn('txn_pnbp_doc_personel', $toAdd);
        }

        // 2. Tambah / update default signers untuk format Nominatif di cfg_pnbp_signers
        $signerTable = $this->db->table('cfg_pnbp_signers');
        
        // PPK Nominatif
        $ppkNom = $signerTable->where('kode', 'ppk_nominatif')->get()->getRowArray();
        if (!$ppkNom) {
            $signerTable->insert([
                'kode'                => 'ppk_nominatif',
                'role_title'          => 'Pejabat Pembuat Komitmen (Nominatif)',
                'default_nip'         => '197104241992032001',
                'default_nama'        => 'LESTARI PRASETIJANI, SE, MM',
                'default_pangkat_gol' => 'Pembina Tingkat I (IV/b)',
                'default_jabatan'     => 'Analis Pengelolaan Keuangan APBN Ahli Madya sebagai Pejabat Pembuat Komitmen Pusat Pengembangan Sistem Rekrutmen (PNBP)',
                'is_active'           => 1,
                'created_at'          => date('Y-m-d H:i:s'),
                'updated_at'          => date('Y-m-d H:i:s'),
            ]);
        }

        // Bendahara Nominatif
        $bendNom = $signerTable->where('kode', 'bendahara_nominatif')->get()->getRowArray();
        if (!$bendNom) {
            $signerTable->insert([
                'kode'                => 'bendahara_nominatif',
                'role_title'          => 'Bendahara Pengeluaran (Nominatif)',
                'default_nip'         => '199009062014022001',
                'default_nama'        => 'FITRIANI PANJAITAN, S.Kom.',
                'default_pangkat_gol' => 'Penata Muda Tingkat I (III/b)',
                'default_jabatan'     => 'Bendahara Pengeluaran',
                'is_active'           => 1,
                'created_at'          => date('Y-m-d H:i:s'),
                'updated_at'          => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function down()
    {
        $cols = ['nik', 'bank_nama', 'status_pegawai', 'jumlah', 'pajak_persen', 'pajak_nominal', 'jumlah_diterima'];
        foreach ($cols as $c) {
            if ($this->db->fieldExists($c, 'txn_pnbp_doc_personel')) {
                $this->forge->dropColumn('txn_pnbp_doc_personel', $c);
            }
        }
    }
}
