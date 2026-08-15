<?php

namespace App\Models\Apps\Services;

use CodeIgniter\Model;

class LostAndFoundModel extends Model
{
    protected $table = 'txn_barang_hilang';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'nama_barang',
        'tanggal_ditemukan',
        'lokasi_ditemukan',
        'status_penyerahan',
        'tanggal_diserahkan',
        'penerima',
        'keterangan',
        'gambar',
        'created_by',
        'created_at'
    ];

    public function getBuilder($type = 'list', $filter = [])
    {
        $builder = $this->db->table($this->table);

        if (!empty($filter['status'])) {
            $builder->where('status_penyerahan', $filter['status']);
        }

        if (!empty($filter['bulan']) && is_array($filter['bulan'])) {
            $builder->groupStart();
            foreach ($filter['bulan'] as $b) {
                $builder->orWhere("MONTH(tanggal_ditemukan)", $b);
            }
            $builder->groupEnd();
        }

        return $builder;
    }

    public function getSummaryData($filter = [])
    {
        $builder = $this->db->table($this->table);

        if (!empty($filter['bulan']) && is_array($filter['bulan'])) {
            $builder->groupStart();
            foreach ($filter['bulan'] as $b) {
                $builder->orWhere("MONTH(tanggal_ditemukan)", $b);
            }
            $builder->groupEnd();
        }

        $builder->select('
            COUNT(id) as total_data,
            SUM(CASE WHEN status_penyerahan = "Diserahkan" THEN 1 ELSE 0 END) as total_diserahkan,
            SUM(CASE WHEN status_penyerahan != "Diserahkan" THEN 1 ELSE 0 END) as total_belum,
            MAX(created_at) as last_update
        ');

        return $builder->get()->getRowArray();
    }

    public function getColumns($type = 'list')
    {
        return [
            'id',
            'nama_barang',
            'tanggal_ditemukan',
            'lokasi_ditemukan',
            'status_penyerahan',
            'tanggal_diserahkan',
            'penerima',
            'keterangan',
            'gambar',
            'created_at'
        ];
    }
}
