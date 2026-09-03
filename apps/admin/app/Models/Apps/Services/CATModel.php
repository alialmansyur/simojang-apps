<?php

namespace App\Models\Apps\Services;

use CodeIgniter\Model;

class CATModel extends Model
{
    protected $table            = 'txn_cat_tilok'; 
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

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function getDataLog(){
        $builder = $this->db->table($this->table);
        return $builder;
    }

    // ----------------------------
    //  QUERY BUILDER UTAMA
    // ----------------------------    
    public function getBuilder($type, $param = null){
        switch ($type) {
            case 'recap-jenis-periode':
                return $this->getDataRecapJenisPeriode($param);
            case 'recap-seleksi':
                return $this->getDataRecapSeleksi($param);
            case 'recap-tilok':
                return $this->getDataRecap($param);
            case 'recap-hasil':
                return $this->getRecapHasil($param);                
            case 'detail':
                return $this->getDataDetail($param);
            default:
                throw new \Exception("Unknown builder type: $type");
        }
    }    

    // ----------------------------
    //  DAPATKAN NAMA KOLOM OTOMATIS
    // ----------------------------    
    public function getColumns($type, $param = null){
        $builder = $this->getBuilder($type, $param);
        $query = $builder->get();
        return $query->getFieldNames();
    }    

    public function getDataRecapJenisPeriode($params = [])
    {
        $tahun = $params['tahun'] ?? [];

        $builder = $this->db->table('txn_cat_jenis_periode a')
            ->select('
                a.*, 
                b.kode AS jenis_tes_kode, 
                b.nama AS jenis_tes_nama, 
                COALESCE(NULLIF(b.nama, ""), b.kode, "Jenis Tes CAT") AS display_nama,
                COUNT(DISTINCT t.id) AS total_tilok,
                SUM(COALESCE(th.hadir, 0) + COALESCE(th.tidak_hadir, 0)) AS total_peserta,
                SUM(COALESCE(th.hadir, 0)) AS total_hadir,
                SUM(COALESCE(th.tidak_hadir, 0)) AS total_tidak_hadir,
                MAX(th.period_date) AS last_rekap_date,
                MAX(th.created_at) AS last_rekap_created,
                MAX(th.updated_at) AS last_rekap_updated,
                MAX(t.created_at) AS last_tilok_created,
                MAX(t.updated_at) AS last_tilok_updated,
                CASE 
                    WHEN MAX(th.period_date) >= CURDATE() THEN 1 
                    ELSE 0 
                END AS is_ongoing
            ')
            ->join('data_support_jenis_tes b', 'b.id = a.jenis_tes_id', 'inner')
            ->join('txn_cat_tilok t', 't.jenis_periode_id = a.id OR (t.jenis_tes_id = a.jenis_tes_id AND t.period LIKE CONCAT(a.periode, "%"))', 'left')
            ->join('txn_cat_hasil th', 'th.tilok_id = t.id', 'left')
            ->groupBy('a.id');

        if (!empty($tahun)) {
            $builder->whereIn('a.periode', (array)$tahun);
        }

        $builder->orderBy('a.periode', 'DESC');
        $builder->orderBy('is_ongoing', 'DESC');
        $builder->orderBy('last_rekap_date', 'DESC');
        $builder->orderBy('b.kode', 'ASC');
        return $builder;
    }

    public function getDataRecapSeleksi($params = [])
    {
        $tahun = $params['tahun'] ?? [];

        $builder = $this->db->table('txn_cat_seleksi a')
            ->select('
                a.*, 
                b.kode AS jenis_tes_kode, 
                b.nama AS jenis_tes_nama, 
                MAX(th.period_date) AS last_rekap_date,
                MAX(th.created_at) AS last_rekap_created,
                MAX(th.updated_at) AS last_rekap_updated,
                MAX(t.created_at) AS last_tilok_created,
                MAX(t.updated_at) AS last_tilok_updated
            ')
            ->join('data_support_jenis_tes b', 'b.id = a.jenis_tes_id', 'left')
            ->join('txn_cat_tilok t', 't.seleksi_id = a.id', 'left')
            ->join('txn_cat_hasil th', 'th.tilok_id = t.id', 'left')
            ->groupBy('a.id');

        if (!empty($tahun)) {
            $builder->whereIn('a.periode', $tahun);
        }

        $builder->orderBy('last_rekap_date', 'DESC');
        $builder->orderBy('a.created_at', 'DESC');
        return $builder;
    }

    public function getDataRecap($params = [])
    {
        $jenis_periode_id = $params['jenis_periode_id'] ?? null;
        $jenis_tes_id     = $params['jenis_tes_id'] ?? null;
        $period           = $params['period'] ?? null;

        $builder = $this->db->table('txn_cat_tilok a')
            ->select('
                a.*, 
                b.kode AS jenis_tes, 
                b.nama AS jenis_tes_nama,
                b.nama AS periode_nama_jenis,
                jp.periode AS periode_tahun,
                (SELECT COUNT(DISTINCT ti.instansi_id) FROM txn_cat_tilok_instansi ti WHERE ti.tilok_id = a.id) as total_instansi,
                (SELECT COUNT(DISTINCT h.seleksi_id) 
                 FROM txn_cat_tilok_instansi ti 
                 JOIN txn_cat_hasil h ON h.tilok_id = ti.tilok_id AND h.instansi_id = ti.instansi_id 
                 WHERE ti.tilok_id = a.id AND h.seleksi_id IS NOT NULL AND h.seleksi_id > 0) as total_event,
                (SELECT COUNT(h.id) 
                 FROM txn_cat_tilok_instansi ti 
                 JOIN txn_cat_hasil h ON h.tilok_id = ti.tilok_id AND h.instansi_id = ti.instansi_id 
                 WHERE ti.tilok_id = a.id) as total_rekap,
                (SELECT SUM(COALESCE(h.hadir, 0) + COALESCE(h.tidak_hadir, 0)) 
                 FROM txn_cat_tilok_instansi ti 
                 JOIN txn_cat_hasil h ON h.tilok_id = ti.tilok_id AND h.instansi_id = ti.instansi_id 
                 WHERE ti.tilok_id = a.id) as total_peserta,
                (SELECT SUM(COALESCE(h.hadir, 0)) 
                 FROM txn_cat_tilok_instansi ti 
                 JOIN txn_cat_hasil h ON h.tilok_id = ti.tilok_id AND h.instansi_id = ti.instansi_id 
                 WHERE ti.tilok_id = a.id) as total_hadir,
                (SELECT MAX(h.period_date) 
                 FROM txn_cat_tilok_instansi ti 
                 JOIN txn_cat_hasil h ON h.tilok_id = ti.tilok_id AND h.instansi_id = ti.instansi_id 
                 WHERE ti.tilok_id = a.id) as last_rekap_date,
                (SELECT MAX(COALESCE(h.updated_at, h.created_at)) 
                 FROM txn_cat_tilok_instansi ti 
                 JOIN txn_cat_hasil h ON h.tilok_id = ti.tilok_id AND h.instansi_id = ti.instansi_id 
                 WHERE ti.tilok_id = a.id) as last_rekap_updated
            ')
            ->join('txn_cat_jenis_periode jp', 'jp.id = a.jenis_periode_id', 'left')
            ->join('data_support_jenis_tes b', 'b.id = a.jenis_tes_id', 'left');

        if (!empty($jenis_periode_id)) {
            $builder->where('a.jenis_periode_id', (int) $jenis_periode_id);
        } else {
            if (!empty($jenis_tes_id)) {
                $builder->where('a.jenis_tes_id', (int) $jenis_tes_id);
            }
            if (!empty($period)) {
                $builder->groupStart()
                    ->where('a.period', $period)
                    ->orLike('a.period', $period, 'after')
                    ->groupEnd();
            }
        }

        $builder->orderBy('last_rekap_updated', 'DESC');
        $builder->orderBy('last_rekap_date', 'DESC');
        $builder->orderBy('a.created_at', 'DESC');
        return $builder;
    }

    public function getRecapHasil($params = [])
    {
        $id          = $params['id'] ?? null;
        $bulan       = $params['bulan'] ?? [];
        $instansi_id = $params['instansi_id'] ?? [];

        $builder = $this->db->table('txn_cat_hasil a')
            ->select('
                b.period,
                b.nama_tilok,
                c.nama AS jenis_tes,
                s.nama_seleksi,
                a.*,
                d.nama AS instansi_nama
            ')
            ->join('txn_cat_tilok b', 'b.id = a.tilok_id', 'left')
            ->join('txn_cat_seleksi s', 's.id = a.seleksi_id', 'left')
            ->join('data_support_jenis_tes c', 'c.id = a.jenis_tes_id', 'left')
            ->join('data_instansi d', 'd.kodeins = a.instansi_id', 'left')
            ->orderBy('a.period_date', 'DESC')
            ->orderBy('a.sesi', 'ASC')
            ->orderBy('a.created_at', 'DESC')
            ->where('b.uid', $id);

        if (!empty($bulan)) {
            $builder->whereIn('MONTH(a.period_date)', $bulan);
        }

        $tanggal = $params['tanggal'] ?? null;
        if (!empty($tanggal)) {
            $builder->where('a.period_date', $tanggal);
        }

        $sesi = $params['sesi'] ?? null;
        if (!empty($sesi)) {
            $builder->where('a.sesi', $sesi);
        }

        $seleksi_id = $params['seleksi_id'] ?? null;
        if (!empty($seleksi_id)) {
            $builder->where('a.seleksi_id', $seleksi_id);
        }
        if (!empty($instansi_id)) {
            $builder->whereIn('a.instansi_id', $instansi_id);
        }

        return $builder;
    }

    public function getInstansiLastUpdate($tilokUid, $instansiId = null, $seleksiId = null)
    {
        $builder = $this->db->table('txn_cat_hasil a')
            ->select('MAX(COALESCE(a.updated_at, a.created_at)) AS last_update')
            ->join('txn_cat_tilok b', 'b.id = a.tilok_id', 'left')
            ->where('b.uid', $tilokUid);

        if (!empty($instansiId)) {
            $builder->whereIn('a.instansi_id', (array)$instansiId);
        }
        if (!empty($seleksiId)) {
            $builder->where('a.seleksi_id', $seleksiId);
        }

        $row = $builder->get()->getRow();
        return $row->last_update ?? null;
    }

    public function getSummaryTilok($jenis_tes_id = null)
    {
        $builder = $this->db->table('txn_cat_tilok a');
        $builder->select("
            COUNT(a.id) AS total_tilok,
            SUM(COALESCE(a.kapasitas, 0)) AS total_kapasitas,
            COUNT(DISTINCT a.jenis_tes_id) AS total_jenis_tes,
            COUNT(DISTINCT a.period) AS total_periode,
            MAX(COALESCE(a.updated_at, a.created_at)) AS last_update
        ", false);

        if (!empty($jenis_tes_id)) {
            $builder->where('a.jenis_tes_id', $jenis_tes_id);
        }

        return $builder->get()->getRowArray() ?? [
            'total_tilok' => 0,
            'total_kapasitas' => 0,
            'total_jenis_tes' => 0,
            'total_periode' => 0,
            'last_update' => null,
        ];
    }

    public function getDetailMeta(string $uid): ?array
    {
        $uid = trim($uid);
        if ($uid === '') {
            return null;
        }

        $builder = $this->db->table('txn_cat_tilok a')
            ->select('a.id, a.uid, a.jenis_tes_id, a.jenis_periode_id, a.period, a.nama_tilok, a.period_start_date, a.period_end_date, a.kapasitas, a.created_at, b.nama AS jenis_tes_nama, b.kode AS jenis_tes, COALESCE(jp.uid, s.uid) AS seleksi_uid, COALESCE(jp.uid, s.uid) AS jenis_periode_uid, COALESCE(b.nama, s.nama_seleksi) AS nama_seleksi')
            ->join('data_support_jenis_tes b', 'b.id = a.jenis_tes_id', 'left')
            ->join('txn_cat_jenis_periode jp', 'jp.id = a.jenis_periode_id', 'left')
            ->join('txn_cat_seleksi s', 's.id = a.seleksi_id', 'left');

        if (is_numeric($uid)) {
            $builder->groupStart()
                ->where('a.id', (int) $uid)
                ->orWhere('a.uid', $uid)
                ->groupEnd();
        } else {
            $builder->where('a.uid', $uid);
        }

        $row = $builder->get()->getRowArray();

        return $row ?: null;
    }

    public function getSummaryDetail(string $uid, array $bulan = []): array
    {
        $builder = $this->db->table('txn_cat_hasil a');
        $builder->select("
            COUNT(a.id) AS total_rekap,
            COUNT(DISTINCT a.instansi_id) AS total_instansi,
            SUM(COALESCE(a.hadir, 0)) AS total_hadir,
            SUM(COALESCE(a.tidak_hadir, 0)) AS total_tidak_hadir,
            SUM(COALESCE(a.hadir, 0) + COALESCE(a.tidak_hadir, 0)) AS total_peserta,
            MAX(COALESCE(a.updated_at, a.created_at)) AS last_update
        ", false);
        $builder->join('txn_cat_tilok b', 'b.id = a.tilok_id', 'inner');
        $builder->where('b.uid', $uid);

        if (!empty($bulan)) {
            $builder->whereIn('MONTH(a.period_date)', $bulan);
        }

        return $builder->get()->getRowArray() ?? [
            'total_rekap' => 0,
            'total_instansi' => 0,
            'total_hadir' => 0,
            'total_tidak_hadir' => 0,
            'total_peserta' => 0,
            'last_update' => null,
        ];
    }

    public function getInstansiTilokGrouped(string $tilokUid, array $bulan = []): array
    {
        $builder = $this->db->table('txn_cat_tilok_instansi ti');
        $builder->select("
            ti.instansi_id,
            COALESCE(d.nama, ti.instansi_id) AS instansi_nama,
            d.logo,
            COUNT(a.id) AS total_sesi,
            SUM(COALESCE(a.hadir, 0)) AS total_hadir,
            SUM(COALESCE(a.tidak_hadir, 0)) AS total_tidak_hadir,
            SUM(COALESCE(a.reschedule, 0)) AS total_reschedule,
            SUM(COALESCE(a.hadir, 0) + COALESCE(a.tidak_hadir, 0)) AS total_peserta,
            SUM(COALESCE(a.memenuhi, 0)) AS total_memenuhi,
            SUM(COALESCE(a.tidak_memenuhi, 0)) AS total_tidak_memenuhi,
            MIN(a.nilai_min) AS min_nilai,
            MAX(a.nilai_max) AS max_nilai,
            MIN(a.period_date) AS min_date,
            MAX(a.period_date) AS max_date,
            MAX(COALESCE(a.updated_at, a.created_at)) AS last_update
        ", false);
        $builder->join('txn_cat_tilok b', 'b.id = ti.tilok_id', 'inner');
        $builder->join('txn_cat_hasil a', 'a.tilok_id = b.id AND a.instansi_id = ti.instansi_id', 'left');
        $builder->join('data_instansi d', 'd.kodeins = ti.instansi_id', 'left');
        $builder->where('b.uid', $tilokUid);

        if (!empty($bulan)) {
            $builder->whereIn('MONTH(a.period_date)', $bulan);
        }

        $builder->groupBy('ti.instansi_id, d.nama, d.logo');
        $builder->orderBy('last_update', 'DESC');
        $builder->orderBy('instansi_nama', 'ASC');

        return $builder->get()->getResultArray() ?? [];
    }

    public function getTimkerjaUidByUrl(string $url): string
    {
        $row = $this->db->table('data_timkerja_layanan a')
            ->select('b.uid')
            ->join('data_timkerja b', 'b.id = a.timkerja_id', 'inner')
            ->where('a.url', $url)
            ->get()->getRowArray();
        return !empty($row['uid']) ? $row['uid'] : 'a13e4110-7ccb-11f0-be4c-5f752d8309a4';
    }

    public function getJenisPeriodeDetail($idOrUid): ?array
    {
        $idOrUid = trim((string) $idOrUid);
        if ($idOrUid === '') {
            return null;
        }

        $builder = $this->db->table('txn_cat_jenis_periode jp')
            ->select('jp.*, jt.nama as jenis_tes_nama, jt.kode as jenis_tes_kode')
            ->join('data_support_jenis_tes jt', 'jt.id = jp.jenis_tes_id', 'left');

        if (is_numeric($idOrUid)) {
            $builder->groupStart()
                ->where('jp.id', (int) $idOrUid)
                ->orWhere('jp.uid', $idOrUid)
                ->groupEnd();
        } else {
            $builder->where('jp.uid', $idOrUid);
        }

        $catPeriode = $builder->get()->getRowArray();

        if ($catPeriode) {
            return $catPeriode;
        }

        // Fallback ke txn_cat_seleksi jika ID lama
        $sBuilder = $this->db->table('txn_cat_seleksi s')
            ->select('s.*, jt.nama as jenis_tes_nama, jt.kode as jenis_tes_kode')
            ->join('data_support_jenis_tes jt', 'jt.id = s.jenis_tes_id', 'left');

        if (is_numeric($idOrUid)) {
            $sBuilder->groupStart()
                ->where('s.id', (int) $idOrUid)
                ->orWhere('s.uid', $idOrUid)
                ->groupEnd();
        } else {
            $sBuilder->where('s.uid', $idOrUid);
        }

        $seleksi = $sBuilder->get()->getRowArray();

        if ($seleksi) {
            return [
                'id' => $seleksi['id'],
                'uid' => $seleksi['uid'],
                'jenis_tes_id' => $seleksi['jenis_tes_id'],
                'periode' => $seleksi['periode'],
                'jenis_tes_nama' => $seleksi['nama_seleksi'] ?: $seleksi['jenis_tes_nama'],
                'jenis_tes_kode' => $seleksi['jenis_tes_kode'],
            ];
        }

        // Fallback ke data_support_jenis_tes
        if (is_numeric($idOrUid)) {
            $jenisTes = $this->db->table('data_support_jenis_tes')->where('id', (int) $idOrUid)->get()->getRowArray();
            if ($jenisTes) {
                return [
                    'id' => '',
                    'uid' => '',
                    'jenis_tes_id' => $jenisTes['id'],
                    'periode' => date('Y'),
                    'jenis_tes_nama' => $jenisTes['nama'],
                    'jenis_tes_kode' => $jenisTes['kode'],
                ];
            }
        }

        return null;
    }

    public function getMasterJenisTes(int $id): ?array
    {
        return $this->db->table('data_support_jenis_tes')->where('id', $id)->get()->getRowArray();
    }

    public function checkDuplicateJenisPeriode(int $jenisTesId, string $periode, ?string $excludeUid = null): ?array
    {
        $q = $this->db->table('txn_cat_jenis_periode')
            ->where('jenis_tes_id', $jenisTesId)
            ->where('periode', $periode);
        if (!empty($excludeUid)) {
            $q->where('uid !=', $excludeUid);
        }
        return $q->get()->getRowArray();
    }

    public function saveJenisPeriode(array $data, ?string $uid = null): bool
    {
        if (!empty($uid)) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            return $this->db->table('txn_cat_jenis_periode')->where('uid', $uid)->update($data);
        }
        $data['uid'] = bin2hex(random_bytes(16));
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->table('txn_cat_jenis_periode')->insert($data);
    }

    public function deleteJenisPeriodeByUid(string $uid): bool
    {
        return $this->db->table('txn_cat_jenis_periode')->where('uid', $uid)->delete();
    }

    public function getJenisPeriodeRow($field, $value): ?array
    {
        return $this->db->table('txn_cat_jenis_periode')->where($field, $value)->get()->getRowArray();
    }

    public function saveTilok(array $data, $key = null): bool
    {
        if (!empty($key)) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            if (is_numeric($key)) {
                return $this->db->table('txn_cat_tilok')->where('id', $key)->update($data);
            }
            return $this->db->table('txn_cat_tilok')->where('uid', $key)->update($data);
        }
        if (empty($data['uid'])) {
            $data['uid'] = bin2hex(random_bytes(16));
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->table('txn_cat_tilok')->insert($data);
    }

    public function deleteTilokByKey($key): bool
    {
        if (is_numeric($key)) {
            return $this->db->table('txn_cat_tilok')->where('id', $key)->delete();
        }
        return $this->db->table('txn_cat_tilok')->where('uid', $key)->delete();
    }
}
