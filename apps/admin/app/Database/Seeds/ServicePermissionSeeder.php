<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ServicePermissionSeeder extends Seeder
{
    /**
     * Mapping data_pegawai_unit_kerja.id => array of data_timkerja.id
     */
    public static array $unitKerjaToTimKerjaMap = [
        20 => [1],             // Tim Kerja Pengangkatan dan Mutasi -> TK 1
        23 => [2],             // Tim Kerja Status dan Pemberhentian -> TK 2
        19 => [3],             // Tim Kerja Pembinaan Manajemen ASN -> TK 3
        21 => [4],             // Tim Kerja Pengawasan dan Pengendalian -> TK 4
        22 => [5],             // Tim Kerja Sistem Informasi dan Digitalisasi -> TK 5
        13 => [6],             // Bagian Tata Usaha -> TK 6
        16 => [6],             // Subbagian Kepegawaian -> TK 6
        17 => [6],             // Subbagian Perencanaan dan Keuangan -> TK 6
        18 => [6],             // Subbagian Umum -> TK 6
        15 => [1, 2, 3, 4, 5, 6], // Kantor Regional III BKN (Pimpinan/Kanreg) -> All TK
        14 => [6],             // CPNS -> TK 6
        24 => [5, 6],          // UPSCPKP ASN Serang -> TK 5 & 6
    ];

    public function run()
    {
        $db = $this->db;

        if (!$db->tableExists('auth_service_permission')) {
            return;
        }

        // 1. Ambil seluruh data pegawai
        $pegawaiList = $db->table('data_pegawai')
            ->select('id, nip, nama, unit_kerja_id')
            ->where('COALESCE(nip, \'\') <>', '')
            ->get()
            ->getResultArray();

        if (empty($pegawaiList)) {
            return;
        }

        // 2. Ambil seluruh layanan aktif
        $allServices = $db->table('data_timkerja_layanan')
            ->select('id, timkerja_id, nama_layanan, url, is_show')
            ->where('is_show', 1)
            ->get()
            ->getResultArray();

        $servicesByTimKerja = [];
        $allServiceIds = [];
        foreach ($allServices as $s) {
            $sid = (int) $s['id'];
            $tkId = (int) $s['timkerja_id'];
            $servicesByTimKerja[$tkId][] = $sid;
            $allServiceIds[] = $sid;
        }

        // 3. Ambil role user dari auth_users
        $users = $db->table('auth_users')
            ->select('username, role')
            ->get()
            ->getResultArray();

        $userRoles = [];
        foreach ($users as $u) {
            $userRoles[trim($u['username'])] = strtoupper(trim((string) $u['role']));
        }

        // 4. Bangun data permission untuk setiap pegawai
        $now = date('Y-m-d H:i:s');
        $batch = [];

        foreach ($pegawaiList as $p) {
            $nip = trim((string) $p['nip']);
            $pegawaiId = (int) $p['id'];
            $unitKerjaRaw = trim((string) ($p['unit_kerja_id'] ?? ''));
            $role = $userRoles[$nip] ?? 'USR';

            $targetServiceIds = [];

            // Jika Administrator atau Pimpinan Kanreg -> Akses semua layanan
            if ($role === 'ADM' || $unitKerjaRaw === '15') {
                $targetServiceIds = $allServiceIds;
            } else {
                // Parsing unit_kerja_id (mendukung format koma "22,21")
                $unitIds = array_map('intval', array_filter(explode(',', $unitKerjaRaw), 'is_numeric'));

                $matchedTimKerjaIds = [];
                foreach ($unitIds as $ukId) {
                    if (isset(self::$unitKerjaToTimKerjaMap[$ukId])) {
                        foreach (self::$unitKerjaToTimKerjaMap[$ukId] as $tkId) {
                            $matchedTimKerjaIds[$tkId] = true;
                        }
                    }
                }

                // Kumpulkan semua service id di tim kerja yang cocok
                foreach (array_keys($matchedTimKerjaIds) as $tkId) {
                    if (!empty($servicesByTimKerja[$tkId])) {
                        foreach ($servicesByTimKerja[$tkId] as $sid) {
                            $targetServiceIds[$sid] = true;
                        }
                    }
                }

                $targetServiceIds = array_keys($targetServiceIds);
            }

            foreach ($targetServiceIds as $serviceId) {
                $batch[] = [
                    'pegawai_id' => $pegawaiId,
                    'nip'        => $nip,
                    'layanan_id' => (int) $serviceId,
                    'is_allowed' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'created_by' => null,
                    'updated_by' => null,
                ];
            }
        }

        // 5. Simpan ke database dengan chunking dan INSERT IGNORE / ON DUPLICATE KEY UPDATE
        if (!empty($batch)) {
            // Bersihkan data lama jika diinginkan atau insert batch ignore
            $chunks = array_chunk($batch, 200);
            foreach ($chunks as $chunk) {
                $placeholders = [];
                $values = [];
                foreach ($chunk as $row) {
                    $placeholders[] = '(?, ?, ?, ?, ?, ?, ?, ?)';
                    $values[] = $row['pegawai_id'];
                    $values[] = $row['nip'];
                    $values[] = $row['layanan_id'];
                    $values[] = $row['is_allowed'];
                    $values[] = $row['created_at'];
                    $values[] = $row['updated_at'];
                    $values[] = $row['created_by'];
                    $values[] = $row['updated_by'];
                }

                $sql = 'INSERT INTO auth_service_permission (pegawai_id, nip, layanan_id, is_allowed, created_at, updated_at, created_by, updated_by) VALUES '
                    . implode(', ', $placeholders)
                    . ' ON DUPLICATE KEY UPDATE is_allowed = VALUES(is_allowed), updated_at = VALUES(updated_at)';

                $db->query($sql, $values);
            }
        }
    }
}
