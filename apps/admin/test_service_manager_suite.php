<?php

/**
 * SIMOJANG Service Manager & Permission Integration Test Suite
 */

ob_start();

require_once __DIR__ . '/vendor/codeigniter4/framework/system/Test/bootstrap.php';

use App\Models\Auth\ServicePermissionModel;
use App\Models\Auth\AccessControlModel;
use App\Models\Apps\AppsModel;
use App\Models\Apps\SettingManagerModel;

class ServiceManagerTestSuite
{
    private ServicePermissionModel $spm;
    private AccessControlModel $acl;
    private AppsModel $apps;
    private SettingManagerModel $smm;
    private $db;

    private int $passed = 0;
    private int $failed = 0;

    public function __construct()
    {
        $this->db = \Config\Database::connect('default');
        $this->spm = new ServicePermissionModel($this->db);
        $this->acl = new AccessControlModel($this->db);
        $this->apps = new AppsModel($this->db);
        $this->smm = new SettingManagerModel($this->db);
    }

    public function run()
    {
        ob_end_clean();
        echo "========================================================\n";
        echo "  SIMOJANG Service Manager & Permission Test Suite\n";
        echo "========================================================\n\n";

        $this->testDatabaseTableExists();
        $this->testPegawaiListAndDetail();
        $this->testServiceTreeHierarchy();
        $this->testDefaultUnitKerjaMapping();
        $this->testToggleServicePermissionCascade();
        $this->testResetPermissionToDefault();
        $this->testCopyPermissionBetweenPegawai();
        $this->testCanNipAccessService();
        $this->testTimKerjaCardAccessStatus();
        $this->testTimKerjaLayananAccessStatus();
        $this->testAccessControlModelIntegration();
        $this->testApiEndpoints();

        echo "\n========================================================\n";

        echo "  Test Summary: {$this->passed} Passed, {$this->failed} Failed\n";
        echo "========================================================\n";

        if ($this->failed > 0) {
            exit(1);
        }
    }

    public function testApiEndpoints()
    {
        $rows = $this->smm->getServicePegawaiList('', 10);
        $hasData = is_array($rows) && !empty($rows) && isset($rows[0]['nip'], $rows[0]['nama']);

        $this->assert(
            'Model getServicePegawaiList mengembalikan list pegawai untuk modal API',
            $hasData,
            "Rows count: " . count($rows)
        );
    }






    private function assert(string $testName, bool $condition, string $details = '')
    {
        if ($condition) {
            echo " [PASS] {$testName}\n";
            $this->passed++;
        } else {
            echo " [FAIL] {$testName}\n";
            if ($details) {
                echo "        Details: {$details}\n";
            }
            $this->failed++;
        }
    }

    public function testDatabaseTableExists()
    {
        $exists = $this->db->tableExists('auth_service_permission');
        $count = $exists ? $this->db->table('auth_service_permission')->countAllResults() : 0;

        $this->assert(
            'Tabel auth_service_permission terbentuk dan memiliki data',
            $exists && $count > 0,
            "Table exists: " . ($exists ? 'YES' : 'NO') . ", Total rows: {$count}"
        );
    }

    public function testPegawaiListAndDetail()
    {
        $list = $this->spm->getPegawaiList('', 10);
        $hasItems = !empty($list) && isset($list[0]['nip'], $list[0]['nama'], $list[0]['total_allowed_services']);

        $fullList = $this->spm->getPegawaiList('', 0);
        $yadiFound = false;
        foreach ($fullList as $p) {
            if ($p['nip'] === '199202092024211007' || stripos($p['nama'], 'YADI KURNIADI') !== false) {
                $yadiFound = true;
                break;
            }
        }

        $this->assert(
            'getPegawaiList mengembalikan seluruh pegawai (termasuk Yadi Kurniadi) tanpa limit terpotong',
            $hasItems && count($fullList) >= 180 && $yadiFound,
            "Total full list: " . count($fullList) . ", Yadi found: " . ($yadiFound ? 'YES' : 'NO')
        );

        if ($hasItems) {
            $nip = $list[0]['nip'];
            $detail = $this->spm->getPegawaiDetail($nip);
            $hasDetail = !empty($detail) && $detail['nip'] === $nip;
            $this->assert(
                'getPegawaiDetail mengembalikan detail pegawai terpilih',
                $hasDetail && isset($detail['total_allowed_services'], $detail['total_allowed_timkerja']),
                "Detail NIP: " . ($detail['nip'] ?? 'NULL')
            );
        }
    }

    public function testServiceTreeHierarchy()
    {
        $list = $this->spm->getPegawaiList('', 1);
        if (empty($list)) {
            $this->assert('Service tree test skipped (no pegawai)', false);
            return;
        }

        $nip = $list[0]['nip'];
        $tree = $this->spm->getServiceTreeWithPegawaiPermission($nip);

        $hasTimKerja = !empty($tree) && isset($tree[0]['type']) && $tree[0]['type'] === 'timkerja';
        $hasServices = $hasTimKerja && !empty($tree[0]['children']) && $tree[0]['children'][0]['type'] === 'service';

        $this->assert(
            'getServiceTreeWithPegawaiPermission menghasilkan hirarki Level 0 (Tim Kerja) -> Level 1 (Layanan)',
            $hasTimKerja && $hasServices,
            "TimKerja count: " . count($tree) . ", First TK children: " . ($hasTimKerja ? count($tree[0]['children']) : 0)
        );
    }

    public function testDefaultUnitKerjaMapping()
    {
        // Cari pegawai di Tim Kerja Pengangkatan dan Mutasi (unit_kerja_id = 20)
        $p = $this->db->table('data_pegawai')
            ->select('nip')
            ->where('unit_kerja_id', 20)
            ->limit(1)
            ->get()
            ->getRowArray();

        if ($p) {
            $nip = $p['nip'];
            $defaultServiceIds = $this->spm->getDefaultServiceIdsForNip($nip);

            // TK 1 (Pengangkatan & Mutasi) memiliki layanan seperti tapnip, pi, pg, pmk, kp (ID 2, 3, 4, 5, 6)
            $hasPnmServices = in_array(2, $defaultServiceIds, true) || in_array(6, $defaultServiceIds, true);
            // Tidak boleh memiliki layanan dari TK lain (misal Layanan 29 Pagu Anggaran dari TU)
            $doesNotHaveTu = !in_array(29, $defaultServiceIds, true);

            $this->assert(
                'Default unit_kerja_id=20 hanya mengizinkan layanan Tim Kerja Pengangkatan dan Mutasi',
                $hasPnmServices && $doesNotHaveTu,
                "Default IDs: " . json_encode($defaultServiceIds)
            );
        } else {
            $this->assert('Default unit kerja test skipped (no unit 20 pegawai)', true);
        }
    }

    public function testToggleServicePermissionCascade()
    {
        $testNip = 'TEST_NIP_99999999';

        // 1. Toggle Level 0 (Tim Kerja 1) ON
        $resTkOn = $this->spm->toggleServicePermission($testNip, 1, true, 'timkerja', 1);
        $this->assert(
            'Toggle Tim Kerja ON meng-cascade ke seluruh layanan di bawahnya',
            $resTkOn['status'] === true && count($resTkOn['affected_ids']) > 0,
            "Affected IDs: " . json_encode($resTkOn['affected_ids'])
        );

        // Verifikasi di DB
        $pnmCount = $this->db->table('auth_service_permission')
            ->where('nip', $testNip)
            ->where('is_allowed', 1)
            ->whereIn('layanan_id', $resTkOn['affected_ids'])
            ->countAllResults();

        $this->assert(
            'Seluruh layanan anak di tim kerja 1 tersimpan aktif di DB',
            $pnmCount === count($resTkOn['affected_ids']),
            "Expected: " . count($resTkOn['affected_ids']) . ", Got: {$pnmCount}"
        );

        // 2. Toggle Single Service (ID 2) OFF
        $resSvcOff = $this->spm->toggleServicePermission($testNip, 2, false, 'service', 1);
        $permSvc2 = $this->db->table('auth_service_permission')
            ->where('nip', $testNip)
            ->where('layanan_id', 2)
            ->get()
            ->getRowArray();

        $this->assert(
            'Toggle single service berhasil mengubah status permission menjadi 0',
            $resSvcOff['status'] === true && (int) ($permSvc2['is_allowed'] ?? 1) === 0
        );

        // 3. Bersihkan data uji
        $this->db->table('auth_service_permission')->where('nip', $testNip)->delete();
    }

    public function testResetPermissionToDefault()
    {
        $p = $this->db->table('data_pegawai')
            ->select('nip, unit_kerja_id')
            ->where('unit_kerja_id', 23) // Tim Kerja Status dan Pemberhentian (TK 2)
            ->limit(1)
            ->get()
            ->getRowArray();

        if ($p) {
            $nip = $p['nip'];
            $this->spm->toggleServicePermission($nip, 29, true, 'service', 1);

            $hasAccessBefore = $this->spm->canNipAccessService($nip, 29);
            $ok = $this->spm->resetPegawaiPermissionToDefault($nip, 1);
            $hasAccessAfter = $this->spm->canNipAccessService($nip, 29);

            $this->assert(
                'Reset Default berhasil mengembalikan permission pegawai sesuai unit kerja (mencabut custom override)',
                $ok && $hasAccessBefore && !$hasAccessAfter,
                "Before reset: " . ($hasAccessBefore ? '1' : '0') . ", After reset: " . ($hasAccessAfter ? '1' : '0')
            );
        } else {
            $this->assert('Reset permission test skipped', true);
        }
    }

    public function testCopyPermissionBetweenPegawai()
    {
        $sourceNip = 'TEST_SOURCE_NIP_1';
        $targetNip = 'TEST_TARGET_NIP_2';

        $this->db->table('auth_service_permission')->whereIn('nip', [$sourceNip, $targetNip])->delete();
        $this->spm->toggleServicePermission($sourceNip, 2, true, 'service', 1);
        $this->spm->toggleServicePermission($sourceNip, 7, true, 'service', 1);

        $copied = $this->spm->copyPegawaiPermission($sourceNip, $targetNip, 1);

        $targetAllowed = $this->spm->getUserAllowedServiceIds($targetNip);
        $has2and7 = in_array(2, $targetAllowed, true) && in_array(7, $targetAllowed, true);

        $this->assert(
            'copyPegawaiPermission berhasil menyalin izin dari source ke target',
            $copied && $has2and7,
            "Target allowed: " . json_encode($targetAllowed)
        );

        $this->db->table('auth_service_permission')->whereIn('nip', [$sourceNip, $targetNip])->delete();
    }

    public function testCanNipAccessService()
    {
        $pimpinan = $this->db->table('data_pegawai')->select('nip')->where('unit_kerja_id', 15)->limit(1)->get()->getRowArray();
        if ($pimpinan) {
            $pimpinanNip = $pimpinan['nip'];
            $canPimpinanAccessAny = $this->spm->canNipAccessService($pimpinanNip, 29) && $this->spm->canNipAccessService($pimpinanNip, 2);
            $this->assert(
                'Pegawai dengan unit kerja 15 (Semua Unit) memiliki akses ke seluruh layanan',
                $canPimpinanAccessAny
            );
        }

        $canAccessByUrl = $this->spm->canNipAccessService('197005131991031001', 'apps-tapnip');
        $this->assert(
            'canNipAccessService mendukung pengecekan via path URL (misal apps-tapnip)',
            $canAccessByUrl === true
        );
    }


    public function testTimKerjaCardAccessStatus()
    {
        $p = $this->db->table('data_pegawai')->select('nip, unit_kerja_id')->where('unit_kerja_id', 20)->limit(1)->get()->getRowArray();
        if ($p) {
            $nip = $p['nip'];
            $timkerjaList = $this->spm->getTimkerjaWithUserAccess($nip);

            $tk1 = null;
            $tk4 = null;
            foreach ($timkerjaList as $tk) {
                if ((int) $tk['id'] === 1) $tk1 = $tk;
                if ((int) $tk['id'] === 4) $tk4 = $tk;
            }

            $tk1Access = $tk1 && $tk1['has_access'] === true && $tk1['accessible_layanan'] > 0;
            $tk4Access = $tk4 && $tk4['has_access'] === false && $tk4['accessible_layanan'] === 0;

            $this->assert(
                'getTimkerjaWithUserAccess menentukan status has_access true jika ada >= 1 layanan, dan false jika 0',
                $tk1Access && $tk4Access,
                "TK1 access: " . ($tk1['has_access'] ? 'true' : 'false') . ", TK4 access: " . ($tk4['has_access'] ? 'true' : 'false')
            );
        } else {
            $this->assert('TimKerja Card Access test skipped', true);
        }
    }

    public function testTimKerjaLayananAccessStatus()
    {
        $tk1 = $this->db->table('data_timkerja')->select('uid')->where('id', 1)->get()->getRowArray();
        $p = $this->db->table('data_pegawai')->select('nip')->where('unit_kerja_id', 20)->limit(1)->get()->getRowArray();

        if ($tk1 && $p) {
            $services = $this->spm->getLayananTimkerjaWithUserAccess($tk1['uid'], $p['nip']);
            $hasAllowedFlag = !empty($services) && isset($services[0]['is_allowed'], $services[0]['has_access']);

            $this->assert(
                'getLayananTimkerjaWithUserAccess melampirkan flag is_allowed dan has_access pada setiap layanan',
                $hasAllowedFlag
            );
        }


        // Test explicit is_allowed = 0 override
        $testNip = 'TEST_EXPLICIT_ZERO_NIP';
        $this->db->table('auth_service_permission')->where('nip', $testNip)->delete();

        $this->spm->toggleServicePermission($testNip, 2, true, 'service', 1);
        $this->spm->toggleServicePermission($testNip, 7, false, 'service', 1);

        $allowedIds = $this->spm->getUserAllowedServiceIds($testNip);
        $is2Allowed = in_array(2, $allowedIds, true);
        $is7Allowed = in_array(7, $allowedIds, true);
        $canAccess2 = $this->spm->canNipAccessService($testNip, 2);
        $canAccess7 = $this->spm->canNipAccessService($testNip, 7);

        $this->assert(
            'Layanan dengan is_allowed = 0 secara akurat bernilai false pada allowed list dan access check',
            $is2Allowed && !$is7Allowed && $canAccess2 && !$canAccess7,
            "Svc 2 (ON): " . ($is2Allowed ? '1' : '0') . ", Svc 7 (OFF): " . ($is7Allowed ? '1' : '0')
        );

        $this->db->table('auth_service_permission')->where('nip', $testNip)->delete();
    }



    public function testAccessControlModelIntegration()
    {
        $pegawai = $this->db->table('data_pegawai')->select('nip')->where('unit_kerja_id', 20)->limit(1)->get()->getRowArray();
        if ($pegawai) {
            $nip = $pegawai['nip'];
            $res = $this->acl->canNipAccessServiceByPath($nip, 'apps-tapnip');
            $this->assert(
                'AccessControlModel::canNipAccessServiceByPath terhubung dan bekerja dengan ServicePermissionModel',
                $res === true
            );
        }
    }

}

$suite = new ServiceManagerTestSuite();
$suite->run();
