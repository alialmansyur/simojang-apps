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

    public function getIndikatorBuilder(array $filter = [])
    {
        $tahun = !empty($filter['tahun']) ? (int) $filter['tahun'] : null;
        $triwulan = !empty($filter['triwulan']) ? (int) $filter['triwulan'] : null;

        $subRealWhere = '';
        if ($tahun !== null) {
            $subRealWhere .= " AND r.tahun = {$tahun}";
        }
        if ($triwulan !== null) {
            $subRealWhere .= " AND r.triwulan = {$triwulan}";
        }

        $selectReal = "
            (SELECT r.progres_capaian FROM txn_ikk_indikator r 
             WHERE r.indikator_id = a.id {$subRealWhere} 
             ORDER BY r.tahun DESC, r.triwulan DESC LIMIT 1) AS periode_progres,
            (SELECT r.jumlah_realisasi FROM txn_ikk_indikator r 
             WHERE r.indikator_id = a.id {$subRealWhere} 
             ORDER BY r.tahun DESC, r.triwulan DESC LIMIT 1) AS periode_realisasi,
            (SELECT r.target_periode FROM txn_ikk_indikator r 
             WHERE r.indikator_id = a.id {$subRealWhere} 
             ORDER BY r.tahun DESC, r.triwulan DESC LIMIT 1) AS periode_target,
            (SELECT r.triwulan FROM txn_ikk_indikator r 
             WHERE r.indikator_id = a.id {$subRealWhere} 
             ORDER BY r.tahun DESC, r.triwulan DESC LIMIT 1) AS realisasi_tw,
            (SELECT r.tahun FROM txn_ikk_indikator r 
             WHERE r.indikator_id = a.id {$subRealWhere} 
             ORDER BY r.tahun DESC, r.triwulan DESC LIMIT 1) AS realisasi_tahun,
            (SELECT r.bukti_dukung FROM txn_ikk_indikator r 
             WHERE r.indikator_id = a.id {$subRealWhere} 
             ORDER BY r.tahun DESC, r.triwulan DESC LIMIT 1) AS realisasi_bukti,
            (SELECT COUNT(*) FROM txn_ikk_indikator r WHERE r.indikator_id = a.id) AS total_realisasi_periods,
            (SELECT GROUP_CONCAT(DISTINCT t.nama ORDER BY t.nama SEPARATOR ', ') 
             FROM data_timkerja t 
             JOIN data_ikk_pengampu p ON p.timkerja_id = t.id 
             LEFT JOIN data_ikk_kegiatan k ON p.relasi_id = k.id AND p.tipe_relasi = 'kegiatan' 
             WHERE (p.tipe_relasi = 'indikator' AND p.relasi_id = a.id) 
                OR (p.tipe_relasi = 'kegiatan' AND k.indikator_id = a.id)) AS pengampu_list,
            (SELECT COUNT(DISTINCT p.timkerja_id) 
             FROM data_ikk_pengampu p 
             LEFT JOIN data_ikk_kegiatan k ON p.relasi_id = k.id AND p.tipe_relasi = 'kegiatan' 
             WHERE (p.tipe_relasi = 'indikator' AND p.relasi_id = a.id) 
                OR (p.tipe_relasi = 'kegiatan' AND k.indikator_id = a.id)) AS total_pengampu
        ";

        return $this->db->table('data_ikk_indikator a')
            ->select("a.id, a.sasaran_id, a.no_urut, a.nama_indikator, a.satuan, a.target_volume, a.created_at, a.updated_at, b.nama_sasaran,
                      (SELECT COUNT(*) FROM data_ikk_kegiatan k WHERE k.indikator_id = a.id) AS total_kegiatan,
                      {$selectReal}", false)
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

        // 1. Ambil seluruh data indikator beserta target volume induknya
        $indikators = $this->db->table('data_ikk_indikator')
            ->select('id, nama_indikator, target_volume, satuan')
            ->get()->getResultArray();

        // 2. Ambil data realisasi indikator (skema baru: txn_ikk_indikator)
        $indTrxBuilder = $this->db->table('txn_ikk_indikator');
        if (!empty($filter['tahun'])) {
            $indTrxBuilder->where('tahun', (int) $filter['tahun']);
        }
        if (!empty($filter['triwulan'])) {
            $indTrxBuilder->where('triwulan <=', (int) $filter['triwulan']);
        }
        $indTrxBuilder->orderBy('tahun', 'DESC')->orderBy('triwulan', 'DESC');
        $indTrxRows = $indTrxBuilder->get()->getResultArray();

        $realisasiByIndikator = [];
        foreach ($indTrxRows as $r) {
            $indId = (int) $r['indikator_id'];
            if (!isset($realisasiByIndikator[$indId])) {
                $realisasiByIndikator[$indId] = $r;
            }
        }

        // 3. Formulasi Capaian Utama Murni Berbasis Tabel Indikator Kinerja:
        // Membandingkan target dan realisasi per indikator
        $capaianIndikatorList = [];
        foreach ($indikators as $ind) {
            $indId = (int) $ind['id'];

            if (isset($realisasiByIndikator[$indId])) {
                $real = $realisasiByIndikator[$indId];
                $prog = (float) ($real['progres_capaian'] ?? 0);

                if ($prog > 0) {
                    $capaianIndikatorList[] = min(100.0, max(0.0, $prog));
                } elseif ($real['jumlah_realisasi'] !== null && (float) $real['jumlah_realisasi'] > 0) {
                    // Jika progres tidak diisi langsung, bandingkan jumlah_realisasi terhadap target periode atau target induk
                    $targetNum = $this->parseNumericVolume($real['target_periode']) 
                              ?: $this->parseNumericVolume($ind['target_volume']);
                    if ($targetNum !== null && $targetNum > 0) {
                        $calcProg = ((float) $real['jumlah_realisasi'] / $targetNum) * 100.0;
                        $capaianIndikatorList[] = min(100.0, max(0.0, $calcProg));
                    } else {
                        $capaianIndikatorList[] = 0.0;
                    }
                } else {
                    $capaianIndikatorList[] = 0.0;
                }
            } else {
                // Indikator belum diinput realisasinya
                $capaianIndikatorList[] = 0.0;
            }
        }

        // 4. Hitung rata-rata keseluruhan capaian instansi (dibagi total seluruh indikator instansi)
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

        // Penghitungan log transaksi harian kegiatan (hanya sebagai informasi log operasional pendukung)
        $trxCountBuilder = $this->db->table('txn_ikk_harian');
        if (!empty($filter['tahun'])) {
            $trxCountBuilder->where('tahun', (int) $filter['tahun']);
        }
        if (!empty($filter['triwulan'])) {
            $trxCountBuilder->where('triwulan <=', (int) $filter['triwulan']);
        }
        $totalTransaksi = $trxCountBuilder->countAllResults();

        return [
            'total_sasaran'       => (int) $totalSasaran,
            'total_indikator'     => (int) $totalIndikator,
            'total_kegiatan'      => (int) $totalKegiatan,
            'total_realisasi_ind' => (int) count($realisasiByIndikator),
            'total_transaksi'     => (int) $totalTransaksi,
            'avg_progres'         => $avgProgres,
            'tanpa_pengampu'      => (int) $tanpaPengampu,
            'filter_applied'      => $filter,
        ];
    }

    public function getAssignedPengampuIds(string $tipe_relasi, int $relasi_id): array
    {
        $rows = $this->db->table('data_ikk_pengampu')
            ->select('timkerja_id')
            ->where('tipe_relasi', $tipe_relasi)
            ->where('relasi_id', $relasi_id)
            ->get()->getResultArray();
        $ids = array_map('intval', array_column($rows, 'timkerja_id'));

        // Jika level indikator belum di-assign spesifik, ambil daftar tim kerja dari kegiatan operasionalnya
        if ($tipe_relasi === 'indikator' && empty($ids)) {
            $kegRows = $this->db->table('data_ikk_pengampu p')
                ->select('p.timkerja_id')
                ->join('data_ikk_kegiatan k', 'p.relasi_id = k.id AND p.tipe_relasi = "kegiatan"')
                ->where('k.indikator_id', $relasi_id)
                ->get()->getResultArray();
            $ids = array_unique(array_map('intval', array_column($kegRows, 'timkerja_id')));
        }

        return array_values($ids);
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
        $years = [];

        // Ambil tahun dari txn_ikk_harian
        $rows1 = $this->db->table('txn_ikk_harian')
            ->select('DISTINCT(tahun) as tahun')
            ->where('tahun IS NOT NULL')
            ->get()->getResultArray();
        foreach ($rows1 as $r) {
            if (!empty($r['tahun'])) $years[] = (int) $r['tahun'];
        }

        // Ambil tahun dari txn_ikk_indikator
        $rows2 = $this->db->table('txn_ikk_indikator')
            ->select('DISTINCT(tahun) as tahun')
            ->where('tahun IS NOT NULL')
            ->get()->getResultArray();
        foreach ($rows2 as $r) {
            if (!empty($r['tahun'])) $years[] = (int) $r['tahun'];
        }

        if (!in_array($currentYear, $years)) {
            $years[] = $currentYear;
        }

        $years = array_unique(array_filter($years));
        rsort($years);
        return array_values($years);
    }

    /**
     * Mengambil matriks realisasi 4 Triwulan untuk suatu indikator pada tahun tertentu
     */
    public function getIndikatorMatrix(int $indikatorId, int $tahun): array
    {
        $rows = $this->db->table('txn_ikk_indikator')
            ->where('indikator_id', $indikatorId)
            ->where('tahun', $tahun)
            ->orderBy('triwulan', 'ASC')
            ->get()->getResultArray();

        $twMap = [];
        foreach ($rows as $r) {
            $twMap[(int) $r['triwulan']] = $r;
        }

        $matrix = [];
        for ($tw = 1; $tw <= 4; $tw++) {
            $matrix[$tw] = [
                'triwulan'    => $tw,
                'tahun'       => $tahun,
                'periode_key' => sprintf('%04d-TW%d', $tahun, $tw),
                'has_data'    => isset($twMap[$tw]),
                'data'        => $twMap[$tw] ?? null,
            ];
        }

        return $matrix;
    }

    /**
     * Menghitung estimasi capaian agregat dari kegiatan operasional (Smart Assist)
     */
    public function calcAggregatedKegiatanProgress(int $indikatorId, ?int $tahun = null, ?int $triwulan = null): array
    {
        $kegiatans = $this->db->table('data_ikk_kegiatan')
            ->select('id, nama_kegiatan, satuan, target_volume')
            ->where('indikator_id', $indikatorId)
            ->get()->getResultArray();

        if (empty($kegiatans)) {
            return [
                'has_kegiatan'        => false,
                'total_kegiatan'      => 0,
                'suggested_progres'   => 0.0,
                'suggested_realisasi' => 0.0,
                'message'             => 'Indikator ini tidak memiliki kegiatan operasional terdaftar.',
            ];
        }

        $kegProgress = [];
        $totalRealSum = 0;
        foreach ($kegiatans as $keg) {
            $trxQuery = $this->db->table('txn_ikk_harian')
                ->where('kegiatan_id', $keg['id']);

            if (!empty($tahun)) {
                $trxQuery->where('tahun', (int) $tahun);
            }
            if (!empty($triwulan)) {
                $trxQuery->where('triwulan <=', (int) $triwulan);
            }

            $trxQuery->orderBy('period_start_date', 'DESC')->orderBy('id', 'DESC');
            $latestTrx = $trxQuery->get()->getRowArray();

            $sumRealQuery = $this->db->table('txn_ikk_harian')
                ->select('SUM(jumlah_realisasi) as sum_real')
                ->where('kegiatan_id', $keg['id']);
            if (!empty($tahun)) {
                $sumRealQuery->where('tahun', (int) $tahun);
            }
            if (!empty($triwulan)) {
                $sumRealQuery->where('triwulan <=', (int) $triwulan);
            }
            $sumRealRow = $sumRealQuery->get()->getRowArray();
            $sumReal = (float) ($sumRealRow['sum_real'] ?? 0);
            $totalRealSum += $sumReal;

            $targetNum = $this->parseNumericVolume($keg['target_volume']);
            if ($targetNum !== null && $targetNum > 0 && $sumReal > 0) {
                $prog = min(100.0, ($sumReal / $targetNum) * 100.0);
            } elseif ($latestTrx && $latestTrx['progres_realisasi'] !== null) {
                $prog = (float) $latestTrx['progres_realisasi'];
            } else {
                $prog = 0.0;
            }
            $kegProgress[] = $prog;
        }

        $avgProg = !empty($kegProgress) ? round(array_sum($kegProgress) / count($kegProgress), 2) : 0.0;

        return [
            'has_kegiatan'        => true,
            'total_kegiatan'      => count($kegiatans),
            'suggested_progres'   => $avgProg,
            'suggested_realisasi' => round($totalRealSum, 2),
            'message'             => 'Nilai berhasil dihitung otomatis dari ' . count($kegiatans) . ' log kegiatan operasional.',
        ];
    }
}
