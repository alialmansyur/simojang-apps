<?php

namespace App\Models\Apps;

use CodeIgniter\Model;

class ActivityGalleryModel extends Model
{
    protected $table = 'data_kegiatan_galeri';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'uid', 'timkerja_id', 'judul', 'deskripsi', 'tanggal_kegiatan', 
        'file_name', 'file_original_name', 'file_size', 'file_ext', 'file_path',
        'created_at', 'updated_at'
    ];

    public function __construct(){
        parent::__construct();
    }

    public function getAllData($search = null, $timkerja_id = null, $bulan = null)
    {
        $builder = $this->db->table($this->table . ' a')
                        ->select('a.*, b.nama as team_name')
                        ->join('data_timkerja b', 'a.timkerja_id = b.id', 'left')
                        ->orderBy('a.tanggal_kegiatan', 'DESC')
                        ->orderBy('a.id', 'DESC');

        if (!empty($search)) {
            $builder->groupStart()
                    ->like('a.judul', $search)
                    ->orLike('a.deskripsi', $search)
                    ->groupEnd();
        }

        if (!empty($timkerja_id)) {
            $builder->where('a.timkerja_id', $timkerja_id);
        }

        if (!empty($bulan)) {
            // $bulan format is 'YYYY-MM'
            $builder->like('a.tanggal_kegiatan', $bulan, 'after');
        }

        return $builder->get()->getResultArray();
    }

    public function getTimKerja()
    {
        return $this->db->table('data_timkerja')
                        ->where('is_show', 1)
                        ->orderBy('nama', 'ASC')
                        ->get()
                        ->getResultArray();
    }
}
