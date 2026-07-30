<?php

namespace App\Models\Apps\Services;

use CodeIgniter\Model;

class ManageAssetsModel extends Model
{
    protected $table = 'data_assets';

    public function __construct(){
        parent::__construct();
    }

    // ----------------------------
    //  QUERY BUILDER UTAMA
    // ----------------------------    
    public function getBuilder($type, $param = null){
        switch ($type) {
            case 'detail':
                return $this->getDataDetail($param);
            default:
                throw new \Exception("Unknown builder type: $type");
        } 
    }    

    // ----------------------------
    //  DAPATKAN NAMA KOLOM OTOMATIS
    // ----------------------------    
    public function getColumns($type, $id = null){
        $builder = $this->getBuilder($type, $id);
        $query = $builder->get();
        return $query->getFieldNames();
    }  

    public function getCategories()
    {
        $builder = $this->db->table('data_asset_categories a')
            ->select('a.*, COUNT(b.id) AS total_assets, SUM(b.qty) AS total_qty')
            ->join('data_assets b', 'b.category_id = a.id', 'left')
            ->groupBy('a.id')
            ->orderBy('a.name', 'ASC');

        return $builder->get()->getResultArray();
    }

    public function getCategoryByUid($uid)
    {
        return $this->db->table('data_asset_categories')
            ->where('uid', $uid)
            ->get()
            ->getRowArray();
    }

    public function getDataDetail($categoryUid = null)
    {
        $builder = $this->db->table('data_assets a')
            ->select('a.*')
            ->join('data_asset_categories b', 'b.id = a.category_id', 'left');

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
                SUM(COALESCE(qty, 0)) AS total_qty,
                MAX(a.created_at) AS last_update
            ')
            ->join('data_asset_categories b', 'b.id = a.category_id', 'left');

        if ($categoryUid) {
            $builder->where('b.uid', $categoryUid);
        }

        return $builder->get()->getRowArray();
    }

}
