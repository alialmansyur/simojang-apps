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
        $catJenisPeriodeList = $this->catmodel->getBuilder('recap-jenis-periode')->get()->getResultArray();
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

        $timkerjaUid = $this->catmodel->getTimkerjaUidByUrl('apps-cat');

        return $this->renderView('Apps/pages/services/cat/main', [
            'seslog' => session()->get(),
            'catJenisPeriodeList' => $catJenisPeriodeList,
            'jenisRows' => $jenisRows,
            'jenisOptions' => $jenisOptions,
            'timkerjaUid' => $timkerjaUid
        ]);            
    }

    public function tilok($id){
        $catPeriode = $this->catmodel->getJenisPeriodeDetail($id);

        if (!$catPeriode) {
            throw PageNotFoundException::forPageNotFound('Data Jenis Tes / Periode tidak ditemukan');
        }

        return $this->renderView('Apps/pages/services/cat/tilok', [
            'seslog' => session()->get(),
            'catPeriode' => $catPeriode,
            'jenisTes' => [
                'id' => $catPeriode['jenis_tes_id'],
                'nama' => $catPeriode['jenis_tes_nama'] ?? '',
                'kode' => $catPeriode['jenis_tes_kode'] ?? ''
            ],
            'periodeTahun' => $catPeriode['periode'] ?? date('Y')
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
        $param = trim((string) $param);
        $builder = $this->catmodel->db->table('txn_cat_tilok a');
        if (is_numeric($param)) {
            $builder->groupStart()->where('a.id', (int) $param)->orWhere('a.uid', $param)->groupEnd();
        } else {
            $builder->where('a.uid', $param);
        }
        $row = $builder->get()->getRowArray();
        if (!$row) {
            throw PageNotFoundException::forPageNotFound('Data tidak ditemukan');
        }

        $meta = $this->catmodel->getDetailMeta($row['uid'] ?? $param);

        return $this->renderView('Apps/pages/services/cat/detail', [
            'seslog' => session()->get(),
            'meta'   => $meta ?? $row,
        ]);
    }     

    public function storeDataSeleksi()
    {
        $sess       = session()->get();
        $key        = trim((string) $this->request->getPost('key'));
        $jenis      = (int) $this->request->getPost('jenis');
        $periode    = trim((string) $this->request->getPost('periode'));

        if ($jenis <= 0 || $periode === '') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Jenis tes dan tahun pelaksanaan wajib diisi.'
            ]);
        }

        // Ambil nama jenis tes dari model
        $masterJenis = $this->catmodel->getMasterJenisTes($jenis);
        if (!$masterJenis) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Jenis tes yang dipilih tidak valid.'
            ]);
        }

        $namaJenis = $masterJenis['nama'] ?: ($masterJenis['kode'] ?: 'Jenis Tes CAT');

        // Validasi: Cegah jenis tes yang sama terdaftar lebih dari sekali pada tahun/periode yang sama
        $existing = $this->catmodel->checkDuplicateJenisPeriode($jenis, $periode, $key !== '' ? $key : null);
        if ($existing) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => "Data sudah ada: Jenis tes '{$namaJenis}' sudah terdaftar untuk tahun pelaksanaan {$periode}."
            ]);
        }

        $dataInsert = [
            'jenis_tes_id'  => $jenis,
            'periode'       => $periode,
            'created_by'    => $sess['username'] ?? 'system'
        ];

        try {
            $this->catmodel->saveJenisPeriode($dataInsert, !empty($key) ? $key : null);
        } catch (\Throwable $e) {
            log_message('error', 'Gagal menyimpan jenis tes periode: ' . $e->getMessage());
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => "Data sudah ada: Jenis tes '{$namaJenis}' sudah terdaftar untuk tahun pelaksanaan {$periode}."
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Jenis tes untuk tahun ' . $periode . ' berhasil disimpan.'
        ]);
    }

    public function storeDataTilok()
    {
        $sess             = session()->get();
        $action           = strtolower(trim((string) $this->request->getPost('action')));
        $key              = $this->request->getPost('key');
        $jenis_periode_id = (int) $this->request->getPost('jenis_periode_id');
        $seleksi_uid      = trim((string) $this->request->getPost('seleksi_uid'));
        $jenis_tes_id     = (int) $this->request->getPost('jenis_tes_id');
        $period           = trim((string) ($this->request->getPost('period') ?? $this->request->getPost('periode')));
        $startdate        = $this->request->getPost('startdate') ?? $this->request->getPost('period_start_date');
        $enddate          = $this->request->getPost('enddate') ?? $this->request->getPost('period_end_date');
        $tilok            = trim((string) ($this->request->getPost('nama_tilok') ?? $this->request->getPost('tilok')));
        $capacity         = $this->request->getPost('kapasitas') ?? $this->request->getPost('capacity');

        // Cari jenis_periode jika belum ada
        if ($jenis_periode_id <= 0 && !empty($seleksi_uid)) {
            $catPeriode = $this->catmodel->getJenisPeriodeRow('uid', $seleksi_uid);
            if ($catPeriode) {
                $jenis_periode_id = (int) $catPeriode['id'];
                if ($jenis_tes_id <= 0) $jenis_tes_id = (int) $catPeriode['jenis_tes_id'];
                if (empty($period)) $period = $catPeriode['periode'];
            }
        }

        if ($jenis_periode_id > 0 && ($jenis_tes_id <= 0 || empty($period))) {
            $catPeriode = $this->catmodel->getJenisPeriodeRow('id', $jenis_periode_id);
            if ($catPeriode) {
                if ($jenis_tes_id <= 0) $jenis_tes_id = (int) $catPeriode['jenis_tes_id'];
                if (empty($period)) $period = $catPeriode['periode'];
            }
        }

        if (empty($tilok)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Nama titik lokasi wajib diisi.'
            ]);
        }

        $dataInsert = [
            'jenis_periode_id'     => $jenis_periode_id > 0 ? $jenis_periode_id : null,
            'jenis_tes_id'         => $jenis_tes_id > 0 ? $jenis_tes_id : null,
            'period'               => $period ?: date('Y'),
            'period_start_date'    => !empty($startdate) ? $startdate : null,
            'period_end_date'      => !empty($enddate) ? $enddate : null,
            'nama_tilok'           => $tilok,
            'kapasitas'            => !empty($capacity) ? (float) $capacity : 0,
            'created_by'           => $sess['username'] ?? 'system'
        ];

        $this->catmodel->saveTilok($dataInsert, !empty($key) ? $key : null);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Data titik lokasi berhasil disimpan.'
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

    public function storeDataInstansi()
    {
        $sess = session()->get();
        $tilokInput = trim((string) ($this->request->getPost('tilok_id') ?: $this->request->getPost('tilok_uid') ?: $this->request->getPost('key')));
        $instansi_id = trim((string) $this->request->getPost('instansi_id'));

        if ($tilokInput === '' || $instansi_id === '') {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Titik lokasi dan Instansi wajib dipilih.'
            ]);
        }

        // Resolve tilok_id jika berupa UUID
        $tilokBuilder = $this->apps->db->table('txn_cat_tilok')
            ->select('id, uid, nama_tilok');
        if (is_numeric($tilokInput)) {
            $tilokBuilder->groupStart()->where('id', (int) $tilokInput)->orWhere('uid', $tilokInput)->groupEnd();
        } else {
            $tilokBuilder->where('uid', $tilokInput);
        }
        $tilok = $tilokBuilder->get()->getRowArray();

        if (!$tilok) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Titik lokasi tidak ditemukan.'
            ]);
        }

        $tilok_id = (int) $tilok['id'];

        // Cek apakah sudah terdaftar di tilok ini
        $existing = $this->apps->db->table('txn_cat_tilok_instansi')
            ->where('tilok_id', $tilok_id)
            ->where('instansi_id', $instansi_id)
            ->get()->getRow();
            
        if (!$existing) {
            $this->apps->db->table('txn_cat_tilok_instansi')->insert([
                'tilok_id'    => $tilok_id,
                'instansi_id' => $instansi_id,
                'created_at'  => date('Y-m-d H:i:s')
            ]);
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Instansi berhasil ditambahkan ke titik lokasi.',
            'data'    => [
                'tilok_id'    => $tilok_id,
                'instansi_id' => $instansi_id
            ]
        ]);
    }

    public function storeDataRekap()
    {
        $sess = session()->get();
        $key        = $this->request->getPost('key'); // tilok_id or uid
        $seleksi_id = $this->request->getPost('seleksi_id');
        $jenis_tes_id = $this->request->getPost('jenis_tes_id');
        
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
    
        if (!$tanggal || !$sesi || !$seleksi_id || !$jenis_tes_id) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data tidak lengkap. Pastikan Event sudah dipilih.'
            ]);
        } 

        // Resolve tilok_id numerik jika dikirim sebagai UID
        $tilokBuilder = $this->apps->db->table('txn_cat_tilok')->select('id');
        if (is_numeric($key)) {
            $tilokBuilder->groupStart()->where('id', (int) $key)->orWhere('uid', $key)->groupEnd();
        } else {
            $tilokBuilder->where('uid', $key);
        }
        $tilokRow = $tilokBuilder->get()->getRowArray();
        $realTilokId = $tilokRow ? (int) $tilokRow['id'] : (int) $key;

        // Pastikan setiap instansi yang diisi terdaftar di txn_cat_tilok_instansi
        if (!empty($instansi) && is_array($instansi)) {
            $uniqueInstansi = array_unique(array_filter($instansi));
            foreach ($uniqueInstansi as $insId) {
                $existIns = $this->apps->db->table('txn_cat_tilok_instansi')
                    ->where('tilok_id', $realTilokId)
                    ->where('instansi_id', $insId)
                    ->get()->getRow();
                if (!$existIns) {
                    $this->apps->db->table('txn_cat_tilok_instansi')->insert([
                        'tilok_id'    => $realTilokId,
                        'instansi_id' => $insId,
                        'created_at'  => date('Y-m-d H:i:s')
                    ]);
                }
            }
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
                'tilok_id'      => $realTilokId,
                'seleksi_id'    => $seleksi_id,
                'jenis_tes_id'  => $jenis_tes_id,
                'instansi_id'   => $n,
                'period_date'   => $tanggal[$i],
                'sesi'          => $sesi[$i],
                'hadir'         => $hadir[$i],
                'tidak_hadir'   => $tidak_hadir[$i],
                'reschedule'    => $reschedule[$i],
                'nilai_min'     => $nilai_min[$i],
                'nilai_max'     => $nilai_max[$i],
		'memenuhi'      => $memenuhi[$i],
		'tidak_memenuhi'=> $tidak_memenuhi[$i],
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

        $this->catmodel->deleteTilokByKey($key);
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

    public function removeDataTilokInstansi()
    {
        $sess = session()->get();
        $tilokInput  = trim((string) ($this->request->getPost('tilok_id') ?: $this->request->getPost('tilok_uid') ?: $this->request->getPost('key')));
        $instansi_id = trim((string) $this->request->getPost('instansi_id'));

        if ($tilokInput === '' || $instansi_id === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => false,
                'message' => 'Parameter tilok dan instansi tidak valid'
            ]);
        }

        $tilokBuilder = $this->apps->db->table('txn_cat_tilok')->select('id');
        if (is_numeric($tilokInput)) {
            $tilokBuilder->groupStart()->where('id', (int) $tilokInput)->orWhere('uid', $tilokInput)->groupEnd();
        } else {
            $tilokBuilder->where('uid', $tilokInput);
        }
        $tilok = $tilokBuilder->get()->getRowArray();

        if (!$tilok) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => false,
                'message' => 'Titik lokasi tidak ditemukan'
            ]);
        }

        $tilokId = (int) $tilok['id'];

        // Hapus data dari txn_cat_tilok_instansi
        $this->apps->db->table('txn_cat_tilok_instansi')
            ->where('tilok_id', $tilokId)
            ->where('instansi_id', $instansi_id)
            ->delete();

        // Hapus juga rekap hasil sesi instansi ini pada tilok terkait
        $this->apps->db->table('txn_cat_hasil')
            ->where('tilok_id', $tilokId)
            ->where('instansi_id', $instansi_id)
            ->delete();

        $this->apps->storeData(
            [
                'layanan_id' => 28,
                'tanggal'    => date('Y-m-d'),
                'created_by' => $sess['username'] ?? 'system'
            ],
            'activity_daily_logs'
        );

        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Instansi dan data rekap terkait berhasil dihapus dari titik lokasi.',
        ]);
    }

    public function getSeleksiOptions()
    {
        $search = trim((string) ($this->request->getGet('search') ?? $this->request->getPost('search') ?? ''));
        $jenis_tes_id = trim((string) ($this->request->getGet('jenis_tes_id') ?? $this->request->getPost('jenis_tes_id') ?? ''));
        
        $builder = $this->apps->db->table('txn_cat_seleksi s')
            ->select('s.id, s.uid, s.nama_seleksi, s.periode, jt.kode as jenis_tes_kode')
            ->join('data_support_jenis_tes jt', 'jt.id = s.jenis_tes_id', 'left');
            
        if ($search !== '') {
            $builder->like('s.nama_seleksi', $search);
        }
        
        if ($jenis_tes_id !== '') {
            $builder->where('s.jenis_tes_id', $jenis_tes_id);
        }
        
        $builder->orderBy('s.periode', 'DESC');
        $builder->orderBy('s.nama_seleksi', 'ASC');
        $builder->limit(50);
        
        $rows = $builder->get()->getResultArray();
        
        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $rows,
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
        $this->catmodel->deleteJenisPeriodeByUid($key);
        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Data Berhasil di hapus',
        ]);
    }

    public function getDataTilok(){
        $sess             = session()->get();
        $jenis_periode_id = $this->request->getPost('jenis_periode_id');
        $jenis_tes_id     = $this->request->getPost('jenis_tes_id');
        $periode          = $this->request->getPost('periode') ?? $this->request->getPost('period');

        $builder = $this->catmodel->getBuilder('recap-tilok', [
            'jenis_periode_id' => $jenis_periode_id,
            'jenis_tes_id'     => $jenis_tes_id,
            'period'           => $periode
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
                'data' => 'total_instansi',
                'search' => false,
                'order' => false,
            ],
            [
                'data' => 'total_event',
                'search' => false,
                'order' => false,
            ],
            [
                'data' => 'total_rekap',
                'search' => false,
                'order' => false,
            ],
            [
                'data' => 'total_peserta',
                'search' => false,
                'order' => false,
            ],
            [
                'data' => 'total_hadir',
                'search' => false,
                'order' => false,
            ],
            [
                'data' => 'last_rekap_date',
                'search' => false,
                'order' => false,
            ],
            [
                'data' => 'last_rekap_updated',
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
        $key = trim((string) ($this->request->getPost('key') ?? $this->request->getGet('key') ?? ''));

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

        $instansi_id = $this->request->getPost('instansi_id');
        $filter_instansi = !empty($instansi_id) ? [$instansi_id] : [];

        $seleksi_id = $this->request->getPost('seleksi_id');
        $tanggal    = trim((string)$this->request->getPost('tanggal'));
        $sesi       = trim((string)$this->request->getPost('sesi'));

        $builder    = $this->catmodel->getBuilder('recap-hasil', [
            'id'          => $key,
            'bulan'       => $bulan,
            'instansi_id' => $filter_instansi,
            'seleksi_id'  => $seleksi_id,
            'tanggal'     => $tanggal,
            'sesi'        => $sesi,
        ]);
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
                'data' => 'memenuhi',
                'search' => false,
                'order' => 'a.memenuhi',
            ],
            [
                'data' => 'tidak_memenuhi',
                'search' => false,
                'order' => 'a.tidak_memenuhi',
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

        $lastUpdateVal = $this->catmodel->getInstansiLastUpdate($key, $filter_instansi, $seleksi_id);

        $result = $this->dataTables->render($builder, $columns);
        $result['last_update'] = $lastUpdateVal;
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
        $jenis_tes_id = $this->request->getPost('jenis_tes_id');

        return $this->response->setJSON([
            'status' => true,
            'summary' => $this->catmodel->getSummaryTilok($jenis_tes_id),
        ]);
    }

    public function getInstansiTilok()
    {
        $uid   = trim((string) $this->request->getPost('key'));
        $bulan = $this->request->getPost('bulan');

        if ($uid === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => false,
                'message' => 'Kunci data tidak valid'
            ]);
        }

        if (!is_array($bulan)) {
            $bulan = [];
        }

        if (count($bulan) > 6) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => false,
                'message' => 'Maksimal 6 bulan diperbolehkan'
            ]);
        }

        $bulan = array_values(array_filter(array_map('intval', $bulan), static function ($item) {
            return $item >= 1 && $item <= 12;
        }));

        $data = $this->catmodel->getInstansiTilokGrouped($uid, $bulan);

        return $this->response->setJSON([
            'status' => true,
            'data'   => $data,
        ]);
    }

    public function getEventsByInstansi()
    {
        $tilokUid = trim((string) $this->request->getPost('tilok_uid'));
        $instansiId = trim((string) $this->request->getPost('instansi_id'));

        if ($tilokUid === '' || $instansiId === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => false,
                'message' => 'Parameter tidak lengkap'
            ]);
        }

        $builder = $this->catmodel->db->table('txn_cat_hasil a');
        $builder->select('
            s.id AS seleksi_id, 
            s.uid AS seleksi_uid, 
            s.nama_seleksi, 
            s.periode, 
            jt.kode AS jenis_tes,
            COUNT(a.id) AS total_sesi,
            MAX(COALESCE(a.updated_at, a.created_at)) AS last_update
        ');
        $builder->join('txn_cat_tilok b', 'b.id = a.tilok_id', 'inner');
        $builder->join('txn_cat_seleksi s', 's.id = a.seleksi_id', 'inner');
        $builder->join('data_support_jenis_tes jt', 'jt.id = a.jenis_tes_id', 'left');
        $builder->where('b.uid', $tilokUid);
        $builder->where('a.instansi_id', $instansiId);
        $builder->groupBy('s.id, s.uid, s.nama_seleksi, s.periode, jt.kode');
        $builder->orderBy('last_update', 'DESC');

        $events = $builder->get()->getResultArray();

        return $this->response->setJSON([
            'status' => true,
            'data'   => $events
        ]);
    }

    public function updateEventSeleksi()
    {
        $sess = session()->get();
        $tilokInput     = trim((string) ($this->request->getPost('tilok_uid') ?: $this->request->getPost('tilok_id')));
        $instansiId     = trim((string) $this->request->getPost('instansi_id'));
        $oldSeleksiId   = (int) $this->request->getPost('old_seleksi_id');
        $newSeleksiId   = (int) $this->request->getPost('new_seleksi_id');

        if ($tilokInput === '' || $instansiId === '' || $oldSeleksiId <= 0 || $newSeleksiId <= 0) {
            return $this->response->setStatusCode(422)->setJSON([
                'status'  => 'error',
                'message' => 'Parameter tidak lengkap untuk mengubah event seleksi.'
            ]);
        }

        if ($oldSeleksiId === $newSeleksiId) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Tidak ada perubahan pada event seleksi.'
            ]);
        }

        // Resolve tilok_id
        $tilokBuilder = $this->apps->db->table('txn_cat_tilok')->select('id');
        if (is_numeric($tilokInput)) {
            $tilokBuilder->groupStart()->where('id', (int) $tilokInput)->orWhere('uid', $tilokInput)->groupEnd();
        } else {
            $tilokBuilder->where('uid', $tilokInput);
        }
        $tilok = $tilokBuilder->get()->getRowArray();

        if (!$tilok) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => 'Titik lokasi tidak ditemukan.'
            ]);
        }

        $tilokId = (int) $tilok['id'];

        // Ambil data event seleksi baru
        $newSeleksi = $this->apps->db->table('txn_cat_seleksi')
            ->where('id', $newSeleksiId)
            ->get()->getRowArray();

        if (!$newSeleksi) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => 'Event seleksi tujuan tidak ditemukan.'
            ]);
        }

        $newJenisTesId = !empty($newSeleksi['jenis_tes_id']) ? (int) $newSeleksi['jenis_tes_id'] : null;

        // Update data rekap hasil sesi pada tilok dan instansi terkait
        $updateData = [
            'seleksi_id' => $newSeleksiId,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        if ($newJenisTesId !== null) {
            $updateData['jenis_tes_id'] = $newJenisTesId;
        }

        $affected = $this->apps->db->table('txn_cat_hasil')
            ->where('tilok_id', $tilokId)
            ->where('instansi_id', $instansiId)
            ->where('seleksi_id', $oldSeleksiId)
            ->update($updateData);

        $this->apps->storeData(
            [
                'layanan_id' => 28,
                'tanggal'    => date('Y-m-d'),
                'created_by' => $sess['username'] ?? 'system'
            ],
            'activity_daily_logs'
        );

        return $this->response->setJSON([
            'status'   => 'success',
            'message'  => 'Event seleksi berhasil diperbarui.',
            'affected' => $affected
        ]);
    }
}
