<?php

namespace App\Models\Apps\Services;

use CodeIgniter\Model;

class PNBPSignerModel extends Model
{
    protected $table            = 'cfg_pnbp_signers';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'kode',
        'role_title',
        'default_nip',
        'default_nama',
        'default_pangkat_gol',
        'default_jabatan',
        'default_signature_path',
        'is_active',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getDefaultSignersMap(): array
    {
        $rows = $this->where('is_active', 1)->findAll();
        $map = [];
        foreach ($rows as $row) {
            $map[$row['kode']] = $row;
        }
        return $map;
    }
}
