<?php

namespace App\Models\Apps\Services;

use CodeIgniter\Model;

class AnggaranModel extends Model
{
    protected $table            = 'txn_anggaran_realisasi';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    private const LEVEL_ORDER = [
        'unit',
        'kementerian',
        'program',
        'kegiatan',
        'output',
        'sub_output',
        'komponen',
        'sub_komponen',
        'akun',
    ];

    private const OVER_BUDGET_EXEMPT_ACCOUNT_CODES = [
        '511111',
        '511119',
        '511121',
        '511122',
        '511123',
        '511124',
        '511125',
        '511126',
        '511151',
        '511611',
        '511619',
        '511621',
        '511622',
        '511624',
        '511625',
        '511633',
    ];

    public function getRealisasiDataTable(array $filters, array $params): array
    {
        $baseSql = $this->buildRealisasiHeaderAggregateBuilder($filters)->getCompiledSelect();
        $search = trim((string) ($params['search'] ?? ''));
        $length = (int) ($params['length'] ?? 10);
        $start = max(0, (int) ($params['start'] ?? 0));
        $draw = (int) ($params['draw'] ?? 1);
        $order = $params['order'] ?? [];

        $totalRecords = $this->db->table("({$baseSql}) agg")->countAllResults();

        $filteredBuilder = $this->db->table("({$baseSql}) agg");
        if ($search !== '') {
            $this->applyHeaderSearch($filteredBuilder, $search);
        }
        $totalFiltered = $filteredBuilder->countAllResults();

        $dataBuilder = $this->db->table("({$baseSql}) agg");
        if ($search !== '') {
            $this->applyHeaderSearch($dataBuilder, $search);
        }

        $this->applyHeaderOrder($dataBuilder, is_array($order) && isset($order[0]) ? $order[0] : []);

        if ($length > 0) {
            $dataBuilder->limit($length, $start);
        }

        return [
            'draw'            => $draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $totalFiltered,
            'data'            => $dataBuilder->get()->getResultArray(),
        ];
    }

    public function getSummary(int $tahun = 0, ?string $dateMode = null, ?string $dateStart = null, ?string $dateEnd = null): array
    {
        $strukturBuilder = $this->db->table('txn_anggaran_struktur s')
            ->select('COUNT(1) AS total_struktur_akun, SUM(COALESCE(s.pagu_revisi, 0)) AS total_pagu_revisi, SUM(COALESCE(s.lock_pagu, 0)) AS total_lock_pagu')
            ->where('s.level', 'akun');

        if ($tahun > 0) {
            $strukturBuilder->where('s.tahun', $tahun);
        }

        $struktur = $strukturBuilder->get()->getRowArray() ?: [];

        $realisasiBuilder = $this->db->table('txn_anggaran_realisasi a')
            ->select('COUNT(DISTINCT a.id) AS total_record, COUNT(ri.id) AS total_item, SUM(COALESCE(ri.nominal, 0)) AS total_realisasi')
            ->join('txn_anggaran_tahun t', 't.id = a.tahun_id', 'left')
            ->join('txn_anggaran_realisasi_item ri', 'ri.realisasi_id = a.id', 'left')
            ->where('a.status', 'POSTED');

        if ($tahun > 0) {
            $realisasiBuilder->where('t.tahun', $tahun);
        }

        $this->applyDateFilterToHeaderBuilder($realisasiBuilder, $dateMode, $dateStart, $dateEnd);
        $realisasi = $realisasiBuilder->get()->getRowArray() ?: [];

        $yearBuilder = $this->db->table('txn_anggaran_tahun')
            ->select('tahun, target_persen');

        if ($tahun > 0) {
            $yearBuilder->where('tahun', $tahun);
        } else {
            $yearBuilder->where('is_active', 1)->orderBy('tahun', 'DESC');
        }

        $yearRow = $yearBuilder->get(1)->getRowArray() ?: [];

        $totalPaguRevisi = (float) ($struktur['total_pagu_revisi'] ?? 0);
        $totalLockPagu = (float) ($struktur['total_lock_pagu'] ?? 0);
        $totalPaguEfektif = max(0, $totalPaguRevisi - $totalLockPagu);
        $totalRealisasi = (float) ($realisasi['total_realisasi'] ?? 0);
        $realisasiPersen = $totalPaguEfektif > 0
            ? round(($totalRealisasi / $totalPaguEfektif) * 100, 2)
            : 0;

        $targetPersen = (float) ($yearRow['target_persen'] ?? 0);

        return [
            'tahun' => $tahun > 0 ? $tahun : ($yearRow['tahun'] ?? null),
            'target_persen' => $targetPersen,
            'total_struktur_akun' => (int) ($struktur['total_struktur_akun'] ?? 0),
            'total_record' => (int) ($realisasi['total_record'] ?? 0),
            'total_item' => (int) ($realisasi['total_item'] ?? 0),
            'total_pagu_revisi' => $totalPaguRevisi,
            'total_lock_pagu' => $totalLockPagu,
            'total_pagu_efektif' => $totalPaguEfektif,
            'total_realisasi' => $totalRealisasi,
            'realisasi_persen' => $realisasiPersen,
            'gap_target_persen' => round($targetPersen - $realisasiPersen, 2),
        ];
    }

    public function getYearOptions(): array
    {
        return $this->db->table('txn_anggaran_tahun')
            ->select('id, tahun, target_persen, is_active, created_at')
            ->orderBy('tahun', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function getYearById(int $id): ?array
    {
        $row = $this->db->table('txn_anggaran_tahun')
            ->select('id, tahun, target_persen, is_active')
            ->where('id', $id)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    public function getYearByValue(int $tahun): ?array
    {
        $row = $this->db->table('txn_anggaran_tahun')
            ->select('id, tahun, target_persen, is_active')
            ->where('tahun', $tahun)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    public function getLatestYear(): ?array
    {
        $row = $this->db->table('txn_anggaran_tahun')
            ->select('id, tahun, target_persen, is_active')
            ->orderBy('tahun', 'DESC')
            ->get(1)
            ->getRowArray();

        return $row ?: null;
    }

    public function setOnlyActiveYear(int $id): bool
    {
        $this->db->transStart();
        $this->db->table('txn_anggaran_tahun')->set('is_active', 0)->update();
        $this->db->table('txn_anggaran_tahun')->set('is_active', 1)->where('id', $id)->update();
        $this->db->transComplete();

        return $this->db->transStatus();
    }

    public function hasRealisasiForYearId(int $tahunId): bool
    {
        return $this->db->table('txn_anggaran_realisasi')
            ->where('tahun_id', $tahunId)
            ->countAllResults() > 0;
    }

    public function hasStrukturForYear(int $tahun): bool
    {
        return $this->db->table('txn_anggaran_struktur')
            ->where('tahun', $tahun)
            ->countAllResults() > 0;
    }

    public function getAkunOptions(?int $tahun = null, string $search = '', int $excludeRealisasiId = 0): array
    {
        $builder = $this->db->table('txn_anggaran_struktur s')
            ->select('
                s.id,
                s.parent_id,
                s.kode,
                s.nama,
                s.level,
                s.tahun,
                p.kode AS parent_kode,
                p.nama AS parent_nama,
                p.level AS parent_level,
                COALESCE(s.pagu_revisi, 0) AS pagu_revisi,
                COALESCE(s.lock_pagu, 0) AS lock_pagu,
                GREATEST(COALESCE(s.pagu_revisi, 0) - COALESCE(s.lock_pagu, 0), 0) AS pagu_efektif
            ')
            ->join('txn_anggaran_struktur p', 'p.id = s.parent_id', 'left')
            ->where('s.level', 'akun')
            ->orderBy('s.tahun', 'DESC')
            ->orderBy('s.kode', 'ASC');

        if (!empty($tahun)) {
            $builder->where('s.tahun', (int) $tahun);
        }

        if ($search !== '') {
            $builder->groupStart()
                ->like('s.kode', $search)
                ->orLike('s.nama', $search)
                ->orLike('p.kode', $search)
                ->orLike('p.nama', $search)
                ->groupEnd();
        }

        $rows = $builder->get()->getResultArray();
        if (empty($rows)) {
            return [];
        }

        $strukturIdsByYear = [];
        foreach ($rows as $row) {
            $rowYear = (int) ($row['tahun'] ?? 0);
            $rowId = (int) ($row['id'] ?? 0);

            if ($rowYear <= 0 || $rowId <= 0) {
                continue;
            }

            if (!isset($strukturIdsByYear[$rowYear])) {
                $strukturIdsByYear[$rowYear] = [];
            }

            $strukturIdsByYear[$rowYear][] = $rowId;
        }

        $postedTotalsByYear = [];
        foreach ($strukturIdsByYear as $rowYear => $ids) {
            $postedTotalsByYear[$rowYear] = $this->getPostedNominalByStrukturIdsForYearValue(
                (int) $rowYear,
                $ids,
                $excludeRealisasiId
            );
        }

        foreach ($rows as &$row) {
            $rowYear = (int) ($row['tahun'] ?? 0);
            $rowId = (int) ($row['id'] ?? 0);
            $postedTotal = (float) ($postedTotalsByYear[$rowYear][$rowId] ?? 0);
            $paguEfektif = (float) ($row['pagu_efektif'] ?? 0);

            $row['posted_realisasi'] = $postedTotal;
            $row['sisa_anggaran'] = max(0, $paguEfektif - $postedTotal);
        }
        unset($row);

        return $rows;
    }

    public function searchAkunSelect2(string $search = '', int $limit = 25, ?int $tahun = null): array
    {
        $builder = $this->db->table('txn_anggaran_struktur s')
            ->select('s.id, s.kode, s.nama, s.tahun, p.kode AS parent_kode, p.nama AS parent_nama, p.level AS parent_level')
            ->join('txn_anggaran_struktur p', 'p.id = s.parent_id', 'left')
            ->where('s.level', 'akun')
            ->orderBy('s.tahun', 'DESC')
            ->orderBy('s.kode', 'ASC');

        if (!empty($tahun)) {
            $builder->where('s.tahun', (int) $tahun);
        }

        $search = trim($search);
        if ($search !== '') {
            $builder->groupStart()
                ->like('s.kode', $search)
                ->orLike('s.nama', $search)
                ->orLike('p.kode', $search)
                ->orLike('p.nama', $search)
                ->groupEnd();
        }

        return $builder->limit(max(1, $limit))->get()->getResultArray();
    }

    public function getStrukturById(int $id): ?array
    {
        $row = $this->db->table('txn_anggaran_struktur')
            ->select('id, parent_id, kode, nama, level, tahun, COALESCE(pagu_revisi, 0) AS pagu_revisi, COALESCE(lock_pagu, 0) AS lock_pagu')
            ->where('id', $id)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    public function getStrukturByIds(array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (empty($ids)) {
            return [];
        }

        $rows = $this->db->table('txn_anggaran_struktur')
            ->select('id, parent_id, kode, nama, level, tahun, COALESCE(pagu_revisi, 0) AS pagu_revisi, COALESCE(lock_pagu, 0) AS lock_pagu')
            ->whereIn('id', $ids)
            ->get()
            ->getResultArray();

        $mapped = [];
        foreach ($rows as $row) {
            $mapped[(int) $row['id']] = $row;
        }

        return $mapped;
    }

    public function getStrukturTree(?int $tahun = null): array
    {
        $builder = $this->db->table('txn_anggaran_struktur')
            ->select('id, parent_id, kode, nama, level, tahun, COALESCE(pagu_revisi, 0) AS pagu_revisi, COALESCE(lock_pagu, 0) AS lock_pagu')
            ->orderBy('tahun', 'DESC')
            ->orderBy('id', 'ASC');

        if (!empty($tahun)) {
            $builder->where('tahun', (int) $tahun);
        }

        $rows = $builder->get()->getResultArray();
        if (empty($rows)) {
            return [];
        }

        $realisasiByStrukturId = $this->getRealisasiTotalsByStrukturIds(array_column($rows, 'id'));
        $nodeMap = [];
        $childrenByParent = [];

        foreach ($rows as $row) {
            $id = (int) $row['id'];
            $parentId = !empty($row['parent_id']) ? (int) $row['parent_id'] : 0;

            $row['raw_pagu_revisi'] = (float) ($row['pagu_revisi'] ?? 0);
            $row['raw_lock_pagu'] = (float) ($row['lock_pagu'] ?? 0);
            $row['raw_realisasi'] = (float) ($realisasiByStrukturId[$id] ?? 0);
            $nodeMap[$id] = $row;

            if (!isset($childrenByParent[$parentId])) {
                $childrenByParent[$parentId] = [];
            }
            $childrenByParent[$parentId][] = $id;
        }

        $roots = [];
        foreach ($rows as $row) {
            $id = (int) $row['id'];
            $parentId = !empty($row['parent_id']) ? (int) $row['parent_id'] : 0;
            if ($parentId === 0 || !isset($nodeMap[$parentId])) {
                $roots[] = $id;
            }
        }

        $sorter = function (int $aId, int $bId) use ($nodeMap): int {
            $a = $nodeMap[$aId];
            $b = $nodeMap[$bId];

            $tahunA = (int) ($a['tahun'] ?? 0);
            $tahunB = (int) ($b['tahun'] ?? 0);
            if ($tahunA !== $tahunB) {
                return $tahunB <=> $tahunA;
            }

            $kodeA = (string) ($a['kode'] ?? '');
            $kodeB = (string) ($b['kode'] ?? '');
            if ($kodeA !== $kodeB) {
                return strcmp($kodeA, $kodeB);
            }

            return $aId <=> $bId;
        };

        usort($roots, $sorter);
        foreach ($childrenByParent as &$childIds) {
            usort($childIds, $sorter);
        }
        unset($childIds);

        $computeAggregate = function (int $id) use (&$computeAggregate, &$nodeMap, $childrenByParent): array {
            $node = $nodeMap[$id];
            $childIds = $childrenByParent[$id] ?? [];

            if (empty($childIds)) {
                $node['pagu_revisi'] = (float) $node['raw_pagu_revisi'];
                $node['lock_pagu'] = (float) $node['raw_lock_pagu'];
                $node['realisasi'] = (float) $node['raw_realisasi'];
                $node['pagu_efektif'] = max(0, $node['pagu_revisi'] - $node['lock_pagu']);
                $nodeMap[$id] = $node;
                return $node;
            }

            $totalPaguRevisi = 0.0;
            $totalLockPagu = 0.0;
            $totalRealisasi = 0.0;

            foreach ($childIds as $childId) {
                $child = $computeAggregate($childId);
                $totalPaguRevisi += (float) ($child['pagu_revisi'] ?? 0);
                $totalLockPagu += (float) ($child['lock_pagu'] ?? 0);
                $totalRealisasi += (float) ($child['realisasi'] ?? 0);
            }

            $node['pagu_revisi'] = $totalPaguRevisi;
            $node['lock_pagu'] = $totalLockPagu;
            $node['realisasi'] = $totalRealisasi;
            $node['pagu_efektif'] = max(0, $totalPaguRevisi - $totalLockPagu);
            $nodeMap[$id] = $node;
            return $node;
        };

        foreach ($roots as $rootId) {
            $computeAggregate($rootId);
        }

        $flat = [];
        $walker = function (int $id, int $depth) use (&$walker, &$flat, $nodeMap, $childrenByParent): void {
            $node = $nodeMap[$id];
            $childIds = $childrenByParent[$id] ?? [];
            $displayLevel = trim((string) ($node['level'] ?? '')) !== ''
                ? (string) $node['level']
                : 'root';

            $node['depth'] = $depth;
            $node['display_level'] = $displayLevel;
            $node['is_leaf'] = empty($childIds);
            $node['next_level'] = $this->getNextLevel((string) ($node['level'] ?? ''));
            $flat[] = $node;

            foreach ($childIds as $childId) {
                $walker($childId, $depth + 1);
            }
        };

        foreach ($roots as $rootId) {
            $walker($rootId, 0);
        }

        return $flat;
    }

    public function getLevelOrder(): array
    {
        return self::LEVEL_ORDER;
    }

    public function getNextLevel(?string $parentLevel): ?string
    {
        if ($parentLevel === null || $parentLevel === '') {
            return self::LEVEL_ORDER[0] ?? null;
        }

        $index = array_search($parentLevel, self::LEVEL_ORDER, true);
        if ($index === false) {
            return null;
        }

        $nextIndex = $index + 1;
        return self::LEVEL_ORDER[$nextIndex] ?? null;
    }

    public function isValidLevel(string $level): bool
    {
        return in_array($level, self::LEVEL_ORDER, true);
    }

    public function isValidLevelTransition(?string $parentLevel, string $childLevel): bool
    {
        if (!$this->isValidLevel($childLevel)) {
            return false;
        }

        if ($parentLevel === null || $parentLevel === '') {
            return $childLevel === (self::LEVEL_ORDER[0] ?? '');
        }

        $expected = $this->getNextLevel($parentLevel);
        return $expected !== null && $expected === $childLevel;
    }

    public function hasStrukturChildren(int $id): bool
    {
        return $this->db->table('txn_anggaran_struktur')
            ->where('parent_id', $id)
            ->countAllResults() > 0;
    }

    public function isStrukturUsed(int $id): bool
    {
        return $this->db->table('txn_anggaran_realisasi_item')
            ->where('struktur_id', $id)
            ->countAllResults() > 0;
    }

    public function isDescendant(int $childId, int $ancestorId): bool
    {
        $currentId = $childId;

        while ($currentId > 0) {
            $row = $this->db->table('txn_anggaran_struktur')
                ->select('id, parent_id')
                ->where('id', $currentId)
                ->get()
                ->getRowArray();

            if (!$row) {
                return false;
            }

            $parentId = !empty($row['parent_id']) ? (int) $row['parent_id'] : 0;
            if ($parentId <= 0) {
                return false;
            }

            if ($parentId === $ancestorId) {
                return true;
            }

            $currentId = $parentId;
        }

        return false;
    }

    public function getRealisasiById(int $id): ?array
    {
        $row = $this->db->table('txn_anggaran_realisasi a')
            ->select('a.id, a.uid, a.tahun_id, t.tahun, a.period_date, a.no_spm, a.spm_date, a.no_sp2d, a.sp2d_date, a.keterangan, a.status, a.created_at, COALESCE(a.updated_at, a.created_at) AS updated_at')
            ->join('txn_anggaran_tahun t', 't.id = a.tahun_id', 'left')
            ->where('a.id', $id)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    public function getRealisasiDetail(int $id): ?array
    {
        $header = $this->getRealisasiById($id);
        if (!$header) {
            return null;
        }

        $items = $this->db->table('txn_anggaran_realisasi_item ri')
            ->select('
                ri.id,
                ri.realisasi_id,
                ri.struktur_id,
                ri.nominal,
                ri.keterangan,
                ri.sort_order,
                s.kode AS struktur_kode,
                s.nama AS struktur_nama,
                s.level AS struktur_level,
                p.kode AS parent_kode,
                p.nama AS parent_nama,
                p.level AS parent_level
            ')
            ->join('txn_anggaran_struktur s', 's.id = ri.struktur_id', 'left')
            ->join('txn_anggaran_struktur p', 'p.id = s.parent_id', 'left')
            ->where('ri.realisasi_id', $id)
            ->orderBy('ri.sort_order', 'ASC')
            ->orderBy('ri.id', 'ASC')
            ->get()
            ->getResultArray();

        $header['item_count'] = count($items);
        $header['total_nominal'] = array_reduce($items, static function (float $carry, array $item): float {
            return $carry + (float) ($item['nominal'] ?? 0);
        }, 0.0);

        return [
            'header' => $header,
            'items' => $items,
        ];
    }

    public function getRealisasiExportRows(array $filters, string $search = '', array $order = []): array
    {
        $baseSql = $this->buildRealisasiHeaderAggregateBuilder($filters)->getCompiledSelect();
        $builder = $this->db->table("({$baseSql}) agg");

        if ($search !== '') {
            $this->applyHeaderSearch($builder, $search);
        }

        $this->applyHeaderOrder($builder, $order);

        return $builder->get()->getResultArray();
    }

    public function findDuplicateRealisasiDocument(string $field, string $value, int $excludeId = 0): ?array
    {
        $field = strtolower(trim($field));
        if (!in_array($field, ['no_spm', 'no_sp2d'], true)) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $normalized = strtolower($value);
        $fieldExpression = "LOWER(TRIM(REPLACE(REPLACE(REPLACE(COALESCE(a.{$field}, ''), '  ', ' '), '  ', ' '), '  ', ' ')))";

        $builder = $this->db->table('txn_anggaran_realisasi a')
            ->select('a.id, a.period_date, a.status, t.tahun')
            ->join('txn_anggaran_tahun t', 't.id = a.tahun_id', 'left')
            ->where($fieldExpression . ' = ' . $this->db->escape($normalized), null, false);

        if ($excludeId > 0) {
            $builder->where('a.id !=', $excludeId);
        }

        $row = $builder->orderBy('a.id', 'DESC')->get(1)->getRowArray();
        return $row ?: null;
    }

    public function saveRealisasi(int $key, array $header, array $items, ?string $createdBy = null): array
    {
        $tahunId = (int) ($header['tahun_id'] ?? 0);
        $status = strtoupper(trim((string) ($header['status'] ?? 'PENDING')));
        $status = $status === 'DRAFT' ? 'PENDING' : $status;
        if (!in_array($status, ['PENDING', 'POSTED', 'CANCEL'], true)) {
            $status = 'PENDING';
        }

        $yearRow = $this->getYearById($tahunId);
        if (!$yearRow) {
            return $this->errorResult('Tahun anggaran tidak ditemukan.');
        }

        $existing = null;
        if ($key > 0) {
            $existing = $this->getRealisasiById($key);
            if (!$existing) {
                return $this->errorResult('Data realisasi yang akan diubah tidak ditemukan.');
            }
        }

        $sanitizedItems = $this->sanitizeRealisasiItems($items);
        if (empty($sanitizedItems)) {
            return $this->errorResult('Minimal satu item realisasi wajib diisi.');
        }

        $strukturIds = array_column($sanitizedItems, 'struktur_id');
        $strukturMap = $this->getStrukturByIds($strukturIds);
        $submittedTotals = [];

        foreach ($sanitizedItems as $index => $item) {
            $strukturId = (int) $item['struktur_id'];
            $struktur = $strukturMap[$strukturId] ?? null;

            if (!$struktur) {
                return $this->errorResult('Akun struktur pada item ke-' . ($index + 1) . ' tidak ditemukan.');
            }

            if ((string) ($struktur['level'] ?? '') !== 'akun') {
                return $this->errorResult('Item ke-' . ($index + 1) . ' harus menggunakan struktur level AKUN.');
            }

            if ((int) ($struktur['tahun'] ?? 0) !== (int) ($yearRow['tahun'] ?? 0)) {
                return $this->errorResult('Tahun item ke-' . ($index + 1) . ' harus sama dengan tahun anggaran.');
            }

            if (!isset($submittedTotals[$strukturId])) {
                $submittedTotals[$strukturId] = 0.0;
            }
            $submittedTotals[$strukturId] += (float) $item['nominal'];
        }

        if ($status === 'POSTED') {
            $existingTotals = $this->getPostedNominalByStrukturIds($tahunId, array_keys($submittedTotals), $key);
            foreach ($submittedTotals as $strukturId => $nominal) {
                $struktur = $strukturMap[$strukturId];
                $paguEfektif = max(0, (float) ($struktur['pagu_revisi'] ?? 0) - (float) ($struktur['lock_pagu'] ?? 0));
                $strukturCode = trim((string) ($struktur['kode'] ?? ''));
                $isBudgetLockEnforced = $this->isBudgetLockEnforcedForAccountCode($strukturCode);

                if ($isBudgetLockEnforced && $paguEfektif <= 0) {
                    return $this->errorResult('Pagu efektif akun ' . ($struktur['kode'] ?? '-') . ' masih 0.');
                }

                $postedTotal = (float) ($existingTotals[$strukturId] ?? 0);
                if ($isBudgetLockEnforced && ($postedTotal + $nominal) > $paguEfektif) {
                    return $this->errorResult('Total realisasi posted melebihi pagu efektif untuk akun ' . ($struktur['kode'] ?? '-') . '.');
                }
            }
        }

        $totalNominal = array_sum(array_map(static fn(array $item): float => (float) $item['nominal'], $sanitizedItems));
        $headerPayload = [
            'tahun_id' => $tahunId,
            'period_date' => $header['period_date'] ?? null,
            'no_spm' => $header['no_spm'] ?? null,
            'spm_date' => $header['spm_date'] ?? null,
            'no_sp2d' => $header['no_sp2d'] ?? null,
            'sp2d_date' => $header['sp2d_date'] ?? null,
            'keterangan' => trim((string) ($header['keterangan'] ?? '')) !== '' ? trim((string) $header['keterangan']) : null,
            'status' => $status,
            'struktur_id' => count($sanitizedItems) === 1 ? (int) $sanitizedItems[0]['struktur_id'] : null,
            'nominal' => $totalNominal,
        ];

        $this->db->transStart();

        if ($key > 0) {
            $this->db->table('txn_anggaran_realisasi')
                ->where('id', $key)
                ->update($headerPayload);
            $headerId = $key;
            $this->db->table('txn_anggaran_realisasi_item')
                ->where('realisasi_id', $headerId)
                ->delete();
        } else {
            $headerPayload['uid'] = bin2hex(random_bytes(16));
            $this->db->table('txn_anggaran_realisasi')->insert($headerPayload);
            $headerId = (int) $this->db->insertID();
        }

        $detailRows = [];
        foreach ($sanitizedItems as $index => $item) {
            $detailRows[] = [
                'realisasi_id' => $headerId,
                'struktur_id' => (int) $item['struktur_id'],
                'nominal' => (float) $item['nominal'],
                'keterangan' => $item['keterangan'] !== '' ? $item['keterangan'] : null,
                'sort_order' => $index + 1,
            ];
        }

        if (!empty($detailRows)) {
            $this->db->table('txn_anggaran_realisasi_item')->insertBatch($detailRows);
        }

        if ($createdBy) {
            $this->db->table('activity_daily_logs')->insert([
                'layanan_id' => 29,
                'tanggal' => date('Y-m-d'),
                'created_by' => $createdBy,
            ]);
        }

        $this->db->transComplete();
        if (!$this->db->transStatus()) {
            return $this->errorResult('Terjadi kesalahan saat menyimpan data realisasi.');
        }

        return [
            'status' => 'success',
            'message' => 'Data realisasi anggaran berhasil disimpan.',
            'id' => $headerId,
        ];
    }

    public function deleteRealisasi(int $id): bool
    {
        $this->db->transStart();
        $this->db->table('txn_anggaran_realisasi_item')->where('realisasi_id', $id)->delete();
        $this->db->table('txn_anggaran_realisasi')->where('id', $id)->delete();
        $this->db->transComplete();

        return $this->db->transStatus();
    }

    private function buildRealisasiHeaderAggregateBuilder(array $filters)
    {
        $tahun = (int) ($filters['tahun'] ?? 0);
        $dateMode = $filters['date_mode'] ?? null;
        $dateStart = $filters['date_start'] ?? null;
        $dateEnd = $filters['date_end'] ?? null;

        $builder = $this->db->table('txn_anggaran_realisasi a')
            ->select(
                "a.id,
                a.uid,
                a.tahun_id,
                t.tahun,
                t.target_persen,
                a.period_date,
                a.no_spm,
                a.spm_date,
                a.no_sp2d,
                a.sp2d_date,
                a.keterangan,
                COUNT(ri.id) AS item_count,
                COALESCE(SUM(ri.nominal), 0) AS total_nominal,
                GROUP_CONCAT(
                    DISTINCT CONCAT(
                        COALESCE(s.kode, '-'),
                        ' - ',
                        COALESCE(s.nama, '-')
                    )
                    ORDER BY COALESCE(s.kode, ''), COALESCE(s.nama, '')
                    SEPARATOR '||'
                ) AS item_summary,
                a.status,
                a.created_at,
                COALESCE(a.updated_at, a.created_at) AS updated_at",
                false
            )
            ->join('txn_anggaran_tahun t', 't.id = a.tahun_id', 'left')
            ->join('txn_anggaran_realisasi_item ri', 'ri.realisasi_id = a.id', 'left')
            ->join('txn_anggaran_struktur s', 's.id = ri.struktur_id', 'left')
            ->groupBy('a.id');

        if ($tahun > 0) {
            $builder->where('t.tahun', $tahun);
        }

        $this->applyDateFilterToHeaderBuilder($builder, $dateMode, $dateStart, $dateEnd);

        return $builder;
    }

    private function applyDateFilterToHeaderBuilder($builder, ?string $dateMode, ?string $dateStart, ?string $dateEnd): void
    {
        $fieldMap = [
            'spm' => 'a.spm_date',
            'sp2d' => 'a.sp2d_date',
        ];

        $mode = strtolower(trim((string) $dateMode));
        $field = $fieldMap[$mode] ?? null;
        if ($field === null) {
            return;
        }

        if (!empty($dateStart)) {
            $builder->where($field . ' >=', $dateStart);
        }

        if (!empty($dateEnd)) {
            $builder->where($field . ' <=', $dateEnd);
        }
    }

    private function applyHeaderSearch($builder, string $search): void
    {
        $builder->groupStart()
            ->like('agg.tahun', $search)
            ->orLike('agg.period_date', $search)
            ->orLike('agg.no_spm', $search)
            ->orLike('agg.spm_date', $search)
            ->orLike('agg.no_sp2d', $search)
            ->orLike('agg.sp2d_date', $search)
            ->orLike('agg.item_summary', $search)
            ->orLike('agg.status', $search)
            ->groupEnd();
    }

    private function applyHeaderOrder($builder, array $order): void
    {
        $columnMap = [
            1 => 'agg.tahun',
            2 => 'agg.period_date',
            3 => 'agg.no_spm',
            4 => 'agg.spm_date',
            5 => 'agg.no_sp2d',
            6 => 'agg.sp2d_date',
            7 => 'agg.item_count',
            8 => 'agg.total_nominal',
            9 => 'agg.status',
            10 => 'agg.updated_at',
        ];

        $column = isset($order['column']) ? (int) $order['column'] : 2;
        $dir = isset($order['dir']) && strtolower((string) $order['dir']) === 'asc' ? 'ASC' : 'DESC';
        $mapped = $columnMap[$column] ?? 'agg.period_date';

        $builder->orderBy($mapped, $dir, false);
        $builder->orderBy('agg.id', 'DESC');
    }

    private function sanitizeRealisasiItems(array $items): array
    {
        $clean = [];

        foreach ($items as $item) {
            $strukturId = (int) ($item['struktur_id'] ?? 0);
            $nominal = (float) ($item['nominal'] ?? 0);
            $keterangan = trim((string) ($item['keterangan'] ?? ''));

            if ($strukturId <= 0 && $nominal <= 0 && $keterangan === '') {
                continue;
            }

            if ($strukturId <= 0) {
                return [];
            }

            if ($nominal <= 0) {
                return [];
            }

            $clean[] = [
                'struktur_id' => $strukturId,
                'nominal' => $nominal,
                'keterangan' => $keterangan,
            ];
        }

        return $clean;
    }

    private function getPostedNominalByStrukturIds(int $tahunId, array $strukturIds, int $excludeHeaderId = 0): array
    {
        $strukturIds = array_values(array_unique(array_map('intval', $strukturIds)));
        if (empty($strukturIds)) {
            return [];
        }

        $builder = $this->db->table('txn_anggaran_realisasi_item ri')
            ->select('ri.struktur_id, SUM(COALESCE(ri.nominal, 0)) AS total_nominal')
            ->join('txn_anggaran_realisasi a', 'a.id = ri.realisasi_id', 'inner')
            ->where('a.tahun_id', $tahunId)
            ->where('a.status', 'POSTED')
            ->whereIn('ri.struktur_id', $strukturIds)
            ->groupBy('ri.struktur_id');

        if ($excludeHeaderId > 0) {
            $builder->where('a.id !=', $excludeHeaderId);
        }

        $rows = $builder->get()->getResultArray();
        $mapped = [];
        foreach ($rows as $row) {
            $mapped[(int) $row['struktur_id']] = (float) ($row['total_nominal'] ?? 0);
        }

        return $mapped;
    }

    private function getPostedNominalByStrukturIdsForYearValue(int $tahun, array $strukturIds, int $excludeHeaderId = 0): array
    {
        $strukturIds = array_values(array_unique(array_map('intval', $strukturIds)));
        if ($tahun <= 0 || empty($strukturIds)) {
            return [];
        }

        $builder = $this->db->table('txn_anggaran_realisasi_item ri')
            ->select('ri.struktur_id, SUM(COALESCE(ri.nominal, 0)) AS total_nominal')
            ->join('txn_anggaran_realisasi a', 'a.id = ri.realisasi_id', 'inner')
            ->join('txn_anggaran_tahun t', 't.id = a.tahun_id', 'inner')
            ->where('t.tahun', $tahun)
            ->where('a.status', 'POSTED')
            ->whereIn('ri.struktur_id', $strukturIds)
            ->groupBy('ri.struktur_id');

        if ($excludeHeaderId > 0) {
            $builder->where('a.id !=', $excludeHeaderId);
        }

        $rows = $builder->get()->getResultArray();
        $mapped = [];
        foreach ($rows as $row) {
            $mapped[(int) ($row['struktur_id'] ?? 0)] = (float) ($row['total_nominal'] ?? 0);
        }

        return $mapped;
    }

    private function getRealisasiTotalsByStrukturIds(array $strukturIds): array
    {
        $strukturIds = array_values(array_unique(array_map('intval', $strukturIds)));
        if (empty($strukturIds)) {
            return [];
        }

        $rows = $this->db->table('txn_anggaran_realisasi_item ri')
            ->select('ri.struktur_id, SUM(COALESCE(ri.nominal, 0)) AS total_nominal')
            ->join('txn_anggaran_realisasi a', 'a.id = ri.realisasi_id', 'inner')
            ->whereIn('ri.struktur_id', $strukturIds)
            ->where('a.status', 'POSTED')
            ->groupBy('ri.struktur_id')
            ->get()
            ->getResultArray();

        $mapped = [];
        foreach ($rows as $row) {
            $mapped[(int) ($row['struktur_id'] ?? 0)] = (float) ($row['total_nominal'] ?? 0);
        }

        return $mapped;
    }

    private function isBudgetLockEnforcedForAccountCode(string $accountCode): bool
    {
        $normalizedCode = trim($accountCode);
        if ($normalizedCode === '') {
            return true;
        }

        return !in_array($normalizedCode, self::OVER_BUDGET_EXEMPT_ACCOUNT_CODES, true);
    }

    private function errorResult(string $message): array
    {
        return [
            'status' => 'error',
            'message' => $message,
        ];
    }
}
