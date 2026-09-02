<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePNBPDocTypesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'doc_type' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'unique'     => true,
            ],
            'number' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 1,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'short_title' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'category' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'category_key' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'badge_class' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'default'    => 'badge-primary',
            ],
            'color' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'default'    => '#059669',
            ],
            'bg_light' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'default'    => '#ecfdf5',
            ],
            'icon_svg' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'is_status' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
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
        $this->forge->addKey('number');
        $this->forge->addKey('is_status');
        $this->forge->createTable('data_pnbp_doc_types', true);

        // Seed initial 9 PNBP doc types
        $now = date('Y-m-d H:i:s');
        $initialDocTypes = [
            [
                'doc_type'     => 'sp',
                'number'       => 1,
                'title'        => 'Surat Perintah (SP)',
                'short_title'  => 'SP',
                'category'     => 'Kepegawaian & Tim',
                'category_key' => 'personel',
                'description'  => 'Dokumen perintah resmi dari Kepala Kantor Regional untuk penugasan tim fasilitasi CAT.',
                'badge_class'  => 'badge-primary',
                'color'        => '#2563eb',
                'bg_light'     => '#eff6ff',
                'icon_svg'     => '<svg viewBox="0 0 24 24" aria-hidden="true" style="width: 24px; height: 24px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>',
                'is_status'    => 0,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'doc_type'     => 'st',
                'number'       => 2,
                'title'        => 'Surat Tugas (ST)',
                'short_title'  => 'ST',
                'category'     => 'Kepegawaian & Tim',
                'category_key' => 'personel',
                'description'  => 'Surat tugas kedinasan personil pelaksana di titik lokasi ujian seleksi CAT.',
                'badge_class'  => 'badge-info',
                'color'        => '#0891b2',
                'bg_light'     => '#ecfeff',
                'icon_svg'     => '<svg viewBox="0 0 24 24" aria-hidden="true" style="width: 24px; height: 24px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect><path d="M9 14l2 2 4-4"></path></svg>',
                'is_status'    => 0,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'doc_type'     => 'nominatif',
                'number'       => 3,
                'title'        => 'Daftar Nominatif',
                'short_title'  => 'Nominatif',
                'category'     => 'Keuangan & Biaya',
                'category_key' => 'personel',
                'description'  => 'Daftar rincian biaya uang harian, uang transport, dan rekening personil pelaksana.',
                'badge_class'  => 'badge-success',
                'color'        => '#059669',
                'bg_light'     => '#ecfdf5',
                'icon_svg'     => '<svg viewBox="0 0 24 24" aria-hidden="true" style="width: 24px; height: 24px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>',
                'is_status'    => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'doc_type'     => 'kwitansi',
                'number'       => 4,
                'title'        => 'Kwitansi Perjalanan Dinas',
                'short_title'  => 'Kwitansi Perjadin',
                'category'     => 'Keuangan & Biaya',
                'category_key' => 'personel',
                'description'  => 'Bukti pembayaran dan tanda terima sah pengeluaran honor/perjadin tim.',
                'badge_class'  => 'badge-warning',
                'color'        => '#d97706',
                'bg_light'     => '#fffbeb',
                'icon_svg'     => '<svg viewBox="0 0 24 24" aria-hidden="true" style="width: 24px; height: 24px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>',
                'is_status'    => 0,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'doc_type'     => 'hadir',
                'number'       => 5,
                'title'        => 'Daftar Hadir Petugas',
                'short_title'  => 'Daftar Hadir',
                'category'     => 'Kepegawaian & Tim',
                'category_key' => 'personel',
                'description'  => 'Presensi dan bukti kehadiran fisik tim pelaksana selama kegiatan di titik lokasi.',
                'badge_class'  => 'badge-secondary',
                'color'        => '#4f46e5',
                'bg_light'     => '#eef2ff',
                'icon_svg'     => '<svg viewBox="0 0 24 24" aria-hidden="true" style="width: 24px; height: 24px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;"><path d="M9 11l3 3L22 4"></path><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>',
                'is_status'    => 0,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'doc_type'     => 'kwitansi_jamuan',
                'number'       => 6,
                'title'        => 'Kwitansi Jamuan',
                'short_title'  => 'Kwitansi Jamuan',
                'category'     => 'Konsumsi & Katering',
                'category_key' => 'jamuan',
                'description'  => 'Bukti pembayaran resmi kepada penyedia katering untuk belanja jamuan/konsumsi kegiatan.',
                'badge_class'  => 'badge-warning',
                'color'        => '#ea580c',
                'bg_light'     => '#fff7ed',
                'icon_svg'     => '<svg viewBox="0 0 24 24" aria-hidden="true" style="width: 24px; height: 24px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;"><path d="M18 8h1a4 4 0 0 1 0 8h-1"></path><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path><line x1="6" y1="1" x2="6" y2="4"></line><line x1="10" y1="1" x2="10" y2="4"></line><line x1="14" y1="1" x2="14" y2="4"></line></svg>',
                'is_status'    => 0,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'doc_type'     => 'surat_jalan',
                'number'       => 7,
                'title'        => 'Surat Jalan Jamuan',
                'short_title'  => 'Surat Jalan',
                'category'     => 'Konsumsi & Katering',
                'category_key' => 'jamuan',
                'description'  => 'Berita acara bukti pengiriman barang konsumsi/snack box dari rekanan ke tilok.',
                'badge_class'  => 'badge-info',
                'color'        => '#0284c7',
                'bg_light'     => '#f0f9ff',
                'icon_svg'     => '<svg viewBox="0 0 24 24" aria-hidden="true" style="width: 24px; height: 24px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>',
                'is_status'    => 0,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'doc_type'     => 'faktur',
                'number'       => 8,
                'title'        => 'Faktur Jamuan (Invoice)',
                'short_title'  => 'Faktur Jamuan',
                'category'     => 'Konsumsi & Katering',
                'category_key' => 'jamuan',
                'description'  => 'Faktur tagihan belanja makanan/snack dari penyedia katering dengan rincian menu.',
                'badge_class'  => 'badge-primary',
                'color'        => '#7c3aed',
                'bg_light'     => '#f5f3ff',
                'icon_svg'     => '<svg viewBox="0 0 24 24" aria-hidden="true" style="width: 24px; height: 24px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="12" y1="18" x2="12" y2="12"></line><line x1="9" y1="15" x2="15" y2="15"></line></svg>',
                'is_status'    => 0,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'doc_type'     => 'hadir_jamuan',
                'number'       => 9,
                'title'        => 'Daftar Hadir Jamuan',
                'short_title'  => 'Hadir Jamuan',
                'category'     => 'Konsumsi & Katering',
                'category_key' => 'jamuan',
                'description'  => 'Daftar bukti serah terima dan distribusi konsumsi makanan/minuman kepada petugas.',
                'badge_class'  => 'badge-secondary',
                'color'        => '#db2777',
                'bg_light'     => '#fdf2f8',
                'icon_svg'     => '<svg viewBox="0 0 24 24" aria-hidden="true" style="width: 24px; height: 24px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;"><path d="M18 8h1a4 4 0 0 1 0 8h-1"></path><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path><line x1="9" y1="12" x2="9" y2="16"></line></svg>',
                'is_status'    => 0,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
        ];

        $db = \Config\Database::connect();
        foreach ($initialDocTypes as $row) {
            $exists = $db->table('data_pnbp_doc_types')->where('doc_type', $row['doc_type'])->countAllResults();
            if ($exists === 0) {
                $db->table('data_pnbp_doc_types')->insert($row);
            }
        }
    }

    public function down()
    {
        $this->forge->dropTable('data_pnbp_doc_types', true);
    }
}
