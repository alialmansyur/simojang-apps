<?php

namespace App\Models\Apps\Services;

use CodeIgniter\Model;

class ManageAssetsModel extends Model
{
    protected $table = 'data_assets';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'uid',
        'category_id',
        'kode',
        'subcode',
        'uraian',
        'satuan',
        'qty',
        'foto',
        'kondisi',
        'lokasi',
        'area',
        'tahun_perolehan',
        'nilai_perolehan',
        'keterangan',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

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
            case 'detail':
                return $this->getDataDetail($param);
            default:
                throw new \Exception("Unknown builder type: $type");
        } 
    }    

    // ----------------------------
    //  DEFINISI KOLOM DATATABLES
    // ----------------------------    
    public function getColumns($type, $id = null)
    {
        switch ($type) {
            case 'detail':
                return [
                    ['data' => 'foto', 'search' => null, 'order' => null],
                    ['data' => 'kode', 'search' => 'a.kode', 'order' => 'a.kode'],
                    ['data' => 'subcode', 'search' => 'a.subcode', 'order' => 'a.subcode'],
                    ['data' => 'uraian', 'search' => 'a.uraian', 'order' => 'a.uraian'],
                    ['data' => 'satuan', 'search' => 'a.satuan', 'order' => 'a.satuan'],
                    ['data' => 'qty', 'search' => 'a.qty', 'order' => 'a.qty'],
                    ['data' => 'nilai_perolehan', 'search' => 'a.nilai_perolehan', 'order' => 'a.nilai_perolehan'],
                    ['data' => 'kondisi', 'search' => 'a.kondisi', 'order' => 'a.kondisi'],
                    ['data' => 'area', 'search' => 'a.area', 'order' => 'a.area'],
                    ['data' => 'lokasi', 'search' => 'a.lokasi', 'order' => 'a.lokasi'],
                    ['data' => 'tahun_perolehan', 'search' => 'a.tahun_perolehan', 'order' => 'a.tahun_perolehan'],
                    ['data' => 'created_at', 'search' => 'a.created_at', 'order' => 'a.created_at'],
                ];
            default:
                throw new \Exception("Unknown builder type: $type");
        }
    }  

    // ----------------------------
    //  KATEGORI ASSET
    // ----------------------------
    public function getCategories()
    {
        $builder = $this->db->table('data_asset_categories a')
            ->select('a.*, COUNT(b.id) AS total_assets, COALESCE(SUM(b.qty), 0) AS total_qty')
            ->join('data_assets b', 'b.category_id = a.id AND b.deleted_at IS NULL', 'left')
            ->where('a.deleted_at IS NULL')
            ->groupBy('a.id')
            ->orderBy('a.name', 'ASC');

        return $builder->get()->getResultArray();
    }

    public function getCategoryByUid($uid)
    {
        return $this->db->table('data_asset_categories')
            ->where('uid', $uid)
            ->where('deleted_at IS NULL')
            ->get()
            ->getRowArray();
    }

    public function getCategoryById($id)
    {
        return $this->db->table('data_asset_categories')
            ->where('id', $id)
            ->where('deleted_at IS NULL')
            ->get()
            ->getRowArray();
    }

    public function insertCategory(array $data)
    {
        if (empty($data['uid'])) {
            $data['uid'] = sprintf(
                '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->table('data_asset_categories')->insert($data);
        return $data['uid'];
    }

    public function updateCategoryByUid($uid, array $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->table('data_asset_categories')
            ->where('uid', $uid)
            ->update($data);
    }

    public function deleteCategoryByUid($uid)
    {
        return $this->db->table('data_asset_categories')
            ->where('uid', $uid)
            ->delete();
    }

    public function countAssetsByCategoryUid($categoryUid)
    {
        return (int) $this->db->table('data_assets a')
            ->join('data_asset_categories b', 'b.id = a.category_id')
            ->where('b.uid', $categoryUid)
            ->where('a.deleted_at IS NULL')
            ->countAllResults();
    }

    // ----------------------------
    //  DETAIL ASSET
    // ----------------------------
    public function getDataDetail($categoryUid = null)
    {
        $builder = $this->db->table('data_assets a')
            ->select('a.id, a.uid, a.category_id, a.kode, a.subcode, a.uraian, a.satuan, a.qty, a.foto, a.kondisi, a.area, a.lokasi, a.tahun_perolehan, a.nilai_perolehan, a.keterangan, a.created_at, a.updated_at')
            ->join('data_asset_categories b', 'b.id = a.category_id', 'left')
            ->where('a.deleted_at IS NULL');

        if ($categoryUid) {
            $builder->where('b.uid', $categoryUid);
        }

        $builder->orderBy('a.created_at', 'DESC');
        return $builder;
    }

    public function getSummary($categoryUid = null)
    {
        $builder = $this->db->table('data_assets a')
            ->select('
                COUNT(1) AS total_data,
                COALESCE(SUM(a.qty), 0) AS total_qty,
                COALESCE(SUM(a.qty * a.nilai_perolehan), 0) AS total_nilai,
                MAX(a.created_at) AS last_update
            ')
            ->join('data_asset_categories b', 'b.id = a.category_id', 'left')
            ->where('a.deleted_at IS NULL');

        if ($categoryUid) {
            $builder->where('b.uid', $categoryUid);
        }

        $row = $builder->get()->getRowArray();
        return [
            'total_data'  => (int) ($row['total_data'] ?? 0),
            'total_qty'   => (float) ($row['total_qty'] ?? 0),
            'total_nilai' => (float) ($row['total_nilai'] ?? 0),
            'last_update' => $row['last_update'] ?? null,
        ];
    }

    public function insertDetail(array $data)
    {
        if (empty($data['uid'])) {
            $data['uid'] = sprintf(
                '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->table($this->table)->insert($data);
    }

    public function updateDetail($id, array $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->table($this->table)->where('id', $id)->update($data);
    }

    public function deleteDetail($id)
    {
        return $this->db->table($this->table)->where('id', $id)->delete();
    }

    public function getDetailById($id)
    {
        return $this->db->table($this->table)
            ->where('id', $id)
            ->where('deleted_at IS NULL')
            ->get()
            ->getRowArray();
    }

    public function getDetailByUid($uid)
    {
        return $this->db->table($this->table)
            ->where('uid', $uid)
            ->where('deleted_at IS NULL')
            ->get()
            ->getRowArray();
    }
}
