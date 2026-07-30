<?php

namespace App\Controllers\Apps\Services;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Exceptions\PageNotFoundException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Libraries\ExcelUploader;
use App\Libraries\DataTablesLib;

use App\Models\Apps\AppsModel;
use App\Models\Apps\Services\CATModel;

class FasilitasiCAT extends BaseController
{
    protected $catmodel;
    protected $apps;
    protected $uploader;
    protected $dataTables;

    public function __construct()
    {
        $this->catmodel     = new CATModel();
        $this->apps         = new AppsModel();
        $this->uploader     = new ExcelUploader();
        $this->dataTables   = new DataTablesLib();
        $sess = session()->get();
    }

    public function index(){
        $jenisRows = $this->apps->getCatJenisTes();
        $jenisOptions = [];
        foreach ($jenisRows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id <= 0) { continue; }
            $kode = trim((string) ($row['kode'] ?? ''));
            $nama = trim((string) ($row['nama'] ?? ''));
            $label = $kode !== '' ? ($kode . ' - ' . $nama) : $nama;
            $jenisOptions[$id] = $label;
        }

        $seleksiList = $this->catmodel->getBuilder('recap-seleksi')->get()->getResultArray();

        return $this->renderView('Apps/pages/services/cat/main', [
            'seslog' => session()->get(),
            'jenisOptions' => $jenisOptions,
            'seleksiList' => $seleksiList
        ]);            
    } 

    public function tilok($uid){
        $seleksi = $this->catmodel->db->table('txn_cat_seleksi')->where('uid', $uid)->get()->getRowArray();
        if (!$seleksi) {
            throw PageNotFoundException::forPageNotFound('Data Seleksi tidak ditemukan');
        }

        return $this->renderView('Apps/pages/services/cat/tilok', [
            'seslog' => session()->get(),
            'seleksi' => $seleksi
        ]);            
    }

    public function getJenisTesList()
    {
        $search = trim((string) $this->request->getGet('search'));
        $rows = $this->apps->getCatJenisTes($search);

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $rows,
        ]);
    }

    public function createJenisTes()
    {
        $kode = trim((string) $this->request->getPost('kode'));
        $nama = trim((string) $this->request->getPost('nama'));

        if ($kode === '' || $nama === '') {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'message' => 'Kode dan nama event wajib diisi.',
            ]);
        }

        if (strlen($kode) > 50 || strlen($nama) > 50) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'message' => 'Panjang kode/nama maksimal 50 karakter.',
            ]);
        }

        if ($this->apps->isCatJenisKodeExists($kode)) {
            return $this->response->setStatusCode(409)->setJSON([
                'status' => 'error',
                'message' => 'Kode event sudah digunakan.',
            ]);
        }

        if ($this->apps->isCatJenisNamaExists($nama)) {
            return $this->response->setStatusCode(409)->setJSON([
                'status' => 'error',
                'message' => 'Nama event sudah digunakan.',
            ]);
        }

        $id = $this->apps->createCatJenisTes($kode, $nama);
        if (!$id) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Gagal menambahkan event.',
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Event berhasil ditambahkan.',
            'id' => (int) $id,
        ]);
    }

    public function updateJenisTes()
    {
        $id = (int) $this->request->getPost('id');
        $kode = trim((string) $this->request->getPost('kode'));
        $nama = trim((string) $this->request->getPost('nama'));

        if ($id <= 0 || $kode === '' || $nama === '') {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'message' => 'ID, kode, dan nama event wajib diisi.',
            ]);
        }

        if (strlen($kode) > 50 || strlen($nama) > 50) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'message' => 'Panjang kode/nama maksimal 50 karakter.',
            ]);
        }

        if ($this->apps->isCatJenisKodeExists($kode, $id)) {
            return $this->response->setStatusCode(409)->setJSON([
                'status' => 'error',
                'message' => 'Kode event sudah digunakan.',
            ]);
        }

        if ($this->apps->isCatJenisNamaExists($nama, $id)) {
            return $this->response->setStatusCode(409)->setJSON([
                'status' => 'error',
                'message' => 'Nama event sudah digunakan.',
            ]);
        }

        $ok = $this->apps->updateCatJenisTes($id, $kode, $nama);
        if (!$ok) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Gagal memperbarui event.',
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Event berhasil diperbarui.',
        ]);
    }

    public function deleteJenisTes()
    {
        $id = (int) $this->request->getPost('id');
        if ($id <= 0) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'message' => 'ID event tidak valid.',
            ]);
        }

        $usedCount = $this->apps->countCatTilokByJenisTes($id);
        if ($usedCount > 0) {
            return $this->response->setStatusCode(409)->setJSON([
                'status' => 'error',
                'message' => 'Event tidak bisa dihapus karena sudah dipakai data tilok.',
            ]);
        }

        $ok = $this->apps->deleteCatJenisTes($id);
        if (!$ok) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Gagal menghapus event.',
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Event berhasil dihapus.',
        ]);
    }
    
    public function detail($param){
        $row = $this->catmodel->where('uid', $param)->first();
        if (!$row) {
            throw PageNotFoundException::forPageNotFound('Data tidak ditemukan');
        }

        return $this->renderView('Apps/pages/services/cat/detail', [
            'seslog' => session()->get(),
        ]);
    }     

    public function storeDataSeleksi()
    {
        $sess = session()->get();
        $key        = $this->request->getPost('key');
        $jenis      = $this->request->getPost('jenis');
        $nama       = $this->request->getPost('nama');
        $periode    = $this->request->getPost('periode');
    
        if (!$jenis || !$nama || !$periode) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data tidak lengkap. Semua kolom wajib diisi.'
            ]);
        }

        $dataInsert = [
            'jenis_tes_id'  => $jenis,
            'nama_seleksi'  => $nama,
            'periode'       => $periode,
            'created_by'    => $sess['username']
        ];

        if (!empty($key)) {
            $this->apps->updateDataByField('uid', $key, $dataInsert, 'txn_cat_seleksi');
        } else {
            $dataInsert['uid'] = bin2hex(random_bytes(16));
            $this->apps->storeData($dataInsert, 'txn_cat_seleksi');
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Data berhasil disimpan.'
        ]);
    }

    public function storeDataTilok()
    {
        $sess = session()->get();
        $action      = strtolower(trim((string) $this->request->getPost('action')));
        $key         = $this->request->getPost('key');
        $seleksi_uid = $this->request->getPost('seleksi_uid');
        $startdate   = $this->request->getPost('startdate');
        $enddate     = $this->request->getPost('enddate');
        $tilok       = $this->request->getPost('tilok');
        $capacity    = $this->request->getPost('capacity');
    
        if (!$seleksi_uid || !$startdate || !$enddate || !$tilok || !$capacity) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data tidak lengkap. Semua kolom wajib diisi.'
            ]);
        }

        $seleksi = $this->catmodel->db->table('txn_cat_seleksi')->where('uid', $seleksi_uid)->get()->getRowArray();
        if (!$seleksi) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data Seleksi tidak valid.'
            ]);
        }

        $dataInsert = [
            'seleksi_id'           => $seleksi['id'],
            'jenis_tes_id'         => $seleksi['jenis_tes_id'],
            'period'               => $seleksi['periode'],
            'period_start_date'    => $startdate,
            'period_end_date'      => $enddate,
            'nama_tilok'           => $tilok,
            'kapasitas'            => $capacity,
            'created_by'           => $sess['username']
        ];

        if ($action === 'update' && !empty($key)) {
            $existingRow = $this->catmodel->find((int) $key);
            if (!$existingRow) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 'error',
                    'message' => 'Data yang akan diperbarui tidak ditemukan.'
                ]);
            }

            $this->apps->updateData($dataInsert,$key,'txn_cat_tilok');
        } else {
            $this->apps->storeData($dataInsert, 'txn_cat_tilok');
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Data berhasil disimpan.'
        ]);
    }

    public function updateDataHasil()
    {
        $sess = session()->get();
        $key        = $this->request->getPost('key');
        $instansi   = $this->request->getPost('instansi');
        $tanggal    = $this->request->getPost('tanggal');
        $sesi       = $this->request->getPost('sesi');
        $nilai_min  = $this->request->getPost('nilai_min');
        $nilai_max  = $this->request->getPost('nilai_max');
        $hadir      = $this->request->getPost('hadir');
        $tidak_hadir= $this->request->getPost('tidak_hadir');
        $reschedule = $this->request->getPost('reschedule');

    
        if (!$instansi || !$tanggal || !$sesi) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data tidak lengkap. Semua kolom wajib diisi.'
            ]);
        }

        $dataInsert = [
            'instansi_id'   => $instansi,
            'period_date'   => $tanggal,
            'sesi'          => $sesi,
            'hadir'         => $hadir,
            'tidak_hadir'   => $tidak_hadir,
            'reschedule'    => $reschedule,
            'nilai_min'     => $nilai_min,
            'nilai_max'     => $nilai_max,
        ];

        $this->apps->updateData($dataInsert,$key,'txn_cat_hasil');

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Data berhasil disimpan.'
        ]);
    }

    public function storeDataRekap()
    {
        $sess = session()->get();
        $key        = $this->request->getPost('key');
        $instansi   = $this->request->getPost('instansi');
        $tanggal    = $this->request->getPost('tanggal');
        $sesi       = $this->request->getPost('sesi');
        $nilai_min  = $this->request->getPost('nilai_min');
        $nilai_max  = $this->request->getPost('nilai_max');
        $hadir      = $this->request->getPost('hadir');
        $tidak_hadir= $this->request->getPost('tidak_hadir');
        $reschedule = $this->request->getPost('reschedule');
        $memenuhi = $this->request->getPost('memenuhi');
        $tidak_memenuhi = $this->request->getPost('tidak_memenuhi');
    
        if (!$tanggal || !$sesi) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data tidak lengkap. Semua kolom wajib diisi.'
            ]);
        } 

        $scema_group = bin2hex(random_bytes(8));

        $dataBatch = [];
        foreach ($instansi as $i => $n) {
            if (empty($n) || (empty($sesi[$i]))) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => "Baris ke-" . ($i + 1) . " tidak lengkap"
                ]);
            }

            $dataBatch[] = [
                'scema_group'   => $scema_group,
                'tilok_id'      => $key,
                'instansi_id'   => $n,
                'period_date'   => $tanggal[$i],
                'sesi'          => $sesi[$i],
                'hadir'         => $hadir[$i],
                'tidak_hadir'   => $tidak_hadir[$i],
                'reschedule'    => $reschedule[$i],
                'nilai_min'     => $nilai_min[$i],
                'nilai_max'     => $nilai_max[$i],
		'memenuhi '     => $memenuhi[$i],
		'tidak_memenuhi'     => $tidak_memenuhi[$i],
                'created_by'    => $sess['username'],
            ];
        }
 
        if ($dataBatch) {
            $this->apps->insertBatchData($dataBatch, 'txn_cat_hasil');
            $this->apps->storeData(
                [
                    'layanan_id' => 28,
                    'tanggal'    => date('Y-m-d'),
                    'created_by' => $sess['username']
                ],
                'activity_daily_logs'
            );            
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Data berhasil disimpan.'
        ]);
    }

    public function removeDataTilok(){
        $key  = trim((string) $this->request->getPost('key'));
        if ($key === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => false,
                'message' => 'Kunci data tidak valid',
            ]);
        }

        $this->apps->removeData($key,'txn_cat_tilok');
        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Data Berhasil di hapus',
        ]);
    }

    public function removeDataRekap(){
        $key  = trim((string) $this->request->getPost('key'));
        if ($key === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => false,
                'message' => 'Kunci data tidak valid',
            ]);
        }

        $this->apps->removeData($key,'txn_cat_hasil');
        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Data Berhasil di hapus',
        ]);
    }

    public function getDataSeleksi(){
        $tahun = $this->request->getPost('tahun');
        if (!is_array($tahun)) { $tahun = []; }
        $tahun = array_values(array_filter(array_map('intval', $tahun), static function ($item) {
            return $item >= 2000 && $item <= 2100;
        }));

        $builder = $this->catmodel->getBuilder('recap-seleksi', [
            'tahun' => $tahun
        ]);

        $columns = [
            ['data' => 'id', 'search' => false, 'order' => 'a.id'],
            ['data' => 'uid', 'search' => false, 'order' => false],
            ['data' => 'jenis_tes_id', 'search' => false, 'order' => false],
            ['data' => 'jenis_tes_kode', 'search' => 'b.kode', 'order' => 'b.kode'],
            ['data' => 'jenis_tes_nama', 'search' => 'b.nama', 'order' => 'b.nama'],
            ['data' => 'nama_seleksi', 'search' => 'a.nama_seleksi', 'order' => 'a.nama_seleksi'],
            ['data' => 'periode', 'search' => 'a.periode', 'order' => 'a.periode'],
            ['data' => 'created_at', 'search' => 'a.created_at', 'order' => 'a.created_at'],
            ['data' => 'created_by', 'search' => false, 'order' => false],
            ['data' => 'updated_at', 'search' => false, 'order' => false],
        ];
        $result = $this->dataTables->render($builder, $columns);
        return $this->response->setJSON($result);   
    }

    public function removeDataSeleksi(){
        $key  = trim((string) $this->request->getPost('key'));
        if ($key === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => false,
                'message' => 'Kunci data tidak valid',
            ]);
        }
        $this->apps->removeDataByField('uid', $key, 'txn_cat_seleksi');
        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Data Berhasil di hapus',
        ]);
    }

    public function getDataTilok(){
        $sess = session()->get();
        $seleksi_uid = $this->request->getPost('seleksi_uid');

        $builder = $this->catmodel->getBuilder('recap-tilok', [
            'seleksi_uid' => $seleksi_uid
        ]);

        $columns = [
            [
                'data' => 'id',
                'search' => false,
                'order' => 'a.id',
            ],
            [
                'data' => 'uid',
                'search' => false,
                'order' => false,
            ],
            [
                'data' => 'jenis_tes_id',
                'search' => false,
                'order' => false,
            ],
            [
                'data' => 'jenis_tes',
                'search' => ['b.kode', 'b.nama'],
                'order' => 'b.kode',
            ],
            [
                'data' => 'period',
                'search' => 'a.period',
                'order' => 'a.period',
            ],
            [
                'data' => 'period_start_date',
                'search' => false,
                'order' => false,
            ],
            [
                'data' => 'period_end_date',
                'search' => false,
                'order' => false,
            ],
            [
                'data' => 'nama_tilok',
                'search' => 'a.nama_tilok',
                'order' => 'a.nama_tilok',
            ],
            [
                'data' => 'kapasitas',
                'search' => false,
                'order' => false,
            ],
            [
                'data' => 'created_at',
                'search' => 'a.created_at',
                'order' => 'a.created_at',
            ],
            [
                'data' => 'created_by',
                'search' => false,
                'order' => false,
            ],
            [
                'data' => 'updated_at',
                'search' => false,
                'order' => false,
            ],
            [
                'data' => 'total_rekap',
                'search' => false,
                'order' => false,
            ],
        ];
        $result     = $this->dataTables->render($builder, $columns);
        return $this->response->setJSON($result);   
    } 

    public function getDataDetailTilok(){
        $sess = session()->get();
        $bulan = $this->request->getPost('bulan');        
        $key   = trim((string) $this->request->getPost('key'));

        if ($key === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'error' => 'Kunci data tidak valid'
            ]);
        }

        if (!is_array($bulan)) {
            $bulan = [];
        }

        if (count($bulan) > 6) {
            return $this->response->setJSON([
                'error' => 'Maksimal 6 bulan diperbolehkan'
            ])->setStatusCode(400);
        }

        $bulan = array_values(array_filter(array_map('intval', $bulan), static function ($item) {
            return $item >= 1 && $item <= 12;
        }));

        $builder    = $this->catmodel->getBuilder('recap-hasil', ['id' => $key,'bulan' => $bulan, 'instansi_id' => '']);
        $columns = [
            [
                'data' => 'id',
                'search' => false,
                'order' => 'a.id',
            ],
            [
                'data' => 'period',
                'search' => false,
                'order' => false,
            ],
            [
                'data' => 'nama_tilok',
                'search' => false,
                'order' => false,
            ],
            [
                'data' => 'jenis_tes',
                'search' => false,
                'order' => false,
            ],
            [
                'data' => 'instansi_nama',
                'search' => 'd.nama',
                'order' => 'd.nama',
            ],
            [
                'data' => 'instansi_id',
                'search' => false,
                'order' => false,
            ],
            [
                'data' => 'period_date',
                'search' => 'a.period_date',
                'order' => 'a.period_date',
            ],
            [
                'data' => 'sesi',
                'search' => 'a.sesi',
                'order' => 'a.sesi',
            ],
            [
                'data' => 'nilai_min',
                'search' => false,
                'order' => 'a.nilai_min',
            ],
            [
                'data' => 'nilai_max',
                'search' => false,
                'order' => 'a.nilai_max',
            ],
            [
                'data' => 'hadir',
                'search' => false,
                'order' => 'a.hadir',
            ],
            [
                'data' => 'tidak_hadir',
                'search' => false,
                'order' => 'a.tidak_hadir',
            ],
            [
                'data' => 'reschedule',
                'search' => false,
                'order' => 'a.reschedule',
            ],
            [
                'data' => 'created_by',
                'search' => 'a.created_by',
                'order' => 'a.created_by',
            ],
            [
                'data' => 'created_at',
                'search' => 'a.created_at',
                'order' => 'a.created_at',
            ],
            [
                'data' => 'updated_at',
                'search' => false,
                'order' => false,
            ],
            [
                'data' => 'scema_group',
                'search' => false,
                'order' => false,
            ],
            [
                'data' => 'tilok_id',
                'search' => false,
                'order' => false,
            ],
        ];
        $result     = $this->dataTables->render($builder, $columns);
        return $this->response->setJSON($result);   
    }

    public function getMetaDetailTilok()
    {
        $uid = trim((string) $this->request->getPost('key'));
        if ($uid === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'error' => 'Kunci data tidak valid'
            ]);
        }

        $meta = $this->catmodel->getDetailMeta($uid);
        if (!$meta) {
            return $this->response->setStatusCode(404)->setJSON([
                'error' => 'Data tilok tidak ditemukan'
            ]);
        }

        return $this->response->setJSON([
            'status' => true,
            'meta' => $meta,
        ]);
    }

    public function getSummaryDetailTilok()
    {
        $uid   = trim((string) $this->request->getPost('key'));
        $bulan = $this->request->getPost('bulan');

        if ($uid === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'error' => 'Kunci data tidak valid'
            ]);
        }

        if (!is_array($bulan)) {
            $bulan = [];
        }

        if (count($bulan) > 6) {
            return $this->response->setStatusCode(400)->setJSON([
                'error' => 'Maksimal 6 bulan diperbolehkan'
            ]);
        }

        $bulan = array_values(array_filter(array_map('intval', $bulan), static function ($item) {
            return $item >= 1 && $item <= 12;
        }));

        return $this->response->setJSON([
            'status' => true,
            'summary' => $this->catmodel->getSummaryDetail($uid, $bulan),
        ]);
    }

    public function getSummaryTilok()
    {
        $seleksi_uid = $this->request->getPost('seleksi_uid');

        return $this->response->setJSON([
            'status' => true,
            'summary' => $this->catmodel->getSummaryTilok($seleksi_uid),
        ]);
    }     
}
