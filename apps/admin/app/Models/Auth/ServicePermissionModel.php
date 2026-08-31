<?php

namespace App\Models\Auth;

use CodeIgniter\Model;

class ServicePermissionModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'auth_service_permission';
    protected $primaryKey       = 'id';

    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'pegawai_id',
        'nip',
        'layanan_id',
        'is_allowed',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

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

    /**
     * Mengambil daftar seluruh pegawai beserta unit kerja dan statistik permission
     */
    public function getPegawaiList(string $search = '', int $limit = 0): array
    {
        $builder = $this->db->table('data_pegawai dp')
            ->select("
                dp.id AS pegawai_id,
                dp.nip,
                dp.nama,
                dp.unit_kerja_id,
                uk.nama AS unit_kerja_nama,
                COALESCE(u.role, 'USR') AS user_role,
                COALESCE(r.role_name, 'User') AS role_name,
                (SELECT COUNT(*) FROM auth_service_permission sp WHERE sp.nip = dp.nip AND sp.is_allowed = 1) AS total_allowed_services
            ")
            ->join('data_pegawai_unit_kerja uk', 'uk.id = dp.unit_kerja_id', 'left')
            ->join('auth_users u', 'u.username = dp.nip', 'left')
            ->join('auth_roles r', 'r.role_code = u.role', 'left')
            ->where('COALESCE(dp.nip, \'\') <>', '')
            ->orderBy('dp.nama', 'ASC');

        if ($limit > 0) {
            $builder->limit($limit);
        }


        $search = trim($search);
        if ($search !== '') {
            $builder->groupStart()
                ->like('dp.nip', $search)
                ->orLike('dp.nama', $search)
                ->orLike('uk.nama', $search)
                ->groupEnd();
        }

        $rows = $builder->get()->getResultArray();
        $totalActiveServices = $this->getTotalActiveServicesCount();

        // Prefetch unit kerja map to prevent N+1 queries
        $unitKerjaRows = $this->db->table('data_pegawai_unit_kerja')->select('id, nama')->get()->getResultArray();
        $ukMap = [];
        foreach ($unitKerjaRows as $uk) {
            $ukMap[(int) $uk['id']] = $uk['nama'];
        }

        foreach ($rows as &$row) {
            $row['total_allowed_services'] = (int) ($row['total_allowed_services'] ?? 0);
            $row['total_active_services'] = $totalActiveServices;
            if (empty($row['unit_kerja_nama'])) {
                $unitIds = array_map('intval', array_filter(explode(',', (string) ($row['unit_kerja_id'] ?? '')), 'is_numeric'));
                if (empty($unitIds)) {
                    $row['unit_kerja_nama'] = '-';
                } else {
                    $names = [];
                    foreach ($unitIds as $ukId) {
                        if (isset($ukMap[$ukId])) {
                            $names[] = $ukMap[$ukId];
                        }
                    }
                    $row['unit_kerja_nama'] = empty($names) ? '-' : implode(', ', $names);
                }
            }
        }
        unset($row);

        return $rows;
    }

    /**
     * Mengambil detail lengkap seorang pegawai berdasarkan NIP
     */
    public function getPegawaiDetail(string $nip): ?array
    {
        $nip = trim($nip);
        if ($nip === '') {
            return null;
        }

        $row = $this->db->table('data_pegawai dp')
            ->select("
                dp.id AS pegawai_id,
                dp.nip,
                dp.nama,
                dp.unit_kerja_id,
                uk.nama AS unit_kerja_nama,
                COALESCE(u.role, 'USR') AS user_role,
                COALESCE(r.role_name, 'User') AS role_name,
                (SELECT COUNT(*) FROM auth_service_permission sp WHERE sp.nip = dp.nip AND sp.is_allowed = 1) AS total_allowed_services
            ")
            ->join('data_pegawai_unit_kerja uk', 'uk.id = dp.unit_kerja_id', 'left')
            ->join('auth_users u', 'u.username = dp.nip', 'left')
            ->join('auth_roles r', 'r.role_code = u.role', 'left')
            ->where('dp.nip', $nip)
            ->limit(1)
            ->get()
            ->getRowArray();

        if (!$row) {
            // Cek jika user ada di auth_users tetapi tidak ada di data_pegawai
            $user = $this->db->table('auth_users u')
                ->select("
                    u.id AS user_id,
                    u.username AS nip,
                    u.fullname AS nama,
                    u.role AS user_role,
                    r.role_name,
                    (SELECT COUNT(*) FROM auth_service_permission sp WHERE sp.nip = u.username AND sp.is_allowed = 1) AS total_allowed_services
                ")
                ->join('auth_roles r', 'r.role_code = u.role', 'left')
                ->where('u.username', $nip)
                ->limit(1)
                ->get()
                ->getRowArray();

            if (!$user) {
                return null;
            }

            $row = [
                'pegawai_id'             => 0,
                'nip'                    => $user['nip'],
                'nama'                   => $user['nama'],
                'unit_kerja_id'          => '',
                'unit_kerja_nama'        => 'Semua Unit Kerja (Akun Sistem)',
                'user_role'              => $user['user_role'] ?? 'USR',
                'role_name'              => $user['role_name'] ?? 'User',
                'total_allowed_services' => (int) ($user['total_allowed_services'] ?? 0),
            ];
        } else {
            $row['total_allowed_services'] = (int) ($row['total_allowed_services'] ?? 0);
            if (empty($row['unit_kerja_nama'])) {
                $row['unit_kerja_nama'] = $this->resolveUnitKerjaNamaMulti((string) ($row['unit_kerja_id'] ?? ''));
            }
        }

        $row['total_active_services'] = $this->getTotalActiveServicesCount();
        $row['total_allowed_timkerja'] = $this->getPegawaiAllowedTimKerjaCount($nip);
        $row['total_timkerja'] = $this->getTotalActiveTimKerjaCount();

        return $row;
    }

    /**
     * Membangun Tree Hirarki Layanan (Level 0: Tim Kerja, Level 1: Layanan) untuk seorang pegawai
     */
    public function getServiceTreeWithPegawaiPermission(string $nip): array
    {
        $nip = trim($nip);
        if ($nip === '') {
            return [];
        }

        // 1. Ambil seluruh Tim Kerja aktif (Level 0)
        $timKerjaList = $this->db->table('data_timkerja')
            ->select('id, uid, nama, code, is_sort, is_show')
            ->where('is_show', 1)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        // 2. Ambil seluruh Layanan aktif (Level 1)
        $servicesList = $this->db->table('data_timkerja_layanan')
            ->select('id, timkerja_id, nama_layanan, alias, url, is_show, deskripsi')
            ->where('is_show', 1)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        // 3. Ambil map izin layanan pegawai dari auth_service_permission
        $permRows = $this->db->table('auth_service_permission')
            ->select('layanan_id, is_allowed')
            ->where('nip', $nip)
            ->get()
            ->getResultArray();

        $explicitMap = [];
        foreach ($permRows as $pr) {
            $explicitMap[(int) $pr['layanan_id']] = (int) ($pr['is_allowed'] ?? 0);
        }

        $defaultServiceIds = $this->getDefaultServiceIdsForNip($nip);
        $defaultMap = array_flip($defaultServiceIds);

        // Kelompokkan layanan berdasarkan timkerja_id
        $servicesByTk = [];
        foreach ($servicesList as $s) {
            $tkId = (int) $s['timkerja_id'];
            $serviceId = (int) $s['id'];

            if (isset($explicitMap[$serviceId])) {
                $isAllowed = ($explicitMap[$serviceId] === 1);
            } else {
                $isAllowed = isset($defaultMap[$serviceId]);
            }

            $servicesByTk[$tkId][] = [
                'id'          => $serviceId,
                'raw_id'      => $serviceId,
                'parent_id'   => 'tk_' . $tkId,
                'type'        => 'service',
                'name'        => (string) $s['nama_layanan'],
                'alias'       => (string) ($s['alias'] ?? ''),
                'url'         => (string) ($s['url'] ?? ''),
                'deskripsi'   => (string) ($s['deskripsi'] ?? ''),
                'level'       => 1,
                'allowed'     => $isAllowed,
                'children'    => [],
            ];
        }


        // Bangun tree parent (Tim Kerja)
        $tree = [];
        foreach ($timKerjaList as $tk) {
            $tkId = (int) $tk['id'];
            $children = $servicesByTk[$tkId] ?? [];
            $totalChild = count($children);
            $allowedChildCount = 0;

            foreach ($children as $c) {
                if (!empty($c['allowed'])) {
                    $allowedChildCount++;
                }
            }

            $isTkAllowed = $totalChild > 0 && ($allowedChildCount === $totalChild);
            $isTkPartial = $allowedChildCount > 0 && ($allowedChildCount < $totalChild);

            $tree[] = [
                'id'            => 'tk_' . $tkId,
                'raw_id'        => $tkId,
                'parent_id'     => null,
                'type'          => 'timkerja',
                'name'          => (string) $tk['nama'],
                'code'          => (string) ($tk['code'] ?? ''),
                'url'           => 'timkerja-layanan/' . (string) ($tk['uid'] ?? ''),
                'icon'          => $this->resolveTimKerjaIcon((string) $tk['nama']),
                'level'         => 0,
                'allowed'       => $isTkAllowed,
                'is_partial'    => $isTkPartial,
                'allowed_count' => $allowedChildCount,
                'total_count'   => $totalChild,
                'children'      => $children,
            ];
        }

        return $tree;
    }

    /**
     * Toggle izin akses layanan atau tim kerja untuk NIP tertentu (Cascade UP & DOWN)
     */
    public function toggleServicePermission(string $nip, $nodeId, bool $allowed, string $nodeType = 'service', ?int $actorUserId = null): array
    {
        $nip = trim($nip);
        if ($nip === '') {
            return ['status' => false, 'message' => 'NIP tidak valid', 'affected_ids' => []];
        }

        // Ambil data pegawai untuk pegawai_id
        $pegawai = $this->db->table('data_pegawai')
            ->select('id')
            ->where('nip', $nip)
            ->limit(1)
            ->get()
            ->getRowArray();
        $pegawaiId = !empty($pegawai['id']) ? (int) $pegawai['id'] : null;

        $this->db->transStart();
        $now = date('Y-m-d H:i:s');
        $affectedServiceIds = [];

        // Normalisasi node ID jika string seperti 'tk_1'
        if (is_string($nodeId) && str_starts_with($nodeId, 'tk_')) {
            $nodeType = 'timkerja';
            $nodeId = (int) substr($nodeId, 3);
        }

        if ($nodeType === 'timkerja') {
            $tkId = (int) $nodeId;
            // Ambil seluruh layanan di bawah tim kerja ini
            $childServices = $this->db->table('data_timkerja_layanan')
                ->select('id')
                ->where('timkerja_id', $tkId)
                ->where('is_show', 1)
                ->get()
                ->getResultArray();

            foreach ($childServices as $cs) {
                $sid = (int) $cs['id'];
                $affectedServiceIds[] = $sid;
                $this->upsertServicePermissionRow($pegawaiId, $nip, $sid, $allowed ? 1 : 0, $now, $actorUserId);
            }
        } else {
            $serviceId = (int) $nodeId;
            $affectedServiceIds[] = $serviceId;
            $this->upsertServicePermissionRow($pegawaiId, $nip, $serviceId, $allowed ? 1 : 0, $now, $actorUserId);
        }

        $this->db->transComplete();
        $ok = $this->db->transStatus() !== false;

        $detail = $this->getPegawaiDetail($nip);

        return [
            'status'       => $ok,
            'allowed'      => $allowed,
            'nip'          => $nip,
            'node_id'      => $nodeId,
            'node_type'    => $nodeType,
            'affected_ids' => array_values(array_unique($affectedServiceIds)),
            'detail'       => $detail,
        ];
    }

    /**
     * Reset seluruh permission pegawai kembali ke default berdasarkan unit_kerja_id
     */
    public function resetPegawaiPermissionToDefault(string $nip, ?int $actorUserId = null): bool
    {
        $nip = trim($nip);
        if ($nip === '') {
            return false;
        }

        $pegawai = $this->db->table('data_pegawai')
            ->select('id, nip, unit_kerja_id')
            ->where('nip', $nip)
            ->limit(1)
            ->get()
            ->getRowArray();

        if (!$pegawai) {
            return false;
        }

        $this->db->transStart();

        // 1. Hapus seluruh permission existing untuk NIP ini
        $this->db->table('auth_service_permission')->where('nip', $nip)->delete();

        // 2. Tentukan service ID default
        $defaultServiceIds = $this->getDefaultServiceIdsForNip($nip);
        $now = date('Y-m-d H:i:s');
        $pegawaiId = (int) $pegawai['id'];

        foreach ($defaultServiceIds as $sid) {
            $this->db->table('auth_service_permission')->insert([
                'pegawai_id' => $pegawaiId,
                'nip'        => $nip,
                'layanan_id' => $sid,
                'is_allowed' => 1,
                'created_at' => $now,
                'updated_at' => $now,
                'created_by' => $actorUserId,
                'updated_by' => $actorUserId,
            ]);
        }

        $this->db->transComplete();
        return $this->db->transStatus() !== false;
    }

    /**
     * Menyalin konfigurasi permission layanan dari satu pegawai ke pegawai target
     */
    public function copyPegawaiPermission(string $sourceNip, string $targetNip, ?int $actorUserId = null): bool
    {
        $sourceNip = trim($sourceNip);
        $targetNip = trim($targetNip);

        if ($sourceNip === '' || $targetNip === '' || $sourceNip === $targetNip) {
            return false;
        }

        $targetPegawai = $this->db->table('data_pegawai')
            ->select('id')
            ->where('nip', $targetNip)
            ->limit(1)
            ->get()
            ->getRowArray();

        $targetPegawaiId = !empty($targetPegawai['id']) ? (int) $targetPegawai['id'] : null;

        $sourcePerms = $this->db->table('auth_service_permission')
            ->select('layanan_id, is_allowed')
            ->where('nip', $sourceNip)
            ->get()
            ->getResultArray();

        if (empty($sourcePerms)) {
            // Jika source belum punya record di auth_service_permission, gunakan default-nya
            $sourceDefaultIds = $this->getDefaultServiceIdsForNip($sourceNip);
            foreach ($sourceDefaultIds as $sid) {
                $sourcePerms[] = ['layanan_id' => $sid, 'is_allowed' => 1];
            }
        }

        $this->db->transStart();

        $this->db->table('auth_service_permission')->where('nip', $targetNip)->delete();
        $now = date('Y-m-d H:i:s');

        foreach ($sourcePerms as $sp) {
            $this->db->table('auth_service_permission')->insert([
                'pegawai_id' => $targetPegawaiId,
                'nip'        => $targetNip,
                'layanan_id' => (int) $sp['layanan_id'],
                'is_allowed' => (int) ($sp['is_allowed'] ?? 1),
                'created_at' => $now,
                'updated_at' => $now,
                'created_by' => $actorUserId,
                'updated_by' => $actorUserId,
            ]);
        }

        $this->db->transComplete();
        return $this->db->transStatus() !== false;
    }

    /**
     * Cek apakah NIP berhak mengakses layanan tertentu (by ID atau URL endpoint)
     */
    public function canNipAccessService(string $nip, int|string $serviceIdOrUrl): bool
    {
        $nip = trim($nip);
        if ($nip === '') {
            return false;
        }

        // 1. Dapatkan layanan ID jika input berupa path URL
        $layananId = 0;
        if (is_numeric($serviceIdOrUrl)) {
            $layananId = (int) $serviceIdOrUrl;
        } else {
            $normalizedUrl = trim((string) $serviceIdOrUrl, '/');
            if ($normalizedUrl === '') {
                return false;
            }

            $layanan = $this->db->table('data_timkerja_layanan')
                ->select('id')
                ->where('url', $normalizedUrl)
                ->limit(1)
                ->get()
                ->getRowArray();

            if (empty($layanan)) {
                // Jika URL tidak terdaftar di data_timkerja_layanan, loloskan
                return true;
            }

            $layananId = (int) $layanan['id'];
        }

        if ($layananId <= 0) {
            return false;
        }

        // 2. Cek di auth_service_permission (explicit override per pegawai)
        $perm = $this->db->table('auth_service_permission')
            ->select('is_allowed')
            ->where('nip', $nip)
            ->where('layanan_id', $layananId)
            ->limit(1)
            ->get()
            ->getRowArray();

        if ($perm !== null) {
            return ((int) ($perm['is_allowed'] ?? 0)) === 1;
        }

        // 3. Fallback default unit kerja jika belum pernah diset secara eksplisit
        $defaultIds = $this->getDefaultServiceIdsForNip($nip);
        return in_array($layananId, $defaultIds, true);
    }

    /**
     * Mengambil daftar tim kerja lengkap dengan status akses bagi seorang NIP
     */
    public function getTimkerjaWithUserAccess(string $nip): array
    {
        $timkerjaList = $this->db->table('data_timkerja')
            ->where('is_show', 1)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        $nip = trim($nip);
        $allAllowedServiceIds = $nip !== '' ? $this->getUserAllowedServiceIds($nip) : [];
        $allowedMap = array_flip($allAllowedServiceIds);

        // Prefetch services to avoid N+1 query
        $allServices = $this->db->table('data_timkerja_layanan')
            ->select('id, timkerja_id')
            ->where('is_show', 1)
            ->get()
            ->getResultArray();

        $servicesByTk = [];
        foreach ($allServices as $s) {
            $servicesByTk[(int) $s['timkerja_id']][] = $s;
        }

        foreach ($timkerjaList as &$tk) {
            $tkId = (int) $tk['id'];
            $services = $servicesByTk[$tkId] ?? [];

            $totalLayanan = count($services);
            $accessibleLayanan = 0;

            foreach ($services as $s) {
                if (isset($allowedMap[(int) $s['id']])) {
                    $accessibleLayanan++;
                }
            }

            $tk['total_layanan'] = $totalLayanan;
            $tk['accessible_layanan'] = $accessibleLayanan;
            $tk['has_access'] = $accessibleLayanan > 0;
        }
        unset($tk);

        return $timkerjaList;
    }

    /**
     * Mengambil daftar layanan di tim kerja tertentu dengan flag `is_allowed` bagi NIP
     */
    public function getLayananTimkerjaWithUserAccess(string $param, string $nip, string $keyword = ''): array
    {
        $tw = $this->db->table('data_timkerja')
            ->where('uid', $param)
            ->get()
            ->getRow();

        if (!$tw) {
            return [];
        }

        $builder = $this->db->table('data_timkerja_layanan a')
            ->select([
                'a.*',
                'd.nama AS timkerja',
                "(SELECT COUNT(id) FROM activity_daily_logs WHERE layanan_id = a.id AND created_at >= CURDATE() AND created_at < CURDATE() + INTERVAL 1 DAY) AS uploads_today",
                "(SELECT IF(COUNT(id) > 0, 1, 0) FROM activity_daily_logs WHERE layanan_id = a.id AND created_at >= CURDATE() AND created_at < CURDATE() + INTERVAL 1 DAY) AS has_today",
                "(SELECT MAX(created_at) FROM activity_daily_logs WHERE layanan_id = a.id) AS last_upload_at",
            ], false)
            ->join('data_timkerja d', 'd.id = a.timkerja_id', 'left')
            ->where('a.timkerja_id', $tw->id)
            ->where('a.is_show', 1)
            ->orderBy('a.id', 'ASC');

        $keyword = trim($keyword);
        if ($keyword !== '') {
            $builder->groupStart()
                ->like('a.nama_layanan', $keyword)
                ->orLike('a.alias', $keyword)
                ->groupEnd();
        }

        $rows = $builder->get()->getResultArray();

        $nip = trim($nip);
        $allowedServiceIds = $nip !== '' ? $this->getUserAllowedServiceIds($nip) : [];
        $allowedMap = array_flip($allowedServiceIds);

        foreach ($rows as &$r) {
            $sid = (int) $r['id'];
            $isAllowed = isset($allowedMap[$sid]);
            $r['is_allowed'] = $isAllowed ? 1 : 0;
            $r['has_access'] = $isAllowed;
        }
        unset($r);

        return $rows;
    }

    /**
     * Mengambil seluruh service ID yang diizinkan untuk NIP tertentu
     */
    public function getUserAllowedServiceIds(string $nip): array
    {
        $nip = trim($nip);
        if ($nip === '') {
            return [];
        }

        $allServices = $this->db->table('data_timkerja_layanan')
            ->select('id')
            ->where('is_show', 1)
            ->get()
            ->getResultArray();

        if (empty($allServices)) {
            return [];
        }

        // 1. Ambil explicit overrides dari auth_service_permission
        $permRows = $this->db->table('auth_service_permission')
            ->select('layanan_id, is_allowed')
            ->where('nip', $nip)
            ->get()
            ->getResultArray();

        $explicitMap = [];
        foreach ($permRows as $pr) {
            $explicitMap[(int) $pr['layanan_id']] = (int) ($pr['is_allowed'] ?? 0);
        }

        // 2. Ambil default unit kerja
        $defaultIds = $this->getDefaultServiceIdsForNip($nip);
        $defaultMap = array_flip($defaultIds);

        // 3. Hitung allowed list: override eksplisit menang, fallback ke default unit kerja
        $allowedIds = [];
        foreach ($allServices as $s) {
            $sid = (int) $s['id'];
            if (isset($explicitMap[$sid])) {
                if ($explicitMap[$sid] === 1) {
                    $allowedIds[] = $sid;
                }
            } else {
                if (isset($defaultMap[$sid])) {
                    $allowedIds[] = $sid;
                }
            }
        }

        return $allowedIds;
    }




    /**
     * Menghitung default service ID untuk NIP berdasarkan data_pegawai.unit_kerja_id
     */
    public function getDefaultServiceIdsForNip(string $nip): array
    {
        $nip = trim($nip);
        if ($nip === '') {
            return [];
        }

        $p = $this->db->table('data_pegawai')
            ->select('unit_kerja_id')
            ->where('nip', $nip)
            ->limit(1)
            ->get()
            ->getRowArray();

        if (!$p) {
            return [];
        }

        $unitKerjaRaw = trim((string) ($p['unit_kerja_id'] ?? ''));
        if ($unitKerjaRaw === '15') {
            $all = $this->db->table('data_timkerja_layanan')->select('id')->where('is_show', 1)->get()->getResultArray();
            return array_map('intval', array_column($all, 'id'));
        }

        $unitIds = array_map('intval', array_filter(explode(',', $unitKerjaRaw), 'is_numeric'));
        $matchedTimKerjaIds = [];
        foreach ($unitIds as $ukId) {
            if (isset(self::$unitKerjaToTimKerjaMap[$ukId])) {
                foreach (self::$unitKerjaToTimKerjaMap[$ukId] as $tkId) {
                    $matchedTimKerjaIds[$tkId] = true;
                }
            }
        }

        if (empty($matchedTimKerjaIds)) {
            return [];
        }

        $services = $this->db->table('data_timkerja_layanan')
            ->select('id')
            ->whereIn('timkerja_id', array_keys($matchedTimKerjaIds))
            ->where('is_show', 1)
            ->get()
            ->getResultArray();

        return array_map('intval', array_column($services, 'id'));
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    private function upsertServicePermissionRow(?int $pegawaiId, string $nip, int $layananId, int $isAllowed, string $now, ?int $actorUserId = null): void
    {
        $exists = $this->db->table('auth_service_permission')
            ->select('id')
            ->where('nip', $nip)
            ->where('layanan_id', $layananId)
            ->limit(1)
            ->get()
            ->getRowArray();

        if ($exists) {
            $this->db->table('auth_service_permission')
                ->where('id', (int) $exists['id'])
                ->update([
                    'is_allowed' => $isAllowed,
                    'updated_at' => $now,
                    'updated_by' => $actorUserId,
                ]);
        } else {
            $this->db->table('auth_service_permission')->insert([
                'pegawai_id' => $pegawaiId,
                'nip'        => $nip,
                'layanan_id' => $layananId,
                'is_allowed' => $isAllowed,
                'created_at' => $now,
                'updated_at' => $now,
                'created_by' => $actorUserId,
                'updated_by' => $actorUserId,
            ]);
        }
    }

    private function getTotalActiveServicesCount(): int
    {
        return (int) $this->db->table('data_timkerja_layanan')
            ->where('is_show', 1)
            ->countAllResults();
    }

    private function getTotalActiveTimKerjaCount(): int
    {
        return (int) $this->db->table('data_timkerja')
            ->where('is_show', 1)
            ->countAllResults();
    }

    private function getPegawaiAllowedTimKerjaCount(string $nip): int
    {
        $allowedServiceIds = $this->getUserAllowedServiceIds($nip);
        if (empty($allowedServiceIds)) {
            return 0;
        }

        $row = $this->db->table('data_timkerja_layanan')
            ->select('COUNT(DISTINCT timkerja_id) AS cnt')
            ->whereIn('id', $allowedServiceIds)
            ->where('is_show', 1)
            ->get()
            ->getRowArray();

        return (int) ($row['cnt'] ?? 0);
    }

    private function resolveUnitKerjaNamaMulti(string $unitKerjaRaw): string
    {
        $unitIds = array_map('intval', array_filter(explode(',', $unitKerjaRaw), 'is_numeric'));
        if (empty($unitIds)) {
            return '-';
        }

        $rows = $this->db->table('data_pegawai_unit_kerja')
            ->select('nama')
            ->whereIn('id', $unitIds)
            ->get()
            ->getResultArray();

        if (empty($rows)) {
            return '-';
        }

        return implode(', ', array_column($rows, 'nama'));
    }

    private function resolveTimKerjaIcon(string $name): string
    {
        $n = strtolower($name);
        if (str_contains($n, 'pengangkatan') || str_contains($n, 'mutasi')) {
            return 'bi bi-person-gear';
        }
        if (str_contains($n, 'status') || str_contains($n, 'pemberhentian')) {
            return 'bi bi-person-badge';
        }
        if (str_contains($n, 'pembinaan') || str_contains($n, 'talenta') || str_contains($n, 'kinerja')) {
            return 'bi bi-award';
        }
        if (str_contains($n, 'pengawasan') || str_contains($n, 'wasdal') || str_contains($n, 'merit')) {
            return 'bi bi-shield-check';
        }
        if (str_contains($n, 'informasi') || str_contains($n, 'digitalisasi')) {
            return 'bi bi-hdd-network';
        }
        if (str_contains($n, 'tata usaha') || str_contains($n, 'tu')) {
            return 'bi bi-building';
        }

        return 'bi bi-folder2-open';
    }
}
