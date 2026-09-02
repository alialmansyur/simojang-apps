<?php

namespace App\Models\Apps\Services;

use CodeIgniter\Model;

class PNBPDocTypeModel extends Model
{
    protected $table            = 'data_pnbp_doc_types';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'doc_type',
        'number',
        'title',
        'short_title',
        'category',
        'category_key',
        'description',
        'badge_class',
        'color',
        'bg_light',
        'icon_svg',
        'is_status',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Mengambil seluruh jenis dokumen yang berstatus aktif (is_status = 1)
     * Hasil di-index berdasarkan kode doc_type
     */
    public function getActiveDocTypes(): array
    {
        $rows = $this->where('is_status', 1)
            ->orderBy('number', 'ASC')
            ->findAll();

        $result = [];
        foreach ($rows as $row) {
            $result[$row['doc_type']] = $row;
        }
        return $result;
    }

    /**
     * Mengambil seluruh 9 jenis dokumen (baik aktif maupun nonaktif)
     * Hasil di-index berdasarkan kode doc_type
     */
    public function getAllDocTypes(): array
    {
        $rows = $this->orderBy('number', 'ASC')->findAll();

        $result = [];
        foreach ($rows as $row) {
            $result[$row['doc_type']] = $row;
        }
        return $result;
    }

    /**
     * Mengambil detail satu jenis dokumen berdasarkan kode doc_type
     */
    public function getDocTypeByCode(string $code): ?array
    {
        return $this->where('doc_type', $code)->first();
    }

    /**
     * Mengecek apakah kode jenis dokumen tertentu berstatus aktif
     */
    public function isDocTypeActive(string $code): bool
    {
        $doc = $this->where('doc_type', $code)->where('is_status', 1)->first();
        return !empty($doc);
    }

    /**
     * Mengambil associative array labels [doc_type => title]
     */
    public function getDocTypeLabels(bool $activeOnly = false): array
    {
        $builder = $this->builder()->select('doc_type, title')->orderBy('number', 'ASC');
        if ($activeOnly) {
            $builder->where('is_status', 1);
        }
        $rows = $builder->get()->getResultArray();

        $labels = [];
        foreach ($rows as $row) {
            $labels[$row['doc_type']] = $row['title'];
        }
        return $labels;
    }

    /**
     * Mengambil associative array badges [doc_type => badge_class]
     */
    public function getDocTypeBadges(bool $activeOnly = false): array
    {
        $builder = $this->builder()->select('doc_type, badge_class')->orderBy('number', 'ASC');
        if ($activeOnly) {
            $builder->where('is_status', 1);
        }
        $rows = $builder->get()->getResultArray();

        $badges = [];
        foreach ($rows as $row) {
            $badges[$row['doc_type']] = $row['badge_class'];
        }
        return $badges;
    }
}
