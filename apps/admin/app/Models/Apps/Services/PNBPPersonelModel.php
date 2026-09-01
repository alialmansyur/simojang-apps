<?php

namespace App\Models\Apps\Services;

use CodeIgniter\Model;

class PNBPPersonelModel extends Model
{
    protected $table            = 'txn_pnbp_doc_personel';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'document_id',
        'nip',
        'nama',
        'pangkat_gol',
        'jabatan',
        'peran',
        'jumlah_hari',
        'uang_harian',
        'transport',
        'total_biaya',
        'no_rekening',
        'sort_order',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getPersonelByDocumentId(int $docId): array
    {
        return $this->where('document_id', $docId)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    public function calculateTotalBudget(int $docId): array
    {
        $builder = $this->db->table($this->table);
        $builder->select("
            COUNT(id) AS total_personel,
            SUM(uang_harian * jumlah_hari) AS total_uang_harian,
            SUM(transport) AS total_transport,
            SUM(total_biaya) AS grand_total
        ", false);
        $builder->where('document_id', $docId);
        
        return $builder->get()->getRowArray() ?? [
            'total_personel'    => 0,
            'total_uang_harian' => 0,
            'total_transport'   => 0,
            'grand_total'       => 0,
        ];
    }
}
