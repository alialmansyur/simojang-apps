<?php

namespace App\Models\Apps\Services;

use CodeIgniter\Model;

class IkkModel extends Model
{
    protected $DBGroup = 'default';

    public function getSasaranBuilder()
    {
        return $this->db->table('data_ikk_sasaran')
            ->select('id, no_urut, nama_sasaran, created_at, updated_at');
    }

    public function getIndikatorBuilder()
    {
        return $this->db->table('data_ikk_indikator a')
            ->select('a.id, a.sasaran_id, a.no_urut, a.nama_indikator, a.satuan, a.target_volume, a.created_at, a.updated_at, b.nama_sasaran')
            ->join('data_ikk_sasaran b', 'a.sasaran_id = b.id', 'left');
    }

    public function getKegiatanBuilder()
    {
        return $this->db->table('data_ikk_kegiatan a')
            ->select('a.id, a.indikator_id, a.no_urut, a.nama_kegiatan, a.satuan, a.target_volume, a.created_at, a.updated_at, b.nama_indikator, c.nama_sasaran')
            ->join('data_ikk_indikator b', 'a.indikator_id = b.id', 'left')
            ->join('data_ikk_sasaran c', 'b.sasaran_id = c.id', 'left');
    }

    public function getPengampuBuilder()
    {
        return $this->db->table('data_ikk_pengampu a')
            ->select("a.id, a.tipe_relasi, a.relasi_id, a.timkerja_id, b.nama_timkerja, 
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
            ->select('a.id, a.kegiatan_id, a.pelaksana_kegiatan_harian, a.period_start_date, a.period_end_date, a.target_mingguan, a.jumlah_realisasi, a.progres_realisasi, a.bukti_pekerjaan, a.created_at, a.updated_at, b.nama_kegiatan, c.nama_indikator, d.nama_sasaran')
            ->join('data_ikk_kegiatan b', 'a.kegiatan_id = b.id', 'left')
            ->join('data_ikk_indikator c', 'b.indikator_id = c.id', 'left')
            ->join('data_ikk_sasaran d', 'c.sasaran_id = d.id', 'left');
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
        $builder = $this->db->table('data_ikk_kegiatan')->select('id, nama_kegiatan as text');
        if ($indikator_id) {
            $builder->where('indikator_id', $indikator_id);
        }
        return $builder->orderBy('no_urut', 'ASC')->get()->getResultArray();
    }

    public function getOptionsTimKerja()
    {
        // Assuming data_timkerja has id and nama_timkerja
        return $this->db->table('data_timkerja')
            ->select('id, nama_timkerja as text')
            ->orderBy('nama_timkerja', 'ASC')
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
}
