<?php

namespace App\Models\Apps\Services;

use CodeIgniter\Model;

class PembinaanDisiplinBudayaCitraModel extends Model
{
    protected $table = 'txn_pembinaan_disiplin_budaya_citra';

    public function __construct()
    {
        parent::__construct();
    }

    public function getBuilder($type, $params = null)
    {
        switch ($type) {
            case 'recap':
                return $this->getDataRecap($params);
            default:
                throw new \Exception("Unknown builder type: {$type}");
        }
    }

    public function getColumns($type, $params = null)
    {
        if ($type === 'recap') {
            return [
                'id',
                'instansi_id',
                'instansi_name',
                'logo',
                'period_year',
                'total_riwayat',
                'last_period_date',
                'last_period_label',
                'updated_at',
                'updated_at_label',
            ];
        }

        $builder = $this->getBuilder($type, $params);
        $query = $builder->get();
        return $query->getFieldNames();
    }

    public function getDataRecap($params = [])
    {
        $kategoriId = (int) ($params['kategori_id'] ?? 0);
        $periodYear = (int) ($params['period_year'] ?? date('Y'));
        $jenis = strtoupper(trim((string) ($params['jenis_layanan'] ?? 'ALL')));
        $periodMonths = array_values(array_filter(array_map('intval', (array) ($params['period_months'] ?? [])), static fn($m) => $m >= 1 && $m <= 12));

        $builder = $this->db->table('txn_pembinaan_disiplin_budaya_citra a')
            ->select("
                a.instansi_id AS id,
                a.instansi_id,
                b.nama AS instansi_name,
                b.logo AS logo,
                a.period_year,
                COUNT(a.id) AS total_riwayat,
                MAX(a.period_date) AS last_period_date,
                DATE_FORMAT(MAX(a.period_date), '%d %M %Y') AS last_period_label,
                MAX(a.updated_at) AS updated_at,
                DATE_FORMAT(MAX(a.updated_at), '%d %M %Y %H:%i:%s') AS updated_at_label
            ")
            ->join('data_instansi b', 'b.kodeins = a.instansi_id', 'left')
            ->join('data_support_pembinaan_disiplin_kategori c', 'c.id = a.kategori_id', 'left')
            ->where('a.period_year', $periodYear);

        if (!empty($periodMonths)) {
            $builder->whereIn('MONTH(a.period_date)', $periodMonths, false);
        }

        if ($kategoriId > 0) {
            $builder->where('a.kategori_id', $kategoriId);
        }
        if ($jenis !== 'ALL') {
            $builder->where('c.jenis_layanan', $jenis);
        }

        return $builder
            ->groupBy('a.instansi_id, b.nama, b.logo, a.period_year')
            ->orderBy('updated_at', 'DESC');
    }

    public function getDetailByInstansi(array $params = []): array
    {
        $instansiId = trim((string) ($params['instansi_id'] ?? ''));
        $periodYear = (int) ($params['period_year'] ?? date('Y'));
        $jenis = strtoupper(trim((string) ($params['jenis_layanan'] ?? 'ALL')));
        $kategoriId = (int) ($params['kategori_id'] ?? 0);
        $periodMonths = array_values(array_filter(array_map('intval', (array) ($params['period_months'] ?? [])), static fn($m) => $m >= 1 && $m <= 12));

        if ($instansiId === '') {
            return [];
        }

        $builder = $this->db->table('txn_pembinaan_disiplin_budaya_citra a')
            ->select("
                a.id,
                a.instansi_id,
                b.nama AS instansi_name,
                c.nama AS kategori_nama,
                c.jenis_layanan,
                a.period_year,
                a.period_date,
                DATE_FORMAT(a.period_date, '%d %M %Y') AS period_date_label,
                a.source_konsultasi,
                a.tempat_kegiatan,
                a.judul_kegiatan,
                a.no_surat_kegiatan,
                a.catatan,
                a.updated_at,
                DATE_FORMAT(a.updated_at, '%d %M %Y %H:%i:%s') AS updated_at_label
            ")
            ->join('data_instansi b', 'b.kodeins = a.instansi_id', 'left')
            ->join('data_support_pembinaan_disiplin_kategori c', 'c.id = a.kategori_id', 'left')
            ->where('a.instansi_id', $instansiId)
            ->where('a.period_year', $periodYear);

        if (!empty($periodMonths)) {
            $builder->whereIn('MONTH(a.period_date)', $periodMonths, false);
        }

        if ($kategoriId > 0) {
            $builder->where('a.kategori_id', $kategoriId);
        }
        if ($jenis !== 'ALL') {
            $builder->where('c.jenis_layanan', $jenis);
        }

        $rows = $builder
            ->orderBy('a.period_date', 'DESC')
            ->orderBy('a.updated_at', 'DESC')
            ->get()
            ->getResultArray();

        if (empty($rows)) {
            return [];
        }

        $txnIds = array_values(array_filter(array_map(static fn($r) => (int) ($r['id'] ?? 0), $rows)));
        if (empty($txnIds)) {
            return $rows;
        }

        $pegawaiRows = $this->db->table('txn_pembinaan_disiplin_budaya_citra_pegawai')
            ->select("
                txn_id,
                GROUP_CONCAT(COALESCE(pegawai_id, 0) ORDER BY id ASC SEPARATOR '||') AS pegawai_ids,
                GROUP_CONCAT(COALESCE(pegawai_nama, pegawai_nip, '-') ORDER BY id ASC SEPARATOR '||') AS pegawai_names
            ")
            ->whereIn('txn_id', $txnIds)
            ->groupBy('txn_id')
            ->get()
            ->getResultArray();

        $pegawaiMap = [];
        foreach ($pegawaiRows as $row) {
            $pegawaiMap[(int) ($row['txn_id'] ?? 0)] = [
                'pegawai_ids' => $row['pegawai_ids'] ?? '',
                'pegawai_names' => $row['pegawai_names'] ?? '',
            ];
        }

        foreach ($rows as &$row) {
            $id = (int) ($row['id'] ?? 0);
            $row['pegawai_ids'] = $pegawaiMap[$id]['pegawai_ids'] ?? '';
            $row['pegawai_names'] = $pegawaiMap[$id]['pegawai_names'] ?? '';
        }
        unset($row);

        return $rows;
    }

    public function getKategoriOptions(bool $onlyActive = true): array
    {
        $builder = $this->db->table('data_support_pembinaan_disiplin_kategori')
            ->select('id, code, nama, jenis_layanan, deskripsi, sort_order, is_active')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('nama', 'ASC');

        if ($onlyActive) {
            $builder->where('is_active', 1);
        }

        return $builder->get()->getResultArray();
    }

    public function getPeriodYears(): array
    {
        $data = $this->db->table('txn_pembinaan_disiplin_budaya_citra')
            ->select('period_year')
            ->groupBy('period_year')
            ->orderBy('period_year', 'DESC')
            ->get()
            ->getResultArray();

        $years = array_map(static fn($r) => (int) $r['period_year'], $data);
        if (empty($years)) {
            $years = [(int) date('Y')];
        }

        return array_values(array_unique($years));
    }

    public function getSummary(array $params = []): array
    {
        $kategoriId = (int) ($params['kategori_id'] ?? 0);
        $periodYear = (int) ($params['period_year'] ?? date('Y'));
        $jenis = strtoupper(trim((string) ($params['jenis_layanan'] ?? 'ALL')));
        $periodMonths = array_values(array_filter(array_map('intval', (array) ($params['period_months'] ?? [])), static fn($m) => $m >= 1 && $m <= 12));

        $builder = $this->db->table('txn_pembinaan_disiplin_budaya_citra a')
            ->select("
                COUNT(a.id) AS total_riwayat,
                SUM(CASE WHEN c.jenis_layanan = 'KONSULTASI' THEN 1 ELSE 0 END) AS total_konsultasi,
                SUM(CASE WHEN a.source_konsultasi = 'SURAT_MASUK' THEN 1 ELSE 0 END) AS total_surat_masuk,
                SUM(CASE WHEN a.source_konsultasi = 'ZOOM' THEN 1 ELSE 0 END) AS total_zoom,
                SUM(CASE WHEN a.source_konsultasi = 'PPT' THEN 1 ELSE 0 END) AS total_ppt,
                MAX(a.updated_at) AS last_update
            ")
            ->join('data_support_pembinaan_disiplin_kategori c', 'c.id = a.kategori_id', 'left')
            ->where('a.period_year', $periodYear);

        if (!empty($periodMonths)) {
            $builder->whereIn('MONTH(a.period_date)', $periodMonths, false);
        }

        if ($kategoriId > 0) {
            $builder->where('a.kategori_id', $kategoriId);
        }
        if ($jenis !== 'ALL') {
            $builder->where('c.jenis_layanan', $jenis);
        }

        $row = $builder->get()->getRowArray() ?? [];

        return [
            'total_riwayat' => (int) ($row['total_riwayat'] ?? 0),
            'total_konsultasi' => (int) ($row['total_konsultasi'] ?? 0),
            'total_surat_masuk' => (int) ($row['total_surat_masuk'] ?? 0),
            'total_ppt' => (int) ($row['total_ppt'] ?? 0),
            'total_zoom' => (int) ($row['total_zoom'] ?? 0),
            'last_update' => $row['last_update'] ?? null,
        ];
    }

    public function getJenisBreakdown(array $params = []): array
    {
        $kategoriId = (int) ($params['kategori_id'] ?? 0);
        $periodYear = (int) ($params['period_year'] ?? date('Y'));
        $jenisFilter = strtoupper(trim((string) ($params['jenis_layanan'] ?? 'ALL')));
        $periodMonths = array_values(array_filter(array_map('intval', (array) ($params['period_months'] ?? [])), static fn($m) => $m >= 1 && $m <= 12));

        $join = 'a.kategori_id = c.id AND a.period_year = ' . $this->db->escape($periodYear);
        if (!empty($periodMonths)) {
            $join .= ' AND MONTH(a.period_date) IN (' . implode(',', $periodMonths) . ')';
        }

        $builder = $this->db->table('data_support_pembinaan_disiplin_kategori c')
            ->select("
                c.jenis_layanan,
                COUNT(a.id) AS total_riwayat,
                SUM(CASE WHEN a.source_konsultasi = 'SURAT_MASUK' THEN 1 ELSE 0 END) AS total_surat_masuk,
                SUM(CASE WHEN a.source_konsultasi = 'ZOOM' THEN 1 ELSE 0 END) AS total_zoom,
                SUM(CASE WHEN a.source_konsultasi = 'PPT' THEN 1 ELSE 0 END) AS total_ppt
            ")
            ->join('txn_pembinaan_disiplin_budaya_citra a', $join, 'left')
            ->where('c.is_active', 1)
            ->groupBy('c.jenis_layanan')
            ->orderBy("FIELD(c.jenis_layanan, 'ASISTENSI', 'KONSULTASI', 'PEMBINAAN')", '', false);

        if ($kategoriId > 0) {
            $builder->where('c.id', $kategoriId);
        }
        if ($jenisFilter !== 'ALL') {
            $builder->where('c.jenis_layanan', $jenisFilter);
        }

        return $builder->get()->getResultArray();
    }

    public function getKategoriBreakdown(array $params = []): array
    {
        $periodYear = (int) ($params['period_year'] ?? date('Y'));
        $jenis = strtoupper(trim((string) ($params['jenis_layanan'] ?? 'ALL')));
        $jenisEscaped = $this->db->escape($jenis);
        $periodMonths = array_values(array_filter(array_map('intval', (array) ($params['period_months'] ?? [])), static fn($m) => $m >= 1 && $m <= 12));

        $join = 'a.kategori_id = c.id AND a.period_year = ' . $this->db->escape($periodYear);
        if (!empty($periodMonths)) {
            $join .= ' AND MONTH(a.period_date) IN (' . implode(',', $periodMonths) . ')';
        }

        $builder = $this->db->table('data_support_pembinaan_disiplin_kategori c')
            ->select("
                c.id,
                c.code,
                c.nama,
                c.jenis_layanan,
                SUM(
                    CASE
                        WHEN {$jenisEscaped} = 'ALL' OR c.jenis_layanan = {$jenisEscaped}
                            THEN CASE WHEN a.id IS NULL THEN 0 ELSE 1 END
                        ELSE 0
                    END
                ) AS total_data
            ")
            ->join('txn_pembinaan_disiplin_budaya_citra a', $join, 'left')
            ->where('c.is_active', 1)
            ->groupBy('c.id')
            ->orderBy('c.sort_order', 'ASC');

        return $builder->get()->getResultArray();
    }

    public function getKategoriById(int $kategoriId): ?array
    {
        if ($kategoriId <= 0) {
            return null;
        }

        $row = $this->db->table('data_support_pembinaan_disiplin_kategori')
            ->select('id, code, nama, jenis_layanan, is_active')
            ->where('id', $kategoriId)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    public function syncPegawaiByTxn(int $txnId, array $pegawaiIds = []): void
    {
        $this->db->table('txn_pembinaan_disiplin_budaya_citra_pegawai')
            ->where('txn_id', $txnId)
            ->delete();

        if (empty($pegawaiIds)) {
            return;
        }

        $rows = $this->db->table('data_pegawai')
            ->select('id, nip, nama')
            ->whereIn('id', $pegawaiIds)
            ->get()
            ->getResultArray();

        if (empty($rows)) {
            return;
        }

        $batch = [];
        $now = date('Y-m-d H:i:s');
        foreach ($rows as $row) {
            $batch[] = [
                'txn_id' => $txnId,
                'pegawai_id' => $row['id'] ?? null,
                'pegawai_nip' => $row['nip'] ?? null,
                'pegawai_nama' => $row['nama'] ?? null,
                'created_at' => $now,
            ];
        }

        if (!empty($batch)) {
            $this->db->table('txn_pembinaan_disiplin_budaya_citra_pegawai')->insertBatch($batch);
        }
    }

    public function removePegawaiByTxn(int $txnId): void
    {
        $this->db->table('txn_pembinaan_disiplin_budaya_citra_pegawai')
            ->where('txn_id', $txnId)
            ->delete();
    }

    public function kategoriCodeExists(string $code, int $excludeId = 0): bool
    {
        $builder = $this->db->table('data_support_pembinaan_disiplin_kategori')
            ->select('id')
            ->where('code', $code);

        if ($excludeId > 0) {
            $builder->where('id <>', $excludeId);
        }

        $row = $builder->get()->getRowArray();
        return (bool) $row;
    }

    public function countUsageByKategoriId(int $kategoriId): int
    {
        $row = $this->db->table('txn_pembinaan_disiplin_budaya_citra')
            ->select('COUNT(*) AS total')
            ->where('kategori_id', $kategoriId)
            ->get()
            ->getRowArray();

        return (int) ($row['total'] ?? 0);
    }
}
