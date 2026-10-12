<?php

namespace App\Models\Apps\Services;

use CodeIgniter\Model;

class IkkModel extends Model
{
    protected $DBGroup = 'default';

    public function getSasaranBuilder()
    {
        return $this->db->table('data_ikk_sasaran s')
            ->select('s.id, s.no_urut, s.nama_sasaran, s.created_at, s.updated_at, 
                      (SELECT COUNT(*) FROM data_ikk_indikator i WHERE i.sasaran_id = s.id) AS total_indikator');
    }

    public function getIndikatorBuilder()
    {
        return $this->db->table('data_ikk_indikator a')
            ->select('a.id, a.sasaran_id, a.no_urut, a.nama_indikator, a.satuan, a.target_volume, a.created_at, a.updated_at, b.nama_sasaran,
                      (SELECT COUNT(*) FROM data_ikk_kegiatan k WHERE k.indikator_id = a.id) AS total_kegiatan')
            ->join('data_ikk_sasaran b', 'a.sasaran_id = b.id', 'left');
    }

    public function getKegiatanBuilder()
    {
        return $this->db->table('data_ikk_kegiatan a')
            ->select('a.id, a.indikator_id, a.no_urut, a.nama_kegiatan, a.satuan, a.target_volume, a.created_at, a.updated_at, b.nama_indikator, c.nama_sasaran, c.id AS sasaran_id,
                      (SELECT GROUP_CONCAT(t.nama ORDER BY t.nama SEPARATOR ", ") 
                       FROM data_ikk_pengampu p 
                       JOIN data_timkerja t ON p.timkerja_id = t.id 
                       WHERE p.tipe_relasi = "kegiatan" AND p.relasi_id = a.id) AS pengampu_list,
                      (SELECT COUNT(*) FROM data_ikk_pengampu p WHERE p.tipe_relasi = "kegiatan" AND p.relasi_id = a.id) AS total_pengampu,
                      (SELECT COUNT(*) FROM txn_ikk_harian h WHERE h.kegiatan_id = a.id) AS total_transaksi')
            ->join('data_ikk_indikator b', 'a.indikator_id = b.id', 'left')
            ->join('data_ikk_sasaran c', 'b.sasaran_id = c.id', 'left');
    }

    public function getPengampuBuilder()
    {
        return $this->db->table('data_ikk_pengampu a')
            ->select("a.id, a.tipe_relasi, a.relasi_id, a.timkerja_id, b.nama AS nama_timkerja, 
                      CASE 
                          WHEN a.tipe_relasi = 'indikator' THEN c.nama_indikator
                          WHEN a.tipe_relasi = 'kegiatan' THEN d.nama_kegiatan
                      END as nama_relasi", false)
            ->join('data_timkerja b', 'a.timkerja_id = b.id', 'left')
            ->join('data_ikk_indikator c', "a.relasi_id = c.id AND a.tipe_relasi = 'indikator'", 'left')
            ->join('data_ikk_kegiatan d', "a.relasi_id = d.id AND a.tipe_relasi = 'kegiatan'", 'left');
    }

    public function getTransaksiBuilder()
    {
        return $this->db->table('txn_ikk_harian a')
            ->select('a.id, a.kegiatan_id, a.pelaksana_kegiatan_harian, a.period_start_date, a.period_end_date, a.tahun, a.triwulan, a.bulan, a.periode_key, a.target_mingguan, a.jumlah_realisasi, a.progres_realisasi, a.bukti_pekerjaan, a.created_at, a.updated_at, b.nama_kegiatan, b.satuan as satuan_kegiatan, c.nama_indikator, d.nama_sasaran,
                      (SELECT GROUP_CONCAT(t.nama ORDER BY t.nama SEPARATOR ", ") 
                       FROM data_ikk_pengampu p 
                       JOIN data_timkerja t ON p.timkerja_id = t.id 
                       WHERE p.tipe_relasi = "kegiatan" AND p.relasi_id = a.kegiatan_id) as pengampu_list')
            ->join('data_ikk_kegiatan b', 'a.kegiatan_id = b.id', 'left')
            ->join('data_ikk_indikator c', 'b.indikator_id = c.id', 'left')
            ->join('data_ikk_sasaran d', 'c.sasaran_id = d.id', 'left');
    }

    /**
     * Mem-parsing metadata periode (tahun, triwulan, bulan, periode_key) dari tanggal
     */
    public static function parsePeriodFromDate(?string $startDate, ?string $endDate): array
    {
        $refDate = !empty($endDate) ? $endDate : (!empty($startDate) ? $startDate : date('Y-m-d'));
        $time = strtotime($refDate);
        if (!$time) {
            $time = time();
        }
        $tahun = (int) date('Y', $time);
        $bulan = (int) date('n', $time);
        $triwulan = (int) ceil($bulan / 3);
        $periodeKey = sprintf('%04d-TW%d', $tahun, $triwulan);

        return [
            'tahun'       => $tahun,
            'triwulan'    => $triwulan,
            'bulan'       => $bulan,
            'periode_key' => $periodeKey,
        ];
    }

    /**
     * Mem-parsing nilai numerik dari string target volume (misal: "10", "4 Laporan", "100%", "2,5")
     */
    protected function parseNumericVolume(?string $val): ?float
    {
        if ($val === null || trim($val) === '') {
            return null;
        }
        $cleaned = trim($val);
        if (preg_match('/^([0-9]+(?:[\.,][0-9]+)?)/', $cleaned, $matches)) {
            $numStr = str_replace(',', '.', $matches[1]);
            $valNum = (float) $numStr;
            return $valNum > 0 ? $valNum : null;
        }
        return null;
    }

    /**
     * Mengambil ringkasan KPI capaian secara hierarkis dengan dukungan filter periode / tahun
     *
     * @param array|null $filter ['tahun' => int, 'triwulan' => int, 'bulan' => int]
     */
    public function getSummaryStats(?array $filter = null): array
    {
        $totalSasaran = $this->db->table('data_ikk_sasaran')->countAllResults();
        $totalIndikator = $this->db->table('data_ikk_indikator')->countAllResults();
        $totalKegiatan = $this->db->table('data_ikk_kegiatan')->countAllResults();

        // Filter penghitungan transaksi
        $trxCountBuilder = $this->db->table('txn_ikk_harian');
        if (!empty($filter['tahun'])) {
            $trxCountBuilder->where('tahun', (int) $filter['tahun']);
        }
        if (!empty($filter['triwulan'])) {
            $trxCountBuilder->where('triwulan <=', (int) $filter['triwulan']);
        }
        if (!empty($filter['bulan'])) {
            $trxCountBuilder->where('bulan <=', (int) $filter['bulan']);
        }
        $totalTransaksi = $trxCountBuilder->countAllResults();

        // Formulasi Capaian Hierarkis:
        // 1. Ambil seluruh indikator
        $indikators = $this->db->table('data_ikk_indikator')->select('id')->get()->getResultArray();
        
        // 2. Ambil seluruh kegiatan dan petakan per indikator
        $kegiatans = $this->db->table('data_ikk_kegiatan')->select('id, indikator_id, target_volume')->get()->getResultArray();
        $kegiatanByIndikator = [];
        foreach ($kegiatans as $keg) {
            $kegiatanByIndikator[$keg['indikator_id']][] = $keg;
        }

        // 3. Ringkas data transaksi per kegiatan:
        //    - Volume dihitung akumulasi SUM(jumlah_realisasi) s.d. periode terpilih
        //    - Progres dihitung dari transaksi TERAKHIR (MAX(id)) pada/sebelum periode terpilih
        $subQuery = $this->db->table('txn_ikk_harian')
            ->select('kegiatan_id, COUNT(*) as total_trx, SUM(jumlah_realisasi) as sum_realisasi, MAX(id) as latest_id');

        if (!empty($filter['tahun'])) {
            $subQuery->where('tahun', (int) $filter['tahun']);
        }
        if (!empty($filter['triwulan'])) {
            $subQuery->where('triwulan <=', (int) $filter['triwulan']);
        }
        if (!empty($filter['bulan'])) {
            $subQuery->where('bulan <=', (int) $filter['bulan']);
        }
        $subQuery->groupBy('kegiatan_id');

        $trxBuilder = $this->db->newQuery()->fromSubquery($subQuery, 'agg')
            ->select('agg.kegiatan_id, agg.total_trx, agg.sum_realisasi, agg.latest_id, t.progres_realisasi as latest_progres')
            ->join('txn_ikk_harian t', 't.id = agg.latest_id', 'left');

        $trxRows = $trxBuilder->get()->getResultArray();

        $trxByKegiatan = [];
        foreach ($trxRows as $t) {
            $trxByKegiatan[$t['kegiatan_id']] = $t;
        }

        // 4. Hitung capaian per indikator berdasar kegiatan di bawahnya
        $capaianIndikatorList = [];
        foreach ($indikators as $ind) {
            $kegList = $kegiatanByIndikator[$ind['id']] ?? [];
            if (empty($kegList)) {
                $capaianIndikatorList[] = 0.0;
                continue;
            }

            $capaianKegList = [];
            foreach ($kegList as $keg) {
                $kegId = $keg['id'];
                if (!isset($trxByKegiatan[$kegId]) || (int) $trxByKegiatan[$kegId]['total_trx'] === 0) {
                    $capaianKegList[] = 0.0;
                    continue;
                }

                $trxInfo = $trxByKegiatan[$kegId];
                $targetNum = $this->parseNumericVolume($keg['target_volume']);
                $sumReal = (float) ($trxInfo['sum_realisasi'] ?? 0);
                $latestProg = (float) ($trxInfo['latest_progres'] ?? 0);

                if ($targetNum !== null && $targetNum > 0 && $sumReal > 0) {
                    $cap = min(100.0, ($sumReal / $targetNum) * 100.0);
                } elseif ($latestProg > 0) {
                    $cap = min(100.0, $latestProg);
                } else {
                    $cap = 0.0;
                }
                $capaianKegList[] = $cap;
            }

            $capaianInd = !empty($capaianKegList) ? (array_sum($capaianKegList) / count($capaianKegList)) : 0.0;
            $capaianIndikatorList[] = $capaianInd;
        }

        // 5. Hitung capaian rata-rata keseluruhan indikator instansi
        $avgTotal = ($totalIndikator > 0 && !empty($capaianIndikatorList))
            ? (array_sum($capaianIndikatorList) / $totalIndikator)
            : 0.0;

        $avgProgres = ($avgTotal > 0 && $avgTotal < 0.1) ? round($avgTotal, 2) : round($avgTotal, 1);

        $tanpaPengampu = $this->db->query("
            SELECT COUNT(*) AS jml FROM data_ikk_kegiatan k
            WHERE NOT EXISTS (
                SELECT 1 FROM data_ikk_pengampu p 
                WHERE p.tipe_relasi = 'kegiatan' AND p.relasi_id = k.id
            )
        ")->getRowArray()['jml'] ?? 0;

        return [
            'total_sasaran'   => (int) $totalSasaran,
            'total_indikator' => (int) $totalIndikator,
            'total_kegiatan'  => (int) $totalKegiatan,
            'total_transaksi' => (int) $totalTransaksi,
            'avg_progres'     => $avgProgres,
            'tanpa_pengampu'  => (int) $tanpaPengampu,
            'filter_applied'  => $filter,
        ];
    }

    public function getAssignedPengampuIds(string $tipe_relasi, int $relasi_id): array
    {
        $rows = $this->db->table('data_ikk_pengampu')
            ->select('timkerja_id')
            ->where('tipe_relasi', $tipe_relasi)
            ->where('relasi_id', $relasi_id)
            ->get()->getResultArray();
        return array_map('intval', array_column($rows, 'timkerja_id'));
    }

    public function syncPengampu(string $tipe_relasi, int $relasi_id, array $timkerja_ids): bool
    {
        $timkerja_ids = array_unique(array_filter(array_map('intval', $timkerja_ids)));

        if (empty($timkerja_ids)) {
            $this->db->table('data_ikk_pengampu')
                ->where('tipe_relasi', $tipe_relasi)
                ->where('relasi_id', $relasi_id)
                ->delete();
            return true;
        }

        // Hapus yang sudah tidak dipilih
        $this->db->table('data_ikk_pengampu')
            ->where('tipe_relasi', $tipe_relasi)
            ->where('relasi_id', $relasi_id)
            ->whereNotIn('timkerja_id', $timkerja_ids)
            ->delete();

        // Masukkan yang baru belum ada
        $existing = $this->getAssignedPengampuIds($tipe_relasi, $relasi_id);
        $toInsert = array_diff($timkerja_ids, $existing);

        foreach ($toInsert as $tid) {
            $this->db->table('data_ikk_pengampu')->insert([
                'tipe_relasi' => $tipe_relasi,
                'relasi_id'   => $relasi_id,
                'timkerja_id' => $tid,
            ]);
        }

        return true;
    }

    public function hasPengampu(int $kegiatan_id): bool
    {
        return $this->db->table('data_ikk_pengampu')
            ->where('tipe_relasi', 'kegiatan')
            ->where('relasi_id', $kegiatan_id)
            ->countAllResults() > 0;
    }

    public function countSasaranChildren(int $sasaranId): array
    {
        $indikatorCount = $this->db->table('data_ikk_indikator')->where('sasaran_id', $sasaranId)->countAllResults();
        return ['indikator' => $indikatorCount];
    }

    public function countIndikatorChildren(int $indikatorId): array
    {
        $kegiatanCount = $this->db->table('data_ikk_kegiatan')->where('indikator_id', $indikatorId)->countAllResults();
        return ['kegiatan' => $kegiatanCount];
    }

    public function countKegiatanChildren(int $kegiatanId): array
    {
        $transaksiCount = $this->db->table('txn_ikk_harian')->where('kegiatan_id', $kegiatanId)->countAllResults();
        return ['transaksi' => $transaksiCount];
    }

    public function cleanupPengampu(string $tipe_relasi, int $relasi_id): void
    {
        $this->db->table('data_ikk_pengampu')
            ->where('tipe_relasi', $tipe_relasi)
            ->where('relasi_id', $relasi_id)
            ->delete();
    }

    public function getOptionsSasaran()
    {
        return $this->db->table('data_ikk_sasaran')
            ->select('id, nama_sasaran as text')
            ->orderBy('no_urut', 'ASC')
            ->get()->getResultArray();
    }

    public function getOptionsIndikator($sasaran_id = null)
    {
        $builder = $this->db->table('data_ikk_indikator')->select('id, nama_indikator as text');
        if ($sasaran_id) {
            $builder->where('sasaran_id', $sasaran_id);
        }
        return $builder->orderBy('no_urut', 'ASC')->get()->getResultArray();
    }

    public function getOptionsKegiatan($indikator_id = null)
    {
        $builder = $this->db->table('data_ikk_kegiatan a')
            ->select('a.id, a.nama_kegiatan as text, 
                      (SELECT COUNT(*) FROM data_ikk_pengampu p WHERE p.tipe_relasi = "kegiatan" AND p.relasi_id = a.id) AS has_pengampu');
        if ($indikator_id) {
            $builder->where('a.indikator_id', $indikator_id);
        }
        return $builder->orderBy('a.no_urut', 'ASC')->get()->getResultArray();
    }

    public function getOptionsTimKerja()
    {
        return $this->db->table('data_timkerja')
            ->select('id, nama as text')
            ->where('is_show', 1)
            ->orderBy('is_sort', 'ASC')
            ->orderBy('nama', 'ASC')
            ->get()->getResultArray();
    }

    public function checkDuplicatePengampu($tipe_relasi, $relasi_id, $timkerja_id, $exclude_id = null)
    {
        $builder = $this->db->table('data_ikk_pengampu')
            ->where('tipe_relasi', $tipe_relasi)
            ->where('relasi_id', $relasi_id)
            ->where('timkerja_id', $timkerja_id);

        if ($exclude_id) {
            $builder->where('id !=', $exclude_id);
        }

        return $builder->countAllResults() > 0;
    }

    public function getHierarchyTree(?array $filter = null): array
    {
        $sasarans = $this->db->table('data_ikk_sasaran')->orderBy('no_urut', 'ASC')->get()->getResultArray();
        foreach ($sasarans as &$sasaran) {
            $indikators = $this->db->table('data_ikk_indikator')
                ->where('sasaran_id', $sasaran['id'])
                ->orderBy('no_urut', 'ASC')
                ->get()->getResultArray();

            foreach ($indikators as &$indikator) {
                $kegiatans = $this->db->table('data_ikk_kegiatan')
                    ->where('indikator_id', $indikator['id'])
                    ->orderBy('no_urut', 'ASC')
                    ->get()->getResultArray();

                foreach ($kegiatans as &$kegiatan) {
                    $kegiatan['pengampus'] = $this->db->table('data_ikk_pengampu p')
                        ->select('t.nama as nama_timkerja')
                        ->join('data_timkerja t', 'p.timkerja_id = t.id')
                        ->where('p.tipe_relasi', 'kegiatan')
                        ->where('p.relasi_id', $kegiatan['id'])
                        ->get()->getResultArray();

                    $trxBuilder = $this->db->table('txn_ikk_harian')
                        ->where('kegiatan_id', $kegiatan['id']);

                    if (!empty($filter['tahun'])) {
                        $trxBuilder->where('tahun', (int) $filter['tahun']);
                    }
                    if (!empty($filter['triwulan'])) {
                        $trxBuilder->where('triwulan <=', (int) $filter['triwulan']);
                    }
                    if (!empty($filter['bulan'])) {
                        $trxBuilder->where('bulan <=', (int) $filter['bulan']);
                    }

                    $kegiatan['transaksis'] = $trxBuilder
                        ->orderBy('period_start_date', 'DESC')
                        ->orderBy('id', 'DESC')
                        ->get()->getResultArray();
                }
                $indikator['kegiatans'] = $kegiatans;
            }
            $sasaran['indikators'] = $indikators;
        }
        return $sasarans;
    }

    public function getAvailableYears(): array
    {
        $currentYear = (int) date('Y');
        $rows = $this->db->table('txn_ikk_harian')
            ->select('DISTINCT(tahun) as tahun')
            ->where('tahun IS NOT NULL')
            ->orderBy('tahun', 'DESC')
            ->get()->getResultArray();

        $years = array_filter(array_map('intval', array_column($rows, 'tahun')));
        if (!in_array($currentYear, $years)) {
            $years[] = $currentYear;
        }
        rsort($years);
        return array_values(array_unique($years));
    }
}
