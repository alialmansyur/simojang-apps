<?php

namespace App\Models\Apps\Services;

use CodeIgniter\Model;

class PNBPItemModel extends Model
{
    protected $table            = 'txn_pnbp_doc_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'document_id',
        'item_name',
        'spesifikasi',
        'quantity',
        'satuan',
        'harga_satuan',
        'total_harga',
        'sort_order',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getItemsByDocumentId(int $docId): array
    {
        return $this->where('document_id', $docId)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    public function calculateTotalItems(int $docId): array
    {
        $builder = $this->db->table($this->table);
        $builder->select("
            COUNT(id) AS total_item_count,
            SUM(quantity) AS total_qty,
            SUM(total_harga) AS grand_total
        ", false);
        $builder->where('document_id', $docId);

        return $builder->get()->getRowArray() ?? [
            'total_item_count' => 0,
            'total_qty'        => 0,
            'grand_total'      => 0,
        ];
    }
}
