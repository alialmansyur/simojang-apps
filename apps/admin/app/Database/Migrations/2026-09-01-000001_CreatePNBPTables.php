<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePNBPTables extends Migration
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
        // --------------------------------------------------------
        // 1. Table: cfg_pnbp_signers (Master Pejabat Penandatangan)
        // --------------------------------------------------------
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'kode' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'unique'     => true,
            ],
            'role_title' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'default_nip' => [
                'type'       => 'VARCHAR',
                'constraint' => '30',
                'null'       => true,
            ],
            'default_nama' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'default_pangkat_gol' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'default_jabatan' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'default_signature_path' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('cfg_pnbp_signers', true);

        // --------------------------------------------------------
        // 2. Table: txn_pnbp_documents (Header Dokumen Utama)
        // --------------------------------------------------------
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'uid' => [
                'type'       => 'VARCHAR',
                'constraint' => '64',
                'unique'     => true,
            ],
            'doc_type' => [
                'type'       => 'ENUM',
                'constraint' => ['sp', 'st', 'nominatif', 'kwitansi', 'hadir', 'kwitansi_jamuan', 'surat_jalan', 'faktur', 'hadir_jamuan'],
                'default'    => 'sp',
            ],
            'doc_number' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'doc_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'seleksi_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'tilok_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'instansi_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['draft', 'generated', 'final'],
                'default'    => 'draft',
            ],
            'meta_data' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'pdf_file_path' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'generated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_by' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('doc_type');
        $this->forge->addKey('seleksi_id');
        $this->forge->addKey('tilok_id');
        $this->forge->createTable('txn_pnbp_documents', true);

        // --------------------------------------------------------
        // 3. Table: txn_pnbp_doc_personel (Personel Tim)
        // --------------------------------------------------------
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'document_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'nip' => [
                'type'       => 'VARCHAR',
                'constraint' => '30',
                'null'       => true,
            ],
            'nama' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'pangkat_gol' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'jabatan' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'peran' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'jumlah_hari' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 1,
            ],
            'uang_harian' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'transport' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'total_biaya' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'no_rekening' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('document_id');
        $this->forge->createTable('txn_pnbp_doc_personel', true);

        // --------------------------------------------------------
        // 4. Table: txn_pnbp_doc_items (Item Belanja / Jamuan)
        // --------------------------------------------------------
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'document_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'item_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'spesifikasi' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'quantity' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 1,
            ],
            'satuan' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'default'    => 'Box',
            ],
            'harga_satuan' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'total_harga' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('document_id');
        $this->forge->createTable('txn_pnbp_doc_items', true);

        // --------------------------------------------------------
        // 5. Table: txn_pnbp_doc_attendees (Presensi Sesi Jamuan)
        // --------------------------------------------------------
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'document_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'tanggal_hadir' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'sesi' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
            'nip' => [
                'type'       => 'VARCHAR',
                'constraint' => '30',
                'null'       => true,
            ],
            'nama' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'instansi' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'jabatan' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('document_id');
        $this->forge->createTable('txn_pnbp_doc_attendees', true);

        // --------------------------------------------------------
        // 6. Table: txn_pnbp_doc_signatures (Data Tanda Tangan Digital)
        // --------------------------------------------------------
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'document_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'sign_position' => [
                'type'       => 'ENUM',
                'constraint' => ['left', 'center', 'right'],
                'default'    => 'right',
            ],
            'sign_role' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'sign_title' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'nip' => [
                'type'       => 'VARCHAR',
                'constraint' => '30',
                'null'       => true,
            ],
            'nama' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'pangkat_gol' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'jabatan' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'sign_token' => [
                'type'       => 'VARCHAR',
                'constraint' => '64',
                'unique'     => true,
            ],
            'sign_status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'signed', 'rejected'],
                'default'    => 'pending',
            ],
            'signature_image_path' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'signed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'signer_ip' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
            'signer_user_agent' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'verification_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => '64',
                'null'       => true,
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('document_id');
        $this->forge->createTable('txn_pnbp_doc_signatures', true);

        // --------------------------------------------------------
        // 7. Register Service into data_timkerja_layanan
        // --------------------------------------------------------
        $db = \Config\Database::connect();
        $catService = $db->table('data_timkerja_layanan')->where('url', 'apps-cat')->get()->getRow();
        if ($catService) {
            $exists = $db->table('data_timkerja_layanan')->where('url', 'apps-pnbp')->countAllResults();
            if ($exists === 0) {
                $db->table('data_timkerja_layanan')->insert([
                    'uuid'         => $this->generateUuid(),
                    'timkerja_id'  => $catService->timkerja_id,
                    'nama_layanan' => 'Layanan Dokumen PNBP CAT',
                    'deskripsi'    => 'Pengelolaan dan generate dokumen formal pertanggungjawaban PNBP CAT',
                    'alias'        => 'Dokumen PNBP',
                    'url'          => 'apps-pnbp',
                    'is_group'     => 0,
                    'is_count'     => 0,
                    'is_show'      => 1,
                    'update_at'    => date('Y-m-d H:i:s'),
                    'update_by'    => 'system'
                ]);
            }
        }
    }

    public function down()
    {
        $this->forge->dropTable('txn_pnbp_doc_signatures', true);
        $this->forge->dropTable('txn_pnbp_doc_attendees', true);
        $this->forge->dropTable('txn_pnbp_doc_items', true);
        $this->forge->dropTable('txn_pnbp_doc_personel', true);
        $this->forge->dropTable('txn_pnbp_documents', true);
        $this->forge->dropTable('cfg_pnbp_signers', true);

        $db = \Config\Database::connect();
        $db->table('data_timkerja_layanan')->where('url', 'apps-pnbp')->delete();
    }
}
