<?php

namespace App\Models\Apps\Services;

use CodeIgniter\Model;

class STKInternalModel extends Model
{
    protected $table = 'data_pegawai';

    public function __construct()
    {
        parent::__construct();
    }

    // ----------------------------
    //  QUERY BUILDER UTAMA 
    // ----------------------------    
    public function getBuilder($type, $param = null)
    {
        switch ($type) {
            case 'accum':
                return $this->getAccumulation($param);
            case 'pegawai':
                return $this->getDataRecap($param);
            case 'peta_jabatan':
                return $this->getPetaJabatan($param);
            default:
                throw new \Exception("Unknown builder type: $type");
        }
    }

    // ----------------------------
    //  DAPATKAN NAMA KOLOM OTOMATIS
    // ----------------------------    
    public function getColumns($type, $id = null)
    {
        $builder = $this->getBuilder($type, $id);
        $query = $builder->get();
        return $query->getFieldNames();
    }

    public function getPetaJabatan($params = [])
    {
        $unit = $params['unit'] ?? [];
        $whereUnit = '';

        if (!empty($unit) && is_array($unit)) {
            $escaped = array_map(
                fn($v) => $this->db->escape($v),
                $unit
            );
            $whereUnit = " AND EXISTS (SELECT 1 FROM data_pegawai_unit_kerja uk WHERE FIND_IN_SET(uk.id, p.unit_kerja_id) > 0 AND uk.nama IN (" . implode(',', $escaped) . "))";
        }

        $rawSql = "
            SELECT 
                j.id,
                j.nama AS jabatan,
                j.kelas_jabatan AS kl,
                COALESCE(j.kebutuhan, 0) AS b,
                COALESCE(agg.k, 0) AS k,
                (COALESCE(j.kebutuhan, 0) - COALESCE(agg.k, 0)) AS s
            FROM data_pegawai_jabatan j
            LEFT JOIN (
                SELECT p.jabatan_id, COUNT(p.id) AS k
                FROM data_pegawai p
                WHERE 1=1 AND p.is_status = 1 
                $whereUnit
                GROUP BY p.jabatan_id
            ) agg ON agg.jabatan_id = j.id
            ORDER BY j.is_order ASC, j.nama ASC
        ";

        return $this->db->table("($rawSql) AS peta");
    }

    public function getDataRecap($params = [])
    {
        $unit = $params['unit'] ?? [];
        $mode = $params['mode'] ?? 'pegawai';

        $whereSql = '';

        if (!empty($unit) && is_array($unit)) {
            // ?? ESCAPE VALUE AGAR AMAN
            $escaped = array_map(
                fn($v) => $this->db->escape($v),
                $unit
            );

            $whereSql = " WHERE EXISTS (SELECT 1 FROM data_pegawai_unit_kerja b WHERE FIND_IN_SET(b.id, a.unit_kerja_id) > 0 AND b.nama IN (" . implode(',', $escaped) . "))";
        }

        $rawSql = "
            SELECT  
                a.id,
                a.nip,
                a.nama,
                a.gender,
                a.tgl_lahir,
                a.jabatan_id,
                COALESCE(j.nama, a.jabatan) AS jabatan,
                a.menikah,
                a.status_pegawai_id,
                a.pendidikan_id,
                a.agama_id,
                a.unit_kerja_id,
                a.unit_sk_id,
                a.jenis_jabatan_id,
                a.gol_id,
                a.pangkat_id,
                a.tmt_gol,
                a.phone,
                a.email,
                a.updated_at,
                (SELECT GROUP_CONCAT(b.nama SEPARATOR ', ') FROM data_pegawai_unit_kerja b WHERE FIND_IN_SET(b.id, a.unit_kerja_id) > 0) AS unit_kerja,
                c.nama AS unit_sk,
                d.nama AS jenis_jabatan,
                e.nama AS pendidikan,
                f.nama AS agama,
                g.nama AS status_pegawai,
                h.nama AS pangkat,
                i.nama AS golongan,
                CASE
                    WHEN YEAR(a.tgl_lahir) BETWEEN 1946 AND 1964 THEN 'Baby Boomer'
                    WHEN YEAR(a.tgl_lahir) BETWEEN 1965 AND 1980 THEN 'Gen X'
                    WHEN YEAR(a.tgl_lahir) BETWEEN 1981 AND 1996 THEN 'Gen Y'
                    WHEN YEAR(a.tgl_lahir) BETWEEN 1997 AND 2012 THEN 'Gen Z'
                    WHEN YEAR(a.tgl_lahir) >= 2013 THEN 'Gen Alpha'
                    ELSE 'Tidak Diketahui'
                END AS generasi,
                CASE
                    WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(SUBSTRING(a.nip, 1, 8), '%Y%m%d'), CURDATE()) >= 58
                        THEN 'Sudah BUP'
                    WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(SUBSTRING(a.nip, 1, 8), '%Y%m%d'), CURDATE()) = 57
                        THEN 'Menjelang BUP'
                    ELSE 'Aktif'
                END AS status_bup
            FROM data_pegawai a
            LEFT JOIN data_pegawai_unit_sk c ON c.id = a.unit_sk_id
            LEFT JOIN data_pegawai_jenis_jabatan d ON d.id = a.jenis_jabatan_id
            LEFT JOIN data_pegawai_pendidikan e ON e.id = a.pendidikan_id
            LEFT JOIN data_pegawai_agama f ON f.id = a.agama_id
            LEFT JOIN data_pegawai_jenis_pegawai g ON g.id = a.status_pegawai_id
            LEFT JOIN data_pegawai_pangkat h ON h.id = a.pangkat_id
            LEFT JOIN data_pegawai_golongan i ON i.id = a.gol_id
            LEFT JOIN data_pegawai_jabatan j ON j.id = a.jabatan_id
            $whereSql
            ORDER BY a.nama ASC
        ";

        $builder = $this->db->table("($rawSql) AS recap");

        // ?? MODE FILTER
        if ($mode === 'pegawai') {
            $builder->where('status_bup', 'Aktif');
        } elseif ($mode === 'bup') {
            $builder->where('status_bup', 'Menjelang BUP');
        } elseif ($mode === 'telah_bup') {
            $builder->where('status_bup', 'Sudah BUP');
        }

        return $builder;
    }

    public function getAccumulation(array $unit = [])
    {
        $whereUnit = '';

        if (!empty($unit)) {
            $escaped = array_map(
                fn($v) => $this->db->escape(trim($v)),
                $unit
            );

            $whereUnit = " AND b.nama IN (" . implode(',', $escaped) . ") ";
        }

        $rawSql = "
            SELECT
                COUNT(*) AS total_pegawai,

                /* ===============================
                GENDER
                =============================== */
                SUM(a.gender = 1) AS total_pria,
                ROUND(SUM(a.gender = 1) / COUNT(*) * 100, 2) AS persen_pria,

                SUM(a.gender = 2) AS total_wanita,
                ROUND(SUM(a.gender = 2) / COUNT(*) * 100, 2) AS persen_wanita,

                /* ===============================
                GENERASI
                =============================== */
                SUM(YEAR(a.tgl_lahir) BETWEEN 1946 AND 1964) AS baby_boomer,
                SUM(YEAR(a.tgl_lahir) BETWEEN 1965 AND 1980) AS gen_x,
                SUM(YEAR(a.tgl_lahir) BETWEEN 1981 AND 1996) AS gen_y,
                SUM(YEAR(a.tgl_lahir) BETWEEN 1997 AND 2012) AS gen_z,
                SUM(YEAR(a.tgl_lahir) >= 2013) AS gen_alpha,

                /* ===============================
                MENJELANG BUP
                =============================== */
                SUM(
                    TIMESTAMPDIFF(YEAR, STR_TO_DATE(SUBSTRING(a.nip, 1, 8), '%Y%m%d'), CURDATE()) = 57
                ) AS total_menjelang_bup,

                /* ===============================
                TELAH BUP / PENSIUN
                =============================== */
                SUM(
                    TIMESTAMPDIFF(YEAR, STR_TO_DATE(SUBSTRING(a.nip, 1, 8), '%Y%m%d'), CURDATE()) >= 58
                ) AS total_telah_bup

            FROM data_pegawai a
            LEFT JOIN data_pegawai_unit_kerja b ON b.id = a.unit_kerja_id
            LEFT JOIN data_pegawai_unit_sk c ON c.id = a.unit_sk_id
            LEFT JOIN data_pegawai_jenis_jabatan d ON d.id = a.jenis_jabatan_id
            WHERE 1=1
            AND TIMESTAMPDIFF(YEAR, STR_TO_DATE(SUBSTRING(a.nip, 1, 8), '%Y%m%d'), CURDATE()) < 58
            $whereUnit
        ";

        return $this->db->query($rawSql)->getRowArray();
    }

    public function getSummary(array $unit = [], string $mode = 'pegawai')
    {
        $base = $this->getDataRecap([
            'unit' => $unit,
            'mode' => $mode,
        ]);

        return $this->db->table('(' . $base->getCompiledSelect() . ') x')
            ->select('
                COUNT(1) AS total_data,
                SUM(CASE WHEN x.gender = 1 THEN 1 ELSE 0 END) AS total_pria,
                SUM(CASE WHEN x.gender = 2 THEN 1 ELSE 0 END) AS total_wanita,
                MAX(x.updated_at) AS last_update
            ')
            ->get()
            ->getRowArray();
    }

public function getMasterData(string $table)
{
    $builder = $this->db->table($table)->select('*');

    if ($table == 'data_pegawai_jabatan') {
        $builder->orderBy('is_order', 'DESC')
                ->orderBy('nama', 'ASC');
    } else {
        $builder->orderBy('nama', 'ASC');
    }

    return $builder->get()->getResultArray();
}

    public function isDuplicateIntegrasi($nip)
    {
        return $this->db->table('data_pegawai')
            ->where('nip', $nip)
            ->countAllResults() > 0;
    }

}
