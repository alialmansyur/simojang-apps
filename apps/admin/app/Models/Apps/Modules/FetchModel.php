<?php

namespace App\Models\Apps\Modules;

use CodeIgniter\Model;

class FetchModel extends Model
{
    protected $table = 'txn_activity_upload_logs';

    public function __construct(){
        parent::__construct();
    }

    public function getDataUploader(int $layananId, array $bulan = [], string $docCategory = '')
    {
        $builder = $this->db->table('txn_activity_upload_logs a')
            ->select('a.*')
            ->where('a.layanan_id', $layananId)
            ->orderBy('a.created_at', 'DESC');

        if (!empty($bulan)) {
            $builder->whereIn('MONTH(a.created_at)', $bulan);
        }

        if ($docCategory !== '') {
            $builder->where('a.doc_category', $docCategory);
        }

        return $builder;
    }

    public function getDataUploaderDetail(int $uploadId)
    {
        return $this->db->table('txn_activity_upload_detail a')
            ->select('b.nama, b.logo, a.*')
            ->join('data_instansi b', 'b.kodeins = a.instansi_id', 'left')
            ->where('a.upload_id', $uploadId)
            ->orderBy('a.id', 'DESC');
    }

    public function getUploaderSummary(int $layananId, array $bulan = [], string $docCategory = '')
    {
        $applyFilters = function ($builder) use ($layananId, $bulan, $docCategory) {
            $builder->where('a.layanan_id', $layananId);

            if (!empty($bulan)) {
                $builder->whereIn('MONTH(a.created_at)', $bulan);
            }

            if ($docCategory !== '') {
                $builder->where('a.doc_category', $docCategory);
            }

            return $builder;
        };

        $uploadSummary = $applyFilters(
            $this->db->table('txn_activity_upload_logs a')
                ->select('
                    COUNT(1) AS total_file,
                    MAX(a.created_at) AS last_upload,
                    MIN(a.created_at) AS first_upload,
                    COUNT(DISTINCT DATE_FORMAT(a.created_at, "%Y-%m")) AS active_periods
                ')
        )->get()->getRowArray();

        $detailSummary = $applyFilters(
            $this->db->table('txn_activity_upload_logs a')
                ->select('
                    COUNT(d.id) AS total_baris_detail,
                    COUNT(DISTINCT d.instansi_id) AS total_instansi
                ')
                ->join('txn_activity_upload_detail d', 'd.upload_id = a.id', 'left')
        )->get()->getRowArray();

        return [
            'total_file' => (int) ($uploadSummary['total_file'] ?? 0),
            'total_baris_detail' => (int) ($detailSummary['total_baris_detail'] ?? 0),
            'total_instansi' => (int) ($detailSummary['total_instansi'] ?? 0),
            'last_upload' => $uploadSummary['last_upload'] ?? null,
            'first_upload' => $uploadSummary['first_upload'] ?? null,
            'active_periods' => (int) ($uploadSummary['active_periods'] ?? 0),
        ];
    }

}
