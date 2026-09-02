<?php

namespace App\Models\Apps\Services;

use CodeIgniter\Model;

class PNBPDocumentModel extends Model
{
    protected $table            = 'txn_pnbp_documents';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'uid',
        'doc_type',
        'doc_number',
        'doc_date',
        'seleksi_id',
        'tilok_id',
        'instansi_id',
        'title',
        'status',
        'meta_data',
        'pdf_file_path',
        'generated_at',
        'created_by',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public static array $docTypeLabels = [
        'sp'               => 'Surat Perintah (SP)',
        'st'               => 'Surat Tugas (ST)',
        'nominatif'        => 'Daftar Nominatif',
        'kwitansi'         => 'Kwitansi Perjalanan Dinas',
        'hadir'            => 'Daftar Hadir Petugas',
        'kwitansi_jamuan'  => 'Kwitansi Jamuan',
        'surat_jalan'      => 'Surat Jalan Jamuan',
        'faktur'           => 'Faktur Jamuan',
        'hadir_jamuan'     => 'Daftar Hadir Jamuan',
    ];

    public static array $docTypeDetails = [
        'sp' => [
            'number'      => 1,
            'title'       => 'Surat Perintah (SP)',
            'short'       => 'SP',
            'category'    => 'Kepegawaian & Tim',
            'category_key'=> 'personel',
            'desc'        => 'Dokumen perintah resmi dari Kepala Kantor Regional untuk penugasan tim fasilitasi CAT.',
            'color'       => '#2563eb',
            'bg_light'    => '#eff6ff',
        ],
        'st' => [
            'number'      => 2,
            'title'       => 'Surat Tugas (ST)',
            'short'       => 'ST',
            'category'    => 'Kepegawaian & Tim',
            'category_key'=> 'personel',
            'desc'        => 'Surat tugas kedinasan personil pelaksana di titik lokasi ujian seleksi CAT.',
            'color'       => '#0891b2',
            'bg_light'    => '#ecfeff',
        ],
        'nominatif' => [
            'number'      => 3,
            'title'       => 'Daftar Nominatif',
            'short'       => 'Nominatif',
            'category'    => 'Keuangan & Biaya',
            'category_key'=> 'personel',
            'desc'        => 'Daftar rincian biaya uang harian, uang transport, dan rekening personil pelaksana.',
            'color'       => '#059669',
            'bg_light'    => '#ecfdf5',
        ],
        'kwitansi' => [
            'number'      => 4,
            'title'       => 'Kwitansi Perjalanan Dinas',
            'short'       => 'Kwitansi Perjadin',
            'category'    => 'Keuangan & Biaya',
            'category_key'=> 'personel',
            'desc'        => 'Bukti pembayaran dan tanda terima sah pengeluaran honor/perjadin tim.',
            'color'       => '#d97706',
            'bg_light'    => '#fffbeb',
        ],
        'hadir' => [
            'number'      => 5,
            'title'       => 'Daftar Hadir Petugas',
            'short'       => 'Daftar Hadir',
            'category'    => 'Kepegawaian & Tim',
            'category_key'=> 'personel',
            'desc'        => 'Presensi dan bukti kehadiran fisik tim pelaksana selama kegiatan di titik lokasi.',
            'color'       => '#4f46e5',
            'bg_light'    => '#eef2ff',
        ],
        'kwitansi_jamuan' => [
            'number'      => 6,
            'title'       => 'Kwitansi Jamuan',
            'short'       => 'Kwitansi Jamuan',
            'category'    => 'Konsumsi & Katering',
            'category_key'=> 'jamuan',
            'desc'        => 'Bukti pembayaran resmi kepada penyedia katering untuk belanja jamuan/konsumsi kegiatan.',
            'color'       => '#ea580c',
            'bg_light'    => '#fff7ed',
        ],
        'surat_jalan' => [
            'number'      => 7,
            'title'       => 'Surat Jalan Jamuan',
            'short'       => 'Surat Jalan',
            'category'    => 'Konsumsi & Katering',
            'category_key'=> 'jamuan',
            'desc'        => 'Berita acara bukti pengiriman barang konsumsi/snack box dari rekanan ke tilok.',
            'color'       => '#0284c7',
            'bg_light'    => '#f0f9ff',
        ],
        'faktur' => [
            'number'      => 8,
            'title'       => 'Faktur Jamuan (Invoice)',
            'short'       => 'Faktur Jamuan',
            'category'    => 'Konsumsi & Katering',
            'category_key'=> 'jamuan',
            'desc'        => 'Faktur tagihan belanja makanan/snack dari penyedia katering dengan rincian menu.',
            'color'       => '#7c3aed',
            'bg_light'    => '#f5f3ff',
        ],
        'hadir_jamuan' => [
            'number'      => 9,
            'title'       => 'Daftar Hadir Jamuan',
            'short'       => 'Hadir Jamuan',
            'category'    => 'Konsumsi & Katering',
            'category_key'=> 'jamuan',
            'desc'        => 'Daftar bukti serah terima dan distribusi konsumsi makanan/minuman kepada petugas.',
            'color'       => '#db2777',
            'bg_light'    => '#fdf2f8',
        ],
    ];

    public static array $docTypeBadges = [
        'sp'               => 'badge-primary',
        'st'               => 'badge-info',
        'nominatif'        => 'badge-success',
        'kwitansi'         => 'badge-warning',
        'hadir'            => 'badge-secondary',
        'kwitansi_jamuan'  => 'badge-warning',
        'surat_jalan'      => 'badge-info',
        'faktur'           => 'badge-primary',
        'hadir_jamuan'     => 'badge-secondary',
    ];

    /**
     * Query Builder untuk DataTables / Card List
     */
    public function getListBuilder(array $params = [])
    {
        $builder = $this->db->table('txn_pnbp_documents a')
            ->select('
                a.*,
                s.nama_seleksi,
                s.periode AS seleksi_periode,
                s.uid AS seleksi_uid,
                t.nama_tilok,
                t.period_start_date,
                t.period_end_date,
                t.kapasitas AS tilok_kapasitas,
                t.uid AS tilok_uid,
                i.nama AS instansi_nama,
                jt.nama AS jenis_tes_nama,
                jt.kode AS jenis_tes_kode,
                (SELECT COUNT(id) FROM txn_pnbp_doc_personel WHERE document_id = a.id) AS total_personel,
                (SELECT COUNT(id) FROM txn_pnbp_doc_items WHERE document_id = a.id) AS total_items,
                (SELECT COUNT(id) FROM txn_pnbp_doc_signatures WHERE document_id = a.id) AS total_signatures,
                (SELECT COUNT(id) FROM txn_pnbp_doc_signatures WHERE document_id = a.id AND sign_status = "signed") AS total_signed
            ')
            ->join('txn_cat_seleksi s', 's.id = a.seleksi_id', 'left')
            ->join('txn_cat_tilok t', 't.id = a.tilok_id', 'left')
            ->join('data_support_jenis_tes jt', 'jt.id = s.jenis_tes_id', 'left')
            ->join('data_instansi i', 'i.kodeins = a.instansi_id', 'left');

        // Filter keyword pencarian
        $keyword = trim((string) ($params['keyword'] ?? ''));
        if ($keyword !== '') {
            $builder->groupStart()
                ->like('a.doc_number', $keyword)
                ->orLike('a.title', $keyword)
                ->orLike('s.nama_seleksi', $keyword)
                ->orLike('t.nama_tilok', $keyword)
                ->orLike('i.nama', $keyword)
                ->groupEnd();
        }

        // Filter tipe dokumen
        $docType = trim((string) ($params['doc_type'] ?? ''));
        if ($docType !== '' && $docType !== 'all') {
            $builder->where('a.doc_type', $docType);
        }

        // Filter status
        $status = trim((string) ($params['status'] ?? ''));
        if ($status !== '' && $status !== 'all') {
            $builder->where('a.status', $status);
        }

        // Filter Seleksi ID
        if (!empty($params['seleksi_id'])) {
            $builder->where('a.seleksi_id', (int) $params['seleksi_id']);
        }

        // Filter Tilok ID
        if (!empty($params['tilok_id'])) {
            $builder->where('a.tilok_id', (int) $params['tilok_id']);
        }

        // Filter Tahun Dokumen
        if (!empty($params['tahun'])) {
            $builder->where('YEAR(a.doc_date)', (int) $params['tahun']);
        }

        // Sorting default: update terbaru
        $builder->orderBy('a.updated_at', 'DESC');
        $builder->orderBy('a.created_at', 'DESC');

        return $builder;
    }

    /**
     * Mengambil ringkasan metrik status dokumen PNBP
     */
    public function getSummaryMetrics(array $params = []): array
    {
        $builder = $this->db->table('txn_pnbp_documents a');
        $builder->select("
            COUNT(a.id) AS total_docs,
            SUM(CASE WHEN a.status = 'draft' THEN 1 ELSE 0 END) AS total_draft,
            SUM(CASE WHEN a.status = 'generated' THEN 1 ELSE 0 END) AS total_generated,
            SUM(CASE WHEN a.status = 'final' THEN 1 ELSE 0 END) AS total_final,
            SUM(CASE WHEN a.doc_type IN ('sp', 'st', 'nominatif', 'kwitansi', 'hadir') THEN 1 ELSE 0 END) AS total_administrasi,
            SUM(CASE WHEN a.doc_type IN ('kwitansi_jamuan', 'surat_jalan', 'faktur', 'hadir_jamuan') THEN 1 ELSE 0 END) AS total_jamuan,
            MAX(COALESCE(a.updated_at, a.created_at)) AS last_update
        ", false);

        if (!empty($params['seleksi_id'])) {
            $builder->where('a.seleksi_id', (int) $params['seleksi_id']);
        }
        if (!empty($params['tilok_id'])) {
            $builder->where('a.tilok_id', (int) $params['tilok_id']);
        }

        $row = $builder->get()->getRowArray();
        return $row ?: [
            'total_docs'         => 0,
            'total_draft'        => 0,
            'total_generated'    => 0,
            'total_final'        => 0,
            'total_administrasi' => 0,
            'total_jamuan'       => 0,
            'last_update'        => null,
        ];
    }

    /**
     * Mengambil detail lengkap 1 dokumen beserta seluruh children (personel, items, signatures)
     */
    public function getFullDocumentDetail(string $uid): ?array
    {
        $doc = $this->db->table('txn_pnbp_documents a')
            ->select('
                a.*,
                s.nama_seleksi,
                s.periode AS seleksi_periode,
                s.uid AS seleksi_uid,
                t.nama_tilok,
                t.period_start_date,
                t.period_end_date,
                t.kapasitas AS tilok_kapasitas,
                t.uid AS tilok_uid,
                i.nama AS instansi_nama,
                jt.nama AS jenis_tes_nama,
                jt.kode AS jenis_tes_kode
            ')
            ->join('txn_cat_seleksi s', 's.id = a.seleksi_id', 'left')
            ->join('txn_cat_tilok t', 't.id = a.tilok_id', 'left')
            ->join('data_support_jenis_tes jt', 'jt.id = s.jenis_tes_id', 'left')
            ->join('data_instansi i', 'i.kodeins = a.instansi_id', 'left')
            ->where('a.uid', $uid)
            ->get()
            ->getRowArray();

        if (!$doc) {
            return null;
        }

        $docId = (int) $doc['id'];

        // Decode JSON meta_data
        $doc['meta_data'] = !empty($doc['meta_data']) ? json_decode($doc['meta_data'], true) : [];

        // Ambil data personel
        $doc['personel'] = $this->db->table('txn_pnbp_doc_personel')
            ->where('document_id', $docId)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        // Ambil data items jamuan
        $doc['items'] = $this->db->table('txn_pnbp_doc_items')
            ->where('document_id', $docId)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        // Ambil data presensi attendees
        $doc['attendees'] = $this->db->table('txn_pnbp_doc_attendees')
            ->where('document_id', $docId)
            ->orderBy('tanggal_hadir', 'ASC')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        // Ambil data signatures
        $doc['signatures'] = $this->db->table('txn_pnbp_doc_signatures')
            ->where('document_id', $docId)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        return $doc;
    }

    /**
     * Mengambil daftar Event Seleksi untuk dropdown / Select2
     */
    public function getSeleksiOptions(): array
    {
        return $this->db->table('txn_cat_seleksi a')
            ->select('a.id, a.uid, a.nama_seleksi, a.periode, b.kode AS jenis_tes_kode, b.nama AS jenis_tes_nama')
            ->join('data_support_jenis_tes b', 'b.id = a.jenis_tes_id', 'left')
            ->orderBy('a.periode', 'DESC')
            ->orderBy('a.nama_seleksi', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Mengambil daftar Titik Lokasi berdasarkan seleksi_id
     */
    public function getTilokOptionsBySeleksi(int $seleksiId): array
    {
        return $this->db->table('txn_cat_tilok a')
            ->select('a.id, a.uid, a.nama_tilok, a.period_start_date, a.period_end_date, a.kapasitas')
            ->where('a.seleksi_id', $seleksiId)
            ->orderBy('a.nama_tilok', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Lookup data master pegawai (data_pegawai) untuk Select2 autocomplete
     */
    public function searchPegawai(string $keyword, int $limit = 30): array
    {
        $builder = $this->db->table('data_pegawai a')
            ->select('
                a.id,
                a.nip,
                a.nama,
                a.jabatan,
                a.status_pegawai_id AS status_pegawai,
                g.nama AS gol,
                a.email,
                a.phone
            ')
            ->join('data_pegawai_golongan g', 'g.id = a.gol_id', 'left')
            ->where('a.is_status', 1);

        $keyword = trim($keyword);
        if ($keyword !== '') {
            $builder->groupStart()
                ->like('a.nip', $keyword)
                ->orLike('a.nama', $keyword)
                ->orLike('a.jabatan', $keyword)
                ->groupEnd();
        }

        return $builder->orderBy('a.nama', 'ASC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * Mengambil statistik jumlah dokumen per jenis dokumen untuk katalog dokumen PNBP
     */
    public function getDocTypeStats(array $docTypeKeys = []): array
    {
        $rows = $this->db->table($this->table)
            ->select('doc_type, COUNT(id) AS total_count, SUM(CASE WHEN status = "draft" THEN 1 ELSE 0 END) AS draft_count, SUM(CASE WHEN status IN ("generated", "final") THEN 1 ELSE 0 END) AS generated_count')
            ->groupBy('doc_type')
            ->get()
            ->getResultArray();

        if (empty($docTypeKeys)) {
            if ($this->db->tableExists('data_pnbp_doc_types')) {
                $activeRows = $this->db->table('data_pnbp_doc_types')->select('doc_type')->where('is_status', 1)->get()->getResultArray();
                $docTypeKeys = array_column($activeRows, 'doc_type');
            }
            if (empty($docTypeKeys)) {
                $docTypeKeys = array_keys(self::$docTypeLabels);
            }
        }

        $stats = [];
        foreach ($docTypeKeys as $key) {
            $stats[$key] = [
                'total'     => 0,
                'draft'     => 0,
                'generated' => 0,
            ];
        }

        foreach ($rows as $r) {
            $t = $r['doc_type'];
            if (isset($stats[$t])) {
                $stats[$t]['total']     = (int) $r['total_count'];
                $stats[$t]['draft']     = (int) $r['draft_count'];
                $stats[$t]['generated'] = (int) $r['generated_count'];
            }
        }

        return $stats;
    }

    /**
     * Mengambil daftar instansi dari data_instansi untuk dropdown form
     */
    public function getInstansiOptions(string $keyword = '', int $limit = 200): array
    {
        $builder = $this->db->table('data_instansi a')
            ->select('a.kodeins, a.nama, a.kanreg, a.wilker')
            ->where('a.is_status', 1);

        $keyword = trim($keyword);
        if ($keyword !== '') {
            $builder->groupStart()
                ->like('a.nama', $keyword)
                ->orLike('a.kodeins', $keyword)
                ->groupEnd();
        }

        return $builder->orderBy('a.nama', 'ASC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * Hapus dokumen beserta seluruh child tables secara atomik
     */
    public function deleteDocumentWithChildren(string $uid): bool
    {
        $doc = $this->where('uid', $uid)->first();
        if (!$doc) {
            return false;
        }

        $docId = (int) $doc['id'];

        $this->db->transStart();
        $this->db->table('txn_pnbp_doc_signatures')->where('document_id', $docId)->delete();
        $this->db->table('txn_pnbp_doc_attendees')->where('document_id', $docId)->delete();
        $this->db->table('txn_pnbp_doc_items')->where('document_id', $docId)->delete();
        $this->db->table('txn_pnbp_doc_personel')->where('document_id', $docId)->delete();
        $this->db->table('txn_pnbp_documents')->where('id', $docId)->delete();
        $this->db->transComplete();

        return $this->db->transStatus();
    }
}
