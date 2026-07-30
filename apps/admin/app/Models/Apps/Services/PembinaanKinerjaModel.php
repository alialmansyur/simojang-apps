<?php

namespace App\Models\Apps\Services;

use CodeIgniter\Model;

class PembinaanKinerjaModel extends Model
{
    protected $table = 'txn_pembinaan_kinerja';

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
                'kategori_id',
                'kategori_nama',
                'kategori_code',
                'period_year',
                'period_date',
                'period_date_label',
                'capaian_percent',
                'status_nama',
                'status_warna',
                'pendampingan_date',
                'pendampingan_date_label',
                'catatan',
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
        $periodMonths = array_values(array_filter(array_map('intval', (array) ($params['period_months'] ?? [])), static fn($m) => $m >= 1 && $m <= 12));

        $sql = "
            SELECT
                a.id,
                a.instansi_id,
                b.nama AS instansi_name,
                b.logo AS logo,
                a.kategori_id,
                c.nama AS kategori_nama,
                c.code AS kategori_code,
                a.period_year,
                a.period_date,
                DATE_FORMAT(a.period_date, '%d %M %Y') AS period_date_label,
                a.capaian_percent,
                d.nama AS status_nama,
                d.warna AS status_warna,
                a.pendampingan_date,
                DATE_FORMAT(a.pendampingan_date, '%d %M %Y') AS pendampingan_date_label,
                a.catatan,
                a.updated_at,
                DATE_FORMAT(a.updated_at, '%d %M %Y %H:%i:%s') AS updated_at_label
            FROM txn_pembinaan_kinerja a
            LEFT JOIN data_instansi b ON b.kodeins = a.instansi_id
            LEFT JOIN data_support_pembinaan_kinerja_kategori c ON c.id = a.kategori_id
            LEFT JOIN data_support_pembinaan_kinerja_status d ON d.id = a.status_id
        ";

        $builder = $this->db->table("({$sql}) recap")
            ->where('period_year', $periodYear)
            ->orderBy('updated_at', 'DESC');

        if ($kategoriId > 0) {
            $builder->where('kategori_id', $kategoriId);
        }
        if (!empty($periodMonths)) {
            $builder->whereIn('MONTH(period_date)', $periodMonths, false);
        }

        return $builder;
    }

    public function getKategoriOptions(): array
    {
        return $this->db->table('data_support_pembinaan_kinerja_kategori')
            ->select('id, code, nama')
            ->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function getPeriodYears(): array
    {
        $data = $this->db->table('txn_pembinaan_kinerja')
            ->select('period_year')
            ->groupBy('period_year')
            ->orderBy('period_year', 'DESC')
            ->get()
            ->getResultArray();

        $years = array_map(static fn($r) => (int) $r['period_year'], $data);
        $current = (int) date('Y');
        if (!in_array($current, $years, true)) {
            array_unshift($years, $current);
        }

        return array_values(array_unique($years));
    }

    public function getSummary(array $params = []): array
    {
        $kategoriId = (int) ($params['kategori_id'] ?? 0);
        $periodYear = (int) ($params['period_year'] ?? date('Y'));
        $periodMonths = array_values(array_filter(array_map('intval', (array) ($params['period_months'] ?? [])), static fn($m) => $m >= 1 && $m <= 12));

        $builder = $this->db->table('txn_pembinaan_kinerja')
            ->select("
                COUNT(*) AS total_data,
                COUNT(DISTINCT instansi_id) AS total_instansi,
                ROUND(AVG(capaian_percent),2) AS avg_capaian,
                MAX(updated_at) AS last_update
            ")
            ->where('period_year', $periodYear);

        if ($kategoriId > 0) {
            $builder->where('kategori_id', $kategoriId);
        }
        if (!empty($periodMonths)) {
            $builder->whereIn('MONTH(period_date)', $periodMonths, false);
        }

        $row = $builder->get()->getRowArray() ?? [];

        return [
            'total_data' => (int) ($row['total_data'] ?? 0),
            'total_instansi' => (int) ($row['total_instansi'] ?? 0),
            'avg_capaian' => (float) ($row['avg_capaian'] ?? 0),
            'last_update' => $row['last_update'] ?? null,
        ];
    }

    public function getKategoriBreakdown(array $params = []): array
    {
        $periodYear = (int) ($params['period_year'] ?? date('Y'));
        $periodMonths = array_values(array_filter(array_map('intval', (array) ($params['period_months'] ?? [])), static fn($m) => $m >= 1 && $m <= 12));

        $join = 'b.kategori_id = a.id AND b.period_year = ' . $this->db->escape($periodYear);
        if (!empty($periodMonths)) {
            $join .= ' AND MONTH(b.period_date) IN (' . implode(',', $periodMonths) . ')';
        }

        return $this->db->table('data_support_pembinaan_kinerja_kategori a')
            ->select('a.id, a.code, a.nama, COUNT(b.id) AS total')
            ->join('txn_pembinaan_kinerja b', $join, 'left')
            ->where('a.is_active', 1)
            ->groupBy('a.id')
            ->orderBy('a.sort_order', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function resolveStatusId(float $percent): ?int
    {
        $row = $this->db->table('data_support_pembinaan_kinerja_status')
            ->select('id')
            ->where('is_active', 1)
            ->where('min_percent <=', $percent)
            ->groupStart()
                ->where('max_percent >=', $percent)
                ->orWhere('max_percent IS NULL', null, false)
            ->groupEnd()
            ->orderBy('sort_order', 'ASC')
            ->get()
            ->getRowArray();

        return isset($row['id']) ? (int) $row['id'] : null;
    }

    public function findExistingTxnId(string $instansiId, int $kategoriId, int $periodYear): ?int
    {
        $row = $this->db->table('txn_pembinaan_kinerja')
            ->select('id')
            ->where('instansi_id', $instansiId)
            ->where('kategori_id', $kategoriId)
            ->where('period_year', $periodYear)
            ->get()
            ->getRowArray();

        return isset($row['id']) ? (int) $row['id'] : null;
    }
}
