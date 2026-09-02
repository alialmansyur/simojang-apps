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
        'nik',
        'jabatan',
        'peran',
        'jumlah_hari',
        'uang_harian',
        'transport',
        'total_biaya',
        'jumlah',
        'pajak_persen',
        'pajak_nominal',
        'jumlah_diterima',
        'no_rekening',
        'bank_nama',
        'status_pegawai',
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
            SUM(total_biaya) AS grand_total,
            SUM(jumlah) AS total_jumlah,
            SUM(pajak_nominal) AS total_pajak,
            SUM(jumlah_diterima) AS total_diterima
        ", false);
        $builder->where('document_id', $docId);
        
        return $builder->get()->getRowArray() ?? [
            'total_personel'    => 0,
            'total_uang_harian' => 0,
            'total_transport'   => 0,
            'grand_total'       => 0,
            'total_jumlah'      => 0,
            'total_pajak'       => 0,
            'total_diterima'    => 0,
        ];
    }

    /**
     * Cari data pegawai existing dari tabel data_pegawai beserta golongan
     */
    public function searchPegawai(string $keyword = '', int $limit = 50): array
    {
        $builder = $this->db->table('data_pegawai p');
        $builder->select('
            p.id,
            p.nip,
            p.nama,
            p.status_pegawai_id AS status_pegawai,
            p.jabatan,
            g.nama AS golongan,
            p.phone,
            p.email
        ');
        $builder->join('data_pegawai_golongan g', 'g.id = p.gol_id', 'left');
        $builder->where('p.is_status', 1);

        if ($keyword !== '') {
            $builder->groupStart()
                ->like('p.nama', $keyword)
                ->orLike('p.nip', $keyword)
                ->orLike('p.jabatan', $keyword)
                ->groupEnd();
        }

        $builder->orderBy('p.nama', 'ASC');
        $builder->limit($limit);

        return $builder->get()->getResultArray();
    }
}
